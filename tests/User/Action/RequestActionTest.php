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
use SensioLabs\AdminBundle\User\Action\RequestAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

final class RequestActionTest extends TestCase
{
    public function testRendersRequestFormWhenNotAuthenticated(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturn('<html>request</html>');

        $pool = new Pool($this->createMock(ContainerInterface::class));
        $templateRegistry = $this->createMock(TemplateRegistryInterface::class);

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->willReturn(false);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $action = new RequestAction($twig, $pool, $templateRegistry, $authChecker, $urlGenerator);
        $response = $action(new Request());

        self::assertSame(200, $response->getStatusCode());
    }

    public function testRedirectsWhenAuthenticated(): void
    {
        $twig = $this->createMock(Environment::class);
        $pool = new Pool($this->createMock(ContainerInterface::class));
        $templateRegistry = $this->createMock(TemplateRegistryInterface::class);

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->willReturn(true);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/admin/dashboard');

        $action = new RequestAction($twig, $pool, $templateRegistry, $authChecker, $urlGenerator);
        $response = $action(new Request());

        self::assertSame(302, $response->getStatusCode());
    }
}
