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
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Util\AdminObjectAclData;
use Sonata\AdminBundle\Util\AdminObjectAclManipulator;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Model\DomainObjectInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 */
class AdminObjectAclManipulatorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->formFactory = $this->createStub(FormFactoryInterface::class);
        $this->data = $this->createMock(AdminObjectAclData::class);

        $this->adminObjectAclManipulator = new AdminObjectAclManipulator(
            $this->formFactory,
            MaskBuilder::class
        );
    }

    public function testGetMaskBuilder(): void
    {
        $this->assertSame(
            MaskBuilder::class,
            $this->adminObjectAclManipulator->getMaskBuilderClass()
        );
    }

    public function testUpdateAclRoles(): void
    {
        $form = $this->createStub(Form::class);
        $acl = $this->createMock(Acl::class);
        $securityHandler = $this->createMock(AclSecurityHandlerInterface::class);

        $form->method('getData')->willReturn([
            ['acl_value' => 'MASTER'],
        ]);
        $acl->method('getObjectAces')->willReturn([]);
        $acl->method('isGranted')
            ->withConsecutive(
                [['MASTER_MASK'], $this->isType('array'), false],
                [['OWNER_MASK'], $this->isType('array'), false]
            )
            ->willReturnOnConsecutiveCalls(true, false);

        $acl->expects($this->once())->method('insertObjectAce')->with($this->isInstanceOf(RoleSecurityIdentity::class), 64);
        $securityHandler->expects($this->once())->method('updateAcl')->with($acl);
        $this->data->method('getAclRolesForm')->willReturn($form);
        $this->data->method('getAclRoles')->willReturn(new \ArrayIterator());
        $this->data->method('getMasks')->willReturn([
            'MASTER' => 'MASTER_MASK',
            'OWNER' => 'OWNER_MASK',
        ]);
        $this->data->method('getAcl')->willReturn($acl);
        $this->data->method('getUserPermissions')->willReturn(['VIEW']);
        $this->data->method('isOwner')->willReturn(false);
        $this->data->method('getOwnerPermissions')->willReturn(['MASTER', 'OWNER']);
        $this->data->method('getSecurityHandler')->willReturn($securityHandler);

        $this->adminObjectAclManipulator->updateAclRoles($this->data);
    }

    public function testCreateAclUsersForm(): void
    {
        $form = $this->createStub(Form::class);
        $formBuilder = $this->createStub(FormBuilder::class);
        $object = $this->createStub(DomainObjectInterface::class);
        $securityHandler = $this->createStub(AclSecurityHandlerInterface::class);
        $acl = $this->createStub(Acl::class);

        $this->data->method('getAclRoles')->willReturn(new \ArrayIterator());
        $this->data->method('getAclUsers')->willReturn(new \ArrayIterator());
        $this->data->expects($this->once())->method('setAclUsersForm')->with($form);
        $this->data->method('getObject')->willReturn($object);
        $this->data->method('getSecurityHandler')->willReturn($securityHandler);
        $this->data->expects($this->once())->method('setAcl')->with($acl);
        $this->data->method('getMasks')->willReturn([
            'MASTER' => 'MASTER_MASK',
            'OWNER' => 'OWNER_MASK',
        ]);
        $this->data->method('getSecurityInformation')->willReturn([]);
        $this->formFactory->method('createNamedBuilder')->with(
            AdminObjectAclManipulator::ACL_USERS_FORM_NAME,
            FormType::class
        )->willReturn($formBuilder);
        $formBuilder->method('getForm')->willReturn($form);
        $securityHandler->method('getObjectAcl')->with($this->isInstanceOf(ObjectIdentityInterface::class))->willReturn($acl);

        $resultForm = $this->adminObjectAclManipulator->createAclUsersForm($this->data);

        $this->assertSame($form, $resultForm);
    }

    public function testCreateAclRolesForm(): void
    {
        $form = $this->createStub(Form::class);
        $formBuilder = $this->createStub(FormBuilder::class);
        $object = $this->createStub(DomainObjectInterface::class);
        $securityHandler = $this->createStub(AclSecurityHandlerInterface::class);
        $acl = $this->createStub(Acl::class);

        $this->data->method('getAclRoles')->willReturn(new \ArrayIterator());
        $this->data->method('getAclUsers')->willReturn(new \ArrayIterator());
        $this->data->expects($this->once())->method('setAclRolesForm')->with($form);
        $this->data->method('getObject')->willReturn($object);
        $this->data->method('getSecurityHandler')->willReturn($securityHandler);
        $this->data->expects($this->once())->method('setAcl')->with($acl);
        $this->data->method('getMasks')->willReturn([
            'MASTER' => 'MASTER_MASK',
            'OWNER' => 'OWNER_MASK',
        ]);
        $this->data->method('getSecurityInformation')->willReturn([]);
        $this->formFactory->method('createNamedBuilder')->with(
            AdminObjectAclManipulator::ACL_ROLES_FORM_NAME,
            FormType::class
        )->willReturn($formBuilder);
        $formBuilder->method('getForm')->willReturn($form);
        $securityHandler->method('getObjectAcl')->willReturn($acl);

        $resultForm = $this->adminObjectAclManipulator->createAclRolesForm($this->data);

        $this->assertSame($form, $resultForm);
    }
}
