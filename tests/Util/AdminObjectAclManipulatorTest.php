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
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Util\DummyDomainObject;
use Sonata\AdminBundle\Util\AdminObjectAclData;
use Sonata\AdminBundle\Util\AdminObjectAclManipulator;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 */
final class AdminObjectAclManipulatorTest extends TestCase
{
    /**
     * @var MockObject&FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var AdminObjectAclManipulator
     */
    private $adminObjectAclManipulator;

    protected function setUp(): void
    {
        $this->formFactory = $this->createMock(FormFactoryInterface::class);

        $this->adminObjectAclManipulator = new AdminObjectAclManipulator(
            $this->formFactory,
            MaskBuilder::class
        );
    }

    public function testGetMaskBuilder(): void
    {
        self::assertSame(
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
                [[MaskBuilder::MASK_MASTER], self::isType('array'), false],
                [[MaskBuilder::MASK_OWNER], self::isType('array'), false]
            )
            ->willReturnOnConsecutiveCalls(true, false);

        $acl
            ->expects(self::once())
            ->method('insertObjectAce')
            ->with(self::isInstanceOf(RoleSecurityIdentity::class), MaskBuilder::MASK_MASTER);

        $securityHandler->expects(self::once())->method('updateAcl')->with($acl);

        $securityHandler
            ->method('getObjectPermissions')
            ->willReturn(['MASTER', 'OWNER']);

        $admin = $this->createStub(AdminInterface::class);
        $admin
            ->method('isAclEnabled')
            ->willReturn(true);

        $admin
            ->method('getSecurityHandler')
            ->willReturn($securityHandler);

        $aclData = new AdminObjectAclData(
            $admin,
            new DummyDomainObject(),
            new \ArrayIterator(),
            MaskBuilder::class
        );

        $aclData->setAclRolesForm($form);
        $aclData->setAcl($acl);

        $this->adminObjectAclManipulator->updateAclRoles($aclData);
    }

    public function testCreateAclUsersForm(): void
    {
        $form = $this->createStub(Form::class);
        $formBuilder = $this->createStub(FormBuilder::class);
        $securityHandler = $this->createMock(AclSecurityHandlerInterface::class);
        $acl = $this->createStub(Acl::class);

        $securityHandler
            ->method('getObjectPermissions')
            ->willReturn(['MASTER', 'OWNER']);

        $admin = $this->createStub(AdminInterface::class);
        $admin
            ->method('isAclEnabled')
            ->willReturn(true);

        $admin
            ->method('getSecurityHandler')
            ->willReturn($securityHandler);

        $aclData = new AdminObjectAclData(
            $admin,
            new DummyDomainObject(),
            new \ArrayIterator(),
            MaskBuilder::class
        );

        $aclData->setAclRolesForm($form);
        $aclData->setAcl($acl);

        $this->formFactory->method('createNamedBuilder')->with(
            AdminObjectAclManipulator::ACL_USERS_FORM_NAME,
            FormType::class
        )->willReturn($formBuilder);
        $formBuilder->method('getForm')->willReturn($form);
        $securityHandler->method('getObjectAcl')->with(self::isInstanceOf(ObjectIdentityInterface::class))->willReturn($acl);

        $resultForm = $this->adminObjectAclManipulator->createAclUsersForm($aclData);

        self::assertSame($form, $resultForm);
    }

    public function testCreateAclRolesForm(): void
    {
        $form = $this->createStub(Form::class);
        $formBuilder = $this->createStub(FormBuilder::class);
        $securityHandler = $this->createStub(AclSecurityHandlerInterface::class);
        $acl = $this->createStub(Acl::class);

        $securityHandler
            ->method('getObjectPermissions')
            ->willReturn(['MASTER', 'OWNER']);

        $admin = $this->createStub(AdminInterface::class);
        $admin
            ->method('isAclEnabled')
            ->willReturn(true);

        $admin
            ->method('getSecurityHandler')
            ->willReturn($securityHandler);

        $aclData = new AdminObjectAclData(
            $admin,
            new DummyDomainObject(),
            new \ArrayIterator(),
            MaskBuilder::class
        );

        $aclData->setAclRolesForm($form);
        $aclData->setAcl($acl);
        $this->formFactory->method('createNamedBuilder')->with(
            AdminObjectAclManipulator::ACL_ROLES_FORM_NAME,
            FormType::class
        )->willReturn($formBuilder);
        $formBuilder->method('getForm')->willReturn($form);
        $securityHandler->method('getObjectAcl')->willReturn($acl);

        $resultForm = $this->adminObjectAclManipulator->createAclRolesForm($aclData);

        self::assertSame($form, $resultForm);
    }
}
