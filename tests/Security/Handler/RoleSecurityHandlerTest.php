<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Security\Handler;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\RoleSecurityHandler;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * Test for RoleSecurityHandler.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class RoleSecurityHandlerTest extends TestCase
{
    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function setUp()
    {
        $this->authorizationChecker = $this->getMockForAbstractClass(AuthorizationCheckerInterface::class);
        $this->admin = $this->getMockForAbstractClass(AdminInterface::class);
    }

    /**
     * @dataProvider getBaseRoleTests
     */
    public function testGetBaseRole($expected, $code)
    {
        $handler = new RoleSecurityHandler($this->authorizationChecker, ['ROLE_BATMAN', 'ROLE_IRONMAN']);

        $this->admin->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue($code));

        $this->assertSame($expected, $handler->getBaseRole($this->admin));
    }

    public function getBaseRoleTests()
    {
        return [
            ['ROLE_FOO_BAR_%s', 'foo.bar'],
            ['ROLE_FOO_BAR_%s', 'Foo.Bar'],
            ['ROLE_FOO_BAR_BAZ_%s', 'foo.bar_baz'],
            ['ROLE_FOO_BAR_%s', 'FOO.BAR'],
        ];
    }

    /**
     * @dataProvider getIsGrantedTests
     */
    public function testIsGranted($expected, array $superAdminRoles, $adminCode, $operation, $object = null)
    {
        $handler = $this->getRoleSecurityHandler($superAdminRoles);

        $this->admin->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($adminCode));

        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->will($this->returnCallback(function (array $attributes, $object) {
                if (in_array('ROLE_BATMAN', $attributes)) {
                    return true;
                }

                if (in_array('ROLE_IRONMAN', $attributes)) {
                    return true;
                }

                if (in_array('ROLE_AUTH_EXCEPTION', $attributes)) {
                    throw new AuthenticationCredentialsNotFoundException();
                }

                if (in_array('ROLE_FOO_BAR_ABC', $attributes)) {
                    return true;
                }

                if (in_array('ROLE_FOO_BAR_DEF', $attributes) && is_a($object, 'stdClass')) {
                    return true;
                }

                if (in_array('ROLE_FOO_BAR_BAZ_ALL', $attributes)) {
                    return true;
                }

                return false;
            }));

        $this->assertSame($expected, $handler->isGranted($this->admin, $operation, $object));
    }

    public function getIsGrantedTests()
    {
        return [
            //empty
            [false, [''], 'foo.bar', ''],
            [false, [''], 'foo.bar', ['']],
            [false, [''], 'foo.bar.abc', ['']],
            [false, [''], 'foo.bar.def', ['']],
            [false, [''], 'foo.bar.baz.xyz', ''],
            [false, [''], 'foo.bar.baz.xyz', ['']],

            //superadmins
            [true, ['ROLE_BATMAN', 'ROLE_IRONMAN'], 'foo.bar', 'BAZ'],
            [true, ['ROLE_BATMAN', 'ROLE_IRONMAN'], 'foo.bar', 'ANYTHING'],
            [true, ['ROLE_BATMAN', 'ROLE_IRONMAN'], 'foo.bar', ['BAZ', 'ANYTHING']],
            [true, ['ROLE_IRONMAN'], 'foo.bar', 'BAZ'],
            [true, ['ROLE_IRONMAN'], 'foo.bar', 'ANYTHING'],
            [true, ['ROLE_IRONMAN'], 'foo.bar.baz.xyz', 'ANYTHING'],
            [true, ['ROLE_IRONMAN'], 'foo.bar', ''],
            [true, ['ROLE_IRONMAN'], 'foo.bar', ['']],

            //operations
            [true, ['ROLE_SPIDERMAN'], 'foo.bar', 'ABC'],
            [true, ['ROLE_SPIDERMAN'], 'foo.bar', ['ABC']],
            [true, ['ROLE_SPIDERMAN'], 'foo.bar', ['ABC', 'DEF']],
            [true, ['ROLE_SPIDERMAN'], 'foo.bar', ['BAZ', 'ABC']],
            [false, ['ROLE_SPIDERMAN'], 'foo.bar', 'DEF'],
            [false, ['ROLE_SPIDERMAN'], 'foo.bar', ['DEF']],
            [false, ['ROLE_SPIDERMAN'], 'foo.bar', 'BAZ'],
            [false, ['ROLE_SPIDERMAN'], 'foo.bar', ['BAZ']],
            [true, [], 'foo.bar', 'ABC'],
            [true, [], 'foo.bar', ['ABC']],
            [false, [], 'foo.bar', 'DEF'],
            [false, [], 'foo.bar', ['DEF']],
            [false, [], 'foo.bar', 'BAZ'],
            [false, [], 'foo.bar', ['BAZ']],
            [false, [], 'foo.bar.baz.xyz', 'ABC'],
            [false, [], 'foo.bar.baz.xyz', ['ABC']],
            [false, [], 'foo.bar.baz.xyz', ['ABC', 'DEF']],
            [false, [], 'foo.bar.baz.xyz', 'DEF'],
            [false, [], 'foo.bar.baz.xyz', ['DEF']],
            [false, [], 'foo.bar.baz.xyz', 'BAZ'],
            [false, [], 'foo.bar.baz.xyz', ['BAZ']],

            //objects
            [true, ['ROLE_SPIDERMAN'], 'foo.bar', ['DEF'], new \stdClass()],
            [true, ['ROLE_SPIDERMAN'], 'foo.bar', ['ABC'], new \stdClass()],
            [true, ['ROLE_SPIDERMAN'], 'foo.bar', ['ABC', 'DEF'], new \stdClass()],
            [true, ['ROLE_SPIDERMAN'], 'foo.bar', ['BAZ', 'DEF'], new \stdClass()],
            [true, ['ROLE_SPIDERMAN'], 'foo.bar', 'DEF', new \stdClass()],
            [true, ['ROLE_SPIDERMAN'], 'foo.bar', 'ABC', new \stdClass()],
            [false, ['ROLE_SPIDERMAN'], 'foo.bar', 'BAZ', new \stdClass()],
            [false, ['ROLE_SPIDERMAN'], 'foo.bar.baz.xyz', 'DEF', new \stdClass()],
            [false, ['ROLE_SPIDERMAN'], 'foo.bar.baz.xyz', 'ABC', new \stdClass()],
            [true, [], 'foo.bar', ['ABC'], new \stdClass()],
            [true, [], 'foo.bar', 'ABC', new \stdClass()],
            [true, [], 'foo.bar', ['DEF'], new \stdClass()],
            [true, [], 'foo.bar', 'DEF', new \stdClass()],
            [false, [], 'foo.bar', ['BAZ'], new \stdClass()],
            [false, [], 'foo.bar', 'BAZ', new \stdClass()],
            [false, [], 'foo.bar.baz.xyz', 'BAZ', new \stdClass()],
            [false, [], 'foo.bar.baz.xyz', ['BAZ'], new \stdClass()],
            [false, ['ROLE_AUTH_EXCEPTION'], 'foo.bar.baz.xyz', ['BAZ'], new \stdClass()],

            // ALL role
            [true, [], 'foo.bar.baz', 'LIST'],
            [true, [], 'foo.bar.baz', ['LIST', 'EDIT']],
        ];
    }

    public function testIsGrantedWithException()
    {
        $this->expectException(\RuntimeException::class, 'Something is wrong');

        $this->admin->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('foo.bar'));

        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->will($this->returnCallback(function (array $attributes, $object) {
                throw new \RuntimeException('Something is wrong');
            }));

        $handler = $this->getRoleSecurityHandler(['ROLE_BATMAN']);
        $handler->isGranted($this->admin, 'BAZ');
    }

    public function testCreateObjectSecurity()
    {
        $handler = $this->getRoleSecurityHandler(['ROLE_FOO']);
        $this->assertNull($handler->createObjectSecurity($this->getSonataAdminObject(), new \stdClass()));
    }

    public function testDeleteObjectSecurity()
    {
        $handler = $this->getRoleSecurityHandler(['ROLE_FOO']);
        $this->assertNull($handler->deleteObjectSecurity($this->getSonataAdminObject(), new \stdClass()));
    }

    public function testBuildSecurityInformation()
    {
        $handler = $this->getRoleSecurityHandler(['ROLE_FOO']);
        $this->assertSame([], $handler->buildSecurityInformation($this->getSonataAdminObject()));
    }

    /**
     * @return RoleSecurityHandler
     */
    private function getRoleSecurityHandler(array $superAdminRoles)
    {
        return new RoleSecurityHandler($this->authorizationChecker, $superAdminRoles);
    }

    /**
     * @return AdminInterface
     */
    private function getSonataAdminObject()
    {
        return $this->getMockForAbstractClass(AdminInterface::class);
    }
}
