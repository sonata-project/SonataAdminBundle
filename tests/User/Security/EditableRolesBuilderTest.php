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

namespace SensioLabs\AdminBundle\Tests\User\Security;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Security\Handler\SecurityHandlerInterface;
use SensioLabs\AdminBundle\User\Security\EditableRolesBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class EditableRolesBuilderTest extends TestCase
{
    public function testGetRolesReturnsSecurityHierarchyRoles(): void
    {
        $pool = new Pool($this->createMock(ContainerInterface::class));

        $token = $this->createMock(TokenInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->willReturn(false);

        $builder = new EditableRolesBuilder($pool, $tokenStorage, $authChecker, [
            'ROLE_ADMIN' => ['ROLE_USER'],
        ]);

        $roles = $builder->getRoles();

        self::assertArrayHasKey('ROLE_ADMIN', $roles);
        self::assertSame(['ROLE_USER'], $roles['ROLE_ADMIN']['children']);
    }

    public function testGetRolesReturnsFullHierarchyWithChildRoles(): void
    {
        $pool = new Pool($this->createMock(ContainerInterface::class));

        $token = $this->createMock(TokenInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->willReturn(true);

        $rolesHierarchy = [
            'ROLE_ADMIN' => ['ROLE_USER'],
            'ROLE_SUPER_ADMIN' => ['ROLE_USER', 'ROLE_SONATA_ADMIN', 'ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'],
        ];

        $builder = new EditableRolesBuilder($pool, $tokenStorage, $authChecker, $rolesHierarchy);
        $roles = $builder->getRoles();

        self::assertArrayHasKey('ROLE_ADMIN', $roles);
        self::assertArrayHasKey('ROLE_SUPER_ADMIN', $roles);
        self::assertSame(['ROLE_USER'], $roles['ROLE_ADMIN']['children']);
        self::assertCount(4, $roles['ROLE_SUPER_ADMIN']['children']);
    }

    public function testGetRolesIncludesAdminRoles(): void
    {
        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $securityHandler->method('getBaseRole')
            ->willReturn('ROLE_FOO_%s');
        $securityHandler->method('buildSecurityInformation')
            ->willReturn([
                'GUEST' => [],
                'STAFF' => [],
                'EDITOR' => [],
                'ADMIN' => [],
            ]);

        $admin = $this->createMock(AdminInterface::class);
        $admin->method('getSecurityHandler')->willReturn($securityHandler);
        $admin->method('getClassnameLabel')->willReturn('Foo');

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with('myadmin')->willReturn($admin);

        $pool = new Pool($container, ['myadmin']);

        $token = $this->createMock(TokenInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->willReturn(true);

        $builder = new EditableRolesBuilder($pool, $tokenStorage, $authChecker, []);
        $roles = $builder->getRoles();

        self::assertArrayHasKey('ROLE_FOO_GUEST', $roles);
        self::assertArrayHasKey('ROLE_FOO_STAFF', $roles);
        self::assertArrayHasKey('ROLE_FOO_EDITOR', $roles);
        self::assertArrayHasKey('ROLE_FOO_ADMIN', $roles);
        self::assertSame('GUEST', $roles['ROLE_FOO_GUEST']['label']);
        self::assertSame('Foo', $roles['ROLE_FOO_GUEST']['admin_label']);
    }

    public function testGetRolesReadOnlyReturnsNonGrantedRoles(): void
    {
        $pool = new Pool($this->createMock(ContainerInterface::class));

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn(null);

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->willReturn(false);

        $builder = new EditableRolesBuilder($pool, $tokenStorage, $authChecker, [
            'ROLE_ADMIN' => [],
        ]);

        $readOnly = $builder->getRolesReadOnly();

        self::assertContains('ROLE_ADMIN', $readOnly);
    }

    public function testSuperAdminSeesAllRolesAsGranted(): void
    {
        $pool = new Pool($this->createMock(ContainerInterface::class));

        $token = $this->createMock(TokenInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        // First call is for ROLE_SUPER_ADMIN check (hasMasterAccess), then for individual roles
        $authChecker->method('isGranted')
            ->willReturnCallback(static function (string $role): bool {
                return 'ROLE_SUPER_ADMIN' === $role;
            });

        $builder = new EditableRolesBuilder($pool, $tokenStorage, $authChecker, [
            'ROLE_ADMIN' => [],
            'ROLE_EDITOR' => [],
        ]);

        $readOnly = $builder->getRolesReadOnly();

        // Super admin can edit everything, so nothing is read-only
        self::assertEmpty($readOnly);
    }

    public function testNoTokenMeansNotMasterAccess(): void
    {
        $pool = new Pool($this->createMock(ContainerInterface::class));

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn(null);

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->willReturn(false);

        $builder = new EditableRolesBuilder($pool, $tokenStorage, $authChecker, [
            'ROLE_ADMIN' => [],
        ]);

        $roles = $builder->getRoles();

        self::assertFalse($roles['ROLE_ADMIN']['is_granted']);
    }
}
