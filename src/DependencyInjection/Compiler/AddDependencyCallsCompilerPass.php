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
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\DependencyInjection\Admin\TaggedAdminInterface;
use Sonata\AdminBundle\Templating\MutableTemplateRegistry;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add all dependencies to the Admin class, this avoid to write too many lines
 * in the configuration files.
 *
 * @internal
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class AddDependencyCallsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('sonata.admin.pool')) {
            return;
        }

        // check if translator service exist
        if (!$container->has('translator')) {
            throw new \RuntimeException('The "translator" service is not yet enabled.
                It\'s required by SonataAdmin to display all labels properly.
                To learn how to enable the translator service please visit:
                http://symfony.com/doc/current/translation.html#configuration
             ');
        }

        $parameterBag = $container->getParameterBag();
        $groupDefaults = $admins = $adminServices = $classes = [];

        $pool = $container->getDefinition('sonata.admin.pool');
        $defaultController = $container->getParameter('sonata.admin.configuration.default_controller');
        \assert(\is_string($defaultController));

        $defaultGroup = $container->getParameter('sonata.admin.configuration.default_group');
        \assert(\is_string($defaultGroup));
        $defaultLabelCatalogue = $container->getParameter('sonata.admin.configuration.default_label_catalogue');
        \assert(\is_string($defaultLabelCatalogue));
        $defaultIcon = $container->getParameter('sonata.admin.configuration.default_icon');
        \assert(\is_string($defaultIcon));

        $defaultValues = [
            'group' => $defaultGroup,
            'label_catalogue' => $defaultLabelCatalogue,
            'icon' => $defaultIcon,
        ];

        foreach ($container->findTaggedServiceIds(TaggedAdminInterface::ADMIN_TAG) as $id => $tags) {
            $adminServices[$id] = new Reference($id);

            foreach ($tags as $attributes) {
                $definition = $container->getDefinition($id);
                $parentDefinition = null;

                if ($definition instanceof ChildDefinition) {
                    $parentDefinition = $container->getDefinition($definition->getParent());
                }

                $this->replaceDefaultArguments([
                    0 => $id,
                    2 => $defaultController,
                ], $definition, $parentDefinition);
                $this->applyConfigurationFromAttribute($definition, $attributes);
                $this->applyDefaults($container, $id, $attributes);

                $arguments = null !== $parentDefinition ?
                    array_merge($parentDefinition->getArguments(), $definition->getArguments()) :
                    $definition->getArguments();

                $admins[] = $id;

                if (!isset($classes[$arguments[1]])) {
                    $classes[$arguments[1]] = [];
                }

                $default = (bool) (isset($attributes['default']) ? $parameterBag->resolveValue($attributes['default']) : false);
                if ($default) {
                    if (isset($classes[$arguments[1]][Pool::DEFAULT_ADMIN_KEY])) {
                        throw new \RuntimeException(sprintf(
                            'The class %s has two admins %s and %s with the "default" attribute set to true. Only one is allowed.',
                            $arguments[1],
                            $classes[$arguments[1]][Pool::DEFAULT_ADMIN_KEY],
                            $id
                        ));
                    }

                    $classes[$arguments[1]][Pool::DEFAULT_ADMIN_KEY] = $id;
                } else {
                    $classes[$arguments[1]][] = $id;
                }

                $showInDashboard = (bool) (isset($attributes['show_in_dashboard']) ? $parameterBag->resolveValue($attributes['show_in_dashboard']) : true);
                if (!$showInDashboard) {
                    continue;
                }

                $resolvedGroupName = isset($attributes['group']) ?
                    $parameterBag->resolveValue($attributes['group']) :
                    $defaultValues['group'];
                \assert(\is_string($resolvedGroupName));

                $labelCatalogue = $attributes['label_catalogue'] ?? $defaultValues['label_catalogue'];
                $icon = $attributes['icon'] ?? $defaultValues['icon'];
                $onTop = $attributes['on_top'] ?? false;
                $keepOpen = $attributes['keep_open'] ?? false;

                if (!isset($groupDefaults[$resolvedGroupName])) {
                    $groupDefaults[$resolvedGroupName] = [
                        'label' => $resolvedGroupName,
                        'label_catalogue' => $labelCatalogue,
                        'icon' => $icon,
                        'items' => [],
                        'roles' => [],
                        'on_top' => false,
                        'keep_open' => false,
                    ];
                }

                $groupDefaults[$resolvedGroupName]['items'][] = [
                    'admin' => $id,
                    'label' => $attributes['label'] ?? '',
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
        \assert(\is_array($dashboardGroupsSettings));
        $sortAdmins = $container->getParameter('sonata.admin.configuration.sort_admins');
        \assert(\is_bool($sortAdmins));

        if ([] !== $dashboardGroupsSettings) {
            $groups = $dashboardGroupsSettings;

            foreach ($dashboardGroupsSettings as $groupName => $group) {
                $resolvedGroupName = $parameterBag->resolveValue($groupName);
                \assert(\is_string($resolvedGroupName));

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

                if (!isset($group['items']) || [] === $group['items']) {
                    $groups[$resolvedGroupName]['items'] = $groupDefaults[$resolvedGroupName]['items'];
                }

                if (!isset($group['label']) || '' === $group['label']) {
                    $groups[$resolvedGroupName]['label'] = $groupDefaults[$resolvedGroupName]['label'];
                }

                if (!isset($group['label_catalogue']) || '' === $group['label_catalogue']) {
                    $groups[$resolvedGroupName]['label_catalogue'] = $groupDefaults[$resolvedGroupName]['label_catalogue'];
                }

                if (!isset($group['icon']) || '' === $group['icon']) {
                    $groups[$resolvedGroupName]['icon'] = $groupDefaults[$resolvedGroupName]['icon'];
                }

                if (isset($group['item_adds']) && [] !== $group['item_adds']) {
                    $groups[$resolvedGroupName]['items'] = array_merge($groups[$resolvedGroupName]['items'], $group['item_adds']);
                }

                if (!isset($group['roles']) || [] === $group['roles']) {
                    $groups[$resolvedGroupName]['roles'] = $groupDefaults[$resolvedGroupName]['roles'];
                }

                if (
                    isset($groups[$resolvedGroupName]['on_top'])
                    && ($group['on_top'] ?? false)
                    && \count($groups[$resolvedGroupName]['items']) > 1
                ) {
                    throw new \RuntimeException('You can\'t use "on_top" option with multiple same name groups.');
                }
                if (!isset($group['on_top'])) {
                    $groups[$resolvedGroupName]['on_top'] = $groupDefaults[$resolvedGroupName]['on_top'];
                }

                if (!isset($group['keep_open'])) {
                    $groups[$resolvedGroupName]['keep_open'] = $groupDefaults[$resolvedGroupName]['keep_open'];
                }
            }
        } elseif ($sortAdmins) {
            $groups = $groupDefaults;

            $elementSort = static function (array &$element): void {
                usort(
                    $element['items'],
                    static function (array $a, array $b): int {
                        $labelA = isset($a['label']) && '' !== $a['label'] ? $a['label'] : $a['admin'];
                        $labelB = isset($b['label']) && '' !== $b['label'] ? $b['label'] : $b['admin'];

                        return $labelA <=> $labelB;
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

        $pool->replaceArgument(0, ServiceLocatorTagPass::register($container, $adminServices));
        $pool->replaceArgument(1, $admins);
        $pool->replaceArgument(2, $groups);
        $pool->replaceArgument(3, $classes);
    }

    /**
     * This method read the attribute keys and configure admin class to use the related dependency.
     *
     * @param array<string, mixed> $attributes
     */
    private function applyConfigurationFromAttribute(Definition $definition, array $attributes): void
    {
        $keys = [
            'model_manager',
            'data_source',
            'field_description_factory',
            'form_contractor',
            'show_builder',
            'list_builder',
            'datagrid_builder',
            'translator',
            'configuration_pool',
            'route_generator',
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
     * Apply the default values required by the AdminInterface to the Admin service definition.
     *
     * @param array<string, mixed> $attributes
     */
    private function applyDefaults(ContainerBuilder $container, string $serviceId, array $attributes = []): Definition
    {
        $definition = $container->getDefinition($serviceId);

        $definition->setShared(false);

        $managerType = $attributes['manager_type'];

        $overwriteAdminConfiguration = $container->getParameter('sonata.admin.configuration.default_admin_services');
        \assert(\is_array($overwriteAdminConfiguration));

        $defaultAddServices = [
            'model_manager' => sprintf('sonata.admin.manager.%s', $managerType),
            'data_source' => sprintf('sonata.admin.data_source.%s', $managerType),
            'field_description_factory' => sprintf('sonata.admin.field_description_factory.%s', $managerType),
            'form_contractor' => sprintf('sonata.admin.builder.%s_form', $managerType),
            'show_builder' => sprintf('sonata.admin.builder.%s_show', $managerType),
            'list_builder' => sprintf('sonata.admin.builder.%s_list', $managerType),
            'datagrid_builder' => sprintf('sonata.admin.builder.%s_datagrid', $managerType),
            'translator' => 'translator',
            'configuration_pool' => 'sonata.admin.pool',
            'route_generator' => 'sonata.admin.route.default_generator',
            'security_handler' => 'sonata.admin.security.handler',
            'menu_factory' => 'knp_menu.factory',
            'route_builder' => 'sonata.admin.route.path_info',
            'label_translator_strategy' => 'sonata.admin.label.strategy.native',
        ];

        $definition->addMethodCall('setManagerType', [$managerType]);

        foreach ($defaultAddServices as $attr => $addServiceId) {
            $method = $this->generateSetterMethodName($attr);

            if (!$definition->hasMethodCall($method)) {
                $args = [new Reference($overwriteAdminConfiguration[$attr] ?? $addServiceId)];
                if ('translator' === $attr) {
                    $args[] = false;
                }

                $definition->addMethodCall($method, $args);
            }
        }

        $pagerType = $overwriteAdminConfiguration['pager_type'] ?? $attributes['pager_type'] ?? Pager::TYPE_DEFAULT;
        $definition->addMethodCall('setPagerType', [$pagerType]);

        $label = $overwriteAdminConfiguration['label'] ?? $attributes['label'] ?? null;
        $definition->addMethodCall('setLabel', [$label]);

        $persistFilters = $attributes['persist_filters']
            ?? $container->getParameter('sonata.admin.configuration.filters.persist');
        \assert(\is_bool($persistFilters));
        $filtersPersister = $attributes['filter_persister']
            ?? $container->getParameter('sonata.admin.configuration.filters.persister');
        \assert(\is_string($filtersPersister));

        // configure filters persistence, if configured to
        if ($persistFilters) {
            $definition->addMethodCall('setFilterPersister', [new Reference($filtersPersister)]);
        }

        $showMosaicButton = $overwriteAdminConfiguration['show_mosaic_button']
            ?? $attributes['show_mosaic_button']
            ?? $container->getParameter('sonata.admin.configuration.show.mosaic.button');
        \assert(\is_bool($showMosaicButton));

        $listModes = TaggedAdminInterface::DEFAULT_LIST_MODES;
        if (!$showMosaicButton) {
            unset($listModes['mosaic']);
        }
        $definition->addMethodCall('setListModes', [$listModes]);

        $this->fixTemplates($serviceId, $container, $definition);

        if ($container->hasParameter('sonata.admin.configuration.security.information') && !$definition->hasMethodCall('setSecurityInformation')) {
            $definition->addMethodCall('setSecurityInformation', ['%sonata.admin.configuration.security.information%']);
        }

        $defaultTemplates = $container->getParameter('sonata.admin.configuration.templates');
        \assert(\is_array($defaultTemplates));

        if (!$definition->hasMethodCall('setFormTheme')) {
            $formTheme = $defaultTemplates['form_theme'] ?? [];
            $definition->addMethodCall('setFormTheme', [$formTheme]);
        }
        if (!$definition->hasMethodCall('setFilterTheme')) {
            $filterTheme = $defaultTemplates['filter_theme'] ?? [];
            $definition->addMethodCall('setFilterTheme', [$filterTheme]);
        }

        return $definition;
    }

    private function fixTemplates(
        string $serviceId,
        ContainerBuilder $container,
        Definition $definition
    ): void {
        $definedTemplates = $container->getParameter('sonata.admin.configuration.templates');
        \assert(\is_array($definedTemplates));

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

        $templateRegistryId = sprintf('%s.template_registry', $serviceId);
        $templateRegistryDefinition = $container
            ->register($templateRegistryId, MutableTemplateRegistry::class)
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
     *
     * @param string[] $defaultArguments
     */
    private function replaceDefaultArguments(
        array $defaultArguments,
        Definition $definition,
        ?Definition $parentDefinition = null
    ): void {
        $arguments = $definition->getArguments();
        $parentArguments = null !== $parentDefinition ? $parentDefinition->getArguments() : [];

        foreach ($defaultArguments as $index => $value) {
            $declaredInParent = null !== $parentDefinition && \array_key_exists($index, $parentArguments);
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
