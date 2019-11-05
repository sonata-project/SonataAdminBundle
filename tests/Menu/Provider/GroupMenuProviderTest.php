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

namespace Sonata\AdminBundle\Tests\Menu\Provider;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use Knp\Menu\Provider\MenuProviderInterface;
use PHPUnit\Framework\MockObject\MockObject as MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Menu\Provider\GroupMenuProvider;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GroupMenuProviderTest extends TestCase
{
    /**
     * @var MockObject|Pool
     */
    private $pool;
    /**
     * @var MockObject|MenuProviderInterface
     */
    private $provider;
    /**
     * @var MockObject|FactoryInterface
     */
    private $factory;

    /**
     * @var MockObject
     */
    private $checker;

    protected function setUp(): void
    {
        $this->pool = $this->getMockBuilder(Pool::class)->disableOriginalConstructor()->getMock();
        $this->checker = $this
            ->getMockBuilder(AuthorizationCheckerInterface::class)
            ->setMethods(['isGranted'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->factory = new MenuFactory();

        $this->provider = new GroupMenuProvider($this->factory, $this->pool, $this->checker);
    }

    public function testGroupMenuProviderName(): void
    {
        $this->assertTrue($this->provider->has('sonata_group_menu'));
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     * @dataProvider getAdminGroups
     */
    public function testGroupMenuProviderWithoutChecker(array $adminGroups): void
    {
        $provider = new GroupMenuProvider($this->factory, $this->pool);

        $this->pool->expects($this->any())
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->willReturn($this->getAdminMock());

        $menu = $provider->get(
            'providerFoo',
            [
                'name' => 'foo',
                'group' => $adminGroups,
            ]
        );

        $this->assertInstanceOf(ItemInterface::class, $menu);
        $this->assertSame('foo', $menu->getName());

        $children = $menu->getChildren();

        $this->assertCount(2, $children);
        $this->assertArrayHasKey('foo_admin_label', $children);
        $this->assertArrayHasKey('route_label', $children);
        $this->assertInstanceOf(MenuItem::class, $menu['foo_admin_label']);
        $this->assertSame('foo_admin_label', $menu['foo_admin_label']->getLabel());

        $extras = $menu['foo_admin_label']->getExtras();
        $this->assertArrayHasKey('label_catalogue', $extras);
        $this->assertSame($extras['label_catalogue'], 'SonataAdminBundle');

        $extras = $menu['route_label']->getExtras();
        $this->assertArrayHasKey('label_catalogue', $extras);
        $this->assertSame($extras['label_catalogue'], 'SonataAdminBundle');
    }

    /**
     * @dataProvider getAdminGroups
     */
    public function testGetMenuProviderWithCheckerGrantedGroupRoles(array $adminGroups): void
    {
        $this->pool->expects($this->any())
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->willReturn($this->getAdminMock());

        $this->checker->expects($this->any())
            ->method('isGranted')
            ->willReturn(false);

        $menu = $this->provider->get(
            'providerFoo',
            [
                'name' => 'foo',
                'group' => $adminGroups,
            ]
        );

        $this->assertInstanceOf(ItemInterface::class, $menu);
        $this->assertSame('foo', $menu->getName());

        $children = $menu->getChildren();

        $this->assertCount(1, $children);
        $this->assertArrayHasKey('foo_admin_label', $children);
        $this->assertArrayNotHasKey('route_label', $children);
        $this->assertInstanceOf(MenuItem::class, $menu['foo_admin_label']);
        $this->assertSame('foo_admin_label', $menu['foo_admin_label']->getLabel());

        $extras = $menu['foo_admin_label']->getExtras();
        $this->assertArrayHasKey('label_catalogue', $extras);
        $this->assertSame($extras['label_catalogue'], 'SonataAdminBundle');
    }

    /**
     * @param array $args
     *
     * @return bool
     */
    public function unanimousGrantCheckerMock($args)
    {
        if ($args === ['foo', 'bar']) {
            return false;
        }

        if ($args === ['foo'] || $args === ['bar'] || $args === ['baz']) {
            return true;
        }

        return false;
    }

    /**
     * @param array $args
     *
     * @return bool
     */
    public function unanimousGrantCheckerNoBazMock($args)
    {
        if ($args === ['foo', 'bar'] || $args === ['baz']) {
            return false;
        }

        if ($args === ['foo'] || $args === ['bar']) {
            return true;
        }

        return false;
    }

    /**
     * @dataProvider getAdminGroupsMultipleRoles
     */
    public function testGetMenuProviderWithCheckerGrantedMultipleGroupRoles(
        array $adminGroups
    ): void {
        $this->checker->expects($this->any())
            ->method('isGranted')
            ->willReturnCallback([$this, 'unanimousGrantCheckerMock']);

        $menu = $this->provider->get(
            'providerFoo',
            [
                'name' => 'foo',
                'group' => $adminGroups,
            ]
        );

        $this->assertInstanceOf(ItemInterface::class, $menu);

        $children = $menu->getChildren();

        $this->assertCount(4, $children);
    }

    /**
     * @dataProvider getAdminGroupsMultipleRoles
     */
    public function testGetMenuProviderWithCheckerGrantedGroupAndItemRoles(
        array $adminGroups
    ): void {
        $this->checker->expects($this->any())
            ->method('isGranted')
            ->willReturnCallback([$this, 'unanimousGrantCheckerNoBazMock']);

        $menu = $this->provider->get(
            'providerFoo',
            [
                'name' => 'foo',
                'group' => $adminGroups,
            ]
        );
        $isBazItem = $adminGroups['roles'] === ['baz'];

        $this->assertInstanceOf(ItemInterface::class, $menu);
        $this->assertSame(!$isBazItem, $menu->isDisplayed());

        $children = $menu->getChildren();
        $this->assertCount($isBazItem ? 0 : 3, $children);
    }

    /**
     * @dataProvider getAdminGroupsMultipleRolesOnTop
     */
    public function testGetMenuProviderWithCheckerGrantedMultipleGroupRolesOnTop(
        array $adminGroups
    ): void {
        $this->checker->expects($this->any())
            ->method('isGranted')
            ->willReturnCallback([$this, 'unanimousGrantCheckerMock']);

        $menu = $this->provider->get(
            'providerFoo',
            [
                'name' => 'foo',
                'group' => $adminGroups,
            ]
        );
        $this->assertInstanceOf(ItemInterface::class, $menu);

        $this->assertTrue($menu->isDisplayed());
    }

    /**
     * @dataProvider getAdminGroups
     */
    public function testGetMenuProviderWithAdmin(array $adminGroups): void
    {
        $this->pool->expects($this->any())
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->willReturn($this->getAdminMock());

        $this->checker->expects($this->any())
            ->method('isGranted')
            ->willReturn(true);

        $menu = $this->provider->get(
            'providerFoo',
            [
                'name' => 'foo',
                'group' => $adminGroups,
            ]
        );

        $this->assertInstanceOf(ItemInterface::class, $menu);
        $this->assertSame('foo', $menu->getName());

        $children = $menu->getChildren();

        $this->assertCount(2, $children);
        $this->assertArrayHasKey('foo_admin_label', $children);
        $this->assertArrayHasKey('route_label', $children);
        $this->assertInstanceOf(MenuItem::class, $menu['foo_admin_label']);
        $this->assertSame('foo_admin_label', $menu['foo_admin_label']->getLabel());

        $extras = $menu['foo_admin_label']->getExtras();
        $this->assertArrayHasKey('label_catalogue', $extras);
        $this->assertSame($extras['label_catalogue'], 'SonataAdminBundle');

        $extras = $menu['route_label']->getExtras();
        $this->assertArrayHasKey('label_catalogue', $extras);
        $this->assertSame($extras['label_catalogue'], 'SonataAdminBundle');
    }

    /**
     * @dataProvider getAdminGroups
     */
    public function testGetKnpMenuWithListRoute(array $adminGroups): void
    {
        $this->pool->expects($this->any())
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->willReturn($this->getAdminMock(false));

        $this->checker->expects($this->any())
            ->method('isGranted')
            ->willReturn(true);

        $menu = $this->provider->get(
            'providerFoo',
            [
                'name' => 'foo',
                'group' => $adminGroups,
            ]
        );

        $this->assertInstanceOf(ItemInterface::class, $menu);
        $this->assertArrayNotHasKey('foo_admin_label', $menu->getChildren());
        $this->assertArrayHasKey('route_label', $menu->getChildren());
        $this->assertCount(1, $menu->getChildren());
    }

    /**
     * @dataProvider getAdminGroups
     */
    public function testGetKnpMenuWithGrantedList(array $adminGroups): void
    {
        $this->pool->expects($this->any())
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->willReturn($this->getAdminMock(true, false));

        $this->checker->expects($this->any())
            ->method('isGranted')
            ->willReturn(true);

        $menu = $this->provider->get(
            'providerFoo',
            [
                'name' => 'foo',
                'group' => $adminGroups,
            ]
        );

        $this->assertInstanceOf(ItemInterface::class, $menu);
        $this->assertArrayNotHasKey('foo_admin_label', $menu->getChildren());
        $this->assertArrayHasKey('route_label', $menu->getChildren());
        $this->assertCount(1, $menu->getChildren());
    }

    /**
     * @dataProvider getAdminGroupsWithOnTopOption
     */
    public function testGetMenuProviderOnTopOptions(array $adminGroupsOnTopOption): void
    {
        $this->pool->expects($this->any())
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->willReturn($this->getAdminMock(true, false));

        $menu = $this->provider->get(
            'providerFoo',
            [
                'name' => 'foo',
                'group' => $adminGroupsOnTopOption,
            ]
        );

        $this->assertInstanceOf(ItemInterface::class, $menu);
        $this->assertCount(0, $menu->getChildren());
    }

    /**
     * @dataProvider getAdminGroups
     */
    public function testGetMenuProviderKeepOpenOption(array $adminGroups): void
    {
        $this->pool->expects($this->any())
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->willReturn($this->getAdminMock());

        $this->checker->expects($this->any())
            ->method('isGranted')
            ->willReturn(true);

        $adminGroups['keep_open'] = true;

        $menu = $this->provider->get(
            'providerFoo',
            [
                'name' => 'foo',
                'group' => $adminGroups,
            ]
        );

        $this->assertInstanceOf(ItemInterface::class, $menu);
        $this->assertSame('keep-open', $menu->getAttribute('class'));
        $this->assertTrue($menu->getExtra('keep_open'));
    }

    /**
     * @return array
     */
    public function getAdminGroups()
    {
        return [
            [
                'bar' => [
                    'label' => 'foo',
                    'icon' => '<i class="fa fa-edit"></i>',
                    'label_catalogue' => 'SonataAdminBundle',
                    'items' => [
                        [
                            'admin' => 'sonata_admin_foo_service',
                            'label' => 'fooLabel',
                            'route_absolute' => true,
                        ],
                        [
                            'admin' => '',
                            'label' => 'route_label',
                            'route' => 'FooRoute',
                            'route_params' => ['foo' => 'bar'],
                            'route_absolute' => true,
                            'roles' => [],
                        ],
                    ],
                    'item_adds' => [],
                    'roles' => ['foo'],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getAdminGroupsMultipleRoles()
    {
        return [
            [
                // group for all roles, children with different roles
                [
                    'label' => 'foo',
                    'icon' => '<i class="fa fa-edit"></i>',
                    'label_catalogue' => 'SonataAdminBundle',
                    'items' => [
                        [
                            'admin' => '',
                            'label' => 'route_label1',
                            'route' => 'FooRoute1',
                            'route_params' => ['foo' => 'bar'],
                            'route_absolute' => true,
                            'roles' => ['foo', 'bar'],
                        ],
                        [
                            'admin' => '',
                            'label' => 'route_label2',
                            'route' => 'FooRoute2',
                            'route_params' => ['foo' => 'bar'],
                            'route_absolute' => true,
                            'roles' => ['foo'],
                        ],
                        [
                            'admin' => '',
                            'label' => 'route_label3',
                            'route' => 'FooRoute3',
                            'route_params' => ['foo' => 'bar'],
                            'route_absolute' => true,
                            'roles' => ['bar'],
                        ],
                        [
                            'admin' => '',
                            'label' => 'route_label4',
                            'route' => 'FooRoute4',
                            'route_params' => ['foo' => 'bar'],
                            'route_absolute' => true,
                            'roles' => ['baz'],
                        ],
                    ],
                    'roles' => ['foo', 'bar'],
                    'item_adds' => [],
                ],
            ], [
                // group for one role, children with different roles
                [
                    'label' => 'foo',
                    'icon' => '<i class="fa fa-edit"></i>',
                    'label_catalogue' => 'SonataAdminBundle',
                    'items' => [
                        [
                            'admin' => '',
                            'label' => 'route_label1',
                            'route' => 'FooRoute1',
                            'route_params' => ['foo' => 'bar'],
                            'route_absolute' => true,
                            'roles' => ['foo', 'bar'],
                        ],
                        [
                            'admin' => '',
                            'label' => 'route_label2',
                            'route' => 'FooRoute2',
                            'route_params' => ['foo' => 'bar'],
                            'route_absolute' => true,
                            'roles' => ['foo'],
                        ],
                        [
                            'admin' => '',
                            'label' => 'route_label3',
                            'route' => 'FooRoute3',
                            'route_params' => ['foo' => 'bar'],
                            'route_absolute' => true,
                            'roles' => ['bar'],
                        ],
                        [
                            'admin' => '',
                            'label' => 'route_label4',
                            'route' => 'FooRoute4',
                            'route_params' => ['foo' => 'bar'],
                            'route_absolute' => true,
                            'roles' => ['baz'],
                        ],
                    ],
                    'roles' => ['baz'],
                    'item_adds' => [],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getAdminGroupsMultipleRolesOnTop()
    {
        return [
            [
                [
                    'label' => 'foo1',
                    'icon' => '<i class="fa fa-edit"></i>',
                    'label_catalogue' => 'SonataAdminBundle',
                    'items' => [
                        [
                            'admin' => '',
                            'label' => 'route_label1',
                            'route' => 'FooRoute1',
                            'route_params' => ['foo' => 'bar'],
                            'route_absolute' => true,
                        ],
                    ],
                    'item_adds' => [],
                    'roles' => ['foo', 'bar'],
                    'on_top' => true,
                ],
            ], [
                [
                    'label' => 'foo2',
                    'icon' => '<i class="fa fa-edit"></i>',
                    'label_catalogue' => 'SonataAdminBundle',
                    'items' => [
                        [
                            'admin' => '',
                            'label' => 'route_label2',
                            'route' => 'FooRoute2',
                            'route_params' => ['foo' => 'bar'],
                            'route_absolute' => true,
                        ],
                    ],
                    'item_adds' => [],
                    'roles' => ['foo'],
                    'on_top' => true,
                ],
            ], [
                [
                    'label' => 'foo3',
                    'icon' => '<i class="fa fa-edit"></i>',
                    'label_catalogue' => 'SonataAdminBundle',
                    'items' => [
                        [
                            'admin' => '',
                            'label' => 'route_label3',
                            'route' => 'FooRoute3',
                            'route_params' => ['foo' => 'bar'],
                            'route_absolute' => true,
                        ],
                    ],
                    'item_adds' => [],
                    'roles' => ['bar'],
                    'on_top' => true,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getAdminGroupsWithOnTopOption()
    {
        return [
            [
                'foo' => [
                    'label' => 'foo_on_top',
                    'icon' => '<i class="fa fa-edit"></i>',
                    'label_catalogue' => 'SonataAdminBundle',
                    'on_top' => true,
                    'items' => [
                        [
                            'admin' => 'sonata_admin_foo_service',
                            'label' => 'fooLabel',
                            'route_absolute' => true,
                            'route_params' => [],
                        ],
                    ],
                    'item_adds' => [],
                    'roles' => [],
                ],
            ],
        ];
    }

    private function getAdminMock(bool $hasRoute = true, bool $isGranted = true): AbstractAdmin
    {
        $admin = $this->createMock(AbstractAdmin::class);
        $admin->expects($this->once())
            ->method('hasRoute')
            ->with($this->equalTo('list'))
            ->willReturn($hasRoute);

        $admin->expects($this->any())
            ->method('hasAccess')
            ->with($this->equalTo('list'))
            ->willReturn($isGranted);

        $admin->expects($this->any())
            ->method('getLabel')
            ->willReturn('foo_admin_label');

        $admin->expects($this->any())
            ->method('generateMenuUrl')
            ->willReturn([]);

        $admin->expects($this->any())
            ->method('getTranslationDomain')
            ->willReturn('SonataAdminBundle');

        return $admin;
    }
}
