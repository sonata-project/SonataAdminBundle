<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add all dependencies to the Admin class, this avoid to write too many lines
 * in the configuration files.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AddDependencyCallsCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
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
                $classes[$arguments[1]] = $id;

                $showInDashboard = (boolean) (isset($attributes['show_in_dashboard']) ? $attributes['show_in_dashboard'] : true);
                if (!$showInDashboard) {
                    continue;
                }

                $groupName = isset($attributes['group']) ? $attributes['group'] : 'default';
                $labelCatalogue = isset($attributes['label_catalogue']) ? $attributes['label_catalogue'] : 'SonataAdminBundle';

                if (!isset($groupDefaults[$groupName])) {
                    $groupDefaults[$groupName] = array(
                        'label'           => $groupName,
                        'label_catalogue' => $labelCatalogue
                    );
                }

                $groupDefaults[$groupName]['items'][] = $id;
            }
        }

        $dashboardGroupsSettings = $container->getParameter('sonata.admin.configuration.dashboard_groups');
        if (!empty($dashboardGroupsSettings)) {
            $groups = $dashboardGroupsSettings;

            foreach ($dashboardGroupsSettings as $groupName => $group) {
                if (!isset($groupDefaults[$groupName])) {
                    $groupDefaults[$groupName] = array(
                        'items' => array(),
                        'label' => $groupName
                    );
                }

                if (empty($group['items'])) {
                    $groups[$groupName]['items'] = $groupDefaults[$groupName]['items'];
                }

                if (empty($group['label'])) {
                    $groups[$groupName]['label'] = $groupDefaults[$groupName]['label'];
                }

                if (empty($group['label_catalogue'])) {
                    $groups[$groupName]['label_catalogue'] = 'SonataAdminBundle';
                }

                if (!empty($group['item_adds'])) {
                    $group['items'] = array_merge($groupDefaults[$groupName]['items'], $group['item_adds']);
                }
            }
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
     * This method read the attribute keys and configure admin class to use the related dependency
     *
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param array                                             $attributes
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
            $method = 'set'.$this->camelize($key);
            if (!isset($attributes[$key]) || $definition->hasMethodCall($method)) {
                continue;
            }

            $definition->addMethodCall($method, array(new Reference($attributes[$key])));
        }
    }

    /**
     * Apply the default values required by the AdminInterface to the Admin service definition
     *
     * @param  \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param  string                                                  $serviceId
     * @param  array                                                   $attributes
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    public function applyDefaults(ContainerBuilder $container, $serviceId, array $attributes = array())
    {
        $definition = $container->getDefinition($serviceId);
        $settings = $container->getParameter('sonata.admin.configuration.admin_services');

        $definition->setScope(ContainerInterface::SCOPE_PROTOTYPE);

        $manager_type = $attributes['manager_type'];

        $addServices = isset($settings[$serviceId]) ? $settings[$serviceId] : array();

        $defaultAddServices = array(
            'model_manager'             => sprintf('sonata.admin.manager.%s', $manager_type),
            'form_contractor'           => sprintf('sonata.admin.builder.%s_form', $manager_type),
            'show_builder'              => sprintf('sonata.admin.builder.%s_show', $manager_type),
            'list_builder'              => sprintf('sonata.admin.builder.%s_list', $manager_type),
            'datagrid_builder'          => sprintf('sonata.admin.builder.%s_datagrid', $manager_type),
            'translator'                => 'translator',
            'configuration_pool'        => 'sonata.admin.pool',
            'route_generator'           => 'sonata.admin.route.default_generator',
            'validator'                 => 'validator',
            'security_handler'          => 'sonata.admin.security.handler',
            'menu_factory'              => 'knp_menu.factory',
            'route_builder'             => 'sonata.admin.route.path_info',
            'label_translator_strategy' => 'sonata.admin.label.strategy.native'
        );

        $definition->addMethodCall('setManagerType', array($manager_type));

        foreach ($defaultAddServices as $attr => $addServiceId) {
            $method = 'set'.$this->camelize($attr);

            if (isset($addServices[$attr]) || !$definition->hasMethodCall($method)) {
                $definition->addMethodCall($method, array(new Reference(isset($addServices[$attr]) ? $addServices[$attr] : $addServiceId)));
            }
        }

        if (isset($service['label'])) {
            $label = $service['label'];
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

        $this->fixTemplates($container, $definition);

        if ($container->hasParameter('sonata.admin.configuration.security.information') && !$definition->hasMethodCall('setSecurityInformation')) {
            $definition->addMethodCall('setSecurityInformation', array('%sonata.admin.configuration.security.information%'));
        }

        $definition->addMethodCall('initialize');

        return $definition;
    }

    /**
     * @param  \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param  \Symfony\Component\DependencyInjection\Definition       $definition
     * @return void
     */
    public function fixTemplates(ContainerBuilder $container, Definition $definition)
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

            $methods[$pos] = $method;
            $pos++;
        }

        $definition->setMethodCalls($methods);

        // make sure the default templates are defined
        $definedTemplates = array_merge(array(
            'user_block'               => 'SonataAdminBundle:Core:user_block.html.twig',
            'layout'                   => 'SonataAdminBundle::standard_layout.html.twig',
            'ajax'                     => 'SonataAdminBundle::ajax_layout.html.twig',
            'dashboard'                => 'SonataAdminBundle:Core:dashboard.html.twig',
            'list'                     => 'SonataAdminBundle:CRUD:list.html.twig',
            'show'                     => 'SonataAdminBundle:CRUD:show.html.twig',
            'edit'                     => 'SonataAdminBundle:CRUD:edit.html.twig',
            'history'                  => 'SonataAdminBundle:CRUD:history.html.twig',
            'history_revision'         => 'SonataAdminBundle:CRUD:history_revision.html.twig',
            'action'                   => 'SonataAdminBundle:CRUD:action.html.twig',
            'short_object_description' => 'SonataAdminBundle:Helper:short-object-description.html.twig',
        ), $definedTemplates);

        $definition->addMethodCall('setTemplates', array($definedTemplates));
    }

    /**
     * method taken from PropertyPath
     *
     * @param  $property
     * @return mixed
     */
    protected function camelize($property)
    {
        return preg_replace(array('/(^|_)+(.)/e', '/\.(.)/e'), array("strtoupper('\\2')", "'_'.strtoupper('\\1')"), $property);
    }
}
