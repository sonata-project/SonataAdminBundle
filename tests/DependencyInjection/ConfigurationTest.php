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

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testOptions()
    {
        $config = $this->process([]);

        $this->assertTrue($config['options']['html5_validate']);
        $this->assertNull($config['options']['pager_links']);
        $this->assertTrue($config['options']['confirm_exit']);
        $this->assertFalse($config['options']['js_debug']);
        $this->assertTrue($config['options']['use_icheck']);
        $this->assertSame('default', $config['options']['default_group']);
        $this->assertSame('SonataAdminBundle', $config['options']['default_label_catalogue']);
        $this->assertSame('<i class="fa fa-folder"></i>', $config['options']['default_icon']);
    }

    public function testBreadcrumbsChildRouteDefaultsToEdit()
    {
        $config = $this->process([]);

        $this->assertSame('edit', $config['breadcrumbs']['child_admin_route']);
    }

    public function testOptionsWithInvalidFormat()
    {
        $this->expectException(InvalidTypeException::class);

        $this->process([[
            'options' => [
                'html5_validate' => '1',
            ],
        ]]);
    }

    public function testCustomTemplatesPerAdmin()
    {
        $config = $this->process([[
            'admin_services' => [
                'my_admin_id' => [
                    'templates' => [
                        'form' => ['form.twig.html', 'form_extra.twig.html'],
                        'view' => ['user_block' => '@SonataAdmin/mycustomtemplate.html.twig'],
                        'filter' => [],
                    ],
                ],
            ],
        ]]);

        $this->assertSame('@SonataAdmin/mycustomtemplate.html.twig', $config['admin_services']['my_admin_id']['templates']['view']['user_block']);
    }

    public function testAdminServicesDefault()
    {
        $config = $this->process([[
            'admin_services' => ['my_admin_id' => []],
        ]]);

        $this->assertSame([
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
            'templates' => [
                'form' => [],
                'filter' => [],
                'view' => [],
            ],
        ], $config['admin_services']['my_admin_id']);
    }

    public function testDashboardWithoutRoles()
    {
        $config = $this->process([]);

        $this->assertEmpty($config['dashboard']['blocks'][0]['roles']);
    }

    public function testDashboardWithRoles()
    {
        $config = $this->process([[
            'dashboard' => [
                'blocks' => [[
                    'roles' => ['ROLE_ADMIN'],
                    'type' => 'my.type',
                ]],
            ],
        ]]);

        $this->assertSame($config['dashboard']['blocks'][0]['roles'], ['ROLE_ADMIN']);
    }

    public function testDashboardGroups()
    {
        $config = $this->process([[
            'dashboard' => [
                'groups' => [
                    'bar' => [
                        'label' => 'foo',
                        'icon' => '<i class="fa fa-edit"></i>',
                        'items' => [
                            'item1',
                            'item2',
                            [
                                'label' => 'fooLabel',
                                'route' => 'fooRoute',
                                'route_params' => ['bar' => 'foo'],
                                'route_absolute' => true,
                            ],
                            [
                                'label' => 'barLabel',
                                'route' => 'barRoute',
                            ],
                        ],
                    ],
                ],
            ],
        ]]);

        $this->assertCount(4, $config['dashboard']['groups']['bar']['items']);
        $this->assertSame(
            $config['dashboard']['groups']['bar']['items'][0],
            [
                'admin' => 'item1',
                'label' => '',
                'route' => '',
                'route_params' => [],
                'route_absolute' => false,
                'roles' => [],
            ]
        );
        $this->assertSame(
            $config['dashboard']['groups']['bar']['items'][1],
            [
                'admin' => 'item2',
                'label' => '',
                'route' => '',
                'route_params' => [],
                'route_absolute' => false,
                'roles' => [],
            ]
        );
        $this->assertSame(
            $config['dashboard']['groups']['bar']['items'][2],
            [
                'label' => 'fooLabel',
                'route' => 'fooRoute',
                'route_params' => ['bar' => 'foo'],
                'route_absolute' => true,
                'admin' => '',
                'roles' => [],
            ]
        );
        $this->assertSame(
            $config['dashboard']['groups']['bar']['items'][3],
            [
                'label' => 'barLabel',
                'route' => 'barRoute',
                'route_params' => [],
                'admin' => '',
                'roles' => [],
                'route_absolute' => false,
            ]
        );
    }

    public function testDashboardGroupsWithBadItemsParams()
    {
        $this->expectException(\InvalidArgumentException::class, 'Expected either parameters "route" and "label" for array items');

        $this->process([[
            'dashboard' => [
                'groups' => [
                    'bar' => [
                        'label' => 'foo',
                        'icon' => '<i class="fa fa-edit"></i>',
                        'items' => [
                            'item1',
                            'item2',
                            [
                                'route' => 'fooRoute',
                            ],
                        ],
                    ],
                ],
            ],
        ]]);
    }

    public function testSecurityConfigurationDefaults()
    {
        $config = $this->process([[]]);

        $this->assertSame('ROLE_SONATA_ADMIN', $config['security']['role_admin']);
        $this->assertSame('ROLE_SUPER_ADMIN', $config['security']['role_super_admin']);
    }

    public function testExtraAssetsDefaults()
    {
        $config = $this->process([[]]);

        $this->assertSame([], $config['assets']['extra_stylesheets']);
        $this->assertSame([], $config['assets']['extra_javascripts']);
    }

    public function testRemoveAssetsDefaults()
    {
        $config = $this->process([[]]);

        $this->assertSame([], $config['assets']['remove_stylesheets']);
        $this->assertSame([], $config['assets']['remove_javascripts']);
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
}
