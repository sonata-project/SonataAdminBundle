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
 * @author Pablo Díez <pablodip@gmail.com>
 */
class AddDependencyCallsPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $groups = $admins = $classes = array();

        // Admin Pool
        $pool = $container->getDefinition('sonata_admin.admin.pool');

        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $attributes) {
            $definition = $container->getDefinition($id);
            $arguments = $definition->getArguments();

            if (strlen($arguments[0]) == 0) {
                $definition->setArgument(0, $id);
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

            $groupName = isset($attributes[0]['group']) ? $attributes[0]['group'] : 'default';

            if (!isset($groups[$groupName])) {
                $groups[$groupName] = array();
            }

            $groups[$groupName][$id] = array(
                'show_in_dashboard' => isset($attributes[0]['show_in_dashboard']) ? (Boolean) $attributes[0]['show_in_dashboard'] : true,
            );
        }

        $pool->addMethodCall('setAdminServiceIds', array($admins));
        $pool->addMethodCall('setAdminGroups', array($groups));
        $pool->addMethodCall('setAdminClasses', array($classes));

        // Routing Loader
        $routeLoader = $container->getDefinition('routing.loader.sonata_admin');
        $routeLoader->addArgument($admins);
    }

    /**
     * Apply the default values required by the AdminInterface to the Admin service definition
     *
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param array $attributes
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    private function applyDefaults(Definition $definition, array $attributes = array())
    {
        $definition->setScope(ContainerInterface::SCOPE_PROTOTYPE);

        $modelManager = $attributes[0]['model_manager'];

        if (!$definition->hasMethodCall('setModelManager')) {
            $definition->addMethodCall('setModelManager', array(new Reference(sprintf('sonata_admin.model_manager.%s', $modelManager))));
        }

        if (!$definition->hasMethodCall('setFormBuilder')) {
            $definition->addMethodCall('setFormBuilder', array(new Reference(sprintf('sonata_admin.model_manager.%s.form_builder', $modelManager))));
        }

        if (!$definition->hasMethodCall('setListBuilder')) {
            $definition->addMethodCall('setListBuilder', array(new Reference(sprintf('sonata_admin.model_manager.%s.list_builder', $modelManager))));
        }

        if (!$definition->hasMethodCall('setDatagridBuilder')) {
            $definition->addMethodCall('setDatagridBuilder', array(new Reference(sprintf('sonata_admin.model_manager.%s.data_grid_builder', $modelManager))));
        }

        if (!$definition->hasMethodCall('setTranslator')) {
            $definition->addMethodCall('setTranslator', array(new Reference('translator')));
        }

        if (!$definition->hasMethodCall('setConfigurationPool')) {
            $definition->addMethodCall('setConfigurationPool', array(new Reference('sonata_admin.admin.pool')));
        }

        if (!$definition->hasMethodCall('setRouter')) {
            $definition->addMethodCall('setRouter', array(new Reference('router')));
        }

        if (!$definition->hasMethodCall('setLabel')) {
            $label = isset($attributes[0]['label']) ? $attributes[0]['label'] : '-';
            $definition->addMethodCall('setLabel', array($label));
        }

        $definition->addMethodCall('configure');

        return $definition;
    }
}
