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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Michael Williams <mtotheikle@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sonata_admin', 'array');

        $rootNode
            ->fixXmlConfig('dashboard_group')
            ->fixXmlConfig('admin_service')
            ->children()
                ->arrayNode('security')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('handler')->defaultValue('sonata.admin.security.handler.noop')->end()
                        ->arrayNode('information')
                            ->useAttributeAsKey('id')
                            ->prototype('array')
                                ->performNoDeepMerging()
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(function($v){ return array($v); })
                                ->end()
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                        ->arrayNode('admin_permissions')
                            ->defaultValue(array('CREATE', 'LIST', 'DELETE', 'UNDELETE', 'EXPORT', 'OPERATOR', 'MASTER'))
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('object_permissions')
                            ->defaultValue(array('VIEW', 'EDIT', 'DELETE', 'UNDELETE', 'OPERATOR', 'MASTER', 'OWNER'))
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()

                ->scalarNode('title')->defaultValue('Sonata Admin')->cannotBeEmpty()->end()
                ->scalarNode('title_logo')->defaultValue('bundles/sonataadmin/logo_title.png')->cannotBeEmpty()->end()

                ->arrayNode('dashboard')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('groups')
                            ->useAttributeAsKey('id')
                            ->prototype('array')
                                ->fixXmlConfig('item')
                                ->fixXmlConfig('item_add')
                                ->children()
                                    ->scalarNode('label')->end()
                                    ->arrayNode('items')
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->arrayNode('item_adds')
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('blocks')
                            ->defaultValue(array(array('position' => 'left', 'settings' => array(), 'type' => 'sonata.admin.block.admin_list')))
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('type')->cannotBeEmpty()->end()
                                    ->arrayNode('settings')
                                        ->useAttributeAsKey('id')
                                        ->prototype('variable')->defaultValue(array())->end()
                                    ->end()
                                    ->scalarNode('position')->defaultValue('right')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('admin_services')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('model_manager')->end()
                            ->scalarNode('form_contractor')->end()
                            ->scalarNode('show_builder')->end()
                            ->scalarNode('list_builder')->end()
                            ->scalarNode('datagrid_builder')->end()
                            ->scalarNode('translator')->end()
                            ->scalarNode('configuration_pool')->end()
                            ->scalarNode('router')->end()
                            ->scalarNode('validator')->end()
                            ->scalarNode('security_handler')->end()
                            ->scalarNode('label')->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('user_block')->defaultValue('SonataAdminBundle:Core:user_block.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('layout')->defaultValue('SonataAdminBundle::standard_layout.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('ajax')->defaultValue('SonataAdminBundle::ajax_layout.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('dashboard')->defaultValue('SonataAdminBundle:Core:dashboard.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('list')->defaultValue('SonataAdminBundle:CRUD:list.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('show')->defaultValue('SonataAdminBundle:CRUD:show.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('edit')->defaultValue('SonataAdminBundle:CRUD:edit.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('preview')->defaultValue('SonataAdminBundle:CRUD:preview.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('history')->defaultValue('SonataAdminBundle:CRUD:history.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('history_revision')->defaultValue('SonataAdminBundle:CRUD:history_revision.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('action')->defaultValue('SonataAdminBundle:CRUD:action.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('list_block')->defaultValue('SonataAdminBundle:Block:block_admin_list.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('short_object_description')->defaultValue('SonataAdminBundle:Helper:short-object-description.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('delete')->defaultValue('SonataAdminBundle:CRUD:delete.html.twig')->cannotBeEmpty()->end()
                    ->end()
                ->end()

                ->scalarNode('persist_filters')->defaultValue(false)->cannotBeEmpty()->end()

            ->end()
        ->end();

        return $treeBuilder;
    }
}
