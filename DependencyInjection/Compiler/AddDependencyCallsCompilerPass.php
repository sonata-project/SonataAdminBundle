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
 * Add all dependencies to the Admin class, this avoid to write to many lines
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
        $settings = $this->fixSettings($container);

        $groups = $admins = $classes = array();

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

            $this->applyDefaults($container, $id, $attributes, $settings);

            $arguments = $definition->getArguments();
            if (preg_match('/%(.*)%/', $arguments[1], $matches)) {
                $class = $container->getParameter($matches[1]);
            } else {
                $class = $arguments[1];
            }

            $admins[] = $id;
            $classes[$class] = $id;

            $group_name = isset($attributes[0]['group']) ? $attributes[0]['group'] : 'default';

            if (!isset($groupDefaults[$group_name])) {
                $groupDefaults[$group_name] = array(
                    'label' => $group_name
                );
            }

            $groupDefaults[$group_name]['items'][] = $id;
        }

        if (isset($settings['dashboard_groups'])) {

            $groups = $settings['dashboard_groups'];

            foreach ($groups as $group_name => $group) {
                if (empty($group['items'])) {
                    $groups[$group_name]['items'] = $groupDefaults[$group_name]['items'];
                }

                if (empty($group['label'])) {
                    $groups[$group_name]['label'] = $groupDefaults[$group_name]['label'];
                }

                if (!empty($groups[$group_name]['item_adds'])) {
                    $groups[$group_name]['items'] = array_merge($groupDefaults[$group_name]['items'], $groups[$group_name]['item_adds']);
                }
            }
        }
        else {
            $groups = $groupDefaults;
        }

        $pool->addMethodCall('setAdminServiceIds', array($admins));
        $pool->addMethodCall('setAdminGroups', array($groups));
        $pool->addMethodCall('setAdminClasses', array($classes));

        $routeLoader = $container->getDefinition('sonata.admin.route_loader');
        $routeLoader->replaceArgument(1, $admins);
    }

    public function fixSettings($container)
    {
        $pool = $container->getDefinition('sonata.admin.pool');

        // not very clean but don't know how to do that for now
        $settings = false;
        $methods  = $pool->getMethodCalls();
        foreach ($methods as $pos => $calls) {
            if ($calls[0] == '__hack__') {
                $settings = $calls[1];
                break;
            }
        }

        if ($settings) {
            unset($methods[$pos]);
        }

        $pool->setMethodCalls($methods);

        return $settings;
    }

    /**
     * Apply the default values required by the AdminInterface to the Admin service definition
     *
     * @param ContainerBuilder $container
     * @param interger $serviceId
     * @param array $attributes
     * @param array $settings
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    public function applyDefaults(ContainerBuilder $container, $serviceId, array $attributes = array(), array $settings = array())
    {
        $definition = $container->getDefinition($serviceId);

        $definition->setScope(ContainerInterface::SCOPE_PROTOTYPE);

        $manager_type = $attributes[0]['manager_type'];

        $addServices = isset($settings['admin_services'][$serviceId]) ? $settings['admin_services'][$serviceId] : false;

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
            'security_handler'   => 'sonata.admin.security.handler'
        );

        foreach ($defaultAddServices as $attr => $addServiceId) {
            $method = 'set'.$this->camelize($attr);

            if (isset($addServices[$attr]) || !$definition->hasMethodCall($method)) {
                $definition->addMethodCall($method, array(new Reference(isset($addServices[$attr]) ? $addServices[$attr] : $addServiceId)));
            }
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
