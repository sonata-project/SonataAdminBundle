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

namespace Sonata\AdminBundle\Tests\Security\Handler;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Acl\Permission\MaskBuilder;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandler;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class AclSecurityHandlerTest extends TestCase
{
    public function getTokenStorageMock()
    {
        return $this->getMockForAbstractClass(TokenStorageInterface::class);
    }

    public function getAuthorizationCheckerMock()
    {
        return $this->getMockForAbstractClass(AuthorizationCheckerInterface::class);
    }

    public function testAcl(): void
    {
        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin
            ->method('getCode')
            ->willReturn('test');

        $authorizationChecker = $this->getAuthorizationCheckerMock();
        $authorizationChecker
            ->method('isGranted')
            ->willReturn(true);

        $aclProvider = $this->getMockForAbstractClass(MutableAclProviderInterface::class);

        $handler = new AclSecurityHandler($this->getTokenStorageMock(), $authorizationChecker, $aclProvider, MaskBuilder::class, []);

        $this->assertTrue($handler->isGranted($admin, ['TOTO']));
        $this->assertTrue($handler->isGranted($admin, 'TOTO'));

        $authorizationChecker = $this->getAuthorizationCheckerMock();
        $authorizationChecker
            ->method('isGranted')
            ->willReturn(false);

        $handler = new AclSecurityHandler($this->getTokenStorageMock(), $authorizationChecker, $aclProvider, MaskBuilder::class, []);

        $this->assertFalse($handler->isGranted($admin, ['TOTO']));
        $this->assertFalse($handler->isGranted($admin, 'TOTO'));
    }

    public function testBuildInformation(): void
    {
        $informations = [
            'EDIT' => ['EDIT'],
        ];

        $authorizationChecker = $this->getAuthorizationCheckerMock();
        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->expects($this->once())
            ->method('getCode')
            ->willReturn('test');

        $admin->expects($this->once())
            ->method('getSecurityInformation')
            ->willReturn($informations);

        $aclProvider = $this->getMockForAbstractClass(MutableAclProviderInterface::class);

        $handler = new AclSecurityHandler($this->getTokenStorageMock(), $authorizationChecker, $aclProvider, MaskBuilder::class, []);

        $results = $handler->buildSecurityInformation($admin);

        $this->assertArrayHasKey('ROLE_TEST_EDIT', $results);
    }

    public function testWithAuthenticationCredentialsNotFoundException(): void
    {
        $admin = $this->getMockForAbstractClass(AdminInterface::class);

        $authorizationChecker = $this->getAuthorizationCheckerMock();
        $authorizationChecker
            ->method('isGranted')
            ->will($this->throwException(new AuthenticationCredentialsNotFoundException('FAIL')));

        $aclProvider = $this->getMockForAbstractClass(MutableAclProviderInterface::class);

        $handler = new AclSecurityHandler($this->getTokenStorageMock(), $authorizationChecker, $aclProvider, MaskBuilder::class, []);

        $this->assertFalse($handler->isGranted($admin, 'raise exception', $admin));
    }

    public function testWithNonAuthenticationCredentialsNotFoundException(): void
    {
        $this->expectException(\RuntimeException::class);

        $admin = $this->getMockForAbstractClass(AdminInterface::class);

        $authorizationChecker = $this->getAuthorizationCheckerMock();
        $authorizationChecker
            ->method('isGranted')
            ->will($this->throwException(new \RuntimeException('FAIL')));

        $aclProvider = $this->getMockForAbstractClass(MutableAclProviderInterface::class);

        $handler = new AclSecurityHandler($this->getTokenStorageMock(), $authorizationChecker, $aclProvider, MaskBuilder::class, []);

        $this->assertFalse($handler->isGranted($admin, 'raise exception', $admin));
    }

    public function testAddObjectOwnerParamMustBeMutableAclInterface(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage(sprintf(
            'Argument 1 passed to "%s::addObjectOwner()" must implement "%s".',
            AclSecurityHandler::class,
            MutableAclInterface::class
        ));
        $handler = new AclSecurityHandler(
            $this->getTokenStorageMock(),
            $this->getAuthorizationCheckerMock(),
            $this->getMockForAbstractClass(MutableAclProviderInterface::class),
            MaskBuilder::class,
            []
        );
        $handler->addObjectOwner($this->createStub(AclInterface::class));
    }

    public function testUpdateAclMustOnlyAcceptMutableAclInterface(): void
    {
        $this->expectWarning();
        $this->expectWarningMessage('assert(): assert($acl instanceof MutableAclInterface) failed');
        $handler = new AclSecurityHandler(
            $this->getTokenStorageMock(),
            $this->getAuthorizationCheckerMock(),
            $this->getMockForAbstractClass(MutableAclProviderInterface::class),
            MaskBuilder::class,
            []
        );
        $acl = $this->createStub(AclInterface::class);
        $handler->updateAcl($acl);
    }

    public function testSuccerfulUpdateAcl(): void
    {
        $acl = $this->createStub(MutableAclInterface::class);
        $aclProvider = $this->getMockForAbstractClass(MutableAclProviderInterface::class);

        $aclProvider
            ->expects($this->once())
            ->method('updateAcl')
            ->with($acl)
        ;

        $handler = new AclSecurityHandler(
            $this->getTokenStorageMock(),
            $this->getAuthorizationCheckerMock(),
            $aclProvider,
            MaskBuilder::class,
            []
        );
        $handler->updateAcl($acl);
    }
}
