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
    /**
     * @var AdminPermissionMap
     */
    private $permissionMap;

    protected function setUp(): void
    {
        $this->permissionMap = new AdminPermissionMap();
    }

    public function testGetMaskReturnsAnArrayOfMasks(): void
    {
        $reflection = new \ReflectionClass(AdminPermissionMap::class);
        foreach ($reflection->getConstants() as $permission) {
            $masks = $this->permissionMap->getMasks(
                $permission,
                new \stdClass()
            );

            self::assertIsArray($masks);

            foreach ($masks as $mask) {
                self::assertIsString(MaskBuilder::getCode($mask));
            }
        }
    }

    public function testGetMaskReturnsNullIfPermissionIsNotSupported(): void
    {
        self::assertNull($this->permissionMap->getMasks(
            'unknown permission',
            new \stdClass()
        ));
    }

    /**
     * @phpstan-return array<array{bool, string}>
     */
    public function permissionProvider(): array
    {
        return [
            [true, AdminPermissionMap::PERMISSION_VIEW],
            [true, AdminPermissionMap::PERMISSION_EDIT],
            [true, AdminPermissionMap::PERMISSION_CREATE],
            [true, AdminPermissionMap::PERMISSION_DELETE],
            [true, AdminPermissionMap::PERMISSION_UNDELETE],
            [true, AdminPermissionMap::PERMISSION_LIST],
            [true, AdminPermissionMap::PERMISSION_EXPORT],
            [true, AdminPermissionMap::PERMISSION_OPERATOR],
            [true, AdminPermissionMap::PERMISSION_MASTER],
            [true, AdminPermissionMap::PERMISSION_OWNER],
            [false, 'unknown permission'],
        ];
    }

    /**
     * @dataProvider permissionProvider
     */
    public function testContainsReturnsABoolean(bool $expectedResult, string $permission): void
    {
        self::assertSame($expectedResult, $this->permissionMap->contains($permission));
    }
}
