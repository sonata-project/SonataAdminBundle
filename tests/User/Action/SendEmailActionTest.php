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
use SensioLabs\AdminBundle\Tests\App\Entity\User;
use SensioLabs\AdminBundle\User\Action\SendEmailAction;
use SensioLabs\AdminBundle\User\Mailer\MailerInterface;
use SensioLabs\AdminBundle\User\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SendEmailActionTest extends TestCase
{
    public function testRedirectsToCheckEmailOnSuccess(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setEnabled(true);

        $userManager = $this->createMock(UserManagerInterface::class);
        $userManager->method('findUserByEmail')->willReturn($user);

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())->method('sendResetPasswordEmail');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->with('sensiolabs_admin_user_resetting_check_email')
            ->willReturn('/resetting/check-email');

        $action = new SendEmailAction($userManager, $mailer, $urlGenerator, 86400);

        $request = new Request([], ['email' => 'test@example.com']);
        $request->setMethod('POST');

        $response = $action($request);

        self::assertSame(302, $response->getStatusCode());
    }

    public function testRedirectsToCheckEmailEvenWhenUserNotFound(): void
    {
        $userManager = $this->createMock(UserManagerInterface::class);
        $userManager->method('findUserByEmail')->willReturn(null);

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::never())->method('sendResetPasswordEmail');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->with('sensiolabs_admin_user_resetting_check_email')
            ->willReturn('/resetting/check-email');

        $action = new SendEmailAction($userManager, $mailer, $urlGenerator, 86400);

        $request = new Request([], ['email' => 'unknown@example.com']);
        $response = $action($request);

        self::assertSame(302, $response->getStatusCode());
    }

    public function testRedirectsToRequestOnEmptyEmail(): void
    {
        $userManager = $this->createMock(UserManagerInterface::class);
        $mailer = $this->createMock(MailerInterface::class);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->with('sensiolabs_admin_user_resetting_request')
            ->willReturn('/resetting/request');

        $action = new SendEmailAction($userManager, $mailer, $urlGenerator, 86400);

        $request = new Request([], ['email' => '']);
        $response = $action($request);

        self::assertSame(302, $response->getStatusCode());
    }
}
