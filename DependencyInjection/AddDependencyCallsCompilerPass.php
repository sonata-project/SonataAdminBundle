<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

            $this->applyDefaults($definition, $attributes);

            $arguments = $definition->getArguments();
            if (preg_match('/%(.*)%/', $arguments[1], $matches)) {
                $class = $container->getParameter($matches[1]);
            } else {
                $class = $arguments[1];
            }

            $admins[] = $id;
            $classes[$class] = $id;

            $group_name = isset($attributes[0]['group']) ? $attributes[0]['group'] : 'default';

            if (!isset($groups[$group_name])) {
                $groups[$group_name] = array();
            }

            $groups[$group_name][$id] = array(
                'show_in_dashboard' => (boolean)(isset($attributes[0]['show_in_dashboard']) ? $attributes[0]['show_in_dashboard'] : true)
            );
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
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param array $attributes
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    public function applyDefaults(Definition $definition, array $attributes = array())
    {
        $definition->setScope(ContainerInterface::SCOPE_PROTOTYPE);

        $manager_type = $attributes[0]['manager_type'];

        if (!$definition->hasMethodCall('setModelManager')) {
            $definition->addMethodCall('setModelManager', array(new Reference(sprintf('sonata.admin.manager.%s', $manager_type))));
        }

        if (!$definition->hasMethodCall('setFormContractor')) {
            $definition->addMethodCall('setFormContractor', array(new Reference(sprintf('sonata.admin.builder.%s_form', $manager_type))));
        }

        if (!$definition->hasMethodCall('setViewBuilder')) {
            $definition->addMethodCall('setViewBuilder', array(new Reference(sprintf('sonata.admin.builder.%s_view', $manager_type))));
        }

        if (!$definition->hasMethodCall('setListBuilder')) {
            $definition->addMethodCall('setListBuilder', array(new Reference(sprintf('sonata.admin.builder.%s_list', $manager_type))));
        }

        if (!$definition->hasMethodCall('setDatagridBuilder')) {
            $definition->addMethodCall('setDatagridBuilder', array(new Reference(sprintf('sonata.admin.builder.%s_datagrid', $manager_type))));
        }

        if (!$definition->hasMethodCall('setTranslator')) {
            $definition->addMethodCall('setTranslator', array(new Reference('translator')));
        }

        if (!$definition->hasMethodCall('setConfigurationPool')) {
            $definition->addMethodCall('setConfigurationPool', array(new Reference('sonata.admin.pool')));
        }

        if (!$definition->hasMethodCall('setRouter')) {
            $definition->addMethodCall('setRouter', array(new Reference('router')));
        }

        if (!$definition->hasMethodCall('setValidator')) {
            $definition->addMethodCall('setValidator', array(new Reference('validator')));
        }

        if (!$definition->hasMethodCall('setSecurityHandler')) {
            $definition->addMethodCall('setSecurityHandler', array(new Reference('sonata.admin.security.handler')));
        }

        if (!$definition->hasMethodCall('setLabel')) {
            $label = isset($attributes[0]['label']) ? $attributes[0]['label'] : '-';
            $definition->addMethodCall('setLabel', array($label));
        }

        $definition->addMethodCall('configure');

        return $definition;
    }
}
