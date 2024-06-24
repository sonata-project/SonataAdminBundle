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

namespace Sonata\AdminBundle\Tests\Security\Handler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\RoleSecurityHandler;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class RoleSecurityHandlerTest extends TestCase
{
    /**
     * @var AdminInterface<object>&MockObject
     */
    private AdminInterface $admin;

    /**
     * @var AuthorizationCheckerInterface&MockObject
     */
    private AuthorizationCheckerInterface $authorizationChecker;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->admin = $this->createMock(AdminInterface::class);
    }

    /**
     * @dataProvider provideGetBaseRoleCases
     */
    public function testGetBaseRole(string $expected, string $code): void
    {
        $handler = new RoleSecurityHandler($this->authorizationChecker, 'ROLE_BATMAN');

        $this->admin->expects(static::once())
            ->method('getCode')
            ->willReturn($code);

        static::assertSame($expected, $handler->getBaseRole($this->admin));
    }

    /**
     * @phpstan-return iterable<array{string, string}>
     */
    public function provideGetBaseRoleCases(): iterable
    {
        yield ['ROLE_FOO_BAR_%s', 'foo.bar'];
        yield ['ROLE_FOO_BAR_%s', 'Foo.Bar'];
        yield ['ROLE_FOO_BAR_BAZ_%s', 'foo.bar_baz'];
        yield ['ROLE_FOO_BAR_%s', 'FOO.BAR'];
    }

    /**
     * @dataProvider provideIsGrantedWithSecurityInformationCases
     *
     * @param array<string, string[]> $informationMapping
     * @param string[]                $userRoles
     */
    public function testIsGrantedWithSecurityInformation(array $informationMapping, array $userRoles, bool $expected): void
    {
        $handler = new RoleSecurityHandler($this->authorizationChecker, 'ROLE_SUPER_ADMIN');

        $subject = new \stdClass();

        $this->admin
            ->method('getCode')
            ->willReturn('test');
        $this->admin->expects(static::once())
            ->method('getSecurityInformation')
            ->willReturn($informationMapping);

        $this->authorizationChecker
            ->method('isGranted')
            ->willReturnCallback(static function (mixed $attribute, mixed $subject = null) use ($userRoles): bool {
                if ($attribute instanceof Expression) {
                    $attribute = (string) $attribute;
                }

                if (\in_array($attribute, $userRoles, true)) {
                    return $subject instanceof \stdClass;
                }

                return false;
            });

        static::assertSame($expected, $handler->isGranted($this->admin, 'EDIT', $subject));
    }

    /**
     * @phpstan-return iterable<array{array<string, string[]>, string[], bool}>
     */
    public function provideIsGrantedWithSecurityInformationCases(): iterable
    {
        yield 'default mapping' => [[], ['ROLE_TEST_EDIT'], true];
        yield 'with single mapping' => [['VIEW' => ['EDIT', 'SHOW']], ['ROLE_TEST_VIEW'], true];
        yield 'with multiple mappings' => [['WRITE' => ['EDIT', 'SHOW'], 'MANAGE' => ['EDIT', 'SHOW', 'DELETE']], ['ROLE_TEST_MANAGE'], true];
        yield 'with all mapping' => [['ADMIN' => ['ALL']], ['ROLE_TEST_ALL'], true];
        yield 'with missing permission' => [['SHOW' => ['VIEW', 'SHOW']], ['ROLE_TEST_VIEW'], false];
    }

    /**
     * NEXT_MAJOR: Remove the group legacy and only keep string $superAdminRoles and string|Expression $operation in dataProvider.
     *
     * @group legacy
     *
     * @param string|string[]                            $superAdminRoles
     * @param string|Expression|array<string|Expression> $operation
     *
     * @dataProvider provideIsGrantedCases
     */
    public function testIsGranted(
        bool $expected,
        string|array $superAdminRoles,
        string $adminCode,
        string|Expression|array $operation,
        ?object $object = null
    ): void {
        $handler = $this->getRoleSecurityHandler($superAdminRoles);

        $this->admin
            ->method('getCode')
            ->willReturn($adminCode);

        $this->authorizationChecker
            ->method('isGranted')
            ->willReturnCallback(static function (mixed $attribute, mixed $subject = null): bool {
                if ($attribute instanceof Expression) {
                    $attribute = (string) $attribute;
                }

                switch ($attribute) {
                    case 'ROLE_BATMAN':
                    case 'ROLE_IRONMAN':
                    case 'ROLE_FOO_BAR_ABC':
                    case 'ROLE_FOO_BAR_BAZ_ALL':
                    case 'ROLE_CUSTOM':
                        return true;
                    case 'ROLE_AUTH_EXCEPTION':
                        throw new AuthenticationCredentialsNotFoundException();
                    case 'ROLE_FOO_BAR_DEF':
                        return $subject instanceof \stdClass;
                    default:
                        return false;
                }
            });

        static::assertSame($expected, $handler->isGranted($this->admin, $operation, $object));
    }

    /**
     * @phpstan-return iterable<array{0: bool, 1: string|array<string>, 2: string, 3: string|Expression|array<string|Expression>, 4?: object|null}>
     */
    public function provideIsGrantedCases(): iterable
    {
        // empty
        yield [false, '', 'foo.bar', ''];
        yield [false, '', 'foo.bar', ['']];
        yield [false, '', 'foo.bar.abc', ['']];
        yield [false, '', 'foo.bar.def', ['']];
        yield [false, '', 'foo.bar.baz.xyz', ''];
        yield [false, '', 'foo.bar.baz.xyz', ['']];
        // superadmins
        yield [true, ['ROLE_BATMAN', 'ROLE_IRONMAN'], 'foo.bar', 'BAZ'];
        yield [true, ['ROLE_BATMAN', 'ROLE_IRONMAN'], 'foo.bar', 'ANYTHING'];
        yield [true, ['ROLE_BATMAN', 'ROLE_IRONMAN'], 'foo.bar', ['BAZ', 'ANYTHING']];
        yield [true, 'ROLE_IRONMAN', 'foo.bar', 'BAZ'];
        yield [true, 'ROLE_IRONMAN', 'foo.bar', 'ANYTHING'];
        yield [true, 'ROLE_IRONMAN', 'foo.bar.baz.xyz', 'ANYTHING'];
        yield [true, 'ROLE_IRONMAN', 'foo.bar', ''];
        yield [true, 'ROLE_IRONMAN', 'foo.bar', ['']];
        // operations
        yield [true, 'ROLE_SPIDERMAN', 'foo.bar', 'ABC'];
        yield [true, 'ROLE_SPIDERMAN', 'foo.bar', ['ABC']];
        yield [true, 'ROLE_SPIDERMAN', 'foo.bar', ['ABC', 'DEF']];
        yield [true, 'ROLE_SPIDERMAN', 'foo.bar', ['BAZ', 'ABC']];
        yield [false, 'ROLE_SPIDERMAN', 'foo.bar', 'DEF'];
        yield [false, 'ROLE_SPIDERMAN', 'foo.bar', ['DEF']];
        yield [false, 'ROLE_SPIDERMAN', 'foo.bar', 'BAZ'];
        yield [false, 'ROLE_SPIDERMAN', 'foo.bar', ['BAZ']];
        yield [true, [], 'foo.bar', 'ABC'];
        yield [true, [], 'foo.bar', ['ABC']];
        yield [false, [], 'foo.bar', 'DEF'];
        yield [false, [], 'foo.bar', ['DEF']];
        yield [false, [], 'foo.bar', 'BAZ'];
        yield [false, [], 'foo.bar', ['BAZ']];
        yield [false, [], 'foo.bar.baz.xyz', 'ABC'];
        yield [false, [], 'foo.bar.baz.xyz', ['ABC']];
        yield [false, [], 'foo.bar.baz.xyz', ['ABC', 'DEF']];
        yield [false, [], 'foo.bar.baz.xyz', 'DEF'];
        yield [false, [], 'foo.bar.baz.xyz', ['DEF']];
        yield [false, [], 'foo.bar.baz.xyz', 'BAZ'];
        yield [false, [], 'foo.bar.baz.xyz', ['BAZ']];
        // objects
        yield [true, 'ROLE_SPIDERMAN', 'foo.bar', ['DEF'], new \stdClass()];
        yield [true, 'ROLE_SPIDERMAN', 'foo.bar', ['ABC'], new \stdClass()];
        yield [true, 'ROLE_SPIDERMAN', 'foo.bar', ['ABC', 'DEF'], new \stdClass()];
        yield [true, 'ROLE_SPIDERMAN', 'foo.bar', ['BAZ', 'DEF'], new \stdClass()];
        yield [true, 'ROLE_SPIDERMAN', 'foo.bar', 'DEF', new \stdClass()];
        yield [true, 'ROLE_SPIDERMAN', 'foo.bar', 'ABC', new \stdClass()];
        yield [false, 'ROLE_SPIDERMAN', 'foo.bar', 'BAZ', new \stdClass()];
        yield [false, 'ROLE_SPIDERMAN', 'foo.bar.baz.xyz', 'DEF', new \stdClass()];
        yield [false, 'ROLE_SPIDERMAN', 'foo.bar.baz.xyz', 'ABC', new \stdClass()];
        yield [true, [], 'foo.bar', ['ABC'], new \stdClass()];
        yield [true, [], 'foo.bar', 'ABC', new \stdClass()];
        yield [true, [], 'foo.bar', ['DEF'], new \stdClass()];
        yield [true, [], 'foo.bar', 'DEF', new \stdClass()];
        yield [false, [], 'foo.bar', ['BAZ'], new \stdClass()];
        yield [false, [], 'foo.bar', 'BAZ', new \stdClass()];
        yield [false, [], 'foo.bar.baz.xyz', 'BAZ', new \stdClass()];
        yield [false, [], 'foo.bar.baz.xyz', ['BAZ'], new \stdClass()];
        yield [false, 'ROLE_AUTH_EXCEPTION', 'foo.bar.baz.xyz', ['BAZ'], new \stdClass()];
        // role
        yield [false, [], 'foo.bar', ['CUSTOM']];
        yield [true, [], 'foo.bar', ['ROLE_CUSTOM']];
        yield [false, [], 'foo.bar', ['ROLE_ANOTHER_CUSTOM']];
        // expression
        yield [false, [], 'foo.bar', [new Expression('CUSTOM')]];
        yield [true, [], 'foo.bar', [new Expression('ROLE_CUSTOM')]];
        yield [false, [], 'foo.bar', [new Expression('ROLE_ANOTHER_CUSTOM')]];
        // ALL role
        yield [true, [], 'foo.bar.baz', 'LIST'];
        yield [true, [], 'foo.bar.baz', ['LIST', 'EDIT']];
    }

    public function testIsGrantedWithException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Something is wrong');

        $this->admin
            ->method('getCode')
            ->willReturn('foo.bar');

        $this->authorizationChecker
            ->method('isGranted')
            ->willReturnCallback(static function (): void {
                throw new \RuntimeException('Something is wrong');
            });

        $handler = $this->getRoleSecurityHandler('ROLE_BATMAN');
        $handler->isGranted($this->admin, 'BAZ');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCreateObjectSecurity(): void
    {
        $handler = $this->getRoleSecurityHandler('ROLE_FOO');
        $handler->createObjectSecurity($this->getSonataAdminObject(), new \stdClass());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testDeleteObjectSecurity(): void
    {
        $handler = $this->getRoleSecurityHandler('ROLE_FOO');
        $handler->deleteObjectSecurity($this->getSonataAdminObject(), new \stdClass());
    }

    public function testBuildSecurityInformation(): void
    {
        $handler = $this->getRoleSecurityHandler('ROLE_FOO');
        static::assertSame([], $handler->buildSecurityInformation($this->getSonataAdminObject()));
    }

    /**
     * @param string|string[] $superAdminRoles
     */
    private function getRoleSecurityHandler(string|array $superAdminRoles): RoleSecurityHandler
    {
        return new RoleSecurityHandler($this->authorizationChecker, $superAdminRoles);
    }

    /**
     * @return AdminInterface<object>&MockObject
     */
    private function getSonataAdminObject(): AdminInterface
    {
        return $this->createMock(AdminInterface::class);
    }
}
