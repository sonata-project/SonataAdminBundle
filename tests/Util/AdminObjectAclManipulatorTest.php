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
use Prophecy\Argument;
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
    public function setUp()
    {
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->data = $this->prophesize(AdminObjectAclData::class);

        $this->adminObjectAclManipulator = new AdminObjectAclManipulator(
            $this->formFactory->reveal(),
            MaskBuilder::class
        );
    }

    public function testGetMaskBuilder()
    {
        $this->assertSame(
            MaskBuilder::class,
            $this->adminObjectAclManipulator->getMaskBuilderClass()
        );
    }

    public function testUpdateAclRoles()
    {
        $form = $this->prophesize(Form::class);
        $acl = $this->prophesize(Acl::class);
        $securityHandler = $this->prophesize(AclSecurityHandlerInterface::class);

        $form->getData()->willReturn([
            ['acl_value' => 'MASTER'],
        ]);
        $acl->getObjectAces()->willReturn([]);
        $acl->isGranted(['MASTER_MASK'], Argument::type('array'))->willReturn(true);
        $acl->isGranted(['OWNER_MASK'], Argument::type('array'))->willReturn(false);
        $acl->insertObjectAce(Argument::type(RoleSecurityIdentity::class), 64)->shouldBeCalled();
        $securityHandler->updateAcl($acl->reveal())->shouldBeCalled();
        $this->data->getAclRolesForm()->willReturn($form->reveal());
        $this->data->getAclRoles()->willReturn(new \ArrayIterator());
        $this->data->getMasks()->willReturn([
            'MASTER' => 'MASTER_MASK',
            'OWNER' => 'OWNER_MASK',
        ]);
        $this->data->getAcl()->willReturn($acl->reveal());
        $this->data->getUserPermissions()->willReturn(['VIEW']);
        $this->data->isOwner()->willReturn(false);
        $this->data->getOwnerPermissions()->willReturn(['MASTER', 'OWNER']);
        $this->data->getSecurityHandler()->willReturn($securityHandler->reveal());

        $this->adminObjectAclManipulator->updateAclRoles($this->data->reveal());
    }

    public function testCreateAclUsersForm()
    {
        $form = $this->prophesize(Form::class);
        $formBuilder = $this->prophesize(FormBuilder::class);
        $object = $this->prophesize(DomainObjectInterface::class);
        $securityHandler = $this->prophesize(AclSecurityHandlerInterface::class);
        $acl = $this->prophesize(Acl::class);

        $this->data->getAclRoles()->willReturn(new \ArrayIterator());
        $this->data->getAclUsers()->willReturn(new \ArrayIterator());
        $this->data->setAclUsersForm($form->reveal())->shouldBeCalled();
        $this->data->getObject()->willReturn($object->reveal());
        $this->data->getSecurityHandler()->willReturn($securityHandler->reveal());
        $this->data->setAcl($acl->reveal())->shouldBeCalled();
        $this->data->getMasks()->willReturn([
            'MASTER' => 'MASTER_MASK',
            'OWNER' => 'OWNER_MASK',
        ]);
        $this->data->getSecurityInformation()->willReturn([]);
        $this->formFactory->createNamedBuilder(
            AdminObjectAclManipulator::ACL_USERS_FORM_NAME,
            FormType::class
        )->willReturn($formBuilder->reveal());
        $formBuilder->getForm()->willReturn($form->reveal());
        $securityHandler->getObjectAcl(Argument::type(ObjectIdentityInterface::class))
            ->willReturn($acl->reveal());

        $resultForm = $this->adminObjectAclManipulator->createAclUsersForm($this->data->reveal());

        $this->assertSame($form->reveal(), $resultForm);
    }

    public function testCreateAclRolesForm()
    {
        $form = $this->prophesize(Form::class);
        $formBuilder = $this->prophesize(FormBuilder::class);
        $object = $this->prophesize(DomainObjectInterface::class);
        $securityHandler = $this->prophesize(AclSecurityHandlerInterface::class);
        $acl = $this->prophesize(Acl::class);

        $this->data->getAclRoles()->willReturn(new \ArrayIterator());
        $this->data->getAclUsers()->willReturn(new \ArrayIterator());
        $this->data->setAclRolesForm($form->reveal())->shouldBeCalled();
        $this->data->getObject()->willReturn($object->reveal());
        $this->data->getSecurityHandler()->willReturn($securityHandler->reveal());
        $this->data->setAcl($acl->reveal())->shouldBeCalled();
        $this->data->getMasks()->willReturn([
            'MASTER' => 'MASTER_MASK',
            'OWNER' => 'OWNER_MASK',
        ]);
        $this->data->getSecurityInformation()->willReturn([]);
        $this->formFactory->createNamedBuilder(
            AdminObjectAclManipulator::ACL_ROLES_FORM_NAME,
            FormType::class
        )->willReturn($formBuilder->reveal());
        $formBuilder->getForm()->willReturn($form->reveal());
        $securityHandler->getObjectAcl(Argument::type(ObjectIdentityInterface::class))
            ->willReturn($acl->reveal());

        $resultForm = $this->adminObjectAclManipulator->createAclRolesForm($this->data->reveal());

        $this->assertSame($form->reveal(), $resultForm);
    }
}
