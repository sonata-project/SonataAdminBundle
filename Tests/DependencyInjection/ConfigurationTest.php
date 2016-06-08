<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;
use Sonata\AdminBundle\DependencyInjection\Configuration;
use Sonata\AdminBundle\DependencyInjection\SonataAdminExtension;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends AbstractExtensionConfigurationTestCase
{
    public function testEmpty()
    {
        $this->assertProcessedConfigurationEquals($this->getDefaultExpectedConfiguration(), array(
            __DIR__.'/../Fixtures/config/empty.yml',
        ));
    }

    public function testOptionsWithInvalidFormat()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidTypeException');

        $this->assertProcessedConfigurationEquals(array(), array(
            __DIR__.'/../Fixtures/config/options_invalid_format.yml',
        ));
    }

    public function testAdminServices()
    {
        $expectedConfiguration = array_replace_recursive($this->getDefaultExpectedConfiguration(), array(
            'admin_services' => array(
                'my_admin_id' => array(
                    'model_manager' => null,
                    'form_contractor' => null,
                    'show_builder' => null,
                    'list_builder' => null,
                    'datagrid_builder' => null,
                    'translator' => null,
                    'configuration_pool' => null,
                    'route_generator' => null,
                    'validator' => null,
                    'security_handler' => null,
                    'label' => null,
                    'menu_factory' => null,
                    'route_builder' => null,
                    'label_translator_strategy' => null,
                    'pager_type' => null,
                    'templates' => array(
                        'form' => array('form.twig.html', 'form_extra.twig.html'),
                        'view' => array('user_block' => 'SonataAdminBundle:mycustomtemplate.html.twig'),
                        'filter' => array(),
                    ),
                ),
            ),
        ));

        $this->assertProcessedConfigurationEquals($expectedConfiguration, array(
            __DIR__.'/../Fixtures/config/admin_services.yml',
        ));
    }

    public function testDashboardRoles()
    {
        $expectedConfiguration = array_replace_recursive($this->getDefaultExpectedConfiguration(), array(
            'dashboard' => array(
                'blocks' => array(
                    array(
                        'position' => 'right',
                        'roles' => array('ROLE_ADMIN'),
                        'type' => 'my.type',
                        'settings' => array(),
                        'class' => 'col-md-4',
                    ),
                ),
            ),
        ));

        $this->assertProcessedConfigurationEquals($expectedConfiguration, array(
            __DIR__.'/../Fixtures/config/dashboard_roles.yml',
        ));
    }

    public function testDashboardGroups()
    {
        $expectedConfiguration = array_replace_recursive($this->getDefaultExpectedConfiguration(), array(
            'dashboard' => array(
                'groups' => array(
                    'bar' => array(
                        'label' => 'foo',
                        'icon' => '<i class="fa fa-edit"></i>',
                        'on_top' => false,
                        'item_adds' => array(),
                        'roles' => array(),
                        'items' => array(
                            array(
                                'admin' => 'item1',
                                'label' => '',
                                'route' => '',
                                'route_params' => array(),
                                'route_absolute' => true,
                            ),
                            array(
                                'admin' => 'item2',
                                'label' => '',
                                'route' => '',
                                'route_params' => array(),
                                'route_absolute' => true,
                            ),
                            array(
                                'admin' => '',
                                'label' => 'fooLabel',
                                'route' => 'fooRoute',
                                'route_params' => array('bar' => 'foo'),
                                'route_absolute' => true,
                            ),
                            array(
                                'admin' => '',
                                'label' => 'barLabel',
                                'route' => 'barRoute',
                                'route_params' => array(),
                                'route_absolute' => true,
                            ),
                        ),
                    ),
                ),
            ),
        ));

        $this->assertProcessedConfigurationEquals($expectedConfiguration, array(
            __DIR__.'/../Fixtures/config/dashboard_groups.yml',
        ));
    }

    public function testDashboardGroupsWithBadItemsParams()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Expected either parameters "route" and "label" for array items');

        $this->assertProcessedConfigurationEquals(array(), array(
            __DIR__.'/../Fixtures/config/dashboard_groups_with_bad_items_params.yml',
        ));
    }

    /**
     * Processes an array of configurations and returns a compiled version.
     *
     * @param array $configs An array of raw configurations
     *
     * @return array A normalized array
     */
    protected function process($configs)
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtension()
    {
        return new SonataAdminExtension();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration();
    }

    private function getDefaultExpectedConfiguration()
    {
        return array(
            'security' => array(
                'handler' => 'sonata.admin.security.handler.noop',
                'information' => array(),
                'admin_permissions' => array(
                    'CREATE',
                    'LIST',
                    'DELETE',
                    'UNDELETE',
                    'EXPORT',
                    'OPERATOR',
                    'MASTER',
                ),
                'object_permissions' => array(
                    'VIEW',
                    'EDIT',
                    'DELETE',
                    'UNDELETE',
                    'OPERATOR',
                    'MASTER',
                    'OWNER',
                ),
                'acl_user_manager' => null,
            ),
            'title' => 'Sonata Admin',
            'title_logo' => 'bundles/sonataadmin/logo_title.png',
            'dashboard' => array(
                'groups' => array(),
                'blocks' => array(
                    array(
                        'position' => 'left',
                        'settings' => array(),
                        'type' => 'sonata.admin.block.admin_list',
                        'roles' => array(),
                    ),
                ),
            ),
            'admin_services' => array(),
            'templates' => array(
                'user_block' => 'SonataAdminBundle:Core:user_block.html.twig',
                'add_block' => 'SonataAdminBundle:Core:add_block.html.twig',
                'layout' => 'SonataAdminBundle::standard_layout.html.twig',
                'ajax' => 'SonataAdminBundle::ajax_layout.html.twig',
                'dashboard' => 'SonataAdminBundle:Core:dashboard.html.twig',
                'search' => 'SonataAdminBundle:Core:search.html.twig',
                'list' => 'SonataAdminBundle:CRUD:list.html.twig',
                'filter' => 'SonataAdminBundle:Form:filter_admin_fields.html.twig',
                'show' => 'SonataAdminBundle:CRUD:show.html.twig',
                'show_compare' => 'SonataAdminBundle:CRUD:show_compare.html.twig',
                'edit' => 'SonataAdminBundle:CRUD:edit.html.twig',
                'preview' => 'SonataAdminBundle:CRUD:preview.html.twig',
                'history' => 'SonataAdminBundle:CRUD:history.html.twig',
                'acl' => 'SonataAdminBundle:CRUD:acl.html.twig',
                'history_revision_timestamp' => 'SonataAdminBundle:CRUD:history_revision_timestamp.html.twig',
                'action' => 'SonataAdminBundle:CRUD:action.html.twig',
                'select' => 'SonataAdminBundle:CRUD:list__select.html.twig',
                'list_block' => 'SonataAdminBundle:Block:block_admin_list.html.twig',
                'search_result_block' => 'SonataAdminBundle:Block:block_search_result.html.twig',
                'short_object_description' => 'SonataAdminBundle:Helper:short-object-description.html.twig',
                'delete' => 'SonataAdminBundle:CRUD:delete.html.twig',
                'batch' => 'SonataAdminBundle:CRUD:list__batch.html.twig',
                'batch_confirmation' => 'SonataAdminBundle:CRUD:batch_confirmation.html.twig',
                'inner_list_row' => 'SonataAdminBundle:CRUD:list_inner_row.html.twig',
                'outer_list_rows_mosaic' => 'SonataAdminBundle:CRUD:list_outer_rows_mosaic.html.twig',
                'outer_list_rows_list' => 'SonataAdminBundle:CRUD:list_outer_rows_list.html.twig',
                'outer_list_rows_tree' => 'SonataAdminBundle:CRUD:list_outer_rows_tree.html.twig',
                'base_list_field' => 'SonataAdminBundle:CRUD:base_list_field.html.twig',
                'pager_links' => 'SonataAdminBundle:Pager:links.html.twig',
                'pager_results' => 'SonataAdminBundle:Pager:results.html.twig',
                'tab_menu_template' => 'SonataAdminBundle:Core:tab_menu_template.html.twig',
                'knp_menu_template' => 'SonataAdminBundle:Menu:sonata_menu.html.twig',
            ),
            'assets' => array(
                'stylesheets' => array(
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
                ),
                'javascripts' => array(
                    'bundles/sonatacore/vendor/jquery/dist/jquery.min.js',
                    'bundles/sonataadmin/vendor/jquery.scrollTo/jquery.scrollTo.min.js',
                    'bundles/sonatacore/vendor/moment/min/moment.min.js',
                    'bundles/sonataadmin/vendor/jqueryui/ui/minified/jquery-ui.min.js',
                    'bundles/sonataadmin/vendor/jqueryui/ui/minified/i18n/jquery-ui-i18n.min.js',
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
                    'bundles/sonataadmin/Admin.js',
                    'bundles/sonataadmin/treeview.js',
                ),
            ),
            'extensions' => array(
                'admins' => array(),
                'excludes' => array(),
                'implements' => array(),
                'extends' => array(),
                'instanceof' => array(),
                'uses' => array(),
            ),
            'persist_filters' => false,
            'show_mosaic_button' => true,
            'options' => array(
                'html5_validate' => true,
                'pager_links' => null,
                'confirm_exit' => true,
                'use_icheck' => true,
                'sort_admins' => false,
                'use_select2' => true,
                'use_bootlint' => false,
                'use_stickyforms' => true,
                'form_type' => 'standard',
                'dropdown_number_groups_per_colums' => 2,
                'title_mode' => 'both',
                'lock_protection' => false,
            ),
        );
    }
}
