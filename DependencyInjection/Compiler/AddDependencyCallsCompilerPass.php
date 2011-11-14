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

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

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
        $groups = $groupDefaults = $admins = $classes = array();

        $pool = $container->getDefinition('sonata.admin.pool');

        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $attributes) {
            $definition = $container->getDefinition($id);

            $arguments = $definition->getArguments();

            if (strlen($arguments[0]) == 0) {
                $definition->replaceArgument(0, $id);
            }

            if (strlen($arguments[2]) == 0) {
                $definition->replaceArgument(2, 'SonataAdminBundle:CRUD');
            }

            $this->applyDefaults($container, $id, $attributes);

            $arguments = $definition->getArguments();
            if (preg_match('/%(.*)%/', $arguments[1], $matches)) {
                $class = $container->getParameter($matches[1]);
            } else {
                $class = $arguments[1];
            }

            $admins[] = $id;
            $classes[$class] = $id;

            $showInDashBord = (boolean)(isset($attributes[0]['show_in_dashboard']) ? $attributes[0]['show_in_dashboard'] : true);
            if (!$showInDashBord) {
                continue;
            }

            $groupName = isset($attributes[0]['group']) ? $attributes[0]['group'] : 'default';

            if (!isset($groupDefaults[$groupName])) {
                $groupDefaults[$groupName] = array(
                    'label' => $groupName
                );
            }

            $groupDefaults[$groupName]['items'][] = $id;
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
     * Apply the default values required by the AdminInterface to the Admin service definition
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $serviceId
     * @param array $attributes
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    public function applyDefaults(ContainerBuilder $container, $serviceId, array $attributes = array())
    {
        $definition = $container->getDefinition($serviceId);
        $settings = $container->getParameter('sonata.admin.configuration.admin_services');

        $definition->setScope(ContainerInterface::SCOPE_PROTOTYPE);

        $manager_type = $attributes[0]['manager_type'];

        $addServices = isset($settings[$serviceId]) ? $settings[$serviceId] : array();

        $defaultAddServices = array(
            'model_manager'      => sprintf('sonata.admin.manager.%s', $manager_type),
            'form_contractor'    => sprintf('sonata.admin.builder.%s_form', $manager_type),
            'show_builder'       => sprintf('sonata.admin.builder.%s_show', $manager_type),
            'list_builder'       => sprintf('sonata.admin.builder.%s_list', $manager_type),
            'datagrid_builder'   => sprintf('sonata.admin.builder.%s_datagrid', $manager_type),
            'translator'         => 'translator',
            'configuration_pool' => 'sonata.admin.pool',
            'router'             => 'router',
            'validator'          => 'validator',
            'security_handler'   => 'sonata.admin.security.handler',
            'menu_factory'       => 'knp_menu.factory',
        );

        foreach ($defaultAddServices as $attr => $addServiceId) {
            $method = 'set'.$this->camelize($attr);

            if (isset($addServices[$attr]) || !$definition->hasMethodCall($method)) {
                $definition->addMethodCall($method, array(new Reference(isset($addServices[$attr]) ? $addServices[$attr] : $addServiceId)));
            }
        }

        if (!$definition->hasMethodCall('setRouteBuilder')) {
            $definition->addMethodCall('setRouteBuilder', array(new Reference('sonata.admin.route.path_info')));
        }

        if (isset($service['label'])) {
            $label = $service['label'];
        } elseif (isset($attributes[0]['label'])) {
            $label = $attributes[0]['label'];
        } else {
            $label = '-';
        }
        $definition->addMethodCall('setLabel', array($label));

        $definition->addMethodCall('configure');

        if (!$definition->hasMethodCall('setTemplates')) {
            $definition->addMethodCall('setTemplates', array('%sonata.admin.configuration.templates%'));
        }

        return $definition;
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
