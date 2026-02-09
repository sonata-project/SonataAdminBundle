<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\User\Security\RolesBuilder;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\User\Security\RolesBuilder\AdminRolesBuilderInterface;
use SensioLabs\AdminBundle\User\Security\RolesBuilder\ExpandableRolesBuilderInterface;
use SensioLabs\AdminBundle\User\Security\RolesBuilder\MatrixRolesBuilder;

final class MatrixRolesBuilderTest extends TestCase
{
    public function testGetRolesCombinesAdminAndSecurity(): void
    {
        $adminBuilder = $this->createMock(AdminRolesBuilderInterface::class);
        $adminBuilder->method('getRoles')->willReturn(['ROLE_ADMIN_EDIT' => ['role' => 'ROLE_ADMIN_EDIT']]);

        $securityBuilder = $this->createMock(ExpandableRolesBuilderInterface::class);
        $securityBuilder->method('getRoles')->willReturn(['ROLE_USER' => ['role' => 'ROLE_USER']]);

        $matrixBuilder = new MatrixRolesBuilder($adminBuilder, $securityBuilder);
        $roles = $matrixBuilder->getRoles();

        self::assertArrayHasKey('ROLE_ADMIN_EDIT', $roles);
        self::assertArrayHasKey('ROLE_USER', $roles);
    }

    public function testGetExpandedRolesUsesExpandedSecurityRoles(): void
    {
        $adminBuilder = $this->createMock(AdminRolesBuilderInterface::class);
        $adminBuilder->method('getRoles')->willReturn(['ROLE_ADMIN' => ['role' => 'ROLE_ADMIN']]);

        $securityBuilder = $this->createMock(ExpandableRolesBuilderInterface::class);
        $securityBuilder->method('getExpandedRoles')->willReturn([
            'ROLE_SUPER' => ['role' => 'ROLE_SUPER'],
            'ROLE_USER' => ['role' => 'ROLE_USER'],
        ]);

        $matrixBuilder = new MatrixRolesBuilder($adminBuilder, $securityBuilder);
        $roles = $matrixBuilder->getExpandedRoles();

        self::assertArrayHasKey('ROLE_ADMIN', $roles);
        self::assertArrayHasKey('ROLE_SUPER', $roles);
        self::assertArrayHasKey('ROLE_USER', $roles);
    }

    public function testGetPermissionLabelsDelegatesToAdminBuilder(): void
    {
        $adminBuilder = $this->createMock(AdminRolesBuilderInterface::class);
        $adminBuilder->method('getPermissionLabels')->willReturn(['EDIT' => 'EDIT', 'LIST' => 'LIST']);

        $securityBuilder = $this->createMock(ExpandableRolesBuilderInterface::class);

        $matrixBuilder = new MatrixRolesBuilder($adminBuilder, $securityBuilder);

        self::assertSame(['EDIT' => 'EDIT', 'LIST' => 'LIST'], $matrixBuilder->getPermissionLabels());
    }
}
