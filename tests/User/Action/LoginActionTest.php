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

namespace SensioLabs\AdminBundle\Tests\User\Action;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Templating\TemplateRegistryInterface;
use SensioLabs\AdminBundle\User\Action\LoginAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

final class LoginActionTest extends TestCase
{
    public function testRendersLoginFormWhenNotAuthenticated(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturn('<html>login</html>');

        $authUtils = $this->createMock(AuthenticationUtils::class);
        $authUtils->method('getLastAuthenticationError')->willReturn(null);
        $authUtils->method('getLastUsername')->willReturn('');

        $pool = new Pool($this->createMock(ContainerInterface::class));
        $templateRegistry = $this->createMock(TemplateRegistryInterface::class);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn(null);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $action = new LoginAction($twig, $authUtils, $pool, $templateRegistry, $tokenStorage, $urlGenerator);
        $response = $action(new Request());

        self::assertSame(200, $response->getStatusCode());
    }

    public function testRedirectsToDashboardWhenAuthenticated(): void
    {
        $twig = $this->createMock(Environment::class);
        $authUtils = $this->createMock(AuthenticationUtils::class);
        $pool = new Pool($this->createMock(ContainerInterface::class));
        $templateRegistry = $this->createMock(TemplateRegistryInterface::class);

        $user = $this->createMock(UserInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->with('sensiolabs_admin_dashboard')
            ->willReturn('/admin/dashboard');

        $action = new LoginAction($twig, $authUtils, $pool, $templateRegistry, $tokenStorage, $urlGenerator);
        $response = $action(new Request());

        self::assertSame(302, $response->getStatusCode());
    }
}
