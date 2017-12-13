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

namespace Sonata\AdminBundle\Tests\Util;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Util\AdminObjectAclData;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 */
class AdminObjectAclDataTest extends TestCase
{
    public function testGetAdmin(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        $this->isInstanceOf(AdminInterface::class, $adminObjectAclData->getAdmin());
    }

    public function testGetObject(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        $this->isInstanceOf(\stdClass::class, $adminObjectAclData->getObject());
    }

    public function testGetAclUsers(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        $this->assertInstanceOf(\ArrayIterator::class, $adminObjectAclData->getAclUsers());
    }

    public function testGetAclRoles(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        $this->assertInstanceOf(\ArrayIterator::class, $adminObjectAclData->getAclRoles());
    }

    public function testSetAcl()
    {
        $acl = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adminObjectAclData = $this->createAdminObjectAclData();
        $ret = $adminObjectAclData->setAcl($acl);

        $this->assertSame($adminObjectAclData, $ret);

        return $adminObjectAclData;
    }

    /**
     * @depends testSetAcl
     */
    public function testGetAcl($adminObjectAclData): void
    {
        $this->isInstanceOf(Acl::class, $adminObjectAclData->getAcl());
    }

    public function testGetMasks(): void
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
     */
    public function testSetForm()
    {
        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adminObjectAclData = $this->createAdminObjectAclData();
        $ret = $adminObjectAclData->setAclUsersForm($form);

        $this->assertSame($adminObjectAclData, $ret);

        return $adminObjectAclData;
    }

    /**
     * @depends testSetForm
     *
     * @group legacy
     */
    public function testGetForm($adminObjectAclData): void
    {
        $this->isInstanceOf(Form::class, $adminObjectAclData->getAclUsersForm());
    }

    public function testSetAclUsersForm()
    {
        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adminObjectAclData = $this->createAdminObjectAclData();
        $ret = $adminObjectAclData->setAclUsersForm($form);

        $this->assertSame($adminObjectAclData, $ret);

        return $adminObjectAclData;
    }

    /**
     * @depends testSetAclUsersForm
     */
    public function testGetAclUsersForm($adminObjectAclData): void
    {
        $this->isInstanceOf(Form::class, $adminObjectAclData->getAclUsersForm());
    }

    public function testSetAclRolesForm()
    {
        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adminObjectAclData = $this->createAdminObjectAclData();
        $ret = $adminObjectAclData->setAclRolesForm($form);

        $this->assertSame($adminObjectAclData, $ret);

        return $adminObjectAclData;
    }

    /**
     * @depends testSetAclRolesForm
     */
    public function testGetAclRolesForm($adminObjectAclData): void
    {
        $this->isInstanceOf(Form::class, $adminObjectAclData->getAclRolesForm());
    }

    public function testGetPermissions(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        $this->assertInternalType('array', $adminObjectAclData->getPermissions());

        foreach ($adminObjectAclData->getPermissions() as $permission) {
            $this->assertInternalType('string', $permission);
        }
    }

    public function testGetUserPermissions(): void
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

    public function testIsOwner(): void
    {
        $adminObjectAclDataOwner = $this->createAdminObjectAclData();
        $this->assertTrue($adminObjectAclDataOwner->isOwner());

        $adminObjectAclData = $this->createAdminObjectAclData(false);
        $this->assertFalse($adminObjectAclData->isOwner());
    }

    public function testGetSecurityHandler(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();

        $this->isInstanceOf(AclSecurityHandlerInterface::class, $adminObjectAclData->getSecurityHandler());
    }

    public function testGetSecurityInformation(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();

        $this->assertSame([], $adminObjectAclData->getSecurityInformation());
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
        return new AdminObjectAclData($this->createAdmin($isOwner), new \stdClass(), self::createAclUsers(), MaskBuilder::class, self::createAclRoles());
    }

    protected function createAdmin($isOwner = true)
    {
        $securityHandler = $this->getMockForAbstractClass(AclSecurityHandlerInterface::class);

        $securityHandler->expects($this->any())
            ->method('getObjectPermissions')
            ->will($this->returnValue(['VIEW', 'EDIT', 'DELETE', 'UNDELETE', 'OPERATOR', 'MASTER', 'OWNER']))
        ;

        $securityHandler->expects($this->any())
            ->method('buildSecurityInformation')
            ->with($this->isInstanceOf(AdminInterface::class))
            ->will($this->returnValue([]))
        ;

        $admin = $this->getMockForAbstractClass(AdminInterface::class);

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
