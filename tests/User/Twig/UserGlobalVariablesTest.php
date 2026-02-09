<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\User\Twig;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\User\Twig\UserGlobalVariables;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class UserGlobalVariablesTest extends TestCase
{
    public function testGetImpersonatingReturnsFalseByDefault(): void
    {
        $pool = new Pool($this->createMock(ContainerInterface::class));
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($this->createMock(TokenInterface::class));

        $globals = new UserGlobalVariables($pool, $tokenStorage, 'avatar.png');

        self::assertFalse($globals->getImpersonating());
    }

    public function testGetImpersonatingReturnsTrueForSwitchUserToken(): void
    {
        $pool = new Pool($this->createMock(ContainerInterface::class));
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($this->createMock(SwitchUserToken::class));

        $globals = new UserGlobalVariables($pool, $tokenStorage, 'avatar.png');

        self::assertTrue($globals->getImpersonating());
    }

    public function testGetDefaultAvatar(): void
    {
        $pool = new Pool($this->createMock(ContainerInterface::class));
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $globals = new UserGlobalVariables($pool, $tokenStorage, 'bundles/sensiolabsadmin/avatar.png');

        self::assertSame('bundles/sensiolabsadmin/avatar.png', $globals->getDefaultAvatar());
    }

    public function testGetUserAdminReturnsNullWhenNotSet(): void
    {
        $pool = new Pool($this->createMock(ContainerInterface::class));
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $globals = new UserGlobalVariables($pool, $tokenStorage, 'avatar.png');

        self::assertNull($globals->getUserAdmin());
    }
}
