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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Security\Handler\SecurityHandlerInterface;
use SensioLabs\AdminBundle\User\Security\RolesBuilder\AdminRolesBuilder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdminRolesBuilderTest extends TestCase
{
    private AuthorizationCheckerInterface&MockObject $authorizationChecker;
    private TranslatorInterface&MockObject $translator;

    /**
     * @var array<string, list<string>>
     */
    private array $securityInformation = [
        'GUEST' => ['VIEW', 'LIST'],
        'STAFF' => ['EDIT', 'LIST', 'CREATE'],
        'EDITOR' => ['OPERATOR', 'EXPORT'],
        'ADMIN' => ['MASTER'],
    ];

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')->willReturnArgument(0);
    }

    public function testGetRolesReturnsAdminRoles(): void
    {
        $admin = $this->createAdminMock('ROLE_APP_ADMIN_USER_%s');
        $pool = $this->createPool(['app.admin.user'], $admin);

        $this->authorizationChecker->method('isGranted')->willReturn(true);

        $builder = new AdminRolesBuilder($pool, $this->authorizationChecker, $this->translator);
        $roles = $builder->getRoles();

        self::assertArrayHasKey('ROLE_APP_ADMIN_USER_GUEST', $roles);
        self::assertArrayHasKey('ROLE_APP_ADMIN_USER_STAFF', $roles);
        self::assertTrue($roles['ROLE_APP_ADMIN_USER_GUEST']['is_granted']);
    }

    public function testGetRolesDetailedStructure(): void
    {
        $admin = $this->createAdminMock('ROLE_SONATA_FOO_%s');
        $pool = $this->createPool(['sonata.admin.bar'], $admin);

        $this->authorizationChecker->method('isGranted')->willReturn(false);

        $builder = new AdminRolesBuilder($pool, $this->authorizationChecker, $this->translator);
        $roles = $builder->getRoles();

        // Should have 4 roles: GUEST, STAFF, EDITOR, ADMIN
        self::assertCount(4, $roles);

        self::assertSame('ROLE_SONATA_FOO_GUEST', $roles['ROLE_SONATA_FOO_GUEST']['role']);
        self::assertSame('GUEST', $roles['ROLE_SONATA_FOO_GUEST']['label']);
        self::assertFalse($roles['ROLE_SONATA_FOO_GUEST']['is_granted']);

        self::assertSame('ROLE_SONATA_FOO_STAFF', $roles['ROLE_SONATA_FOO_STAFF']['role']);
        self::assertSame('STAFF', $roles['ROLE_SONATA_FOO_STAFF']['label']);

        self::assertSame('ROLE_SONATA_FOO_EDITOR', $roles['ROLE_SONATA_FOO_EDITOR']['role']);
        self::assertSame('EDITOR', $roles['ROLE_SONATA_FOO_EDITOR']['label']);

        self::assertSame('ROLE_SONATA_FOO_ADMIN', $roles['ROLE_SONATA_FOO_ADMIN']['role']);
        self::assertSame('ADMIN', $roles['ROLE_SONATA_FOO_ADMIN']['label']);
    }

    public function testGetPermissionLabelsReturnsLabels(): void
    {
        $admin = $this->createAdminMock('ROLE_%s');
        $pool = $this->createPool(['test'], $admin);

        $builder = new AdminRolesBuilder($pool, $this->authorizationChecker, $this->translator);
        $labels = $builder->getPermissionLabels();

        self::assertArrayHasKey('GUEST', $labels);
        self::assertArrayHasKey('STAFF', $labels);
        self::assertArrayHasKey('EDITOR', $labels);
        self::assertArrayHasKey('ADMIN', $labels);
    }

    public function testExcludedAdminsAreSkipped(): void
    {
        $pool = new Pool($this->createMock(ContainerInterface::class), ['app.admin.user']);

        $builder = new AdminRolesBuilder($pool, $this->authorizationChecker, $this->translator);
        $builder->setExcludedAdmins(['app.admin.user']);

        $roles = $builder->getRoles();

        self::assertSame([], $roles);
    }

    public function testMasterAdminGrantsAllRoles(): void
    {
        $admin = $this->createAdminMock('ROLE_SONATA_FOO_%s', isMaster: true);
        $pool = $this->createPool(['admin.foo'], $admin);

        // Even though authorizationChecker denies, isMaster=true overrides
        $this->authorizationChecker->method('isGranted')->willReturn(false);

        $builder = new AdminRolesBuilder($pool, $this->authorizationChecker, $this->translator);
        $roles = $builder->getRoles();

        foreach ($roles as $role) {
            self::assertTrue($role['is_granted'], \sprintf('Role %s should be granted for master admin', $role['role']));
        }
    }

    public function testRolesIncludeAdminCodeAndTranslationDomain(): void
    {
        $admin = $this->createAdminMock('ROLE_APP_%s', code: 'app.admin.post', translationDomain: 'messages');
        $pool = $this->createPool(['app.admin.post'], $admin);

        $builder = new AdminRolesBuilder($pool, $this->authorizationChecker, $this->translator);
        $roles = $builder->getRoles();

        foreach ($roles as $role) {
            self::assertSame('app.admin.post', $role['admin_code']);
            self::assertSame('messages', $role['admin_translation_domain']);
        }
    }

    /**
     * @param string[] $serviceCodes
     */
    private function createPool(array $serviceCodes, AdminInterface $admin): Pool
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn($admin);

        return new Pool($container, $serviceCodes);
    }

    /**
     * @return AdminInterface&MockObject
     */
    private function createAdminMock(
        string $baseRole,
        bool $isMaster = false,
        string $code = 'app.admin.foo',
        string $translationDomain = 'messages',
    ): AdminInterface {
        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $securityHandler->method('getBaseRole')
            ->willReturn($baseRole);
        $securityHandler->method('buildSecurityInformation')
            ->willReturn($this->securityInformation);

        $admin = $this->createMock(AdminInterface::class);
        $admin->method('getSecurityHandler')->willReturn($securityHandler);
        $admin->method('isGranted')->willReturn($isMaster);
        $admin->method('getTranslationLabel')->willReturn('Foo');
        $admin->method('getCode')->willReturn($code);
        $admin->method('getClassnameLabel')->willReturn('Foo');
        $admin->method('getTranslationDomain')->willReturn($translationDomain);

        return $admin;
    }
}
