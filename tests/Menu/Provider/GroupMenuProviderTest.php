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

use Knp\Menu\Integration\Symfony\RoutingExtension;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Menu\Provider\GroupMenuProvider;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @phpstan-import-type Group from \Sonata\AdminBundle\Admin\Pool
 */
final class GroupMenuProviderTest extends TestCase
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var GroupMenuProvider
     */
    private $provider;

    /**
     * @var MenuFactory
     */
    private $factory;

    /**
     * @var AuthorizationCheckerInterface&Stub
     */
    private $checker;

    /**
     * @var Container
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->pool = new Pool($this->container, ['sonata_admin_foo_service', 'sonata_admin_absolute_url']);
        $this->checker = $this->createStub(AuthorizationCheckerInterface::class);

        $this->factory = new MenuFactory();

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnCallback(static function (string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string {
            switch ($referenceType) {
                case UrlGeneratorInterface::ABSOLUTE_URL:
                    return sprintf('http://sonata-project/%s%s', $name, [] !== $parameters ? '?'.http_build_query($parameters) : '');
                case UrlGeneratorInterface::ABSOLUTE_PATH:
                    return sprintf('/%s%s', $name, [] !== $parameters ? '?'.http_build_query($parameters) : '');
                default:
                    throw new \InvalidArgumentException(sprintf(
                        'Dummy router does not support the reference type "%s".',
                        $referenceType
                    ));
            }
        });

        $this->factory->addExtension(new RoutingExtension($urlGenerator));

        $this->provider = new GroupMenuProvider($this->factory, $this->pool, $this->checker);
    }

    public function testGroupMenuProviderName(): void
    {
        self::assertTrue($this->provider->has('sonata_group_menu'));
    }

    /**
     * @phpstan-param Group $adminGroups
     *
     * @dataProvider getAdminGroups
     */
    public function testGetMenuProviderWithCheckerGrantedGroupRoles(array $adminGroups): void
    {
        $this->container->set('sonata_admin_foo_service', $this->getAdminMock());

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

        self::assertInstanceOf(ItemInterface::class, $menu);
        self::assertSame('foo', $menu->getName());

        $children = $menu->getChildren();

        self::assertCount(1, $children);
        self::assertArrayHasKey('foo_admin_label', $children);
        self::assertArrayNotHasKey('route_label', $children);
        self::assertInstanceOf(MenuItem::class, $menu['foo_admin_label']);
        self::assertSame('foo_admin_label', $menu['foo_admin_label']->getLabel());

        $extras = $menu['foo_admin_label']->getExtras();
        self::assertArrayHasKey('label_catalogue', $extras);
        self::assertSame($extras['label_catalogue'], 'SonataAdminBundle');
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
     * @phpstan-param Group $adminGroups
     *
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

        self::assertInstanceOf(ItemInterface::class, $menu);

        $children = $menu->getChildren();

        self::assertCount(4, $children);
    }

    /**
     * @phpstan-param Group $adminGroups
     *
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

        self::assertInstanceOf(ItemInterface::class, $menu);
        self::assertSame(!$isBazItem, $menu->isDisplayed());

        $children = $menu->getChildren();
        self::assertCount($isBazItem ? 0 : 3, $children);
    }

    /**
     * @phpstan-param Group $adminGroups
     *
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
        self::assertInstanceOf(ItemInterface::class, $menu);

        self::assertTrue($menu->isDisplayed());
    }

    /**
     * @phpstan-param Group $adminGroups
     *
     * @dataProvider getAdminGroups
     */
    public function testGetMenuProviderWithAdmin(array $adminGroups): void
    {
        $this->container->set('sonata_admin_foo_service', $this->getAdminMock());

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

        self::assertInstanceOf(ItemInterface::class, $menu);
        self::assertSame('foo', $menu->getName());

        $children = $menu->getChildren();

        self::assertCount(3, $children);
        self::assertArrayHasKey('foo_admin_label', $children);
        self::assertArrayHasKey('route_label', $children);
        self::assertInstanceOf(MenuItem::class, $menu['foo_admin_label']);
        self::assertSame('foo_admin_label', $menu['foo_admin_label']->getLabel());

        $extras = $menu['foo_admin_label']->getExtras();
        self::assertArrayHasKey('label_catalogue', $extras);
        self::assertSame('SonataAdminBundle', $extras['label_catalogue']);

        self::assertInstanceOf(MenuItem::class, $menu['route_label']);
        $extras = $menu['route_label']->getExtras();
        self::assertArrayHasKey('label_catalogue', $extras);
        self::assertSame('SonataAdminBundle', $extras['label_catalogue']);

        self::assertSame('http://sonata-project/FooRoute?foo=bar', $menu['route_label']->getUri());
        self::assertInstanceOf(MenuItem::class, $menu['relative_route']);
        self::assertSame('/FooRelativeRoute?baz=qux', $menu['relative_route']->getUri());
    }

    /**
     * @phpstan-param Group $adminGroups
     *
     * @dataProvider getAdminGroups
     */
    public function testGetKnpMenuWithListRoute(array $adminGroups): void
    {
        $this->container->set('sonata_admin_foo_service', $this->getAdminMock(false));

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

        self::assertInstanceOf(ItemInterface::class, $menu);
        self::assertArrayNotHasKey('foo_admin_label', $menu->getChildren());
        self::assertArrayHasKey('route_label', $menu->getChildren());
        self::assertCount(2, $menu->getChildren());
    }

    /**
     * @phpstan-param Group $adminGroups
     *
     * @dataProvider getAdminGroups
     */
    public function testGetKnpMenuWithGrantedList(array $adminGroups): void
    {
        $this->container->set('sonata_admin_foo_service', $this->getAdminMock(true, false));

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

        self::assertInstanceOf(ItemInterface::class, $menu);
        self::assertArrayNotHasKey('foo_admin_label', $menu->getChildren());
        self::assertArrayHasKey('route_label', $menu->getChildren());
        self::assertCount(2, $menu->getChildren());
    }

    /**
     * @phpstan-param Group $adminGroupsOnTopOption
     *
     * @dataProvider getAdminGroupsWithOnTopOption
     */
    public function testGetMenuProviderOnTopOptions(array $adminGroupsOnTopOption): void
    {
        $this->container->set('sonata_admin_foo_service', $this->getAdminMock(true, false));

        $menu = $this->provider->get(
            'providerFoo',
            [
                'name' => 'foo',
                'group' => $adminGroupsOnTopOption,
            ]
        );

        self::assertInstanceOf(ItemInterface::class, $menu);
        self::assertCount(0, $menu->getChildren());
    }

    /**
     * @phpstan-param Group $adminGroups
     *
     * @dataProvider getAdminGroups
     */
    public function testGetMenuProviderKeepOpenOption(array $adminGroups): void
    {
        $this->container->set('sonata_admin_foo_service', $this->getAdminMock());

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

        self::assertInstanceOf(ItemInterface::class, $menu);
        self::assertSame('keep-open', $menu->getAttribute('class'));
        self::assertTrue($menu->getExtra('keep_open'));
    }

    /**
     * @phpstan-param Group $item
     *
     * @dataProvider getRootMenuItemWithDifferentUrlTypes
     */
    public function testRootMenuItemUrl(string $expectedUrl, array $item): void
    {
        $this->container->set('sonata_admin_absolute_url', $this->getAdminMock());

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

        self::assertInstanceOf(ItemInterface::class, $menu);
        self::assertSame('foo', $menu->getName());
        self::assertInstanceOf(ItemInterface::class, $menu['foo_admin_label']);
        self::assertSame($expectedUrl, $menu['foo_admin_label']->getUri());
    }

    /**
     * @phpstan-return array<array{Group}>
     */
    public function getAdminGroups(): array
    {
        return [
            [
                [
                    'label' => 'foo',
                    'icon' => '<i class="fas fa-edit"></i>',
                    'label_catalogue' => 'SonataAdminBundle',
                    'items' => [
                        [
                            'admin' => 'sonata_admin_foo_service',
                            'label' => 'fooLabel',
                            'route' => 'FooServiceRoute',
                            'route_params' => [],
                            'route_absolute' => true,
                            'roles' => [],
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
                    'keep_open' => false,
                    'on_top' => false,
                ],
            ],
        ];
    }

    /**
     * @phpstan-return array<array{Group}>
     */
    public function getAdminGroupsMultipleRoles(): array
    {
        return [
            [
                // group for all roles, children with different roles
                [
                    'label' => 'foo',
                    'icon' => '<i class="fas fa-edit"></i>',
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
                    'keep_open' => false,
                    'on_top' => false,
                ],
            ], [
                // group for one role, children with different roles
                [
                    'label' => 'foo',
                    'icon' => '<i class="fas fa-edit"></i>',
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
                    'keep_open' => false,
                    'on_top' => false,
                ],
            ],
        ];
    }

    /**
     * @phpstan-return array<array{Group}>
     */
    public function getAdminGroupsMultipleRolesOnTop(): array
    {
        return [
            [
                [
                    'label' => 'foo1',
                    'icon' => '<i class="fas fa-edit"></i>',
                    'label_catalogue' => 'SonataAdminBundle',
                    'items' => [
                        [
                            'admin' => '',
                            'label' => 'route_label1',
                            'roles' => ['bar'],
                            'route' => 'FooRoute1',
                            'route_params' => ['foo' => 'bar'],
                            'route_absolute' => true,
                        ],
                    ],
                    'item_adds' => [],
                    'roles' => ['foo', 'bar'],
                    'on_top' => true,
                    'keep_open' => false,
                ],
            ], [
                [
                    'label' => 'foo2',
                    'icon' => '<i class="fas fa-edit"></i>',
                    'label_catalogue' => 'SonataAdminBundle',
                    'items' => [
                        [
                            'admin' => '',
                            'label' => 'route_label2',
                            'roles' => ['bar'],
                            'route' => 'FooRoute2',
                            'route_params' => ['foo' => 'bar'],
                            'route_absolute' => true,
                        ],
                    ],
                    'item_adds' => [],
                    'roles' => ['foo'],
                    'on_top' => true,
                    'keep_open' => false,
                ],
            ], [
                [
                    'label' => 'foo3',
                    'icon' => '<i class="fas fa-edit"></i>',
                    'label_catalogue' => 'SonataAdminBundle',
                    'items' => [
                        [
                            'admin' => '',
                            'label' => 'route_label3',
                            'roles' => ['bar'],
                            'route' => 'FooRoute3',
                            'route_params' => ['foo' => 'bar'],
                            'route_absolute' => true,
                        ],
                    ],
                    'item_adds' => [],
                    'roles' => ['bar'],
                    'on_top' => true,
                    'keep_open' => false,
                ],
            ],
        ];
    }

    /**
     * @phpstan-return array<array{Group}>
     */
    public function getAdminGroupsWithOnTopOption(): array
    {
        return [
            [
                [
                    'label' => 'foo_on_top',
                    'icon' => '<i class="fas fa-edit"></i>',
                    'label_catalogue' => 'SonataAdminBundle',
                    'keep_open' => false,
                    'on_top' => true,
                    'items' => [
                        [
                            'admin' => 'sonata_admin_foo_service',
                            'label' => 'fooLabel',
                            'route' => 'fakeRoute',
                            'route_absolute' => true,
                            'route_params' => [],
                            'roles' => [],
                        ],
                    ],
                    'item_adds' => [],
                    'roles' => [],
                ],
            ],
        ];
    }

    /**
     * @phpstan-return iterable<array-key, array{string, Group}>
     */
    public function getRootMenuItemWithDifferentUrlTypes(): iterable
    {
        yield 'absolute_url' => [
            'http://sonata-project/list',
            [
                'label' => 'foo',
                'icon' => '<i class="fas fa-edit"></i>',
                'label_catalogue' => 'SonataAdminBundle',
                'keep_open' => false,
                'on_top' => false,
                'items' => [
                    [
                        'admin' => 'sonata_admin_absolute_url',
                        'label' => 'fooLabel',
                        'roles' => ['foo'],
                        'route' => 'FooAbsoulteRoute',
                        'route_absolute' => true,
                        'route_params' => [],
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
                'icon' => '<i class="fas fa-edit"></i>',
                'label_catalogue' => 'SonataAdminBundle',
                'keep_open' => false,
                'on_top' => false,
                'items' => [
                    [
                        'admin' => 'sonata_admin_absolute_url',
                        'label' => 'fooLabel',
                        'roles' => ['foo'],
                        'route' => 'FooAbsolutePath',
                        'route_absolute' => false,
                        'route_params' => [],
                    ],
                ],
                'item_adds' => [],
                'roles' => ['foo'],
            ],
        ];
    }

    /**
     * @return AdminInterface<object>
     */
    private function getAdminMock(bool $hasRoute = true, bool $isGranted = true): AdminInterface
    {
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects(self::once())
            ->method('hasRoute')
            ->with(self::equalTo('list'))
            ->willReturn($hasRoute);

        $admin
            ->method('hasAccess')
            ->with(self::equalTo('list'))
            ->willReturn($isGranted);

        $admin->method('getLabel')->willReturn('foo_admin_label');

        $admin
            ->method('generateMenuUrl')
            ->willReturnCallback(static function (string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): array {
                if (!\in_array($referenceType, [UrlGeneratorInterface::ABSOLUTE_URL, UrlGeneratorInterface::ABSOLUTE_PATH], true)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Dummy router does not support the reference type "%s".',
                        $referenceType
                    ));
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
