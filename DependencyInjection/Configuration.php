<?php

/*
 * This file is part of the Sonata Project package.
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
 * This class contains the configuration information for the bundle.
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
            ->fixXmlConfig('option')
            ->fixXmlConfig('admin_service')
            ->fixXmlConfig('template')
            ->fixXmlConfig('extension')
            ->children()
                ->arrayNode('security')
                    ->addDefaultsIfNotSet()
                    ->fixXmlConfig('admin_permission')
                    ->fixXmlConfig('object_permission')
                    ->children()
                        ->scalarNode('handler')->defaultValue('sonata.admin.security.handler.noop')->end()
                        ->arrayNode('information')
                            ->useAttributeAsKey('id')
                            ->prototype('array')
                                ->performNoDeepMerging()
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(function ($v) { return array($v); })
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
                        ->scalarNode('acl_user_manager')->defaultValue(null)->end()
                    ->end()
                ->end()

                ->scalarNode('title')->defaultValue('Sonata Admin')->cannotBeEmpty()->end()
                ->scalarNode('title_logo')->defaultValue('bundles/sonataadmin/logo_title.png')->cannotBeEmpty()->end()
                ->arrayNode('options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('html5_validate')->defaultValue(true)->end()
                        ->booleanNode('confirm_exit')->defaultValue(true)->end()
                        ->booleanNode('use_select2')->defaultValue(true)->end()
                        ->booleanNode('use_icheck')->defaultValue(true)->end()
                        ->integerNode('pager_links')->defaultValue(null)->end()
                        ->scalarNode('form_type')->defaultValue('standard')->end()
                        ->integerNode('dropdown_number_groups_per_colums')->defaultValue(2)->end()
                        ->enumNode('title_mode')
                            ->values(array('single_text', 'single_image', 'both'))
                            ->defaultValue('both')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('dashboard')
                    ->addDefaultsIfNotSet()
                    ->fixXmlConfig('group')
                    ->fixXmlConfig('block')
                    ->children()
                        ->arrayNode('groups')
                            ->useAttributeAsKey('id')
                            ->prototype('array')
                                ->fixXmlConfig('item')
                                ->fixXmlConfig('item_add')
                                ->children()
                                    ->scalarNode('label')->end()
                                    ->scalarNode('label_catalogue')->end()
                                    ->scalarNode('icon')->defaultValue('<i class="fa fa-folder"></i>')->end()
                                    ->arrayNode('items')
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->arrayNode('item_adds')
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->arrayNode('roles')
                                        ->prototype('scalar')->defaultValue(array())->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('blocks')
                            ->defaultValue(array(array('position' => 'left', 'settings' => array(), 'type' => 'sonata.admin.block.admin_list')))
                            ->prototype('array')
                                ->fixXmlConfig('setting')
                                ->children()
                                    ->scalarNode('type')->cannotBeEmpty()->end()
                                    ->arrayNode('settings')
                                        ->useAttributeAsKey('id')
                                        ->prototype('variable')->defaultValue(array())->end()
                                    ->end()
                                    ->scalarNode('position')->defaultValue('right')->end()
                                    ->scalarNode('class')->defaultValue('col-md-4')->end()
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
                        ->scalarNode('add_block')->defaultValue('SonataAdminBundle:Core:add_block.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('layout')->defaultValue('SonataAdminBundle::standard_layout.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('ajax')->defaultValue('SonataAdminBundle::ajax_layout.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('dashboard')->defaultValue('SonataAdminBundle:Core:dashboard.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('search')->defaultValue('SonataAdminBundle:Core:search.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('list')->defaultValue('SonataAdminBundle:CRUD:list.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('filter')->defaultValue('SonataAdminBundle:Form:filter_admin_fields.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('show')->defaultValue('SonataAdminBundle:CRUD:show.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('show_compare')->defaultValue('SonataAdminBundle:CRUD:show_compare.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('edit')->defaultValue('SonataAdminBundle:CRUD:edit.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('preview')->defaultValue('SonataAdminBundle:CRUD:preview.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('history')->defaultValue('SonataAdminBundle:CRUD:history.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('acl')->defaultValue('SonataAdminBundle:CRUD:acl.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('history_revision_timestamp')->defaultValue('SonataAdminBundle:CRUD:history_revision_timestamp.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('action')->defaultValue('SonataAdminBundle:CRUD:action.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('select')->defaultValue('SonataAdminBundle:CRUD:list__select.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('list_block')->defaultValue('SonataAdminBundle:Block:block_admin_list.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('search_result_block')->defaultValue('SonataAdminBundle:Block:block_search_result.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('short_object_description')->defaultValue('SonataAdminBundle:Helper:short-object-description.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('delete')->defaultValue('SonataAdminBundle:CRUD:delete.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('batch')->defaultValue('SonataAdminBundle:CRUD:list__batch.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('batch_confirmation')->defaultValue('SonataAdminBundle:CRUD:batch_confirmation.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('inner_list_row')->defaultValue('SonataAdminBundle:CRUD:list_inner_row.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('base_list_field')->defaultValue('SonataAdminBundle:CRUD:base_list_field.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('pager_links')->defaultValue('SonataAdminBundle:Pager:links.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('pager_results')->defaultValue('SonataAdminBundle:Pager:results.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('tab_menu_template')->defaultValue('SonataAdminBundle:Core:tab_menu_template.html.twig')->cannotBeEmpty()->end()
                    ->end()
                ->end()

                ->arrayNode('assets')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('stylesheets')
                            ->defaultValue(array(
                                'bundles/sonataadmin/vendor/bootstrap/dist/css/bootstrap.min.css',
                                'bundles/sonataadmin/vendor/AdminLTE/css/font-awesome.min.css',
                                'bundles/sonataadmin/vendor/AdminLTE/css/ionicons.min.css',
                                'bundles/sonataadmin/vendor/AdminLTE/css/AdminLTE.css',

                                'bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',

                                'bundles/sonataadmin/vendor/jqueryui/themes/base/jquery-ui.css',

                                'bundles/sonataadmin/vendor/select2/select2.css',
                                'bundles/sonataadmin/vendor/select2/select2-bootstrap.css',

                                'bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css',

                                'bundles/sonataadmin/css/styles.css',
                                'bundles/sonataadmin/css/layout.css',
                            ))
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('javascripts')
                            ->defaultValue(array(
                                'bundles/sonataadmin/vendor/jquery/dist/jquery.min.js',
                                'bundles/sonataadmin/vendor/jquery.scrollTo/jquery.scrollTo.min.js',

                                'bundles/sonatacore/vendor/moment/min/moment.min.js',

                                'bundles/sonataadmin/vendor/bootstrap/dist/js/bootstrap.min.js',

                                'bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',

                                'bundles/sonataadmin/vendor/jqueryui/ui/minified/jquery-ui.min.js',
                                'bundles/sonataadmin/vendor/jqueryui/ui/minified/i18n/jquery-ui-i18n.min.js',

                                'bundles/sonataadmin/jquery/jquery.form.js',
                                'bundles/sonataadmin/jquery/jquery.confirmExit.js',

                                'bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js',

                                'bundles/sonataadmin/vendor/select2/select2.min.js',

                                'bundles/sonataadmin/App.js',
                                'bundles/sonataadmin/Admin.js',
                            ))
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('extensions')
                ->useAttributeAsKey('id')
                ->defaultValue(array('admins' => array(), 'excludes' => array(), 'implements' => array(), 'extends' => array(), 'instanceof' => array()))
                    ->prototype('array')
                        ->fixXmlConfig('admin')
                        ->fixXmlConfig('exclude')
                        ->fixXmlConfig('implement')
                        ->fixXmlConfig('extend')
                        ->children()
                            ->arrayNode('admins')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('excludes')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('implements')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('extends')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('instanceof')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->scalarNode('persist_filters')->defaultValue(false)->end()

            ->end()
        ->end();

        return $treeBuilder;
    }
}
