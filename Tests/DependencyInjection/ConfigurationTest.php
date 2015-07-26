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

use Sonata\AdminBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testOptions()
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), array());

        $this->assertTrue($config['options']['html5_validate']);
        $this->assertNull($config['options']['pager_links']);
        $this->assertTrue($config['options']['confirm_exit']);
        $this->assertTrue($config['options']['use_icheck']);
    }

    public function testOptionsWithInvalidFormat()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidTypeException');

        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), array(array(
            'options' => array(
                'html5_validate' => '1',
            ),
        )));
    }

    public function testCustomTemplatesPerAdmin()
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), array(array(
            'admin_services' => array(
                'my_admin_id' => array(
                    'templates' => array(
                        'form'   => array('form.twig.html', 'form_extra.twig.html'),
                        'view'   => array('user_block' => 'SonataAdminBundle:mycustomtemplate.html.twig'),
                        'filter' => array(),
                    ),
                ),
            ),
        )));

        $this->assertEquals('SonataAdminBundle:mycustomtemplate.html.twig', $config['admin_services']['my_admin_id']['templates']['view']['user_block']);
    }

    public function testAdminServicesDefault()
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), array(array(
            'admin_services' => array('my_admin_id' => array()),
        )));

        $this->assertEquals(array(
            'model_manager'             => null,
            'form_contractor'           => null,
            'show_builder'              => null,
            'list_builder'              => null,
            'datagrid_builder'          => null,
            'translator'                => null,
            'configuration_pool'        => null,
            'validator'                 => null,
            'security_handler'          => null,
            'label'                     => null,
            'templates'                 => array(),
            'route_generator'           => null,
            'menu_factory'              => null,
            'route_builder'             => null,
            'label_translator_strategy' => null,
            'pager_type'                => null,
            'templates'                 => array(
                'form'   => array(),
                'filter' => array(),
                'view'   => array(),
            ),
        ), $config['admin_services']['my_admin_id']);
    }

    public function testDashboardWithoutRoles()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), array());

        $this->assertEmpty($config['dashboard']['blocks'][0]['roles']);
    }

    public function testDashboardWithRoles()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), array(array(
            'dashboard' => array(
                'blocks' => array(array(
                    'roles' => array('ROLE_ADMIN'),
                    'type'  => 'my.type',
                )),
            ),
        )));

        $this->assertEquals($config['dashboard']['blocks'][0]['roles'], array('ROLE_ADMIN'));
    }

    public function testDashboardGroups()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), array(array(
            'dashboard' => array(
                'groups' => array(
                    'bar' => array(
                        'label' => 'foo',
                        'icon'  => '<i class="fa fa-edit"></i>',
                        'items' => array(
                            'item1',
                            'item2',
                            array(
                                'label'        => 'fooLabel',
                                'route'        => 'fooRoute',
                                'route_params' => array('bar' => 'foo'),
                            ),
                            array(
                                'label' => 'barLabel',
                                'route' => 'barRoute',
                            ),
                        ),
                    ),
                ),
            ),
        )));

        $this->assertCount(4, $config['dashboard']['groups']['bar']['items']);
        $this->assertEquals(
            $config['dashboard']['groups']['bar']['items'][0],
            array(
                'admin'        => 'item1',
                'route'        => '',
                'route_params' => array(),
                'label'        => '',
            )
        );
        $this->assertEquals(
            $config['dashboard']['groups']['bar']['items'][1],
            array(
                'admin'        => 'item2',
                'route'        => '',
                'route_params' => array(),
                'label'        => '',
            )
        );
        $this->assertEquals(
            $config['dashboard']['groups']['bar']['items'][2],
            array(
                'admin'        => '',
                'route'        => 'fooRoute',
                'route_params' => array('bar' => 'foo'),
                'label'        => 'fooLabel',
            )
        );
        $this->assertEquals(
            $config['dashboard']['groups']['bar']['items'][3],
            array(
                'admin'        => '',
                'route'        => 'barRoute',
                'route_params' => array(),
                'label'        => 'barLabel',
            )
        );
    }

    public function testDashboardGroupsWithBadItemsParams()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Expected either parameters "route" and "label" for array items');

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), array(array(
            'dashboard' => array(
                'groups' => array(
                    'bar' => array(
                        'label' => 'foo',
                        'icon'  => '<i class="fa fa-edit"></i>',
                        'items' => array(
                            'item1',
                            'item2',
                            array(
                                'route' => 'fooRoute',
                            ),
                        ),
                    ),
                ),
            ),
        )));
    }
}
