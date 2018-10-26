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
     * @return TreeBuilder
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
                                    ->then(function ($v) {
                                        return [$v];
                                    })
                                ->end()
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                        ->arrayNode('admin_permissions')
                            ->defaultValue(['CREATE', 'LIST', 'DELETE', 'UNDELETE', 'EXPORT', 'OPERATOR', 'MASTER'])
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('role_admin')
                            ->cannotBeEmpty()
                            ->defaultValue('ROLE_SONATA_ADMIN')
                            ->info('Role which will see the top nav bar and dropdown groups regardless of its configuration')
                        ->end()
                            ->scalarNode('role_super_admin')
                            ->cannotBeEmpty()
                            ->defaultValue('ROLE_SUPER_ADMIN')
                            ->info('Role which will perform all admin actions, see dashboard, menu and search groups regardless of its configuration')
                        ->end()
                        ->arrayNode('object_permissions')
                            ->defaultValue(['VIEW', 'EDIT', 'DELETE', 'UNDELETE', 'OPERATOR', 'MASTER', 'OWNER'])
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('acl_user_manager')->defaultNull()->end()
                    ->end()
                ->end()

                ->scalarNode('title')->defaultValue('Sonata Admin')->cannotBeEmpty()->end()
                ->scalarNode('title_logo')->defaultValue('bundles/sonataadmin/logo_title.png')->cannotBeEmpty()->end()
                ->booleanNode('search')->defaultTrue()->info('Enable/disable the search form in the sidebar')->end()

                ->arrayNode('global_search')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('empty_boxes')
                            ->defaultValue('show')
                            ->info('Perhaps one of the three options: show, fade, hide.')
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return !\in_array($v, ['show', 'fade', 'hide']);
                                })
                                ->thenInvalid('Configuration value of "global_search.empty_boxes" must be one of show, fade or hide.')
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('breadcrumbs')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('child_admin_route')
                            ->defaultValue('edit')
                            ->info('Change the default route used to generate the link to the parent object, when in a child admin')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('html5_validate')->defaultTrue()->end()
                        ->booleanNode('sort_admins')->defaultFalse()->info('Auto order groups and admins by label or id')->end()
                        ->booleanNode('confirm_exit')->defaultTrue()->end()
                        ->booleanNode('use_select2')->defaultTrue()->end()
                        ->booleanNode('use_icheck')->defaultTrue()->end()
                        ->booleanNode('use_bootlint')->defaultFalse()->end()
                        ->booleanNode('use_stickyforms')->defaultTrue()->end()
                        ->integerNode('pager_links')->defaultNull()->end()
                        ->scalarNode('form_type')->defaultValue('standard')->end()
                        ->integerNode('dropdown_number_groups_per_colums')->defaultValue(2)->end()
                        ->enumNode('title_mode')
                            ->values(['single_text', 'single_image', 'both'])
                            ->defaultValue('both')
                            ->cannotBeEmpty()
                        ->end()
                        ->booleanNode('lock_protection')
                            ->defaultFalse()
                            ->info('Enable locking when editing an object, if the corresponding object manager supports it.')
                        ->end()
                        ->booleanNode('enable_jms_di_extra_autoregistration') // NEXT_MAJOR: remove this option
                            ->defaultTrue()
                            ->info('Enable automatic registration of annotations with JMSDiExtraBundle')
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
                                ->beforeNormalization()
                                    ->ifArray()
                                    ->then(function ($items) {
                                        if (isset($items['provider'])) {
                                            $disallowedItems = ['items', 'label'];
                                            foreach ($disallowedItems as $item) {
                                                if (isset($items[$item])) {
                                                    throw new \InvalidArgumentException(sprintf('The config value "%s" cannot be used alongside "provider" config value', $item));
                                                }
                                            }
                                        }

                                        return $items;
                                    })
                                ->end()
                                ->fixXmlConfig('item')
                                ->fixXmlConfig('item_add')
                                ->children()
                                    ->scalarNode('label')->end()
                                    ->scalarNode('label_catalogue')->end()
                                    ->scalarNode('icon')->defaultValue('<i class="fa fa-folder"></i>')->end()
                                    ->scalarNode('on_top')->defaultFalse()->info('Show menu item in side dashboard menu without treeview')->end()
                                    ->scalarNode('keep_open')->defaultFalse()->info('Keep menu group always open')->end()
                                    ->scalarNode('provider')->end()
                                    ->arrayNode('items')
                                        ->beforeNormalization()
                                            ->ifArray()
                                            ->then(function ($items) {
                                                foreach ($items as $key => $item) {
                                                    if (\is_array($item)) {
                                                        if (!array_key_exists('label', $item) || !array_key_exists('route', $item)) {
                                                            throw new \InvalidArgumentException('Expected either parameters "route" and "label" for array items');
                                                        }

                                                        if (!array_key_exists('route_params', $item)) {
                                                            $items[$key]['route_params'] = [];
                                                        }

                                                        $items[$key]['admin'] = '';
                                                    } else {
                                                        $items[$key] = [
                                                            'admin' => $item,
                                                            'label' => '',
                                                            'route' => '',
                                                            'route_params' => [],
                                                            'route_absolute' => false,
                                                        ];
                                                    }
                                                }

                                                return $items;
                                            })
                                        ->end()
                                        ->prototype('array')
                                            ->children()
                                                ->scalarNode('admin')->end()
                                                ->scalarNode('label')->end()
                                                ->scalarNode('route')->end()
                                                ->arrayNode('roles')
                                                    ->prototype('scalar')
                                                        ->info('Roles which will see the route in the menu')
                                                        ->defaultValue([])
                                                    ->end()
                                                ->end()
                                                ->arrayNode('route_params')
                                                    ->prototype('scalar')->end()
                                                ->end()
                                                ->booleanNode('route_absolute')
                                                    ->info('Whether the generated url should be absolute')
                                                    ->defaultFalse()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('item_adds')
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->arrayNode('roles')
                                        ->prototype('scalar')->defaultValue([])->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('blocks')
                            ->defaultValue([[
                                'position' => 'left',
                                'settings' => [],
                                'type' => 'sonata.admin.block.admin_list',
                                'roles' => [],
                            ]])
                            ->prototype('array')
                                ->fixXmlConfig('setting')
                                ->children()
                                    ->scalarNode('type')->cannotBeEmpty()->end()
                                    ->arrayNode('roles')
                                        ->defaultValue([])
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->arrayNode('settings')
                                        ->useAttributeAsKey('id')
                                        ->prototype('variable')->defaultValue([])->end()
                                    ->end()
                                    ->scalarNode('position')->defaultValue('right')->end()
                                    ->scalarNode('class')->defaultValue('col-md-4')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('admin_services')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('model_manager')->defaultNull()->end()
                            ->scalarNode('form_contractor')->defaultNull()->end()
                            ->scalarNode('show_builder')->defaultNull()->end()
                            ->scalarNode('list_builder')->defaultNull()->end()
                            ->scalarNode('datagrid_builder')->defaultNull()->end()
                            ->scalarNode('translator')->defaultNull()->end()
                            ->scalarNode('configuration_pool')->defaultNull()->end()
                            ->scalarNode('route_generator')->defaultNull()->end()
                            ->scalarNode('validator')->defaultNull()->end()
                            ->scalarNode('security_handler')->defaultNull()->end()
                            ->scalarNode('label')->defaultNull()->end()
                            ->scalarNode('menu_factory')->defaultNull()->end()
                            ->scalarNode('route_builder')->defaultNull()->end()
                            ->scalarNode('label_translator_strategy')->defaultNull()->end()
                            ->scalarNode('pager_type')->defaultNull()->end()
                            ->arrayNode('templates')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->arrayNode('form')
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->arrayNode('filter')
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->arrayNode('view')
                                        ->useAttributeAsKey('id')
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('user_block')->defaultValue('@SonataAdmin/Core/user_block.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('add_block')->defaultValue('@SonataAdmin/Core/add_block.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('layout')->defaultValue('@SonataAdmin/standard_layout.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('ajax')->defaultValue('@SonataAdmin/ajax_layout.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('dashboard')->defaultValue('@SonataAdmin/Core/dashboard.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('search')->defaultValue('@SonataAdmin/Core/search.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('list')->defaultValue('@SonataAdmin/CRUD/list.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('filter')->defaultValue('@SonataAdmin/Form/filter_admin_fields.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('show')->defaultValue('@SonataAdmin/CRUD/show.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('show_compare')->defaultValue('@SonataAdmin/CRUD/show_compare.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('edit')->defaultValue('@SonataAdmin/CRUD/edit.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('preview')->defaultValue('@SonataAdmin/CRUD/preview.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('history')->defaultValue('@SonataAdmin/CRUD/history.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('acl')->defaultValue('@SonataAdmin/CRUD/acl.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('history_revision_timestamp')->defaultValue('@SonataAdmin/CRUD/history_revision_timestamp.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('action')->defaultValue('@SonataAdmin/CRUD/action.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('select')->defaultValue('@SonataAdmin/CRUD/list__select.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('list_block')->defaultValue('@SonataAdmin/Block/block_admin_list.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('search_result_block')->defaultValue('@SonataAdmin/Block/block_search_result.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('short_object_description')->defaultValue('@SonataAdmin/Helper/short-object-description.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('delete')->defaultValue('@SonataAdmin/CRUD/delete.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('batch')->defaultValue('@SonataAdmin/CRUD/list__batch.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('batch_confirmation')->defaultValue('@SonataAdmin/CRUD/batch_confirmation.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('inner_list_row')->defaultValue('@SonataAdmin/CRUD/list_inner_row.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('outer_list_rows_mosaic')->defaultValue('@SonataAdmin/CRUD/list_outer_rows_mosaic.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('outer_list_rows_list')->defaultValue('@SonataAdmin/CRUD/list_outer_rows_list.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('outer_list_rows_tree')->defaultValue('@SonataAdmin/CRUD/list_outer_rows_tree.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('base_list_field')->defaultValue('@SonataAdmin/CRUD/base_list_field.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('pager_links')->defaultValue('@SonataAdmin/Pager/links.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('pager_results')->defaultValue('@SonataAdmin/Pager/results.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('tab_menu_template')->defaultValue('@SonataAdmin/Core/tab_menu_template.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('knp_menu_template')->defaultValue('@SonataAdmin/Menu/sonata_menu.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('action_create')->defaultValue('@SonataAdmin/CRUD/dashboard__action_create.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('button_acl')->defaultValue('@SonataAdmin/Button/acl_button.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('button_create')->defaultValue('@SonataAdmin/Button/create_button.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('button_edit')->defaultValue('@SonataAdmin/Button/edit_button.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('button_history')->defaultValue('@SonataAdmin/Button/history_button.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('button_list')->defaultValue('@SonataAdmin/Button/list_button.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('button_show')->defaultValue('@SonataAdmin/Button/show_button.html.twig')->cannotBeEmpty()->end()
                    ->end()
                ->end()

                ->arrayNode('assets')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('stylesheets')
                            ->defaultValue([
                                'bundles/sonatacore/vendor/bootstrap/dist/css/bootstrap.min.css',
                                'bundles/sonatacore/vendor/components-font-awesome/css/font-awesome.min.css',
                                'bundles/sonatacore/vendor/ionicons/css/ionicons.min.css',
                                'bundles/sonataadmin/vendor/admin-lte/dist/css/AdminLTE.min.css',
                                'bundles/sonataadmin/vendor/admin-lte/dist/css/skins/skin-black.min.css',
                                'bundles/sonataadmin/vendor/iCheck/skins/square/blue.css',

                                'bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',

                                'bundles/sonataadmin/vendor/jqueryui/themes/base/jquery-ui.css',

                                'bundles/sonatacore/vendor/select2/select2.css',
                                'bundles/sonatacore/vendor/select2-bootstrap-css/select2-bootstrap.min.css',

                                'bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css',

                                'bundles/sonataadmin/css/styles.css',
                                'bundles/sonataadmin/css/layout.css',
                                'bundles/sonataadmin/css/tree.css',
                            ])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('extra_stylesheets')
                            ->info('stylesheets to add to the page')
                            ->defaultValue([])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('remove_stylesheets')
                            ->info('stylesheets to remove from the page')
                            ->defaultValue([])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('javascripts')
                            ->defaultValue([
                                'bundles/sonatacore/vendor/jquery/dist/jquery.min.js',
                                'bundles/sonataadmin/vendor/jquery.scrollTo/jquery.scrollTo.min.js',

                                'bundles/sonataadmin/vendor/jqueryui/ui/minified/jquery-ui.min.js',
                                'bundles/sonataadmin/vendor/jqueryui/ui/minified/i18n/jquery-ui-i18n.min.js',

                                'bundles/sonatacore/vendor/moment/min/moment.min.js',

                                'bundles/sonatacore/vendor/bootstrap/dist/js/bootstrap.min.js',

                                'bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',

                                'bundles/sonataadmin/vendor/jquery-form/jquery.form.js',
                                'bundles/sonataadmin/jquery/jquery.confirmExit.js',

                                'bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js',

                                'bundles/sonatacore/vendor/select2/select2.min.js',

                                'bundles/sonataadmin/vendor/admin-lte/dist/js/app.min.js',
                                'bundles/sonataadmin/vendor/iCheck/icheck.min.js',
                                'bundles/sonataadmin/vendor/slimScroll/jquery.slimscroll.min.js',
                                'bundles/sonataadmin/vendor/waypoints/lib/jquery.waypoints.min.js',
                                'bundles/sonataadmin/vendor/waypoints/lib/shortcuts/sticky.min.js',
                                'bundles/sonataadmin/vendor/readmore-js/readmore.min.js',

                                'bundles/sonataadmin/vendor/masonry/dist/masonry.pkgd.min.js',

                                'bundles/sonataadmin/Admin.js',
                                'bundles/sonataadmin/treeview.js',
                                'bundles/sonataadmin/sidebar.js',
                            ])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('extra_javascripts')
                            ->info('javascripts to add to the page')
                            ->defaultValue([])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('remove_javascripts')
                            ->info('javascripts to remove from the page')
                            ->defaultValue([])
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('extensions')
                ->useAttributeAsKey('id')
                ->defaultValue(['admins' => [], 'excludes' => [], 'implements' => [], 'extends' => [], 'instanceof' => [], 'uses' => []])
                    ->prototype('array')
                        ->fixXmlConfig('admin')
                        ->fixXmlConfig('exclude')
                        ->fixXmlConfig('implement')
                        ->fixXmlConfig('extend')
                        ->fixXmlConfig('use')
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
                            ->arrayNode('uses')
                                ->prototype('scalar')->end()
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return !empty($v) && version_compare(PHP_VERSION, '5.4.0', '<');
                                    })
                                    ->thenInvalid('PHP >= 5.4.0 is required to use traits.')
                                ->end()
                            ->end()
                            ->integerNode('priority')
                                ->info('Positive or negative integer. The higher the priority, the earlier it’s executed.')
                                ->defaultValue(0)
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->scalarNode('persist_filters')->defaultFalse()->end()
                ->scalarNode('filter_persister')->defaultValue('sonata.admin.filter_persister.session')->end()

                ->booleanNode('show_mosaic_button')
                    ->defaultTrue()
                    ->info('Show mosaic button on all admin screens')
                ->end()

                // NEXT_MAJOR : remove this option
                ->booleanNode('translate_group_label')
                    ->defaultFalse()
                    ->info('Translate group label')
                ->end()

            ->end()
        ->end();

        return $treeBuilder;
    }
}
