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

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Util\AdminObjectAclData;
use Sonata\AdminBundle\Util\AdminObjectAclManipulator;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 */
class AdminObjectAclManipulatorTest extends TestCase
{
    public function setUp()
    {
        $this->formFactoryInterface = $this->prophesize(FormFactoryInterface::class);

        $this->adminObjectAclManipulator = new AdminObjectAclManipulator(
            $this->formFactoryInterface->reveal(),
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
        $data = $this->prophesize(AdminObjectAclData::class);

        $form->getData()->willReturn([
            ['acl_value' => 'MASTER'],
        ]);
        $acl->getObjectAces()->willReturn([]);
        $acl->isGranted(['MASTER_MASK'], Argument::type('array'))->willReturn(true);
        $acl->isGranted(['OWNER_MASK'], Argument::type('array'))->willReturn(false);
        $acl->insertObjectAce(Argument::type(RoleSecurityIdentity::class), 64)->shouldBeCalled();
        $securityHandler->updateAcl($acl->reveal())->shouldBeCalled();
        $data->getAclRolesForm()->willReturn($form->reveal());
        $data->getAclRoles()->willReturn(new \ArrayIterator());
        $data->getMasks()->willReturn([
            'MASTER' => 'MASTER_MASK',
            'OWNER' => 'OWNER_MASK',
        ]);
        $data->getAcl()->willReturn($acl->reveal());
        $data->getUserPermissions()->willReturn(['VIEW']);
        $data->isOwner()->willReturn(false);
        $data->getOwnerPermissions()->willReturn(['MASTER', 'OWNER']);
        $data->getSecurityHandler()->willReturn($securityHandler->reveal());

        $this->adminObjectAclManipulator->updateAclRoles($data->reveal());
    }
}
