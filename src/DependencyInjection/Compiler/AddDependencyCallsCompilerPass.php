<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\DependencyInjection\Compiler;

use Doctrine\Inflector\InflectorFactory;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add all dependencies to the Admin class, this avoid to write too many lines
 * in the configuration files.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class AddDependencyCallsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // check if translator service exist
        if (!$container->has('translator')) {
            throw new \RuntimeException('The "translator" service is not yet enabled.
                It\'s required by SonataAdmin to display all labels properly.
                To learn how to enable the translator service please visit:
                http://symfony.com/doc/current/translation.html#configuration
             ');
        }

        $parameterBag = $container->getParameterBag();
        $groupDefaults = $admins = $classes = [];

        $pool = $container->getDefinition('sonata.admin.pool');

        $defaultValues = [
            'group' => $container->getParameter('sonata.admin.configuration.default_group'),
            'label_catalogue' => $container->getParameter('sonata.admin.configuration.default_label_catalogue'),
            'icon' => $container->getParameter('sonata.admin.configuration.default_icon'),
        ];

        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition = $container->getDefinition($id);
                $parentDefinition = null;

                // Temporary fix until we can support service locators
                $definition->setPublic(true);

                if ($definition instanceof ChildDefinition) {
                    $parentDefinition = $container->getDefinition($definition->getParent());
                }

                $this->replaceDefaultArguments([
                    0 => $id,
                    2 => 'sonata.admin.controller.crud',
                ], $definition, $parentDefinition);
                $this->applyConfigurationFromAttribute($definition, $attributes);
                $this->applyDefaults($container, $id, $attributes);

                $arguments = $parentDefinition ?
                    array_merge($parentDefinition->getArguments(), $definition->getArguments()) :
                    $definition->getArguments();

                $admins[] = $id;

                if (!isset($classes[$arguments[1]])) {
                    $classes[$arguments[1]] = [];
                }

                $classes[$arguments[1]][] = $id;

                $showInDashboard = (bool) (isset($attributes['show_in_dashboard']) ? $parameterBag->resolveValue($attributes['show_in_dashboard']) : true);
                if (!$showInDashboard) {
                    continue;
                }

                $resolvedGroupName = isset($attributes['group']) ?
                    $parameterBag->resolveValue($attributes['group']) :
                    $defaultValues['group'];
                $labelCatalogue = $attributes['label_catalogue'] ?? $defaultValues['label_catalogue'];
                $icon = $attributes['icon'] ?? $defaultValues['icon'];
                $onTop = $attributes['on_top'] ?? false;
                $keepOpen = $attributes['keep_open'] ?? false;

                if (!isset($groupDefaults[$resolvedGroupName])) {
                    $groupDefaults[$resolvedGroupName] = [
                        'label' => $resolvedGroupName,
                        'label_catalogue' => $labelCatalogue,
                        'icon' => $icon,
                        'roles' => [],
                        'on_top' => false,
                        'keep_open' => false,
                    ];
                }

                $groupDefaults[$resolvedGroupName]['items'][] = [
                    'admin' => $id,
                    'label' => !empty($attributes['label']) ? $attributes['label'] : '',
                    'route' => '',
                    'route_params' => [],
                    'route_absolute' => false,
                ];

                if (isset($groupDefaults[$resolvedGroupName]['on_top']) && $groupDefaults[$resolvedGroupName]['on_top']
                    || $onTop && (\count($groupDefaults[$resolvedGroupName]['items']) > 1)) {
                    throw new \RuntimeException('You can\'t use "on_top" option with multiple same name groups.');
                }
                $groupDefaults[$resolvedGroupName]['on_top'] = $onTop;

                $groupDefaults[$resolvedGroupName]['keep_open'] = $keepOpen;
            }
        }

        $dashboardGroupsSettings = $container->getParameter('sonata.admin.configuration.dashboard_groups');
        if (!empty($dashboardGroupsSettings)) {
            $groups = $dashboardGroupsSettings;

            foreach ($dashboardGroupsSettings as $groupName => $group) {
                $resolvedGroupName = $parameterBag->resolveValue($groupName);
                if (!isset($groupDefaults[$resolvedGroupName])) {
                    $groupDefaults[$resolvedGroupName] = [
                        'items' => [],
                        'label' => $resolvedGroupName,
                        'label_catalogue' => $defaultValues['label_catalogue'],
                        'icon' => $defaultValues['icon'],
                        'roles' => [],
                        'on_top' => false,
                        'keep_open' => false,
                    ];
                }

                if (empty($group['items'])) {
                    $groups[$resolvedGroupName]['items'] = $groupDefaults[$resolvedGroupName]['items'];
                }

                if (empty($group['label'])) {
                    $groups[$resolvedGroupName]['label'] = $groupDefaults[$resolvedGroupName]['label'];
                }

                if (empty($group['label_catalogue'])) {
                    $groups[$resolvedGroupName]['label_catalogue'] = $groupDefaults[$resolvedGroupName]['label_catalogue'];
                }

                if (empty($group['icon'])) {
                    $groups[$resolvedGroupName]['icon'] = $groupDefaults[$resolvedGroupName]['icon'];
                }

                if (!empty($group['item_adds'])) {
                    $groups[$resolvedGroupName]['items'] = array_merge($groups[$resolvedGroupName]['items'], $group['item_adds']);
                }

                if (empty($group['roles'])) {
                    $groups[$resolvedGroupName]['roles'] = $groupDefaults[$resolvedGroupName]['roles'];
                }

                if (isset($groups[$resolvedGroupName]['on_top']) && !empty($group['on_top']) && $group['on_top']
                    && (\count($groups[$resolvedGroupName]['items']) > 1)) {
                    throw new \RuntimeException('You can\'t use "on_top" option with multiple same name groups.');
                }
                if (empty($group['on_top'])) {
                    $groups[$resolvedGroupName]['on_top'] = $groupDefaults[$resolvedGroupName]['on_top'];
                }

                if (empty($group['keep_open'])) {
                    $groups[$resolvedGroupName]['keep_open'] = $groupDefaults[$resolvedGroupName]['keep_open'];
                }
            }
        } elseif ($container->getParameter('sonata.admin.configuration.sort_admins')) {
            $groups = $groupDefaults;

            $elementSort = static function (array &$element): void {
                usort(
                    $element['items'],
                    static function (array $a, array $b): int {
                        $a = !empty($a['label']) ? $a['label'] : $a['admin'];
                        $b = !empty($b['label']) ? $b['label'] : $b['admin'];

                        return $a <=> $b;
                    }
                );
            };

            /*
             * 1) sort the groups by their index
             * 2) sort the elements within each group by label/admin
             */
            ksort($groups);
            array_walk($groups, $elementSort);
        } else {
            $groups = $groupDefaults;
        }

        $pool->addMethodCall('setAdminServiceIds', [$admins]);
        $pool->addMethodCall('setAdminGroups', [$groups]);
        $pool->addMethodCall('setAdminClasses', [$classes]);

        $routeLoader = $container->getDefinition('sonata.admin.route_loader');
        $routeLoader->replaceArgument(1, $admins);
    }

    /**
     * NEXT_MAJOR: Change visibility to private.
     *
     * This method read the attribute keys and configure admin class to use the related dependency.
     */
    public function applyConfigurationFromAttribute(Definition $definition, array $attributes): void
    {
        $keys = [
            'model_manager',
            'form_contractor',
            'show_builder',
            'list_builder',
            'datagrid_builder',
            'translator',
            'configuration_pool',
            'router',
            'validator',
            'security_handler',
            'menu_factory',
            'route_builder',
            'label_translator_strategy',
        ];

        foreach ($keys as $key) {
            $method = $this->generateSetterMethodName($key);

            if (!isset($attributes[$key]) || $definition->hasMethodCall($method)) {
                continue;
            }

            $definition->addMethodCall($method, [new Reference($attributes[$key])]);
        }
    }

    /**
     * NEXT_MAJOR: Change visibility to private.
     *
     * Apply the default values required by the AdminInterface to the Admin service definition.
     */
    public function applyDefaults(ContainerBuilder $container, string $serviceId, array $attributes = []): Definition
    {
        $definition = $container->getDefinition($serviceId);
        $settings = $container->getParameter('sonata.admin.configuration.admin_services');

        $definition->setShared(false);

        $managerType = $attributes['manager_type'];

        $overwriteAdminConfiguration = $settings[$serviceId] ?? [];

        $defaultAddServices = [
            'model_manager' => sprintf('sonata.admin.manager.%s', $managerType),
            'data_source' => sprintf('sonata.admin.data_source.%s', $managerType),
            'form_contractor' => sprintf('sonata.admin.builder.%s_form', $managerType),
            'show_builder' => sprintf('sonata.admin.builder.%s_show', $managerType),
            'list_builder' => sprintf('sonata.admin.builder.%s_list', $managerType),
            'datagrid_builder' => sprintf('sonata.admin.builder.%s_datagrid', $managerType),
            'translator' => 'translator',
            'configuration_pool' => 'sonata.admin.pool',
            'route_generator' => 'sonata.admin.route.default_generator',
            'validator' => 'validator',
            'security_handler' => 'sonata.admin.security.handler',
            'menu_factory' => 'knp_menu.factory',
            'route_builder' => 'sonata.admin.route.path_info'.
                (('doctrine_phpcr' === $managerType) ? '_slashes' : ''),
            'label_translator_strategy' => 'sonata.admin.label.strategy.native',
        ];

        $definition->addMethodCall('setManagerType', [$managerType]);

        foreach ($defaultAddServices as $attr => $addServiceId) {
            // NEXT_MAJOR: Remove this check
            if ('data_source' === $attr && !$container->has($addServiceId)) {
                continue;
            }

            $method = $this->generateSetterMethodName($attr);

            if (isset($overwriteAdminConfiguration[$attr]) || !$definition->hasMethodCall($method)) {
                $args = [new Reference($overwriteAdminConfiguration[$attr] ?? $addServiceId)];
                if ('translator' === $attr) {
                    $args[] = false;
                }

                $definition->addMethodCall($method, $args);
            }
        }

        if (isset($overwriteAdminConfiguration['pager_type'])) {
            $pagerType = $overwriteAdminConfiguration['pager_type'];
        } elseif (isset($attributes['pager_type'])) {
            $pagerType = $attributes['pager_type'];
        } else {
            $pagerType = Pager::TYPE_DEFAULT;
        }

        $definition->addMethodCall('setPagerType', [$pagerType]);

        if (isset($overwriteAdminConfiguration['label'])) {
            $label = $overwriteAdminConfiguration['label'];
        } elseif (isset($attributes['label'])) {
            $label = $attributes['label'];
        } else {
            $label = '-';
        }

        $definition->addMethodCall('setLabel', [$label]);

        $persistFilters = $container->getParameter('sonata.admin.configuration.filters.persist');
        // override default configuration with admin config if set
        if (isset($attributes['persist_filters'])) {
            $persistFilters = $attributes['persist_filters'];
        }
        $filtersPersister = $container->getParameter('sonata.admin.configuration.filters.persister');
        // override default configuration with admin config if set
        if (isset($attributes['filter_persister'])) {
            $filtersPersister = $attributes['filter_persister'];
        }
        // configure filters persistence, if configured to
        if ($persistFilters) {
            $definition->addMethodCall('setFilterPersister', [new Reference($filtersPersister)]);
        }

        if (isset($overwriteAdminConfiguration['show_mosaic_button'])) {
            $showMosaicButton = $overwriteAdminConfiguration['show_mosaic_button'];
        } elseif (isset($attributes['show_mosaic_button'])) {
            $showMosaicButton = $attributes['show_mosaic_button'];
        } else {
            $showMosaicButton = $container->getParameter('sonata.admin.configuration.show.mosaic.button');
        }

        $definition->addMethodCall('showMosaicButton', [$showMosaicButton]);

        $this->fixTemplates(
            $serviceId,
            $container,
            $definition,
            $overwriteAdminConfiguration['templates'] ?? ['view' => []]
        );

        if ($container->hasParameter('sonata.admin.configuration.security.information') && !$definition->hasMethodCall('setSecurityInformation')) {
            $definition->addMethodCall('setSecurityInformation', ['%sonata.admin.configuration.security.information%']);
        }

        $definition->addMethodCall('initialize');

        return $definition;
    }

    /**
     * NEXT_MAJOR: Change visibility to private.
     */
    public function fixTemplates(
        string $serviceId,
        ContainerBuilder $container,
        Definition $definition,
        array $overwrittenTemplates = []
    ): void {
        $definedTemplates = $container->getParameter('sonata.admin.configuration.templates');

        $methods = [];
        $pos = 0;
        foreach ($definition->getMethodCalls() as [$method, $args]) {
            if ('setTemplates' === $method) {
                $definedTemplates = array_merge($definedTemplates, $args[0]);

                continue;
            }

            if ('setTemplate' === $method) {
                $definedTemplates[$args[0]] = $args[1];

                continue;
            }

            // set template for simple pager if it is not already overwritten
            if ('setPagerType' === $method
                && Pager::TYPE_SIMPLE === $args[0]
                && (
                    !isset($definedTemplates['pager_results'])
                    || '@SonataAdmin/Pager/results.html.twig' === $definedTemplates['pager_results']
                )
            ) {
                $definedTemplates['pager_results'] = '@SonataAdmin/Pager/simple_pager_results.html.twig';
            }

            $methods[$pos] = [$method, $args];
            ++$pos;
        }

        $definition->setMethodCalls($methods);

        $definedTemplates = $overwrittenTemplates['view'] + $definedTemplates;

        $templateRegistryId = sprintf('%s.template_registry', $serviceId);
        $templateRegistryDefinition = $container
            ->register($templateRegistryId, TemplateRegistry::class)
            ->addTag('sonata.admin.template_registry')
            ->setPublic(true); // Temporary fix until we can support service locators

        if ($container->getParameter('sonata.admin.configuration.templates') !== $definedTemplates) {
            $templateRegistryDefinition->addArgument($definedTemplates);
        } else {
            $templateRegistryDefinition->addArgument('%sonata.admin.configuration.templates%');
        }

        $definition->addMethodCall('setTemplateRegistry', [new Reference($templateRegistryId)]);
    }

    /**
     * Replace the empty arguments required by the Admin service definition.
     */
    private function replaceDefaultArguments(
        array $defaultArguments,
        Definition $definition,
        ?Definition $parentDefinition = null
    ): void {
        $arguments = $definition->getArguments();
        $parentArguments = $parentDefinition ? $parentDefinition->getArguments() : [];

        foreach ($defaultArguments as $index => $value) {
            $declaredInParent = $parentDefinition && \array_key_exists($index, $parentArguments);
            $argumentValue = $declaredInParent ? $parentArguments[$index] : $arguments[$index];

            if (null === $argumentValue || 0 === \strlen($argumentValue)) {
                $arguments[$declaredInParent ? sprintf('index_%s', $index) : $index] = $value;
            }
        }

        $definition->setArguments($arguments);
    }

    private function generateSetterMethodName(string $key): string
    {
        return 'set'.InflectorFactory::create()->build()->classify($key);
    }
}
