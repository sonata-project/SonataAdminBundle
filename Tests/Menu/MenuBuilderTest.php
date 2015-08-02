<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Menu;

use Knp\Menu\MenuFactory;
use Sonata\AdminBundle\Menu\MenuBuilder;

class MenuBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $pool;
    private $provider;
    private $factory;
    private $eventDispatcher;
    /**
     * @var MenuBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock();
        $this->provider = $this->getMock('Knp\Menu\Provider\MenuProviderInterface');
        $this->factory = new MenuFactory();
        $this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->builder = new MenuBuilder($this->pool, $this->factory, $this->provider, $this->eventDispatcher);
    }

    public function testGetKnpMenuWithDefaultProvider()
    {
        $adminGroups = array(
            'bar' => array(
                'icon'            => '<i class="fa fa-edit"></i>',
                'label_catalogue' => '',
                'roles'           => array(),
            ),
        );

        $this->provider
            ->expects($this->once())
            ->method('get')
            ->with('sonata_group_menu')
            ->will($this->returnValue($this->factory->createItem('bar')->addChild('foo')->getParent()));

        $this->preparePool($adminGroups);
        $menu = $this->builder->createSidebarMenu();

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $menu);
        $this->assertArrayHasKey('bar', $menu->getChildren());

        foreach ($menu->getChildren() as $key => $child) {
            $this->assertInstanceOf('Knp\Menu\MenuItem', $child);
            $this->assertSame('bar', $child->getName());
            $this->assertSame('bar', $child->getLabel());

            // menu items
            $children = $child->getChildren();
            $this->assertCount(1, $children);
            $this->assertArrayHasKey('foo', $children);
            $this->assertInstanceOf('Knp\Menu\MenuItem', $child['foo']);
            $this->assertSame('foo', $child['foo']->getLabel());
        }
    }

    public function testGetKnpMenuWithSpecifiedProvider()
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
            ->will($this->returnValue($this->factory->createItem('bar')->addChild('foo')->getParent()));

        $this->preparePool($adminGroups);
        $menu = $this->builder->createSidebarMenu();

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $menu);
        $this->assertArrayHasKey('bar', $menu->getChildren());

        foreach ($menu->getChildren() as $key => $child) {
            $this->assertInstanceOf('Knp\Menu\MenuItem', $child);
            $this->assertSame('bar', $child->getName());
            $this->assertSame('bar', $child->getLabel());

            // menu items
            $children = $child->getChildren();
            $this->assertCount(1, $children);
            $this->assertArrayHasKey('foo', $children);
            $this->assertInstanceOf('Knp\Menu\MenuItem', $child['foo']);
            $this->assertSame('foo', $child['foo']->getLabel());
        }
    }

    public function testGetKnpMenuAndDispatchEvent()
    {
        $adminGroups = array(
            'bar' => array(
                'label'           => 'foo',
                'icon'            => '<i class="fa fa-edit"></i>',
                'label_catalogue' => 'SonataAdminBundle',
                'items'           => array(),
                'item_adds'       => array(),
                'roles'           => array(),
            ),
        );

        $this->preparePool($adminGroups);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('sonata.admin.event.configure.menu.sidebar'),
                $this->isInstanceOf('Sonata\AdminBundle\Event\ConfigureMenuEvent')
            );

        $this->builder->createSidebarMenu();
    }

    private function preparePool($adminGroups, $admin = null)
    {
        $this->pool->expects($this->once())
            ->method('getAdminGroups')
            ->will($this->returnValue($adminGroups));

        if (null !== $admin) {
            $this->pool->expects($this->once())
                ->method('getInstance')
                ->with($this->equalTo('sonata_admin_foo_service'))
                ->will($this->returnValue($admin));
        }
    }
}
