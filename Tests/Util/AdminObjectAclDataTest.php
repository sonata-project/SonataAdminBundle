<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Util;

use Sonata\AdminBundle\Util\AdminObjectAclData;

/**
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 */
class AdminObjectAclDataTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAdmin()
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        $this->isInstanceOf('Sonata\AdminBundle\Admin\AdminInterface', $adminObjectAclData->getAdmin());
    }

    public function testGetObject()
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        $this->isInstanceOf('stdClass', $adminObjectAclData->getObject());
    }

    public function testGetAclUsers()
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        $this->assertInstanceOf('ArrayIterator', $adminObjectAclData->getAclUsers());
    }

    public function testGetAclRoles()
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        $this->assertInstanceOf('ArrayIterator', $adminObjectAclData->getAclRoles());
    }

    public function testSetAcl()
    {
        $acl = $this->getMock('Symfony\Component\Security\Acl\Domain\Acl', array(), array(), '', false);
        $adminObjectAclData = $this->createAdminObjectAclData();
        $ret = $adminObjectAclData->setAcl($acl);

        $this->assertSame($adminObjectAclData, $ret);

        return $adminObjectAclData;
    }

    /**
     * @depends testSetAcl
     */
    public function testGetAcl($adminObjectAclData)
    {
        $this->isInstanceOf('Symfony\Component\Security\Acl\Domain\Acl', $adminObjectAclData->getAcl());
    }

    public function testGetMasks()
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        $this->assertInternalType('array', $adminObjectAclData->getMasks());

        foreach ($adminObjectAclData->getMasks() as $key => $mask) {
            $this->assertInternalType('string', $key);
            $this->assertInternalType('int', $mask);
        }
    }

    /**
     * @group legacy
     *
     * @deprecated
     */
    public function testSetForm()
    {
        $form = $this->getMock('\Symfony\Component\Form\Form', array(), array(), '', false);
        $adminObjectAclData = $this->createAdminObjectAclData();
        $ret = $adminObjectAclData->setAclUsersForm($form);

        $this->assertSame($adminObjectAclData, $ret);

        return $adminObjectAclData;
    }

    /**
     * @depends testSetForm
     *
     * @group legacy
     *
     * @deprecated
     */
    public function testGetForm($adminObjectAclData)
    {
        $this->isInstanceOf('Symfony\Component\Form\Form', $adminObjectAclData->getAclUsersForm());
    }

    public function testSetAclUsersForm()
    {
        $form = $this->getMock('\Symfony\Component\Form\Form', array(), array(), '', false);
        $adminObjectAclData = $this->createAdminObjectAclData();
        $ret = $adminObjectAclData->setAclUsersForm($form);

        $this->assertSame($adminObjectAclData, $ret);

        return $adminObjectAclData;
    }

    /**
     * @depends testSetAclUsersForm
     */
    public function testGetAclUsersForm($adminObjectAclData)
    {
        $this->isInstanceOf('Symfony\Component\Form\Form', $adminObjectAclData->getAclUsersForm());
    }

    public function testSetAclRolesForm()
    {
        $form = $this->getMock('\Symfony\Component\Form\Form', array(), array(), '', false);
        $adminObjectAclData = $this->createAdminObjectAclData();
        $ret = $adminObjectAclData->setAclRolesForm($form);

        $this->assertSame($adminObjectAclData, $ret);

        return $adminObjectAclData;
    }

    /**
     * @depends testSetAclRolesForm
     */
    public function testGetAclRolesForm($adminObjectAclData)
    {
        $this->isInstanceOf('Symfony\Component\Form\Form', $adminObjectAclData->getAclRolesForm());
    }

    public function testGetPermissions()
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        $this->assertInternalType('array', $adminObjectAclData->getPermissions());

        foreach ($adminObjectAclData->getPermissions() as $permission) {
            $this->assertInternalType('string', $permission);
        }
    }

    public function testGetUserPermissions()
    {
        $adminObjectAclDataOwner = $this->createAdminObjectAclData();
        $this->assertInternalType('array', $adminObjectAclDataOwner->getUserPermissions());

        foreach ($adminObjectAclDataOwner->getUserPermissions() as $permission) {
            $this->assertInternalType('string', $permission);
        }

        $this->assertTrue(false !== array_search('OWNER', $adminObjectAclDataOwner->getUserPermissions()));
        $this->assertTrue(false !== array_search('MASTER', $adminObjectAclDataOwner->getUserPermissions()));

        $adminObjectAclData = $this->createAdminObjectAclData(false);
        $this->assertInternalType('array', $adminObjectAclData->getUserPermissions());

        foreach ($adminObjectAclData->getUserPermissions() as $permission) {
            $this->assertInternalType('string', $permission);
        }

        $this->assertFalse(array_search('OWNER', $adminObjectAclData->getUserPermissions()));
        $this->assertFalse(array_search('MASTER', $adminObjectAclData->getUserPermissions()));
    }

    public function testIsOwner()
    {
        $adminObjectAclDataOwner = $this->createAdminObjectAclData();
        $this->assertTrue($adminObjectAclDataOwner->isOwner());

        $adminObjectAclData = $this->createAdminObjectAclData(false);
        $this->assertFalse($adminObjectAclData->isOwner());
    }

    public function testGetSecurityHandler()
    {
        $adminObjectAclData = $this->createAdminObjectAclData();

        $this->isInstanceOf('Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface', $adminObjectAclData->getSecurityHandler());
    }

    public function testGetSecurityInformation()
    {
        $adminObjectAclData = $this->createAdminObjectAclData();

        $this->assertSame(array(), $adminObjectAclData->getSecurityInformation());
    }

    protected static function createAclUsers()
    {
        return new \ArrayIterator();
    }

    protected static function createAclRoles()
    {
        return new \ArrayIterator();
    }

    protected function createAdminObjectAclData($isOwner = true)
    {
        return new AdminObjectAclData($this->createAdmin($isOwner), new \stdClass(), self::createAclUsers(), '\Symfony\Component\Security\Acl\Permission\MaskBuilder', self::createAclRoles());
    }

    protected function createAdmin($isOwner = true)
    {
        $securityHandler = $this->getMock('Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface');

        $securityHandler->expects($this->any())
            ->method('getObjectPermissions')
            ->will($this->returnValue(array('VIEW', 'EDIT', 'DELETE', 'UNDELETE', 'OPERATOR', 'MASTER', 'OWNER')))
        ;

        $securityHandler->expects($this->any())
            ->method('buildSecurityInformation')
            ->with($this->isInstanceOf('Sonata\AdminBundle\Admin\AdminInterface'))
            ->will($this->returnValue(array()))
        ;

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        $admin->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue($isOwner))
        ;

        $admin->expects($this->any())
            ->method('getSecurityHandler')
            ->will($this->returnValue($securityHandler))
        ;

        return $admin;
    }
}
