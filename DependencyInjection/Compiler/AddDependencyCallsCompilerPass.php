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
use Sonata\AdminBundle\Datagrid\Pager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add all dependencies to the Admin class, this avoid to write too many lines
 * in the configuration files.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AddDependencyCallsCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // check if translator service exist
        if (!$container->hasAlias('translator')) {
            throw new \RuntimeException('The "translator" service is not yet enabled.
                It\'s required by SonataAdmin to display all labels properly.

                To learn how to enable the translator service please visit:
                http://symfony.com/doc/current/book/translation.html#book-translation-configuration
             ');
        }

        $parameterBag = $container->getParameterBag();
        $groupDefaults = $admins = $classes = array();

        $pool = $container->getDefinition('sonata.admin.pool');

        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition = $container->getDefinition($id);

                $arguments = $definition->getArguments();

                if (strlen($arguments[0]) == 0) {
                    $definition->replaceArgument(0, $id);
                }

                if (strlen($arguments[2]) == 0) {
                    $definition->replaceArgument(2, 'SonataAdminBundle:CRUD');
                }

                $this->applyConfigurationFromAttribute($definition, $attributes);
                $this->applyDefaults($container, $id, $attributes);

                $arguments = $definition->getArguments();

                $admins[] = $id;

                if (!isset($classes[$arguments[1]])) {
                    $classes[$arguments[1]] = array();
                }

                $classes[$arguments[1]][] = $id;

                $showInDashboard = (bool) (isset($attributes['show_in_dashboard']) ?  $parameterBag->resolveValue($attributes['show_in_dashboard']) : true);
                if (!$showInDashboard) {
                    continue;
                }

                $resolvedGroupName = isset($attributes['group']) ? $parameterBag->resolveValue($attributes['group']) : 'default';
                $labelCatalogue = isset($attributes['label_catalogue']) ? $attributes['label_catalogue'] : 'SonataAdminBundle';
                $icon = isset($attributes['icon']) ? $attributes['icon'] : '<i class="fa fa-folder"></i>';
                $onTop = isset($attributes['on_top']) ? $attributes['on_top'] : false;

                if (!isset($groupDefaults[$resolvedGroupName])) {
                    $groupDefaults[$resolvedGroupName] = array(
                        'label' => $resolvedGroupName,
                        'label_catalogue' => $labelCatalogue,
                        'icon' => $icon,
                        'roles' => array(),
                        'on_top' => false,
                    );
                }

                $groupDefaults[$resolvedGroupName]['items'][] = array(
                    'admin' => $id,
                    'label' => !empty($attributes['label']) ? $attributes['label'] : '',
                    'route' => '',
                    'route_params' => array(),
                    'route_absolute' => true,
                );

                if (isset($groupDefaults[$resolvedGroupName]['on_top']) && $groupDefaults[$resolvedGroupName]['on_top']
                    || $onTop && (count($groupDefaults[$resolvedGroupName]['items']) > 1)) {
                    throw new \RuntimeException('You can\'t use "on_top" option with multiple same name groups.');
                } else {
                    $groupDefaults[$resolvedGroupName]['on_top'] = $onTop;
                }
            }
        }

        $dashboardGroupsSettings = $container->getParameter('sonata.admin.configuration.dashboard_groups');
        if (!empty($dashboardGroupsSettings)) {
            $groups = $dashboardGroupsSettings;

            foreach ($dashboardGroupsSettings as $groupName => $group) {
                $resolvedGroupName = $parameterBag->resolveValue($groupName);
                if (!isset($groupDefaults[$resolvedGroupName])) {
                    $groupDefaults[$resolvedGroupName] = array(
                        'items' => array(),
                        'label' => $resolvedGroupName,
                        'roles' => array(),
                        'on_top' => false,
                    );
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
                    && (count($groups[$resolvedGroupName]['items']) > 1)) {
                    throw new \RuntimeException('You can\'t use "on_top" option with multiple same name groups.');
                } else {
                    if (empty($group['on_top'])) {
                        $groups[$resolvedGroupName]['on_top'] = $groupDefaults[$resolvedGroupName]['on_top'];
                    }
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

        $pool->addMethodCall('setAdminServiceIds', array($admins));
        $pool->addMethodCall('setAdminGroups', array($groups));
        $pool->addMethodCall('setAdminClasses', array($classes));

        $routeLoader = $container->getDefinition('sonata.admin.route_loader');
        $routeLoader->replaceArgument(1, $admins);
    }

    /**
     * This method read the attribute keys and configure admin class to use the related dependency.
     *
     * @param Definition $definition
     * @param array      $attributes
     */
    public function applyConfigurationFromAttribute(Definition $definition, array $attributes)
    {
        $keys = array(
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
        );

        foreach ($keys as $key) {
            $method = 'set'.Inflector::classify($key);
            if (!isset($attributes[$key]) || $definition->hasMethodCall($method)) {
                continue;
            }

            $definition->addMethodCall($method, array(new Reference($attributes[$key])));
        }
    }

    /**
     * Apply the default values required by the AdminInterface to the Admin service definition.
     *
     * @param ContainerBuilder $container
     * @param string           $serviceId
     * @param array            $attributes
     *
     * @return Definition
     */
    public function applyDefaults(ContainerBuilder $container, $serviceId, array $attributes = array())
    {
        $definition = $container->getDefinition($serviceId);
        $settings = $container->getParameter('sonata.admin.configuration.admin_services');

        if (method_exists($definition, 'setShared')) { // Symfony 2.8+
            $definition->setShared(false);
        } else { // For Symfony <2.8 compatibility
            $definition->setScope(ContainerInterface::SCOPE_PROTOTYPE);
        }

        $manager_type = $attributes['manager_type'];

        $overwriteAdminConfiguration = isset($settings[$serviceId]) ? $settings[$serviceId] : array();

        $defaultAddServices = array(
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
                (($manager_type == 'doctrine_phpcr') ? '_slashes' : ''),
            'label_translator_strategy' => 'sonata.admin.label.strategy.native',
        );

        $definition->addMethodCall('setManagerType', array($manager_type));

        foreach ($defaultAddServices as $attr => $addServiceId) {
            $method = 'set'.Inflector::classify($attr);

            if (isset($overwriteAdminConfiguration[$attr]) || !$definition->hasMethodCall($method)) {
                $definition->addMethodCall($method, array(new Reference(isset($overwriteAdminConfiguration[$attr]) ? $overwriteAdminConfiguration[$attr] : $addServiceId)));
            }
        }

        if (isset($overwriteAdminConfiguration['pager_type'])) {
            $pagerType = $overwriteAdminConfiguration['pager_type'];
        } elseif (isset($attributes['pager_type'])) {
            $pagerType = $attributes['pager_type'];
        } else {
            $pagerType = Pager::TYPE_DEFAULT;
        }

        $definition->addMethodCall('setPagerType', array($pagerType));

        if (isset($overwriteAdminConfiguration['label'])) {
            $label = $overwriteAdminConfiguration['label'];
        } elseif (isset($attributes['label'])) {
            $label = $attributes['label'];
        } else {
            $label = '-';
        }

        $definition->addMethodCall('setLabel', array($label));

        if (isset($attributes['persist_filters'])) {
            $persistFilters = (bool) $attributes['persist_filters'];
        } else {
            $persistFilters = (bool) $container->getParameter('sonata.admin.configuration.filters.persist');
        }

        $definition->addMethodCall('setPersistFilters', array($persistFilters));

        $this->fixTemplates($container, $definition, isset($overwriteAdminConfiguration['templates']) ? $overwriteAdminConfiguration['templates'] : array('view' => array()));

        if ($container->hasParameter('sonata.admin.configuration.security.information') && !$definition->hasMethodCall('setSecurityInformation')) {
            $definition->addMethodCall('setSecurityInformation', array('%sonata.admin.configuration.security.information%'));
        }

        $definition->addMethodCall('initialize');

        return $definition;
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition       $definition
     * @param array            $overwrittenTemplates
     */
    public function fixTemplates(ContainerBuilder $container, Definition $definition, array $overwrittenTemplates = array())
    {
        $definedTemplates = $container->getParameter('sonata.admin.configuration.templates');

        $methods = array();
        $pos = 0;
        foreach ($definition->getMethodCalls() as $method) {
            if ($method[0] == 'setTemplates') {
                $definedTemplates = array_merge($definedTemplates, $method[1][0]);
                continue;
            }

            if ($method[0] == 'setTemplate') {
                $definedTemplates[$method[1][0]] = $method[1][1];
                continue;
            }

            // set template for simple pager if it is not already overwritten
            if ($method[0] === 'setPagerType'
                && $method[1][0] === Pager::TYPE_SIMPLE
                && (
                    !isset($definedTemplates['pager_results'])
                    || $definedTemplates['pager_results'] === 'SonataAdminBundle:Pager:results.html.twig'
                )
            ) {
                $definedTemplates['pager_results'] = 'SonataAdminBundle:Pager:simple_pager_results.html.twig';
            }

            $methods[$pos] = $method;
            ++$pos;
        }

        $definition->setMethodCalls($methods);

        // make sure the default templates are defined
        $definedTemplates = array_merge(array(
            'user_block' => 'SonataAdminBundle:Core:user_block.html.twig',
            'add_block' => 'SonataAdminBundle:Core:add_block.html.twig',
            'layout' => 'SonataAdminBundle::standard_layout.html.twig',
            'ajax' => 'SonataAdminBundle::ajax_layout.html.twig',
            'dashboard' => 'SonataAdminBundle:Core:dashboard.html.twig',
            'list' => 'SonataAdminBundle:CRUD:list.html.twig',
            'filter' => 'SonataAdminBundle:Form:filter_admin_fields.html.twig',
            'show' => 'SonataAdminBundle:CRUD:show.html.twig',
            'show_compare' => 'SonataAdminBundle:CRUD:show_compare.html.twig',
            'edit' => 'SonataAdminBundle:CRUD:edit.html.twig',
            'history' => 'SonataAdminBundle:CRUD:history.html.twig',
            'history_revision_timestamp' => 'SonataAdminBundle:CRUD:history_revision_timestamp.html.twig',
            'acl' => 'SonataAdminBundle:CRUD:acl.html.twig',
            'action' => 'SonataAdminBundle:CRUD:action.html.twig',
            'short_object_description' => 'SonataAdminBundle:Helper:short-object-description.html.twig',
            'preview' => 'SonataAdminBundle:CRUD:preview.html.twig',
            'list_block' => 'SonataAdminBundle:Block:block_admin_list.html.twig',
            'delete' => 'SonataAdminBundle:CRUD:delete.html.twig',
            'batch' => 'SonataAdminBundle:CRUD:list__batch.html.twig',
            'select' => 'SonataAdminBundle:CRUD:list__select.html.twig',
            'batch_confirmation' => 'SonataAdminBundle:CRUD:batch_confirmation.html.twig',
            'inner_list_row' => 'SonataAdminBundle:CRUD:list_inner_row.html.twig',
            'base_list_field' => 'SonataAdminBundle:CRUD:base_list_field.html.twig',
            'pager_links' => 'SonataAdminBundle:Pager:links.html.twig',
            'pager_results' => 'SonataAdminBundle:Pager:results.html.twig',
            'tab_menu_template' => 'SonataAdminBundle:Core:tab_menu_template.html.twig',
            'knp_menu_template' => 'SonataAdminBundle:Menu:sonata_menu.html.twig',
            'outer_list_rows_mosaic' => 'SonataAdminBundle:CRUD:list_outer_rows_mosaic.html.twig',
            'outer_list_rows_list' => 'SonataAdminBundle:CRUD:list_outer_rows_list.html.twig',
            'outer_list_rows_tree' => 'SonataAdminBundle:CRUD:list_outer_rows_tree.html.twig',
        ), $definedTemplates, $overwrittenTemplates['view']);

        $definition->addMethodCall('setTemplates', array($definedTemplates));
    }
}
