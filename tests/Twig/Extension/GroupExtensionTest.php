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
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Twig\Extension\GroupExtension;
use Symfony\Component\DependencyInjection\Container;

final class GroupExtensionTest extends TestCase
{
    public function testGetDashboardGroupsWithCreatableAdmins(): void
    {
        $container = new Container();
        $pool = new Pool($container, ['sonata_admin_non_creatable', 'sonata_admin_creatable'], [
            'group_without_creatable' => [
                'items' => [
                    'itemKey' => ['admin' => 'sonata_admin_non_creatable'],
                ],
            ],
            'group_with_creatable' => [
                'items' => [
                    'itemKey' => ['admin' => 'sonata_admin_creatable'],
                ],
            ],
        ]);
        $twigExtension = new GroupExtension($pool);

        $adminNonCreatable = $this->createMock(AbstractAdmin::class);
        $adminCreatable = $this->createMock(AbstractAdmin::class);

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
            ],
        ], $twigExtension->getDashboardGroupsWithCreatableAdmins());
    }
}
