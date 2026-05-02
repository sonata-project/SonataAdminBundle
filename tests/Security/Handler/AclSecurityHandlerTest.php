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

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Acl\Permission\MaskBuilder;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandler;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

final class AclSecurityHandlerTest extends TestCase
{
    /**
     * NEXT_MAJOR: Remove the group legacy.
     */
    #[IgnoreDeprecations]
    public function testAcl(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $admin
            ->method('getCode')
            ->willReturn('test');

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->method('isGranted')
            ->willReturn(true);

        $aclProvider = static::createStub(MutableAclProviderInterface::class);

        $handler = new AclSecurityHandler(static::createStub(TokenStorageInterface::class), $authorizationChecker, $aclProvider, MaskBuilder::class, 'ROLE_SUPER_ADMIN');

        // NEXT_MAJOR: Remove the next line.
        static::assertTrue($handler->isGranted($admin, ['TOTO']));
        static::assertTrue($handler->isGranted($admin, 'TOTO'));

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->method('isGranted')
            ->willReturn(false);

        $handler = new AclSecurityHandler(static::createStub(TokenStorageInterface::class), $authorizationChecker, $aclProvider, MaskBuilder::class, 'ROLE_SUPER_ADMIN');

        // NEXT_MAJOR: Remove the next line.
        static::assertFalse($handler->isGranted($admin, ['TOTO']));
        static::assertFalse($handler->isGranted($admin, 'TOTO'));
    }

    public function testBuildInformation(): void
    {
        $informations = [
            'EDIT' => ['EDIT'],
        ];

        $authorizationChecker = static::createStub(AuthorizationCheckerInterface::class);
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects(static::once())
            ->method('getCode')
            ->willReturn('test');

        $admin->expects(static::once())
            ->method('getSecurityInformation')
            ->willReturn($informations);

        $aclProvider = static::createStub(MutableAclProviderInterface::class);

        $handler = new AclSecurityHandler(static::createStub(TokenStorageInterface::class), $authorizationChecker, $aclProvider, MaskBuilder::class, 'ROLE_SUPER_ADMIN');

        $results = $handler->buildSecurityInformation($admin);

        static::assertArrayHasKey('ROLE_TEST_EDIT', $results);
    }

    public function testWithAuthenticationCredentialsNotFoundException(): void
    {
        $admin = static::createStub(AdminInterface::class);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->method('isGranted')
            ->willThrowException(new AuthenticationCredentialsNotFoundException('FAIL'));

        $aclProvider = static::createStub(MutableAclProviderInterface::class);

        $handler = new AclSecurityHandler(static::createStub(TokenStorageInterface::class), $authorizationChecker, $aclProvider, MaskBuilder::class, 'ROLE_SUPER_ADMIN');

        static::assertFalse($handler->isGranted($admin, 'raise exception', $admin));
    }

    public function testWithNonAuthenticationCredentialsNotFoundException(): void
    {
        $this->expectException(\RuntimeException::class);

        $admin = static::createStub(AdminInterface::class);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->method('isGranted')
            ->willThrowException(new \RuntimeException('FAIL'));

        $aclProvider = static::createStub(MutableAclProviderInterface::class);

        $handler = new AclSecurityHandler(static::createStub(TokenStorageInterface::class), $authorizationChecker, $aclProvider, MaskBuilder::class, 'ROLE_SUPER_ADMIN');

        static::assertFalse($handler->isGranted($admin, 'raise exception', $admin));
    }

    public function testSuccessfulUpdateAcl(): void
    {
        $acl = static::createStub(MutableAclInterface::class);
        $aclProvider = $this->createMock(MutableAclProviderInterface::class);

        $aclProvider
            ->expects(static::once())
            ->method('updateAcl')
            ->with($acl);

        $handler = new AclSecurityHandler(
            static::createStub(TokenStorageInterface::class),
            static::createStub(AuthorizationCheckerInterface::class),
            $aclProvider,
            MaskBuilder::class,
            'ROLE_SUPER_ADMIN'
        );
        $handler->updateAcl($acl);
    }
}
