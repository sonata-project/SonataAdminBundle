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
use Knp\Menu\Integration\Symfony\RoutingExtension;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use Knp\Menu\Provider\MenuProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Menu\Provider\GroupMenuProvider;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnCallback(static function (string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string {
            switch ($referenceType) {
                case UrlGeneratorInterface::ABSOLUTE_URL:
                    return 'http://sonata-project/'.$name.($parameters ? '?'.http_build_query($parameters) : '');
                case UrlGeneratorInterface::ABSOLUTE_PATH:
                    return '/'.$name.($parameters ? '?'.http_build_query($parameters) : '');
                default:
                    throw new \InvalidArgumentException(sprintf('Dummy router does not support the reference type "%s".', $referenceType));
            }
        });

        $this->factory->addExtension(new RoutingExtension($urlGenerator));

        $this->provider = new GroupMenuProvider($this->factory, $this->pool, $this->checker);
    }

    public function testGroupMenuProviderName(): void
    {
        $this->assertTrue($this->provider->has('sonata_group_menu'));
    }

    /**
     * @dataProvider getAdminGroups
     */
    public function testGetMenuProviderWithCheckerGrantedGroupRoles(array $adminGroups): void
    {
        $this->pool
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->willReturn($this->getAdminMock());

        $this->checker
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

    public function unanimousGrantCheckerMock(string $role): bool
    {
        return \in_array($role, ['foo', 'bar', 'baz'], true);
    }

    public function unanimousGrantCheckerNoBazMock(string $role): bool
    {
        return \in_array($role, ['foo', 'bar'], true);
    }

    /**
     * @dataProvider getAdminGroupsMultipleRoles
     */
    public function testGetMenuProviderWithCheckerGrantedMultipleGroupRoles(
        array $adminGroups
    ): void {
        $this->checker
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
        $this->checker
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
        $this->checker
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
        $this->pool
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->willReturn($this->getAdminMock());

        $this->checker
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

        $this->assertCount(3, $children);
        $this->assertArrayHasKey('foo_admin_label', $children);
        $this->assertArrayHasKey('route_label', $children);
        $this->assertInstanceOf(MenuItem::class, $menu['foo_admin_label']);
        $this->assertSame('foo_admin_label', $menu['foo_admin_label']->getLabel());

        $extras = $menu['foo_admin_label']->getExtras();
        $this->assertArrayHasKey('label_catalogue', $extras);
        $this->assertSame('SonataAdminBundle', $extras['label_catalogue']);

        $extras = $menu['route_label']->getExtras();
        $this->assertArrayHasKey('label_catalogue', $extras);
        $this->assertSame('SonataAdminBundle', $extras['label_catalogue']);

        $this->assertSame('http://sonata-project/FooRoute?foo=bar', $menu['route_label']->getUri());
        $this->assertSame('/FooRelativeRoute?baz=qux', $menu['relative_route']->getUri());
    }

    /**
     * @dataProvider getAdminGroups
     */
    public function testGetKnpMenuWithListRoute(array $adminGroups): void
    {
        $this->pool
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->willReturn($this->getAdminMock(false));

        $this->checker
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
        $this->assertCount(2, $menu->getChildren());
    }

    /**
     * @dataProvider getAdminGroups
     */
    public function testGetKnpMenuWithGrantedList(array $adminGroups): void
    {
        $this->pool
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->willReturn($this->getAdminMock(true, false));

        $this->checker
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
        $this->assertCount(2, $menu->getChildren());
    }

    /**
     * @dataProvider getAdminGroupsWithOnTopOption
     */
    public function testGetMenuProviderOnTopOptions(array $adminGroupsOnTopOption): void
    {
        $this->pool
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
        $this->pool
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->willReturn($this->getAdminMock());

        $this->checker
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
     * @dataProvider getRootMenuItemWithDifferentUrlTypes
     */
    public function testRootMenuItemUrl(string $expectedUrl, array $item): void
    {
        $this->pool
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_absolute_url'))
            ->willReturn($this->getAdminMock());

        $this->checker
            ->method('isGranted')
            ->willReturn(true);

        $menu = $this->provider->get(
            'providerFoo',
            [
                'name' => 'foo',
                'group' => $item,
            ]
        );

        $this->assertInstanceOf(ItemInterface::class, $menu);
        $this->assertSame('foo', $menu->getName());
        $this->assertSame($expectedUrl, $menu['foo_admin_label']->getUri());
    }

    public function getAdminGroups(): array
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
                            'route' => 'FooServiceRoute',
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
                        [
                            'admin' => '',
                            'label' => 'relative_route',
                            'route' => 'FooRelativeRoute',
                            'route_params' => ['baz' => 'qux'],
                            'route_absolute' => false,
                            'roles' => [],
                        ],
                    ],
                    'item_adds' => [],
                    'roles' => ['foo'],
                ],
            ],
        ];
    }

    public function getAdminGroupsMultipleRoles(): array
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

    public function getAdminGroupsMultipleRolesOnTop(): array
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

    public function getAdminGroupsWithOnTopOption(): array
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

    public function getRootMenuItemWithDifferentUrlTypes(): iterable
    {
        yield 'absolute_url' => [
            'http://sonata-project/list',
            [
                'label' => 'foo',
                'icon' => '<i class="fa fa-edit"></i>',
                'label_catalogue' => 'SonataAdminBundle',
                'items' => [
                    [
                        'admin' => 'sonata_admin_absolute_url',
                        'label' => 'fooLabel',
                        'route' => 'FooAbsoulteRoute',
                        'route_absolute' => true,
                    ],
                ],
                'item_adds' => [],
                'roles' => ['foo'],
            ],
        ];

        yield 'absolute_path' => [
            '/list',
            [
                'label' => 'foo',
                'icon' => '<i class="fa fa-edit"></i>',
                'label_catalogue' => 'SonataAdminBundle',
                'items' => [
                    [
                        'admin' => 'sonata_admin_absolute_url',
                        'label' => 'fooLabel',
                        'route' => 'FooAbsolutePath',
                        'route_absolute' => false,
                    ],
                ],
                'item_adds' => [],
                'roles' => ['foo'],
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

        $admin
            ->method('hasAccess')
            ->with($this->equalTo('list'))
            ->willReturn($isGranted);

        $admin
            ->method('getLabel')
            ->willReturn('foo_admin_label');

        $admin
            ->method('generateMenuUrl')
            ->willReturnCallback(static function (string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): array {
                if (!\in_array($referenceType, [UrlGeneratorInterface::ABSOLUTE_URL, UrlGeneratorInterface::ABSOLUTE_PATH], true)) {
                    throw new \InvalidArgumentException(sprintf('Dummy router does not support the reference type "%s".', $referenceType));
                }

                return [
                    'route' => $name,
                    'routeParameters' => $parameters,
                    'routeAbsolute' => UrlGeneratorInterface::ABSOLUTE_URL === $referenceType,
                ];
            });

        $admin
            ->method('getTranslationDomain')
            ->willReturn('SonataAdminBundle');

        return $admin;
    }
}
