<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Security\Handler;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\RoleSecurityHandler;

/**
 * Test for RoleSecurityHandler
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class RoleSecurityHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    public function setUp()
    {
        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');

        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
    }

    /**
     * @dataProvider getBaseRoleTests
     */
    public function testGetBaseRole($expected, $code)
    {
        $handler = new RoleSecurityHandler($this->securityContext, array('ROLE_BATMAN', 'ROLE_IRONMAN'));

        $this->admin->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue($code));

        $this->assertEquals($expected, $handler->getBaseRole($this->admin));
    }

    public function getBaseRoleTests()
    {
        return array(
            array('ROLE_FOO_BAR_%s', 'foo.bar'),
            array('ROLE_FOO_BAR_%s', 'Foo.Bar'),
            array('ROLE_FOO_BAR_BAZ_%s', 'foo.bar_baz'),
            array('ROLE_FOO_BAR_%s', 'FOO.BAR'),
        );
    }

    /**
     * @dataProvider getIsGrantedTests
     */
    public function testIsGranted($expected, array $superAdminRoles, $adminCode, $operation, $object = null)
    {
        $handler = new RoleSecurityHandler($this->securityContext, $superAdminRoles);

        $this->admin->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($adminCode));

        $this->securityContext->expects($this->any())
            ->method('isGranted')
            ->will($this->returnCallback(function(array $attributes, $object) {

                if (in_array('ROLE_BATMAN', $attributes)) {
                    return true;
                }

                if (in_array('ROLE_IRONMAN', $attributes)) {
                    return true;
                }

                if (in_array('ROLE_FOO_BAR_ABC', $attributes)) {
                    return true;
                }

                if (in_array('ROLE_FOO_BAR_DEF', $attributes) && is_a($object, 'stdClass')) {
                    return true;
                }

                return false;
            }));

        $this->assertEquals($expected, $handler->isGranted($this->admin, $operation, $object));
    }

    public function getIsGrantedTests()
    {
        return array(
            //empty
            array(false, array(''), 'foo.bar', ''),
            array(false, array(''), 'foo.bar', array('')),
            array(false, array(''), 'foo.bar.abc', array('')),
            array(false, array(''), 'foo.bar.def', array('')),
            array(false, array(''), 'foo.bar.baz.xyz', ''),
            array(false, array(''), 'foo.bar.baz.xyz', array('')),

            //superadmins
            array(true, array('ROLE_BATMAN', 'ROLE_IRONMAN'), 'foo.bar', 'BAZ'),
            array(true, array('ROLE_BATMAN', 'ROLE_IRONMAN'), 'foo.bar', 'ANYTHING'),
            array(true, array('ROLE_BATMAN', 'ROLE_IRONMAN'), 'foo.bar', array('BAZ', 'ANYTHING')),
            array(true, array('ROLE_IRONMAN'), 'foo.bar', 'BAZ'),
            array(true, array('ROLE_IRONMAN'), 'foo.bar', 'ANYTHING'),
            array(true, array('ROLE_IRONMAN'), 'foo.bar.baz.xyz', 'ANYTHING'),
            array(true, array('ROLE_IRONMAN'), 'foo.bar', ''),
            array(true, array('ROLE_IRONMAN'), 'foo.bar', array('')),

            //operations
            array(true, array('ROLE_SPIDERMAN'), 'foo.bar', 'ABC'),
            array(true, array('ROLE_SPIDERMAN'), 'foo.bar', array('ABC')),
            array(true, array('ROLE_SPIDERMAN'), 'foo.bar', array('ABC', 'DEF')),
            array(true, array('ROLE_SPIDERMAN'), 'foo.bar', array('BAZ', 'ABC')),
            array(false, array('ROLE_SPIDERMAN'), 'foo.bar', 'DEF'),
            array(false, array('ROLE_SPIDERMAN'), 'foo.bar', array('DEF')),
            array(false, array('ROLE_SPIDERMAN'), 'foo.bar', 'BAZ'),
            array(false, array('ROLE_SPIDERMAN'), 'foo.bar', array('BAZ')),
            array(true, array(), 'foo.bar', 'ABC'),
            array(true, array(), 'foo.bar', array('ABC')),
            array(false, array(), 'foo.bar', 'DEF'),
            array(false, array(), 'foo.bar', array('DEF')),
            array(false, array(), 'foo.bar', 'BAZ'),
            array(false, array(), 'foo.bar', array('BAZ')),
            array(false, array(), 'foo.bar.baz.xyz', 'ABC'),
            array(false, array(), 'foo.bar.baz.xyz', array('ABC')),
            array(false, array(), 'foo.bar.baz.xyz', array('ABC', 'DEF')),
            array(false, array(), 'foo.bar.baz.xyz', 'DEF'),
            array(false, array(), 'foo.bar.baz.xyz', array('DEF')),
            array(false, array(), 'foo.bar.baz.xyz', 'BAZ'),
            array(false, array(), 'foo.bar.baz.xyz', array('BAZ')),

            //objects
            array(true, array('ROLE_SPIDERMAN'), 'foo.bar', array('DEF'), new \stdClass()),
            array(true, array('ROLE_SPIDERMAN'), 'foo.bar', array('ABC'), new \stdClass()),
            array(true, array('ROLE_SPIDERMAN'), 'foo.bar', array('ABC', 'DEF'), new \stdClass()),
            array(true, array('ROLE_SPIDERMAN'), 'foo.bar', array('BAZ', 'DEF'), new \stdClass()),
            array(true, array('ROLE_SPIDERMAN'), 'foo.bar', 'DEF', new \stdClass()),
            array(true, array('ROLE_SPIDERMAN'), 'foo.bar', 'ABC', new \stdClass()),
            array(false, array('ROLE_SPIDERMAN'), 'foo.bar', 'BAZ', new \stdClass()),
            array(false, array('ROLE_SPIDERMAN'), 'foo.bar.baz.xyz', 'DEF', new \stdClass()),
            array(false, array('ROLE_SPIDERMAN'), 'foo.bar.baz.xyz', 'ABC', new \stdClass()),
            array(true, array(), 'foo.bar', array('ABC'), new \stdClass()),
            array(true, array(), 'foo.bar', 'ABC', new \stdClass()),
            array(true, array(), 'foo.bar', array('DEF'), new \stdClass()),
            array(true, array(), 'foo.bar', 'DEF', new \stdClass()),
            array(false, array(), 'foo.bar', array('BAZ'), new \stdClass()),
            array(false, array(), 'foo.bar', 'BAZ', new \stdClass()),
            array(false, array(), 'foo.bar.baz.xyz', 'BAZ', new \stdClass()),
            array(false, array(), 'foo.bar.baz.xyz', array('BAZ'), new \stdClass()),
        );
    }
}
