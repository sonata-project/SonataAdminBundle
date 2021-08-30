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

use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
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
final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress PossiblyNullReference, PossiblyUndefinedMethod
     *
     * @see https://github.com/psalm/psalm-plugin-symfony/issues/174
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sonata_admin');
        $rootNode = $treeBuilder->getRootNode();

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
                                    ->then(static function (string $value): array {
                                        return [$value];
                                    })
                                ->end()
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                        ->arrayNode('admin_permissions')
                            ->defaultValue([
                                AdminPermissionMap::PERMISSION_CREATE,
                                AdminPermissionMap::PERMISSION_LIST,
                                AdminPermissionMap::PERMISSION_DELETE,
                                AdminPermissionMap::PERMISSION_UNDELETE,
                                AdminPermissionMap::PERMISSION_EXPORT,
                                AdminPermissionMap::PERMISSION_OPERATOR,
                                AdminPermissionMap::PERMISSION_MASTER,
                            ])
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
                            ->defaultValue([
                                AdminPermissionMap::PERMISSION_VIEW,
                                AdminPermissionMap::PERMISSION_EDIT,
                                AdminPermissionMap::PERMISSION_HISTORY,
                                AdminPermissionMap::PERMISSION_DELETE,
                                AdminPermissionMap::PERMISSION_UNDELETE,
                                AdminPermissionMap::PERMISSION_OPERATOR,
                                AdminPermissionMap::PERMISSION_MASTER,
                                AdminPermissionMap::PERMISSION_OWNER,
                            ])
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('acl_user_manager')->defaultNull()->end()
                    ->end()
                ->end()

                ->scalarNode('title')->defaultValue('Sonata Admin')->cannotBeEmpty()->end()
                ->scalarNode('title_logo')->defaultValue('bundles/sonataadmin/images/logo_title.png')->cannotBeEmpty()->end()
                ->booleanNode('search')->defaultTrue()->info('Enable/disable the search form in the sidebar')->end()

                ->arrayNode('global_search')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('empty_boxes')
                            ->defaultValue('show')
                            ->info('Perhaps one of the three options: show, fade, hide.')
                            ->validate()
                                ->ifTrue(static function (string $v): bool {
                                    return !\in_array($v, ['show', 'fade', 'hide'], true);
                                })
                                ->thenInvalid('Configuration value of "global_search.empty_boxes" must be one of show, fade or hide.')
                            ->end()
                        ->end()
                        ->scalarNode('admin_route')
                            ->defaultValue('show')
                            ->info('Change the default route used to generate the link to the object')
                        ->end()
                    ->end()
                ->end()

                ->scalarNode('default_controller')
                    ->defaultValue('sonata.admin.controller.crud')
                    ->cannotBeEmpty()
                    ->info('Name of the controller class to be used as a default in admin definitions')
                ->end()

                ->arrayNode('breadcrumbs')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('child_admin_route')
                            ->defaultValue('show')
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
                        ->enumNode('skin')
                            ->defaultValue('skin-black')
                            ->values([
                                'skin-black',
                                'skin-black-light',
                                'skin-blue',
                                'skin-blue-light',
                                'skin-green',
                                'skin-green-light',
                                'skin-purple',
                                'skin-purple-light',
                                'skin-red',
                                'skin-red-light',
                                'skin-yellow',
                                'skin-yellow-light',
                            ])
                        ->end()
                        ->booleanNode('use_select2')->defaultTrue()->end()
                        ->booleanNode('use_icheck')->defaultTrue()->end()
                        ->booleanNode('use_bootlint')->defaultFalse()->end()
                        ->booleanNode('use_stickyforms')->defaultTrue()->end()
                        ->integerNode('pager_links')->defaultNull()->end()
                        ->scalarNode('form_type')->defaultValue('standard')->end()
                        ->scalarNode('default_admin_route')
                            ->defaultValue('show')
                            ->info('Name of the admin route to be used as a default to generate the link to the object')
                        ->end()
                        ->scalarNode('default_group')
                            ->defaultValue('default')
                            ->info('Group used for admin services if one isn\'t provided.')
                        ->end()
                        ->scalarNode('default_label_catalogue')
                            ->defaultValue('SonataAdminBundle')
                            ->info('Label Catalogue used for admin services if one isn\'t provided.')
                        ->end()
                        ->scalarNode('default_icon')
                            ->defaultValue('fas fa-folder')
                            ->info('Icon used for admin services if one isn\'t provided.')
                        ->end()
                        ->integerNode('dropdown_number_groups_per_colums')->defaultValue(2)->end()
                        ->enumNode('logo_content')
                            ->values(['text', 'icon', 'all'])
                            ->defaultValue('all')
                            ->cannotBeEmpty()
                        ->end()
                        ->enumNode('list_action_button_content')
                            ->values(['text', 'icon', 'all'])
                            ->defaultValue('all')
                            ->cannotBeEmpty()
                        ->end()
                        ->booleanNode('lock_protection')
                            ->defaultFalse()
                            ->info('Enable locking when editing an object, if the corresponding object manager supports it.')
                        ->end()
                        ->scalarNode('mosaic_background')
                            ->defaultValue('bundles/sonataadmin/images/default_mosaic_image.png')
                            ->info('Background used in mosaic view')
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
                                    ->then(static function (array $items): array {
                                        if (isset($items['provider'])) {
                                            $disallowedItems = ['items', 'label'];
                                            foreach ($disallowedItems as $item) {
                                                if (isset($items[$item])) {
                                                    throw new \InvalidArgumentException(sprintf(
                                                        'The config value "%s" cannot be used alongside "provider" config value',
                                                        $item
                                                    ));
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
                                    ->scalarNode('icon')->end()
                                    ->scalarNode('on_top')->defaultFalse()->info('Show menu item in side dashboard menu without treeview')->end()
                                    ->scalarNode('keep_open')->defaultFalse()->info('Keep menu group always open')->end()
                                    ->scalarNode('provider')->end()
                                    ->arrayNode('items')
                                        ->beforeNormalization()
                                            ->ifArray()
                                            ->then(static function (array $items): array {
                                                foreach ($items as $key => $item) {
                                                    if (!\is_array($item)) {
                                                        $item = ['admin' => $item];
                                                        $items[$key] = $item;

                                                        continue;
                                                    }

                                                    if (!isset($item['route'])) {
                                                        throw new \InvalidArgumentException('Expected parameter "route" for array items');
                                                    }

                                                    if (!isset($item['label'])) {
                                                        throw new \InvalidArgumentException('Expected parameter "label" for array items');
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
                                                    ->defaultValue([])
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
                ->arrayNode('default_admin_services')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('model_manager')->defaultNull()->end()
                        ->scalarNode('data_source')->defaultNull()->end()
                        ->scalarNode('field_description_factory')->defaultNull()->end()
                        ->scalarNode('form_contractor')->defaultNull()->end()
                        ->scalarNode('show_builder')->defaultNull()->end()
                        ->scalarNode('list_builder')->defaultNull()->end()
                        ->scalarNode('datagrid_builder')->defaultNull()->end()
                        ->scalarNode('translator')->defaultNull()->end()
                        ->scalarNode('configuration_pool')->defaultNull()->end()
                        ->scalarNode('route_generator')->defaultNull()->end()
                        ->scalarNode('security_handler')->defaultNull()->end()
                        ->scalarNode('menu_factory')->defaultNull()->end()
                        ->scalarNode('route_builder')->defaultNull()->end()
                        ->scalarNode('label_translator_strategy')->defaultNull()->end()
                        ->scalarNode('pager_type')->defaultNull()->end()
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
                        ->arrayNode('form_theme')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('filter_theme')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('assets')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('stylesheets')
                            ->defaultValue([
                                'bundles/sonataadmin/app.css',
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
                                'bundles/sonataadmin/app.js',
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
                ->defaultValue([])
                    ->prototype('array')
                        ->fixXmlConfig('admin')
                        ->fixXmlConfig('exclude')
                        ->fixXmlConfig('implement')
                        ->fixXmlConfig('extend')
                        ->fixXmlConfig('use')
                        ->children()
                            ->booleanNode('global')->defaultValue(false)->end()
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
            ->end()
        ->end();

        return $treeBuilder;
    }
}
