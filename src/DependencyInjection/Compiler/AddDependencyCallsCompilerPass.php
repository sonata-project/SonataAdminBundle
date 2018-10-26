<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\DependencyInjection\Compiler;

use Doctrine\Common\Inflector\Inflector;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add all dependencies to the Admin class, this avoid to write too many lines
 * in the configuration files.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AddDependencyCallsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
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

        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition = $container->getDefinition($id);
                $parentDefinition = null;

                // Temporary fix until we can support service locators
                $definition->setPublic(true);

                // NEXT_MAJOR: Remove check for DefinitionDecorator instance when dropping Symfony <3.3 support
                if ($definition instanceof ChildDefinition ||
                    (!class_exists(ChildDefinition::class) && $definition instanceof DefinitionDecorator)) {
                    $parentDefinition = $container->getDefinition($definition->getParent());
                }

                $this->replaceDefaultArguments([
                    0 => $id,
                    2 => CRUDController::class,
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

                $resolvedGroupName = isset($attributes['group']) ? $parameterBag->resolveValue($attributes['group']) : 'default';
                $labelCatalogue = isset($attributes['label_catalogue']) ? $attributes['label_catalogue'] : 'SonataAdminBundle';
                $icon = isset($attributes['icon']) ? $attributes['icon'] : '<i class="fa fa-folder"></i>';
                $onTop = isset($attributes['on_top']) ? $attributes['on_top'] : false;
                $keepOpen = isset($attributes['keep_open']) ? $attributes['keep_open'] : false;

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
                    $groups[$resolvedGroupName]['label_catalogue'] = 'SonataAdminBundle';
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

            $elementSort = function (&$element) {
                usort(
                    $element['items'],
                    function ($a, $b) {
                        $a = !empty($a['label']) ? $a['label'] : $a['admin'];
                        $b = !empty($b['label']) ? $b['label'] : $b['admin'];

                        if ($a === $b) {
                            return 0;
                        }

                        return $a < $b ? -1 : 1;
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
     * This method read the attribute keys and configure admin class to use the related dependency.
     */
    public function applyConfigurationFromAttribute(Definition $definition, array $attributes)
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
            $method = 'set'.Inflector::classify($key);
            if (!isset($attributes[$key]) || $definition->hasMethodCall($method)) {
                continue;
            }

            $definition->addMethodCall($method, [new Reference($attributes[$key])]);
        }
    }

    /**
     * Apply the default values required by the AdminInterface to the Admin service definition.
     *
     * @param string $serviceId
     *
     * @return Definition
     */
    public function applyDefaults(ContainerBuilder $container, $serviceId, array $attributes = [])
    {
        $definition = $container->getDefinition($serviceId);
        $settings = $container->getParameter('sonata.admin.configuration.admin_services');

        $definition->setShared(false);

        $manager_type = $attributes['manager_type'];

        $overwriteAdminConfiguration = isset($settings[$serviceId]) ? $settings[$serviceId] : [];

        $defaultAddServices = [
            'model_manager' => sprintf('sonata.admin.manager.%s', $manager_type),
            'form_contractor' => sprintf('sonata.admin.builder.%s_form', $manager_type),
            'show_builder' => sprintf('sonata.admin.builder.%s_show', $manager_type),
            'list_builder' => sprintf('sonata.admin.builder.%s_list', $manager_type),
            'datagrid_builder' => sprintf('sonata.admin.builder.%s_datagrid', $manager_type),
            'translator' => 'translator',
            'configuration_pool' => 'sonata.admin.pool',
            'route_generator' => 'sonata.admin.route.default_generator',
            'validator' => 'validator',
            'security_handler' => 'sonata.admin.security.handler',
            'menu_factory' => 'knp_menu.factory',
            'route_builder' => 'sonata.admin.route.path_info'.
                (('doctrine_phpcr' == $manager_type) ? '_slashes' : ''),
            'label_translator_strategy' => 'sonata.admin.label.strategy.native',
        ];

        $definition->addMethodCall('setManagerType', [$manager_type]);

        foreach ($defaultAddServices as $attr => $addServiceId) {
            $method = 'set'.Inflector::classify($attr);

            if (isset($overwriteAdminConfiguration[$attr]) || !$definition->hasMethodCall($method)) {
                $args = [new Reference(isset($overwriteAdminConfiguration[$attr]) ? $overwriteAdminConfiguration[$attr] : $addServiceId)];
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
            isset($overwriteAdminConfiguration['templates']) ? $overwriteAdminConfiguration['templates'] : ['view' => []]
        );

        if ($container->hasParameter('sonata.admin.configuration.security.information') && !$definition->hasMethodCall('setSecurityInformation')) {
            $definition->addMethodCall('setSecurityInformation', ['%sonata.admin.configuration.security.information%']);
        }

        $definition->addMethodCall('initialize');

        return $definition;
    }

    /**
     * @param string $serviceId
     */
    public function fixTemplates(
        $serviceId,
        ContainerBuilder $container,
        Definition $definition,
        array $overwrittenTemplates = []
    ) {
        $definedTemplates = $container->getParameter('sonata.admin.configuration.templates');

        $methods = [];
        $pos = 0;
        foreach ($definition->getMethodCalls() as $method) {
            if ('setTemplates' == $method[0]) {
                $definedTemplates = array_merge($definedTemplates, $method[1][0]);

                continue;
            }

            if ('setTemplate' == $method[0]) {
                $definedTemplates[$method[1][0]] = $method[1][1];

                continue;
            }

            // set template for simple pager if it is not already overwritten
            if ('setPagerType' === $method[0]
                && Pager::TYPE_SIMPLE === $method[1][0]
                && (
                    !isset($definedTemplates['pager_results'])
                    || '@SonataAdmin/Pager/results.html.twig' === $definedTemplates['pager_results']
                )
            ) {
                $definedTemplates['pager_results'] = '@SonataAdmin/Pager/simple_pager_results.html.twig';
            }

            $methods[$pos] = $method;
            ++$pos;
        }

        $definition->setMethodCalls($methods);

        $definedTemplates = $overwrittenTemplates['view'] + $definedTemplates;

        $templateRegistryId = $serviceId.'.template_registry';
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
        Definition $parentDefinition = null
    ) {
        $arguments = $definition->getArguments();
        $parentArguments = $parentDefinition ? $parentDefinition->getArguments() : [];

        foreach ($defaultArguments as $index => $value) {
            $declaredInParent = $parentDefinition && array_key_exists($index, $parentArguments);
            $argumentValue = $declaredInParent ? $parentArguments[$index] : $arguments[$index];

            if (null === $argumentValue || 0 === \strlen($argumentValue)) {
                $arguments[$declaredInParent ? sprintf('index_%s', $index) : $index] = $value;
            }
        }

        $definition->setArguments($arguments);
    }
}
