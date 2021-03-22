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

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Twig\Extension\GroupExtension;
use Symfony\Component\DependencyInjection\Container;

final class GroupExtensionTest extends TestCase
{
    /**
     * @var GroupExtension
     */
    private $twigExtension;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var Container
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->pool = new Pool($this->container);
        $this->twigExtension = new GroupExtension($this->pool);
    }

    public function testGetDashboardGroupsWithCreatableAdmins(): void
    {
        $container = new Container();
        $pool = new Pool($container, ['sonata_admin_non_creatable', 'sonata_admin_creatable'], [
            'group_without_creatable' => [
                'label' => 'non_creatable',
                'label_catalogue' => 'default',
                'icon' => 'icon1',
                'items' => [
                    'itemKey' => [
                        'admin' => 'sonata_admin_non_creatable',
                        'label' => 'admin1',
                        'roles' => [],
                        'route' => 'foo',
                        'route_params' => [],
                        'router_absolute' => false,
                    ],
                ],
                'item_adds' => [],
                'keep_open' => false,
                'on_top' => false,
                'roles' => [],
            ],
            'group_with_creatable' => [
                'label' => 'creatable',
                'label_catalogue' => 'default',
                'icon' => 'icon2',
                'items' => [
                    'itemKey' => [
                        'admin' => 'sonata_admin_creatable',
                        'label' => 'admin1',
                        'roles' => [],
                        'route' => 'foo',
                        'route_params' => [],
                        'router_absolute' => false,
                    ],
                ],
                'item_adds' => [],
                'keep_open' => false,
                'on_top' => false,
                'roles' => [],
            ],
        ]);
        $twigExtension = new GroupExtension($pool);

        $adminNonCreatable = $this->createMock(AdminInterface::class);
        $adminCreatable = $this->createMock(AdminInterface::class);

        $container->set('sonata_admin_non_creatable', $adminNonCreatable);
        $container->set('sonata_admin_creatable', $adminCreatable);

        $adminCreatable
            ->method('showIn')
            ->with(AbstractAdmin::CONTEXT_DASHBOARD)
            ->willReturn(true);

        $adminCreatable
            ->method('hasRoute')
            ->with('create')
            ->willReturn(true);

        $adminCreatable
            ->method('hasAccess')
            ->with('create')
            ->willReturn(true);

        $adminNonCreatable
            ->method('hasAccess')
            ->with('create')
            ->willReturn(false);

        $this->assertSame([
            [
                'items' => [
                    'itemKey' => $adminCreatable,
                ],
                'label' => 'creatable',
                'label_catalogue' => 'default',
                'icon' => 'icon2',
                'item_adds' => [],
                'keep_open' => false,
                'on_top' => false,
                'roles' => [],
            ],
        ], $twigExtension->getDashboardGroupsWithCreatableAdmins());
    }
}
