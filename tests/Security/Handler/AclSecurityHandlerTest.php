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

    public function testAcl()
    {
        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('test'));

        $authorizationChecker = $this->getAuthorizationCheckerMock();
        $authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(true));

        $aclProvider = $this->getMockForAbstractClass(MutableAclProviderInterface::class);

        $handler = new AclSecurityHandler($this->getTokenStorageMock(), $authorizationChecker, $aclProvider, MaskBuilder::class, []);

        $this->assertTrue($handler->isGranted($admin, ['TOTO']));
        $this->assertTrue($handler->isGranted($admin, 'TOTO'));

        $authorizationChecker = $this->getAuthorizationCheckerMock();
        $authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(false));

        $handler = new AclSecurityHandler($this->getTokenStorageMock(), $authorizationChecker, $aclProvider, MaskBuilder::class, []);

        $this->assertFalse($handler->isGranted($admin, ['TOTO']));
        $this->assertFalse($handler->isGranted($admin, 'TOTO'));
    }

    public function testBuildInformation()
    {
        $informations = [
            'EDIT' => ['EDIT'],
        ];

        $authorizationChecker = $this->getAuthorizationCheckerMock();
        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('test'));

        $admin->expects($this->once())
            ->method('getSecurityInformation')
            ->will($this->returnValue($informations));

        $aclProvider = $this->getMockForAbstractClass(MutableAclProviderInterface::class);

        $handler = new AclSecurityHandler($this->getTokenStorageMock(), $authorizationChecker, $aclProvider, MaskBuilder::class, []);

        $results = $handler->buildSecurityInformation($admin);

        $this->assertArrayHasKey('ROLE_TEST_EDIT', $results);
    }

    public function testWithAuthenticationCredentialsNotFoundException()
    {
        $admin = $this->getMockForAbstractClass(AdminInterface::class);

        $authorizationChecker = $this->getAuthorizationCheckerMock();
        $authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->will($this->throwException(new AuthenticationCredentialsNotFoundException('FAIL')));

        $aclProvider = $this->getMockForAbstractClass(MutableAclProviderInterface::class);

        $handler = new AclSecurityHandler($this->getTokenStorageMock(), $authorizationChecker, $aclProvider, MaskBuilder::class, []);

        $this->assertFalse($handler->isGranted($admin, 'raise exception', $admin));
    }

    public function testWithNonAuthenticationCredentialsNotFoundException()
    {
        $this->expectException(\RuntimeException::class);

        $admin = $this->getMockForAbstractClass(AdminInterface::class);

        $authorizationChecker = $this->getAuthorizationCheckerMock();
        $authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->will($this->throwException(new \RuntimeException('FAIL')));

        $aclProvider = $this->getMockForAbstractClass(MutableAclProviderInterface::class);

        $handler = new AclSecurityHandler($this->getTokenStorageMock(), $authorizationChecker, $aclProvider, MaskBuilder::class, []);

        $this->assertFalse($handler->isGranted($admin, 'raise exception', $admin));
    }
}
