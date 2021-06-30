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
        self::assertInstanceOf(AdminInterface::class, $adminObjectAclData->getAdmin());
    }

    public function testGetObject(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        self::assertInstanceOf(\stdClass::class, $adminObjectAclData->getObject());
    }

    public function testGetAclUsers(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        self::assertInstanceOf(\ArrayIterator::class, $adminObjectAclData->getAclUsers());
    }

    public function testGetAclRoles(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();
        self::assertInstanceOf(\ArrayIterator::class, $adminObjectAclData->getAclRoles());
    }

    public function testSetAcl(): AdminObjectAclData
    {
        $acl = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adminObjectAclData = $this->createAdminObjectAclData();
        $ret = $adminObjectAclData->setAcl($acl);

        self::assertSame($adminObjectAclData, $ret);

        return $adminObjectAclData;
    }

    /**
     * @depends testSetAcl
     */
    public function testGetAcl(AdminObjectAclData $adminObjectAclData): void
    {
        self::assertInstanceOf(Acl::class, $adminObjectAclData->getAcl());
    }

    public function testGetMasks(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();

        foreach ($adminObjectAclData->getMasks() as $key => $mask) {
            self::assertIsString($key);
            self::assertIsInt($mask);
        }
    }

    public function testSetForm(): AdminObjectAclData
    {
        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adminObjectAclData = $this->createAdminObjectAclData();
        $ret = $adminObjectAclData->setAclUsersForm($form);

        self::assertSame($adminObjectAclData, $ret);

        return $adminObjectAclData;
    }

    /**
     * @depends testSetForm
     */
    public function testGetForm(AdminObjectAclData $adminObjectAclData): void
    {
        self::assertInstanceOf(Form::class, $adminObjectAclData->getAclUsersForm());
    }

    public function testSetAclUsersForm(): AdminObjectAclData
    {
        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adminObjectAclData = $this->createAdminObjectAclData();
        $ret = $adminObjectAclData->setAclUsersForm($form);

        self::assertSame($adminObjectAclData, $ret);

        return $adminObjectAclData;
    }

    /**
     * @depends testSetAclUsersForm
     */
    public function testGetAclUsersForm(AdminObjectAclData $adminObjectAclData): void
    {
        self::assertInstanceOf(Form::class, $adminObjectAclData->getAclUsersForm());
    }

    public function testSetAclRolesForm(): AdminObjectAclData
    {
        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adminObjectAclData = $this->createAdminObjectAclData();
        $ret = $adminObjectAclData->setAclRolesForm($form);

        self::assertSame($adminObjectAclData, $ret);

        return $adminObjectAclData;
    }

    /**
     * @depends testSetAclRolesForm
     */
    public function testGetAclRolesForm(AdminObjectAclData $adminObjectAclData): void
    {
        self::assertInstanceOf(Form::class, $adminObjectAclData->getAclRolesForm());
    }

    public function testGetPermissions(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();

        foreach ($adminObjectAclData->getPermissions() as $permission) {
            self::assertIsString($permission);
        }
    }

    public function testGetUserPermissions(): void
    {
        $adminObjectAclDataOwner = $this->createAdminObjectAclData();

        foreach ($adminObjectAclDataOwner->getUserPermissions() as $permission) {
            self::assertIsString($permission);
        }

        self::assertContains('OWNER', $adminObjectAclDataOwner->getUserPermissions());
        self::assertContains('MASTER', $adminObjectAclDataOwner->getUserPermissions());

        $adminObjectAclData = $this->createAdminObjectAclData(false);

        foreach ($adminObjectAclData->getUserPermissions() as $permission) {
            self::assertIsString($permission);
        }

        self::assertFalse(array_search('OWNER', $adminObjectAclData->getUserPermissions(), true));
        self::assertFalse(array_search('MASTER', $adminObjectAclData->getUserPermissions(), true));
    }

    public function testIsOwner(): void
    {
        $adminObjectAclDataOwner = $this->createAdminObjectAclData();
        self::assertTrue($adminObjectAclDataOwner->isOwner());

        $adminObjectAclData = $this->createAdminObjectAclData(false);
        self::assertFalse($adminObjectAclData->isOwner());
    }

    public function testGetSecurityHandler(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();

        self::assertInstanceOf(AclSecurityHandlerInterface::class, $adminObjectAclData->getSecurityHandler());
    }

    public function testGetSecurityInformation(): void
    {
        $adminObjectAclData = $this->createAdminObjectAclData();

        self::assertSame([], $adminObjectAclData->getSecurityInformation());
    }

    public function testAdminAclIsNotEnabled(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->createAdminObjectAclData(true, false);
    }

    protected function createAdminObjectAclData(bool $isOwner = true, bool $isAclEnabled = true): AdminObjectAclData
    {
        return new AdminObjectAclData(
            $this->createAdmin($isOwner, $isAclEnabled),
            new \stdClass(),
            new \ArrayIterator(),
            MaskBuilder::class,
            new \ArrayIterator()
        );
    }

    /**
     * @return AdminInterface<object>
     */
    protected function createAdmin(bool $isOwner = true, bool $isAclEnabled = true): AdminInterface
    {
        $securityHandler = $this->createMock(AclSecurityHandlerInterface::class);

        $securityHandler
            ->method('getObjectPermissions')
            ->willReturn(['VIEW', 'EDIT', 'DELETE', 'UNDELETE', 'OPERATOR', 'MASTER', 'OWNER']);

        $securityHandler
            ->method('buildSecurityInformation')
            ->with(self::isInstanceOf(AdminInterface::class))
            ->willReturn([]);

        $admin = $this->createMock(AdminInterface::class);

        $admin
            ->method('isGranted')
            ->willReturn($isOwner);

        $admin
            ->method('getSecurityHandler')
            ->willReturn($securityHandler);

        $admin
            ->method('isAclEnabled')
            ->willReturn($isAclEnabled);

        return $admin;
    }
}
