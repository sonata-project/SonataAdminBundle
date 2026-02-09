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
use SensioLabs\AdminBundle\Tests\App\Entity\User;
use SensioLabs\AdminBundle\User\Action\ResetAction;
use SensioLabs\AdminBundle\User\Model\UserManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final class ResetActionTest extends TestCase
{
    public function testThrowsNotFoundWhenTokenInvalid(): void
    {
        $twig = $this->createMock(Environment::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $userManager = $this->createMock(UserManagerInterface::class);
        $userManager->method('findUserByConfirmationToken')->willReturn(null);

        $pool = new Pool($this->createMock(ContainerInterface::class));
        $templateRegistry = $this->createMock(TemplateRegistryInterface::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $action = new ResetAction($twig, $formFactory, $userManager, $pool, $templateRegistry, $urlGenerator, 86400);

        $this->expectException(NotFoundHttpException::class);
        $action(new Request(), 'invalid-token');
    }

    public function testRedirectsWhenTokenExpired(): void
    {
        $user = new User();
        $user->setConfirmationToken('valid-token');
        $user->setPasswordRequestedAt(new \DateTimeImmutable('-2 days'));

        $twig = $this->createMock(Environment::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $userManager = $this->createMock(UserManagerInterface::class);
        $userManager->method('findUserByConfirmationToken')->willReturn($user);

        $pool = new Pool($this->createMock(ContainerInterface::class));
        $templateRegistry = $this->createMock(TemplateRegistryInterface::class);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->with('sensiolabs_admin_user_resetting_request')
            ->willReturn('/resetting/request');

        $action = new ResetAction($twig, $formFactory, $userManager, $pool, $templateRegistry, $urlGenerator, 86400);

        $response = $action(new Request(), 'valid-token');

        self::assertSame(302, $response->getStatusCode());
    }

    public function testRendersFormWhenTokenValid(): void
    {
        $user = new User();
        $user->setConfirmationToken('valid-token');
        $user->setPasswordRequestedAt(new \DateTimeImmutable());

        $formView = $this->createMock(FormView::class);

        $form = $this->createMock(FormInterface::class);
        $form->method('handleRequest')->willReturnSelf();
        $form->method('isSubmitted')->willReturn(false);
        $form->method('createView')->willReturn($formView);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->method('create')->willReturn($form);

        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturn('<html>reset form</html>');

        $userManager = $this->createMock(UserManagerInterface::class);
        $userManager->method('findUserByConfirmationToken')->willReturn($user);

        $pool = new Pool($this->createMock(ContainerInterface::class));
        $templateRegistry = $this->createMock(TemplateRegistryInterface::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $action = new ResetAction($twig, $formFactory, $userManager, $pool, $templateRegistry, $urlGenerator, 86400);

        $response = $action(new Request(), 'valid-token');

        self::assertSame(200, $response->getStatusCode());
    }
}
