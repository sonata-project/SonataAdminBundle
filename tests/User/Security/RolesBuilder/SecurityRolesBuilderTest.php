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
use SensioLabs\AdminBundle\User\Security\RolesBuilder\SecurityRolesBuilder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SecurityRolesBuilderTest extends TestCase
{
    public function testGetRolesReturnsHierarchy(): void
    {
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->willReturn(true);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        $hierarchy = [
            'ROLE_ADMIN' => ['ROLE_USER'],
            'ROLE_SUPER_ADMIN' => ['ROLE_ADMIN'],
        ];

        $builder = new SecurityRolesBuilder($authChecker, $translator, $hierarchy);
        $roles = $builder->getRoles();

        self::assertArrayHasKey('ROLE_ADMIN', $roles);
        self::assertArrayHasKey('ROLE_SUPER_ADMIN', $roles);
        self::assertSame(['ROLE_USER'], $roles['ROLE_ADMIN']['children']);
    }

    public function testGetRolesStructure(): void
    {
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->willReturn(true);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        $hierarchy = [
            'ROLE_FOO' => ['ROLE_BAR', 'ROLE_ADMIN'],
        ];

        $builder = new SecurityRolesBuilder($authChecker, $translator, $hierarchy);
        $roles = $builder->getRoles();

        self::assertArrayHasKey('ROLE_FOO', $roles);
        self::assertSame('ROLE_FOO', $roles['ROLE_FOO']['role']);
        self::assertSame('ROLE_FOO', $roles['ROLE_FOO']['role_translated']);
        self::assertTrue($roles['ROLE_FOO']['is_granted']);
        self::assertSame(['ROLE_BAR', 'ROLE_ADMIN'], $roles['ROLE_FOO']['children']);
    }

    public function testGetRolesWithDomain(): void
    {
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->willReturn(false);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')
            ->willReturnCallback(static function (string $id, array $parameters, ?string $domain): string {
                return \sprintf('[%s]%s', $domain, $id);
            });

        $hierarchy = [
            'ROLE_ADMIN' => [],
        ];

        $builder = new SecurityRolesBuilder($authChecker, $translator, $hierarchy);
        $roles = $builder->getRoles('messages');

        self::assertSame('[messages]ROLE_ADMIN', $roles['ROLE_ADMIN']['role_translated']);
    }

    public function testGetRolesWithNullDomainDoesNotTranslate(): void
    {
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->willReturn(false);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::never())->method('trans');

        $hierarchy = [
            'ROLE_ADMIN' => [],
        ];

        $builder = new SecurityRolesBuilder($authChecker, $translator, $hierarchy);
        $roles = $builder->getRoles(null);

        self::assertSame('ROLE_ADMIN', $roles['ROLE_ADMIN']['role_translated']);
    }

    public function testGetExpandedRolesIncludesChildren(): void
    {
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->willReturn(false);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        $hierarchy = [
            'ROLE_ADMIN' => ['ROLE_USER', 'ROLE_EDITOR'],
        ];

        $builder = new SecurityRolesBuilder($authChecker, $translator, $hierarchy);
        $roles = $builder->getExpandedRoles();

        self::assertArrayHasKey('ROLE_ADMIN', $roles);
        self::assertArrayHasKey('ROLE_USER', $roles);
        self::assertArrayHasKey('ROLE_EDITOR', $roles);
    }

    public function testGetExpandedRolesDoesNotDuplicateExistingChildren(): void
    {
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->willReturn(true);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        $hierarchy = [
            'ROLE_FOO' => ['ROLE_BAR', 'ROLE_ADMIN'],
            'ROLE_STAFF' => ['ROLE_BAR'],  // ROLE_BAR already exists from ROLE_FOO expansion
        ];

        $builder = new SecurityRolesBuilder($authChecker, $translator, $hierarchy);
        $roles = $builder->getExpandedRoles();

        // ROLE_BAR should appear only once
        self::assertCount(4, $roles); // ROLE_FOO, ROLE_BAR, ROLE_ADMIN, ROLE_STAFF
        self::assertArrayHasKey('ROLE_BAR', $roles);
    }

    public function testGetExpandedRolesWithGrantedCheck(): void
    {
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')
            ->willReturnCallback(static function (string $role): bool {
                return 'ROLE_ADMIN' === $role;
            });

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        $hierarchy = [
            'ROLE_ADMIN' => ['ROLE_USER'],
        ];

        $builder = new SecurityRolesBuilder($authChecker, $translator, $hierarchy);
        $roles = $builder->getExpandedRoles();

        self::assertTrue($roles['ROLE_ADMIN']['is_granted']);
        self::assertFalse($roles['ROLE_USER']['is_granted']);
    }
}
