<?php

declare(strict_types=1);

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
 * @final since sonata-project/admin-bundle 3.52
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
        $treeBuilder = new TreeBuilder('sonata_admin');

        // Keep compatibility with symfony/config < 4.2
        if (!method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->root('sonata_admin');
        } else {
            $rootNode = $treeBuilder->getRootNode();
        }

        $caseSensitiveInfo = <<<'CASESENSITIVE'
Whether the global search should behave case sensitive or not.
Using case-insensitivity might lead to performance issues.

See https://use-the-index-luke.com/sql/where-clause/functions/case-insensitive-search
for more information.
CASESENSITIVE;

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
                                    ->then(static function ($v) {
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
                                ->ifTrue(static function ($v) {
                                    return !\in_array($v, ['show', 'fade', 'hide'], true);
                                })
                                ->thenInvalid('Configuration value of "global_search.empty_boxes" must be one of show, fade or hide.')
                            ->end()
                        ->end()
                        ->booleanNode('case_sensitive')
                            ->defaultTrue()
                            ->info($caseSensitiveInfo)
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
                        ->booleanNode('js_debug')->defaultFalse()->end()
                        ->booleanNode('use_select2')->defaultTrue()->end()
                        ->booleanNode('use_icheck')->defaultTrue()->end()
                        ->booleanNode('use_bootlint')->defaultFalse()->end()
                        ->booleanNode('use_stickyforms')->defaultTrue()->end()
                        ->integerNode('pager_links')->defaultNull()->end()
                        ->scalarNode('form_type')->defaultValue('standard')->end()
                        ->scalarNode('default_group')
                            ->defaultValue('default')
                            ->info("Group used for admin services if one isn't provided.")
                        ->end()
                        ->scalarNode('default_label_catalogue')
                            ->defaultValue('SonataAdminBundle')
                            ->info("Label Catalogue used for admin services if one isn't provided.")
                        ->end()
                        ->scalarNode('default_icon')
                            ->defaultValue('<i class="fa fa-folder"></i>')
                            ->info("Icon used for admin services if one isn't provided.")
                        ->end()
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
                        ->scalarNode('mosaic_background')
                            ->defaultValue('data:image/png;base64,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')
                            ->info('Background used in mosaic view')
                        ->end()
                        // NEXT_MAJOR : remove this option
                        ->booleanNode('legacy_twig_text_extension')
                            ->info('Use text filters from "twig/extensions" instead of those provided by "twig/string-extra".')
                            ->defaultValue(static function (): bool {
                                @trigger_error(
                                    'Using `true` as value for "sonata.admin.configuration.legacy_twig_text_extension" option is deprecated since sonata-project/admin-bundle 3.64.'.
                                    'You should set it to `false`, which will be the default value since version 4.0.'
                                );

                                return true;
                            })
                            ->validate()
                                ->ifTrue()
                                ->then(static function (bool $v): bool {
                                    @trigger_error(
                                        'Using `true` as value for "sonata.admin.configuration.legacy_twig_text_extension" option is deprecated since sonata-project/admin-bundle 3.64.'.
                                        'You should set it to `false`, which will be the default value since version 4.0.'
                                    );

                                    return $v;
                                })
                            ->end()
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
                                    ->then(static function ($items) {
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
                                            ->then(static function ($items) {
                                                foreach ($items as $key => $item) {
                                                    if (\is_array($item)) {
                                                        if (!\array_key_exists('label', $item) || !\array_key_exists('route', $item)) {
                                                            throw new \InvalidArgumentException('Expected either parameters "route" and "label" for array items');
                                                        }

                                                        if (!\array_key_exists('route_params', $item)) {
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

                                'bundles/sonatacore/css/flashmessage.css',
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

                                'bundles/sonatacore/js/base.js',
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
