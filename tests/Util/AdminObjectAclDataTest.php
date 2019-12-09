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
        $this->assertInstanceOf(AdminInterface::class, $adminObjectAclData->getAdmin());
    }

    public function testGetObject(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        $this->assertInstanceOf(\stdClass::class, $adminObjectAclData->getObject());
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
        $this->assertInstanceOf(Acl::class, $adminObjectAclData->getAcl());
    }

    public function testGetMasks(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        $this->assertIsArray($adminObjectAclData->getMasks());

        foreach ($adminObjectAclData->getMasks() as $key => $mask) {
            $this->assertIsString($key);
            $this->assertIsInt($mask);
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
        $this->assertInstanceOf(Form::class, $adminObjectAclData->getAclUsersForm());
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
        $this->assertInstanceOf(Form::class, $adminObjectAclData->getAclUsersForm());
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
        $this->assertInstanceOf(Form::class, $adminObjectAclData->getAclRolesForm());
    }

    public function testGetPermissions(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        $this->assertIsArray($adminObjectAclData->getPermissions());

        foreach ($adminObjectAclData->getPermissions() as $permission) {
            $this->assertIsString($permission);
        }
    }

    public function testGetUserPermissions(): void
    {
        $adminObjectAclDataOwner = $this->createAdminObjectAclData();
        $this->assertIsArray($adminObjectAclDataOwner->getUserPermissions());

        foreach ($adminObjectAclDataOwner->getUserPermissions() as $permission) {
            $this->assertIsString($permission);
        }

        $this->assertContains('OWNER', $adminObjectAclDataOwner->getUserPermissions());
        $this->assertContains('MASTER', $adminObjectAclDataOwner->getUserPermissions());

        $adminObjectAclData = $this->createAdminObjectAclData(false);
        $this->assertIsArray($adminObjectAclData->getUserPermissions());

        foreach ($adminObjectAclData->getUserPermissions() as $permission) {
            $this->assertIsString($permission);
        }

        $this->assertFalse(array_search('OWNER', $adminObjectAclData->getUserPermissions(), true));
        $this->assertFalse(array_search('MASTER', $adminObjectAclData->getUserPermissions(), true));
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

        $this->assertInstanceOf(AclSecurityHandlerInterface::class, $adminObjectAclData->getSecurityHandler());
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

        $securityHandler
            ->method('getObjectPermissions')
            ->willReturn(['VIEW', 'EDIT', 'DELETE', 'UNDELETE', 'OPERATOR', 'MASTER', 'OWNER'])
        ;

        $securityHandler
            ->method('buildSecurityInformation')
            ->with($this->isInstanceOf(AdminInterface::class))
            ->willReturn([])
        ;

        $admin = $this->getMockForAbstractClass(AdminInterface::class);

        $admin
            ->method('isGranted')
            ->willReturn($isOwner)
        ;

        $admin
            ->method('getSecurityHandler')
            ->willReturn($securityHandler)
        ;

        return $admin;
    }
}
