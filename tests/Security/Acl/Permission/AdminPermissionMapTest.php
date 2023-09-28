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

namespace Sonata\AdminBundle\Tests\Security\Acl\Permission;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Sonata\AdminBundle\Security\Acl\Permission\MaskBuilder;

final class AdminPermissionMapTest extends TestCase
{
    private AdminPermissionMap $permissionMap;

    protected function setUp(): void
    {
        $this->permissionMap = new AdminPermissionMap();
    }

    public function testGetMaskReturnsAnArrayOfMasks(): void
    {
        $reflection = new \ReflectionClass(AdminPermissionMap::class);
        foreach ($reflection->getConstants() as $permission) {
            static::assertIsString($permission);

            $masks = $this->permissionMap->getMasks(
                $permission,
                new \stdClass()
            );

            static::assertIsArray($masks);

            foreach ($masks as $mask) {
                static::assertIsString(MaskBuilder::getCode($mask));
            }
        }
    }

    public function testGetMaskReturnsNullIfPermissionIsNotSupported(): void
    {
        static::assertNull($this->permissionMap->getMasks(
            'unknown permission',
            new \stdClass()
        ));
    }

    /**
     * @phpstan-return iterable<array{bool, string}>
     */
    public function provideContainsReturnsABooleanCases(): iterable
    {
        yield [true, AdminPermissionMap::PERMISSION_VIEW];
        yield [true, AdminPermissionMap::PERMISSION_EDIT];
        yield [true, AdminPermissionMap::PERMISSION_HISTORY];
        yield [true, AdminPermissionMap::PERMISSION_CREATE];
        yield [true, AdminPermissionMap::PERMISSION_DELETE];
        yield [true, AdminPermissionMap::PERMISSION_UNDELETE];
        yield [true, AdminPermissionMap::PERMISSION_LIST];
        yield [true, AdminPermissionMap::PERMISSION_EXPORT];
        yield [true, AdminPermissionMap::PERMISSION_OPERATOR];
        yield [true, AdminPermissionMap::PERMISSION_MASTER];
        yield [true, AdminPermissionMap::PERMISSION_OWNER];
        yield [false, 'unknown permission'];
    }

    /**
     * @dataProvider provideContainsReturnsABooleanCases
     */
    public function testContainsReturnsABoolean(bool $expectedResult, string $permission): void
    {
        static::assertSame($expectedResult, $this->permissionMap->contains($permission));
    }
}
