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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
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
final class AdminObjectAclManipulatorTest extends TestCase
{
    /**
     * @var Stub&FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var MockObject&AdminObjectAclData
     */
    private $aclData;

    /**
     * @var AdminObjectAclManipulator
     */
    private $adminObjectAclManipulator;

    protected function setUp(): void
    {
        $this->formFactory = $this->createStub(FormFactoryInterface::class);
        $this->aclData = $this->createMock(AdminObjectAclData::class);

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
        $this->aclData->method('getAclRolesForm')->willReturn($form);
        $this->aclData->method('getAclRoles')->willReturn(new \ArrayIterator());
        $this->aclData->method('getMasks')->willReturn([
            'MASTER' => 'MASTER_MASK',
            'OWNER' => 'OWNER_MASK',
        ]);
        $this->aclData->method('getAcl')->willReturn($acl);
        $this->aclData->method('getUserPermissions')->willReturn(['VIEW']);
        $this->aclData->method('isOwner')->willReturn(false);
        $this->aclData->method('getOwnerPermissions')->willReturn(['MASTER', 'OWNER']);
        $this->aclData->method('getSecurityHandler')->willReturn($securityHandler);

        $this->adminObjectAclManipulator->updateAclRoles($this->aclData);
    }

    public function testCreateAclUsersForm(): void
    {
        $form = $this->createStub(Form::class);
        $formBuilder = $this->createStub(FormBuilder::class);
        $object = $this->createStub(DomainObjectInterface::class);
        $securityHandler = $this->createStub(AclSecurityHandlerInterface::class);
        $acl = $this->createStub(Acl::class);

        $this->aclData->method('getAclRoles')->willReturn(new \ArrayIterator());
        $this->aclData->method('getAclUsers')->willReturn(new \ArrayIterator());
        $this->aclData->expects($this->once())->method('setAclUsersForm')->with($form);
        $this->aclData->method('getObject')->willReturn($object);
        $this->aclData->method('getSecurityHandler')->willReturn($securityHandler);
        $this->aclData->expects($this->once())->method('setAcl')->with($acl);
        $this->aclData->method('getMasks')->willReturn([
            'MASTER' => 'MASTER_MASK',
            'OWNER' => 'OWNER_MASK',
        ]);
        $this->aclData->method('getSecurityInformation')->willReturn([]);
        $this->formFactory->method('createNamedBuilder')->with(
            AdminObjectAclManipulator::ACL_USERS_FORM_NAME,
            FormType::class
        )->willReturn($formBuilder);
        $formBuilder->method('getForm')->willReturn($form);
        $securityHandler->method('getObjectAcl')->with($this->isInstanceOf(ObjectIdentityInterface::class))->willReturn($acl);

        $resultForm = $this->adminObjectAclManipulator->createAclUsersForm($this->aclData);

        $this->assertSame($form, $resultForm);
    }

    public function testCreateAclRolesForm(): void
    {
        $form = $this->createStub(Form::class);
        $formBuilder = $this->createStub(FormBuilder::class);
        $object = $this->createStub(DomainObjectInterface::class);
        $securityHandler = $this->createStub(AclSecurityHandlerInterface::class);
        $acl = $this->createStub(Acl::class);

        $this->aclData->method('getAclRoles')->willReturn(new \ArrayIterator());
        $this->aclData->method('getAclUsers')->willReturn(new \ArrayIterator());
        $this->aclData->expects($this->once())->method('setAclRolesForm')->with($form);
        $this->aclData->method('getObject')->willReturn($object);
        $this->aclData->method('getSecurityHandler')->willReturn($securityHandler);
        $this->aclData->expects($this->once())->method('setAcl')->with($acl);
        $this->aclData->method('getMasks')->willReturn([
            'MASTER' => 'MASTER_MASK',
            'OWNER' => 'OWNER_MASK',
        ]);
        $this->aclData->method('getSecurityInformation')->willReturn([]);
        $this->formFactory->method('createNamedBuilder')->with(
            AdminObjectAclManipulator::ACL_ROLES_FORM_NAME,
            FormType::class
        )->willReturn($formBuilder);
        $formBuilder->method('getForm')->willReturn($form);
        $securityHandler->method('getObjectAcl')->willReturn($acl);

        $resultForm = $this->adminObjectAclManipulator->createAclRolesForm($this->aclData);

        $this->assertSame($form, $resultForm);
    }
}
