<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use Knp\Menu\MenuFactory;
use Sonata\AdminBundle\Menu\MenuBuilder;

class MenuBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $pool;
    private $provider;
    private $factory;
    private $eventDispatcher;
    private $builder;

    protected function setUp()
    {
        $this->pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock();
        $this->provider = $this->getMock('Knp\Menu\Provider\MenuProviderInterface');
        $this->factory = new MenuFactory();
        $this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->builder = new MenuBuilder($this->pool, $this->factory, $this->provider, $this->eventDispatcher);
    }

    public function testGetKnpMenu()
    {
        $adminGroups = array(
            'bar' => array(
                'label'            => 'foo',
                'icon'             => '<i class="fa fa-edit"></i>',
                'label_catalogue'  => 'SonataAdminBundle',
                'items'            => array(
                    array(
                        'admin'        => '',
                        'label'        => 'fooLabel',
                        'route'        => 'FooRoute',
                        'route_params' => array('foo' => 'bar'),
                    ),
                ),
                'item_adds' => array(),
                'roles'     => array(),

            ),
        );

        $this->preparePool($adminGroups);

        $menu = $this->builder->createSidebarMenu();

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $menu);
        $this->assertArrayHasKey('bar', $menu->getChildren());

        foreach ($menu->getChildren() as $key => $child) {
            $this->assertInstanceOf('Knp\Menu\MenuItem', $child);
            $this->assertEquals('bar', $child->getName());
            $this->assertEquals($adminGroups['bar']['label'], $child->getLabel());

            // menu items
            $children = $child->getChildren();
            $this->assertCount(1, $children);
            $this->assertArrayHasKey('fooLabel', $children);
            $this->assertInstanceOf('Knp\Menu\MenuItem', $child['fooLabel']);
            $this->assertEquals('fooLabel', $child['fooLabel']->getLabel());
        }
    }

    public function testGetKnpMenuWithAdmin()
    {
        $adminGroups = array(
            'bar' => array(
                'label'            => 'foo',
                'icon'             => '<i class="fa fa-edit"></i>',
                'label_catalogue'  => 'SonataAdminBundle',
                'items'            => array(
                    array(
                        'admin'        => 'sonata_admin_foo_service',
                        'label'        => 'fooLabel',
                    ),
                ),
                'item_adds' => array(),
                'roles'     => array(),
            ),
        );

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())
            ->method('hasRoute')
            ->with($this->equalTo('list'))
            ->will($this->returnValue(true))
        ;

        $admin->expects($this->any())
            ->method('isGranted')
            ->with($this->equalTo('LIST'))
            ->will($this->returnValue(true))
        ;

        $admin->expects($this->once())
            ->method('getLabel')
            ->will($this->returnValue('foo_admin_label'))
        ;

        $admin->expects($this->once())
            ->method('generateMenuUrl')
            ->will($this->returnValue(array()))
        ;

        $this->preparePool($adminGroups, $admin);
        $menu = $this->builder->createSidebarMenu();

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $menu);
        $this->assertArrayHasKey('bar', $menu->getChildren());

        foreach ($menu->getChildren() as $key => $child) {
            $this->assertInstanceOf('Knp\Menu\MenuItem', $child);
            $this->assertEquals('bar', $child->getName());
            $this->assertEquals($adminGroups['bar']['label'], $child->getLabel());

            // menu items
            $children = $child->getChildren();
            $this->assertCount(1, $children);
            $this->assertArrayHasKey('foo_admin_label', $children);
            $this->assertInstanceOf('Knp\Menu\MenuItem', $child['foo_admin_label']);
            $this->assertEquals('foo_admin_label', $child['foo_admin_label']->getLabel());
        }
    }

    public function testGetKnpMenuWithNoListRoute()
    {
        $adminGroups = array(
            'bar' => array(
                'label'            => 'foo',
                'icon'             => '<i class="fa fa-edit"></i>',
                'label_catalogue'  => 'SonataAdminBundle',
                'items'            => array(
                    array(
                        'admin'        => 'sonata_admin_foo_service',
                        'label'        => 'fooLabel',
                    ),
                ),
                'item_adds' => array(),
                'roles'     => array(),
            ),
        );

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())
            ->method('hasRoute')
            ->with($this->equalTo('list'))
            ->will($this->returnValue(false))
        ;

        $this->preparePool($adminGroups, $admin);
        $menu = $this->builder->createSidebarMenu();

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $menu);
        $this->assertArrayNotHasKey('bar', $menu->getChildren());
        $this->assertCount(0, $menu->getChildren());
    }

    public function testGetKnpMenuWithNotGrantedList()
    {
        $adminGroups = array(
            'bar' => array(
                'label'            => 'foo',
                'icon'             => '<i class="fa fa-edit"></i>',
                'label_catalogue'  => 'SonataAdminBundle',
                'items'            => array(
                    array(
                        'admin'        => 'sonata_admin_foo_service',
                        'label'        => 'fooLabel',
                    ),
                ),
                'item_adds' => array(),
                'roles'     => array(),
            ),
        );

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())
            ->method('hasRoute')
            ->with($this->equalTo('list'))
            ->will($this->returnValue(true))
        ;

        $admin->expects($this->any())
            ->method('isGranted')
            ->with($this->equalTo('LIST'))
            ->will($this->returnValue(false))
        ;

        $this->preparePool($adminGroups, $admin);
        $menu = $this->builder->createSidebarMenu();

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $menu);
        $this->assertArrayNotHasKey('bar', $menu->getChildren());
        $this->assertCount(0, $menu->getChildren());
    }

    public function testGetKnpMenuWithProvider()
    {
        $adminGroups = array(
            'bar' => array(
                'provider'        => 'my_menu',
                'label_catalogue' => '',
                'icon'            => '<i class="fa fa-edit"></i>',
                'roles'           => array(),
            ),
        );

        $this->provider
            ->expects($this->once())
            ->method('get')
            ->with('my_menu')
            ->will($this->returnValue($this->factory->createItem('bar')->addChild('foo')->getParent()))
        ;

        $this->preparePool($adminGroups);
        $menu = $this->builder->createSidebarMenu();

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $menu);
        $this->assertArrayHasKey('bar', $menu->getChildren());

        foreach ($menu->getChildren() as $key => $child) {
            $this->assertInstanceOf('Knp\Menu\MenuItem', $child);
            $this->assertEquals('bar', $child->getName());
            $this->assertEquals('bar', $child->getLabel());

            // menu items
            $children = $child->getChildren();
            $this->assertCount(1, $children);
            $this->assertArrayHasKey('foo', $children);
            $this->assertInstanceOf('Knp\Menu\MenuItem', $child['foo']);
            $this->assertEquals('foo', $child['foo']->getLabel());
        }
    }

    public function testGetKnpMenuAndDispatchEvent()
    {
        $adminGroups = array(
            'bar' => array(
                'label'            => 'foo',
                'icon'             => '<i class="fa fa-edit"></i>',
                'label_catalogue'  => 'SonataAdminBundle',
                'items'            => array(),
                'item_adds'        => array(),
                'roles'            => array(),
            ),
        );

        $this->preparePool($adminGroups);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo('sonata.admin.event.configure.menu.sidebar'), $this->isInstanceOf('Sonata\AdminBundle\Event\ConfigureMenuEvent'))
        ;

        $this->builder->createSidebarMenu();
    }

    private function preparePool($adminGroups, $admin = null)
    {
        $this->pool->expects($this->once())
            ->method('getAdminGroups')
            ->will($this->returnValue($adminGroups))
        ;

        if (null !== $admin) {
            $this->pool->expects($this->once())
                ->method('getInstance')
                ->with($this->equalTo('sonata_admin_foo_service'))
                ->will($this->returnValue($admin))
            ;
        }
    }
}
