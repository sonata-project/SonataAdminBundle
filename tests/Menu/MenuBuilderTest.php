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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Event\ConfigureMenuEvent;
use Sonata\AdminBundle\Menu\MenuBuilder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class MenuBuilderTest extends TestCase
{
    /**
     * @var MenuProviderInterface&MockObject
     */
    private $provider;

    /**
     * @var MenuFactory
     */
    private $factory;

    /**
     * @var MockObject&EventDispatcherInterface
     */
    private $eventDispatcher;

    protected function setUp(): void
    {
        $this->provider = $this->getMockForAbstractClass(MenuProviderInterface::class);
        $this->factory = new MenuFactory();
        $this->eventDispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
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
            ->expects(static::once())
            ->method('get')
            ->with('sonata_group_menu')
            ->willReturn($this->factory->createItem('bar')->addChild('foo')->getParent());

        $builder = $this->createMenuBuilder($adminGroups);
        $menu = $builder->createSidebarMenu();

        static::assertInstanceOf(ItemInterface::class, $menu);
        static::assertArrayHasKey('bar', $menu->getChildren());

        foreach ($menu->getChildren() as $key => $child) {
            static::assertInstanceOf(MenuItem::class, $child);
            static::assertSame('bar', $child->getName());
            static::assertSame('bar', $child->getLabel());

            // menu items
            $children = $child->getChildren();
            static::assertCount(1, $children);
            static::assertArrayHasKey('foo', $children);
            static::assertInstanceOf(MenuItem::class, $child['foo']);
            static::assertSame('foo', $child['foo']->getLabel());
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
            ->expects(static::once())
            ->method('get')
            ->with('my_menu')
            ->willReturn($this->factory->createItem('bar')->addChild('foo')->getParent());

        $builder = $this->createMenuBuilder($adminGroups);
        $menu = $builder->createSidebarMenu();

        static::assertInstanceOf(ItemInterface::class, $menu);
        static::assertArrayHasKey('bar', $menu->getChildren());

        foreach ($menu->getChildren() as $key => $child) {
            static::assertInstanceOf(MenuItem::class, $child);
            static::assertSame('bar', $child->getName());
            static::assertSame('bar', $child->getLabel());

            // menu items
            $children = $child->getChildren();
            static::assertCount(1, $children);
            static::assertArrayHasKey('foo', $children);
            static::assertInstanceOf(MenuItem::class, $child['foo']);
            static::assertSame('foo', $child['foo']->getLabel());
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

        $builder = $this->createMenuBuilder($adminGroups);

        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                static::isInstanceOf(ConfigureMenuEvent::class),
                static::equalTo('sonata.admin.event.configure.menu.sidebar')
            );

        $this->provider
            ->expects(static::once())
            ->method('get')
            ->with('sonata_group_menu')
            ->willReturn($this->factory->createItem('bar'));

        $builder->createSidebarMenu();
    }

    private function createMenuBuilder(array $adminGroups): MenuBuilder
    {
        return new MenuBuilder(new Pool(new Container(), [], $adminGroups), $this->factory, $this->provider, $this->eventDispatcher);
    }
}
