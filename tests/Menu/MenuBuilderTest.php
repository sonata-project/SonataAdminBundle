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

/**
 * @phpstan-import-type Group from \Sonata\AdminBundle\Admin\Pool
 */
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
        $this->provider = $this->createMock(MenuProviderInterface::class);
        $this->factory = new MenuFactory();
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    public function testGetKnpMenuWithDefaultProvider(): void
    {
        $adminGroups = [
            'bar' => [
                'label' => '',
                'icon' => '<i class="fas fa-edit"></i>',
                'label_catalogue' => '',
                'roles' => [],
                'item_adds' => [],
                'items' => [],
                'keep_open' => false,
                'on_top' => false,
            ],
        ];

        $this->provider
            ->expects(self::once())
            ->method('get')
            ->with('sonata_group_menu')
            ->willReturn($this->factory->createItem('bar')->addChild('foo')->getParent());

        $builder = $this->createMenuBuilder($adminGroups);
        $menu = $builder->createSidebarMenu();

        self::assertInstanceOf(ItemInterface::class, $menu);
        self::assertArrayHasKey('bar', $menu->getChildren());

        foreach ($menu->getChildren() as $key => $child) {
            self::assertInstanceOf(MenuItem::class, $child);
            self::assertSame('bar', $child->getName());
            self::assertSame('bar', $child->getLabel());

            // menu items
            $children = $child->getChildren();
            self::assertCount(1, $children);
            self::assertArrayHasKey('foo', $children);
            self::assertInstanceOf(MenuItem::class, $child['foo']);
            self::assertSame('foo', $child['foo']->getLabel());
        }
    }

    public function testGetKnpMenuWithSpecifiedProvider(): void
    {
        $adminGroups = [
            'bar' => [
                'label' => '',
                'provider' => 'my_menu',
                'label_catalogue' => '',
                'icon' => '<i class="fas fa-edit"></i>',
                'roles' => [],
                'item_adds' => [],
                'items' => [],
                'keep_open' => false,
                'on_top' => false,
            ],
        ];

        $this->provider
            ->expects(self::once())
            ->method('get')
            ->with('my_menu')
            ->willReturn($this->factory->createItem('bar')->addChild('foo')->getParent());

        $builder = $this->createMenuBuilder($adminGroups);
        $menu = $builder->createSidebarMenu();

        self::assertInstanceOf(ItemInterface::class, $menu);
        self::assertArrayHasKey('bar', $menu->getChildren());

        foreach ($menu->getChildren() as $key => $child) {
            self::assertInstanceOf(MenuItem::class, $child);
            self::assertSame('bar', $child->getName());
            self::assertSame('bar', $child->getLabel());

            // menu items
            $children = $child->getChildren();
            self::assertCount(1, $children);
            self::assertArrayHasKey('foo', $children);
            self::assertInstanceOf(MenuItem::class, $child['foo']);
            self::assertSame('foo', $child['foo']->getLabel());
        }
    }

    public function testGetKnpMenuAndDispatchEvent(): void
    {
        $adminGroups = [
            'bar' => [
                'label' => 'foo',
                'icon' => '<i class="fas fa-edit"></i>',
                'label_catalogue' => 'SonataAdminBundle',
                'items' => [],
                'item_adds' => [],
                'roles' => [],
                'keep_open' => false,
                'on_top' => false,
            ],
        ];

        $builder = $this->createMenuBuilder($adminGroups);

        $this->eventDispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(
                self::isInstanceOf(ConfigureMenuEvent::class),
                self::equalTo('sonata.admin.event.configure.menu.sidebar')
            );

        $this->provider
            ->expects(self::once())
            ->method('get')
            ->with('sonata_group_menu')
            ->willReturn($this->factory->createItem('bar'));

        $builder->createSidebarMenu();
    }

    /**
     * @phpstan-param array<Group> $adminGroups
     */
    private function createMenuBuilder(array $adminGroups): MenuBuilder
    {
        return new MenuBuilder(new Pool(new Container(), [], $adminGroups), $this->factory, $this->provider, $this->eventDispatcher);
    }
}
