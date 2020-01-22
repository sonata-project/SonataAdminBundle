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

namespace Sonata\AdminBundle\Tests\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use Knp\Menu\Provider\MenuProviderInterface;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Event\ConfigureMenuEvent;
use Sonata\AdminBundle\Menu\MenuBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MenuBuilderTest extends TestCase
{
    private $pool;
    private $provider;
    private $factory;
    private $eventDispatcher;
    /**
     * @var MenuBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        $this->pool = $this->getMockBuilder(Pool::class)->disableOriginalConstructor()->getMock();
        $this->provider = $this->getMockForAbstractClass(MenuProviderInterface::class);
        $this->factory = new MenuFactory();
        $this->eventDispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);

        $this->builder = new MenuBuilder($this->pool, $this->factory, $this->provider, $this->eventDispatcher);
    }

    public function testGetKnpMenuWithDefaultProvider(): void
    {
        $adminGroups = [
            'bar' => [
                'icon' => '<i class="fa fa-edit"></i>',
                'label_catalogue' => '',
                'roles' => [],
            ],
        ];

        $this->provider
            ->expects($this->once())
            ->method('get')
            ->with('sonata_group_menu')
            ->willReturn($this->factory->createItem('bar')->addChild('foo')->getParent());

        $this->preparePool($adminGroups);
        $menu = $this->builder->createSidebarMenu();

        $this->assertInstanceOf(ItemInterface::class, $menu);
        $this->assertArrayHasKey('bar', $menu->getChildren());

        foreach ($menu->getChildren() as $key => $child) {
            $this->assertInstanceOf(MenuItem::class, $child);
            $this->assertSame('bar', $child->getName());
            $this->assertSame('bar', $child->getLabel());

            // menu items
            $children = $child->getChildren();
            $this->assertCount(1, $children);
            $this->assertArrayHasKey('foo', $children);
            $this->assertInstanceOf(MenuItem::class, $child['foo']);
            $this->assertSame('foo', $child['foo']->getLabel());
        }
    }

    public function testGetKnpMenuWithSpecifiedProvider(): void
    {
        $adminGroups = [
            'bar' => [
                'provider' => 'my_menu',
                'label_catalogue' => '',
                'icon' => '<i class="fa fa-edit"></i>',
                'roles' => [],
            ],
        ];

        $this->provider
            ->expects($this->once())
            ->method('get')
            ->with('my_menu')
            ->willReturn($this->factory->createItem('bar')->addChild('foo')->getParent());

        $this->preparePool($adminGroups);
        $menu = $this->builder->createSidebarMenu();

        $this->assertInstanceOf(ItemInterface::class, $menu);
        $this->assertArrayHasKey('bar', $menu->getChildren());

        foreach ($menu->getChildren() as $key => $child) {
            $this->assertInstanceOf(MenuItem::class, $child);
            $this->assertSame('bar', $child->getName());
            $this->assertSame('bar', $child->getLabel());

            // menu items
            $children = $child->getChildren();
            $this->assertCount(1, $children);
            $this->assertArrayHasKey('foo', $children);
            $this->assertInstanceOf(MenuItem::class, $child['foo']);
            $this->assertSame('foo', $child['foo']->getLabel());
        }
    }

    public function testGetKnpMenuAndDispatchEvent(): void
    {
        $adminGroups = [
            'bar' => [
                'label' => 'foo',
                'icon' => '<i class="fa fa-edit"></i>',
                'label_catalogue' => 'SonataAdminBundle',
                'items' => [],
                'item_adds' => [],
                'roles' => [],
            ],
        ];

        $this->preparePool($adminGroups);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(ConfigureMenuEvent::class),
                $this->equalTo('sonata.admin.event.configure.menu.sidebar')
            );

        $this->builder->createSidebarMenu();
    }

    private function preparePool(array $adminGroups, ?AdminInterface $admin = null): void
    {
        $this->pool->expects($this->once())
            ->method('getAdminGroups')
            ->willReturn($adminGroups);

        if (null !== $admin) {
            $this->pool->expects($this->once())
                ->method('getInstance')
                ->with($this->equalTo('sonata_admin_foo_service'))
                ->willReturn($admin);
        }
    }
}
