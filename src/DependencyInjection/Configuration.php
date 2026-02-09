<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\DependencyInjection;

use SensioLabs\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use SensioLabs\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle.
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Michael Williams <mtotheikle@gmail.com>
 *
 * @phpstan-import-type ExtensionMap from ExtensionCompilerPass
 *
 * @phpstan-type SonataAdminConfigurationOptions = array{
 *     confirm_exit: bool,
 *     default_admin_route: string,
 *     default_icon: string,
 *     default_translation_domain: string,
 *     form_type: 'standard'|'horizontal',
 *     html5_validate: bool,
 *     js_debug: bool,
 *     list_action_button_content: 'text'|'icon'|'all',
 *     lock_protection: bool,
 *     logo_content: 'text'|'icon'|'all',
 *     mosaic_background: string,
 *     pager_links: int|null,
 *     use_select2: bool,
 *     use_stickyforms: bool,
 * }
 * @phpstan-type SonataAdminAsset = array{
 *     path: string,
 *     package_name: string|null,
 * }
 * @phpstan-type SonataAdminConfiguration = array{
 *     assets: array{
 *         extra_javascripts: list<SonataAdminAsset>,
 *         extra_stylesheets: list<SonataAdminAsset>,
 *         javascripts: list<SonataAdminAsset>,
 *         remove_javascripts: list<SonataAdminAsset>,
 *         remove_stylesheets: list<SonataAdminAsset>,
 *         stylesheets: list<SonataAdminAsset>,
 *     },
 *     breadcrumbs: array{
 *         child_admin_route: string,
 *     },
 *     default_admin_services: array{
 *         configuration_pool: string|null,
 *         datagrid_builder: string|null,
 *         data_source: string|null,
 *         field_description_factory: string|null,
 *         form_contractor: string|null,
 *         label_translator_strategy: string|null,
 *         list_builder: string|null,
 *         menu_factory: string|null,
 *         model_manager: string|null,
 *         pager_type: string|null,
 *         route_builder: string|null,
 *         route_generator: string|null,
 *         security_handler: string|null,
 *         show_builder: string|null,
 *         translator: string|null,
 *     },
 *     default_controller: string,
 *     extensions: array<string, ExtensionMap>,
 *     filter_persister: string,
 *     options: SonataAdminConfigurationOptions,
 *     persist_filters: bool,
 *     security: array{
 *         acl_user_manager: string|null,
 *         admin_permissions: list<string>,
 *         information: array<string, list<string>>,
 *         object_permissions: list<string>,
 *         handler: string,
 *         role_admin: string,
 *         role_super_admin: string,
 *     },
 *     show_mosaic_button: bool,
 *     templates: array{
 *         acl: string,
 *         action: string,
 *         action_create: string,
 *         add_block: string,
 *         ajax: string,
 *         base_list_field: string,
 *         batch: string,
 *         batch_confirmation: string,
 *         button_acl: string,
 *         button_create: string,
 *         button_edit: string,
 *         button_history: string,
 *         button_list: string,
 *         button_show: string,
 *         dashboard: string,
 *         delete: string,
 *         edit: string,
 *         filter: string,
 *         filter_theme: list<string>,
 *         form_theme: list<string>,
 *         history: string,
 *         history_revision_timestamp: string,
 *         inner_list_row: string,
 *         knp_menu_template: string,
 *         layout: string,
 *         list: string,
 *         list_block: string,
 *         outer_list_rows_list: string,
 *         outer_list_rows_mosaic: string,
 *         outer_list_rows_tree: string,
 *         pager_links: string,
 *         pager_results: string,
 *         preview: string,
 *         select: string,
 *         short_object_description: string,
 *         show: string,
 *         show_compare: string,
 *         tab_menu_template: string,
 *         user_block: string,
 *     },
 *     title: string,
 *     title_logo: string,
 * }
 */
final class Configuration implements ConfigurationInterface
{
    private const DEFAULT_PACKAGE = 'sensiolabs_admin';

    /**
     * @return TreeBuilder<'array'>
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sensiolabs_admin');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->fixXmlConfig('option')
            ->fixXmlConfig('default_admin_service')
            ->fixXmlConfig('template')
            ->fixXmlConfig('extension')
            ->children()
                ->arrayNode('security')
                    ->addDefaultsIfNotSet()
                    ->fixXmlConfig('admin_permission')
                    ->fixXmlConfig('object_permission')
                    ->children()
                        ->scalarNode('handler')->defaultValue('sensiolabs.admin.security.handler.noop')->end()
                        ->arrayNode('information')
                            ->useAttributeAsKey('id')
                            ->prototype('array')
                                ->performNoDeepMerging()
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(static fn (string $value): array => [$value])
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
                            ->info('Role which will perform all admin actions, see dashboard and menu groups regardless of its configuration')
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

                ->scalarNode('title')->defaultValue('SensioLabs Admin')->cannotBeEmpty()->end()
                ->scalarNode('title_logo')->defaultValue('bundles/sonataadmin/images/logo_title.png')->cannotBeEmpty()->end()

                ->scalarNode('default_controller')
                    ->defaultValue('sensiolabs.admin.controller.crud')
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
                        ->booleanNode('confirm_exit')->defaultTrue()->end()
                        ->booleanNode('js_debug')->defaultFalse()->end()
                        ->booleanNode('use_select2')->defaultTrue()->end()
                        ->booleanNode('use_stickyforms')->defaultTrue()->end()
                        ->integerNode('pager_links')->defaultNull()->end()
                        ->enumNode('form_type')
                            ->defaultValue('standard')
                            ->values(['standard', 'horizontal'])
                        ->end()
                        ->scalarNode('default_admin_route')
                            ->defaultValue('show')
                            ->info('Name of the admin route to be used as a default to generate the link to the object')
                        ->end()
                        ->scalarNode('default_translation_domain')
                            ->defaultValue('messages')
                            ->info('Translation domain used for admin services if one isn\'t provided.')
                        ->end()
                        ->scalarNode('default_icon')
                            ->defaultValue('lucide:folder')
                            ->info('Icon used for admin services if one isn\'t provided.')
                        ->end()
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
                        ->scalarNode('user_block')->defaultValue('@SensioLabsAdmin/Core/user_block.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('add_block')->defaultValue('@SensioLabsAdmin/Core/add_block.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('layout')->defaultValue('@SensioLabsAdmin/standard_layout.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('ajax')->defaultValue('@SensioLabsAdmin/ajax_layout.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('dashboard')->defaultValue('@SensioLabsAdmin/Core/dashboard.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('list')->defaultValue('@SensioLabsAdmin/CRUD/list.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('filter')->defaultValue('@SensioLabsAdmin/Form/filter_admin_fields.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('show')->defaultValue('@SensioLabsAdmin/CRUD/show.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('show_compare')->defaultValue('@SensioLabsAdmin/CRUD/show_compare.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('edit')->defaultValue('@SensioLabsAdmin/CRUD/edit.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('preview')->defaultValue('@SensioLabsAdmin/CRUD/preview.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('history')->defaultValue('@SensioLabsAdmin/CRUD/history.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('acl')->defaultValue('@SensioLabsAdmin/CRUD/acl.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('history_revision_timestamp')->defaultValue('@SensioLabsAdmin/CRUD/history_revision_timestamp.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('action')->defaultValue('@SensioLabsAdmin/CRUD/action.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('select')->defaultValue('@SensioLabsAdmin/CRUD/list__select.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('short_object_description')->defaultValue('@SensioLabsAdmin/Helper/short-object-description.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('delete')->defaultValue('@SensioLabsAdmin/CRUD/delete.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('batch')->defaultValue('@SensioLabsAdmin/CRUD/list__batch.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('batch_confirmation')->defaultValue('@SensioLabsAdmin/CRUD/batch_confirmation.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('inner_list_row')->defaultValue('@SensioLabsAdmin/CRUD/list_inner_row.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('outer_list_rows_mosaic')->defaultValue('@SensioLabsAdmin/CRUD/list_outer_rows_mosaic.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('outer_list_rows_list')->defaultValue('@SensioLabsAdmin/CRUD/list_outer_rows_list.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('outer_list_rows_tree')->defaultValue('@SensioLabsAdmin/CRUD/list_outer_rows_tree.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('base_list_field')->defaultValue('@SensioLabsAdmin/CRUD/base_list_field.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('pager_links')->defaultValue('@SensioLabsAdmin/Pager/links.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('pager_results')->defaultValue('@SensioLabsAdmin/Pager/results.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('tab_menu_template')->defaultValue('@SensioLabsAdmin/Core/tab_menu_template.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('action_create')->defaultValue('@SensioLabsAdmin/CRUD/dashboard__action_create.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('button_acl')->defaultValue('@SensioLabsAdmin/Button/acl_button.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('button_create')->defaultValue('@SensioLabsAdmin/Button/create_button.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('button_edit')->defaultValue('@SensioLabsAdmin/Button/edit_button.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('button_history')->defaultValue('@SensioLabsAdmin/Button/history_button.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('button_list')->defaultValue('@SensioLabsAdmin/Button/list_button.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('button_show')->defaultValue('@SensioLabsAdmin/Button/show_button.html.twig')->cannotBeEmpty()->end()
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
                            ->beforeNormalization()
                                ->always(static fn (array $value) => self::normalizeAssetList($value, 'stylesheets'))
                            ->end()
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('path')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('package_name')->defaultValue(self::DEFAULT_PACKAGE)->end()
                                ->end()
                            ->end()
                            ->defaultValue([])
                        ->end()
                        ->arrayNode('extra_stylesheets')
                            ->info('stylesheets to add to the page')
                            ->beforeNormalization()
                                ->always(static fn (array $value) => self::normalizeAssetList($value, 'extra_stylesheets'))
                            ->end()
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('path')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('package_name')->defaultValue(self::DEFAULT_PACKAGE)->end()
                                ->end()
                            ->end()
                            ->defaultValue([])
                        ->end()
                        ->arrayNode('remove_stylesheets')
                            ->info('stylesheets to remove from the page')
                            ->beforeNormalization()
                                ->always(static fn (array $value) => self::normalizeAssetList($value, 'extra_javascripts'))
                            ->end()
                            ->defaultValue([])
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('path')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('package_name')->defaultValue(self::DEFAULT_PACKAGE)->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('javascripts')
                            ->beforeNormalization()
                                ->always(static fn (array $value) => self::normalizeAssetList($value, 'javascripts'))
                            ->end()
                            ->defaultValue([])
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('path')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('package_name')->defaultValue(self::DEFAULT_PACKAGE)->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('extra_javascripts')
                            ->info('javascripts to add to the page')
                            ->beforeNormalization()
                                ->always(static fn (array $value) => self::normalizeAssetList($value, 'extra_javascripts'))
                            ->end()
                            ->defaultValue([])
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('path')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('package_name')->defaultValue(self::DEFAULT_PACKAGE)->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('remove_javascripts')
                            ->info('javascripts to remove from the page')
                            ->beforeNormalization()
                                ->always(static fn (array $value) => self::normalizeAssetList($value, 'extra_javascripts'))
                            ->end()
                            ->defaultValue([])
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('path')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('package_name')->defaultValue(self::DEFAULT_PACKAGE)->end()
                                ->end()
                            ->end()
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
                            ->arrayNode('admin_implements')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('admin_extends')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('admin_instanceof')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('admin_uses')
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
                ->scalarNode('filter_persister')->defaultValue('sensiolabs.admin.filter_persister.session')->end()

                ->booleanNode('show_mosaic_button')
                    ->defaultTrue()
                    ->info('Show mosaic button on all admin screens')
                ->end()

                ->arrayNode('user')
                    ->canBeEnabled()
                    ->children()
                        ->arrayNode('class')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('user')->defaultValue('App\\Entity\\User')->cannotBeEmpty()->end()
                                ->scalarNode('group')->defaultValue('App\\Entity\\Group')->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                        ->arrayNode('admin')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('user')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('class')->defaultValue('SensioLabs\\AdminBundle\\User\\Admin\\UserAdmin')->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue('sensiolabs.admin.controller.crud')->cannotBeEmpty()->end()
                                        ->scalarNode('translation_domain')->defaultValue('SensioLabsAdminBundle')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('group')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('class')->defaultValue('SensioLabs\\AdminBundle\\User\\Admin\\GroupAdmin')->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue('sensiolabs.admin.controller.crud')->cannotBeEmpty()->end()
                                        ->scalarNode('translation_domain')->defaultValue('SensioLabsAdminBundle')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('impersonating')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('route')->defaultNull()->end()
                                ->arrayNode('parameters')
                                    ->useAttributeAsKey('id')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('resetting')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('ttl')->defaultValue(86400)->end()
                                ->scalarNode('from_email')->defaultValue('noreply@example.com')->cannotBeEmpty()->end()
                                ->scalarNode('email_template')->defaultValue('@SensioLabsAdmin/User/Email/reset_password.html.twig')->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                        ->arrayNode('profile')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('default_avatar')->defaultValue('bundles/sensiolabsadmin/default_avatar.png')->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

    /**
     * Normalizes an asset list node to an array of items with shape:
     *   [ ['path' => string, 'package_name' => string], ... ]
     * Supports input elements as string or associative array {path: ..., package_name: ...}.
     *
     * @param array<mixed> $value
     *
     * @return list<SonataAdminAsset>
     */
    private static function normalizeAssetList(mixed $value, string $nodeName): array
    {
        if (null === $value) {
            return [];
        }

        if (!\is_array($value)) {
            throw new \InvalidArgumentException(\sprintf('The "%s" node must be an array.', $nodeName));
        }

        return array_values(array_map(static fn (mixed $item) => self::normalizeAssetItem($item, $nodeName), $value));
    }

    /**
     * @return SonataAdminAsset
     */
    private static function normalizeAssetItem(mixed $item, string $nodeName): array
    {
        // 1) Simple string form
        if (\is_string($item)) {
            return [
                'path' => $item,
                'package_name' => self::DEFAULT_PACKAGE,
            ];
        }

        // 2) Associative form: {path: ..., package_name: ...}
        if (\is_array($item)) {
            if (!\array_key_exists('path', $item) || !\array_key_exists('package_name', $item)) {
                throw new \InvalidArgumentException(\sprintf('The "%s" item with array form must contain the "path" and the "package_name" keys.', $nodeName));
            }

            if (null === $item['path']) {
                throw new \InvalidArgumentException(\sprintf('The "path" key of the "%s" item can not be null.', $nodeName));
            }

            return [
                'path' => (string) $item['path'],
                'package_name' => null === $item['package_name'] ? null : (string) $item['package_name'],
            ];
        }

        throw new \InvalidArgumentException(\sprintf('Invalid "%s" item type. String or associative array are allowed.', $nodeName));
    }

    /**
     * Helper to build normalized defaults from a list of asset strings.
     *
     * @param array<int, string> $assets
     *
     * @return list<SonataAdminAsset>
     */
    private static function normalizeDefaultAssets(array $assets): array
    {
        return array_values(
            array_map(static fn (string $asset) => [
                'path' => $asset,
                'package_name' => self::DEFAULT_PACKAGE,
            ], $assets)
        );
    }
}
