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

class AdminPermissionMapTest extends TestCase
{
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

            $this->assertInternalType('array', $masks);

            foreach ($masks as $mask) {
                $this->assertInternalType('string', MaskBuilder::getCode($mask));
            }
        }
    }

    public function testGetMaskReturnsNullIfPermissionIsNotSupported(): void
    {
        $this->assertNull($this->permissionMap->getMasks(
            'unknown permission',
            new \stdClass()
        ));
    }

    public function permissionProvider()
    {
        $dataSet = [];
        $reflection = new \ReflectionClass(AdminPermissionMap::class);

        foreach ($reflection->getConstants() as $permission) {
            $dataSet[$permission] = [true, $permission];
        }

        return $dataSet + [
            'unknown permission' => [false, 'unknown permission'],
        ];
    }

    /**
     * @dataProvider permissionProvider
     */
    public function testContainsReturnsABoolean($expectedResult, $permission): void
    {
        $this->assertSame($expectedResult, $this->permissionMap->contains($permission));
    }
}
