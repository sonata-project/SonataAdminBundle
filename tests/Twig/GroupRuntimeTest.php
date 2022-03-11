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

namespace Sonata\AdminBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Twig\GroupRuntime;
use Symfony\Component\DependencyInjection\Container;

final class GroupRuntimeTest extends TestCase
{
    public function testGetDashboardGroupsWithCreatableAdmins(): void
    {
        $container = new Container();
        $pool = new Pool($container, ['sonata_admin_non_creatable', 'sonata_admin_creatable'], [
            'group_without_creatable' => [
                'label' => 'non_creatable',
                'translation_domain' => 'default',
                'icon' => 'icon1',
                'items' => [
                    [
                        'admin' => 'sonata_admin_non_creatable',
                        'label' => 'admin1',
                        'roles' => [],
                        'route' => 'foo',
                        'route_params' => [],
                        'route_absolute' => false,
                    ],
                ],
                'keep_open' => false,
                'on_top' => false,
                'roles' => [],
            ],
            'group_with_creatable' => [
                'label' => 'creatable',
                'translation_domain' => 'default',
                'icon' => 'icon2',
                'items' => [
                    [
                        'admin' => 'sonata_admin_creatable',
                        'label' => 'admin1',
                        'roles' => [],
                        'route' => 'foo',
                        'route_params' => [],
                        'route_absolute' => false,
                    ],
                ],
                'keep_open' => false,
                'on_top' => false,
                'roles' => [],
            ],
        ]);
        $groupRuntime = new GroupRuntime($pool);

        $adminNonCreatable = $this->createMock(AdminInterface::class);
        $adminCreatable = $this->createMock(AdminInterface::class);

        $container->set('sonata_admin_non_creatable', $adminNonCreatable);
        $container->set('sonata_admin_creatable', $adminCreatable);

        $adminCreatable
            ->method('showInDashboard')
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

        static::assertSame([
            [
                'items' => [
                    $adminCreatable,
                ],
                'label' => 'creatable',
                'translation_domain' => 'default',
                'icon' => 'icon2',
                'keep_open' => false,
                'on_top' => false,
                'roles' => [],
            ],
        ], $groupRuntime->getDashboardGroupsWithCreatableAdmins());
    }
}
