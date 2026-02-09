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

namespace SensioLabs\AdminBundle\Tests\User\Mailer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Tests\App\Entity\User;
use SensioLabs\AdminBundle\User\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final class MailerTest extends TestCase
{
    public function testSendResetPasswordEmail(): void
    {
        $symfonyMailer = $this->createMock(SymfonyMailerInterface::class);
        $symfonyMailer->expects(self::once())
            ->method('send')
            ->with(self::callback(static function (Email $email): bool {
                return 'Password Reset' === $email->getSubject()
                    && 'noreply@example.com' === $email->getFrom()[0]->getAddress()
                    && 'test@example.com' === $email->getTo()[0]->getAddress();
            }));

        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturn("Password Reset\n<p>Click the link below to reset your password.</p>");

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('https://example.com/reset/token123');

        $mailer = new Mailer(
            $symfonyMailer,
            $twig,
            $urlGenerator,
            'noreply@example.com',
            '@SensioLabsAdmin/User/Email/reset_password.html.twig',
        );

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setConfirmationToken('token123');

        $mailer->sendResetPasswordEmail($user);
    }

    /**
     * Tests that the mailer correctly parses various line ending formats
     * from rendered templates (first line = subject, rest = body).
     */
    #[DataProvider('emailTemplateData')]
    public function testSendResetPasswordEmailWithVariousLineEndings(
        string $template,
        string $expectedSubject,
        string $expectedBody,
    ): void {
        $symfonyMailer = $this->createMock(SymfonyMailerInterface::class);
        $symfonyMailer->expects(self::once())
            ->method('send')
            ->with(self::callback(static function (Email $email) use ($expectedSubject, $expectedBody): bool {
                return $expectedSubject === $email->getSubject()
                    && $expectedBody === $email->getHtmlBody();
            }));

        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturn($template);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('https://example.com/reset/token');

        $mailer = new Mailer($symfonyMailer, $twig, $urlGenerator, 'noreply@example.com', 'template.html.twig');

        $user = new User();
        $user->setEmail('user@example.com');
        $user->setConfirmationToken('token');

        $mailer->sendResetPasswordEmail($user);
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function emailTemplateData(): iterable
    {
        yield 'LF line endings' => [
            "Subject\nFirst line\nSecond line",
            'Subject',
            "First line\nSecond line",
        ];

        yield 'multiline body with blank line' => [
            "Subject\n\nFirst line\n\nSecond line",
            'Subject',
            "First line\n\nSecond line",
        ];

        yield 'single line body' => [
            "Subject\nBody only",
            'Subject',
            'Body only',
        ];

        yield 'HTML body' => [
            "Reset Password\n<p>Click <a href=\"https://example.com\">here</a></p>",
            'Reset Password',
            '<p>Click <a href="https://example.com">here</a></p>',
        ];
    }

    public function testThrowsWhenNoConfirmationToken(): void
    {
        $symfonyMailer = $this->createMock(SymfonyMailerInterface::class);
        $twig = $this->createMock(Environment::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $mailer = new Mailer($symfonyMailer, $twig, $urlGenerator, 'noreply@example.com', 'template.html.twig');

        $user = new User();
        $user->setEmail('test@example.com');

        $this->expectException(\RuntimeException::class);
        $mailer->sendResetPasswordEmail($user);
    }

    public function testThrowsWhenEmptyConfirmationToken(): void
    {
        $symfonyMailer = $this->createMock(SymfonyMailerInterface::class);
        $twig = $this->createMock(Environment::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $mailer = new Mailer($symfonyMailer, $twig, $urlGenerator, 'noreply@example.com', 'template.html.twig');

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setConfirmationToken('');

        $this->expectException(\RuntimeException::class);
        $mailer->sendResetPasswordEmail($user);
    }

    public function testGeneratesCorrectResetUrl(): void
    {
        $symfonyMailer = $this->createMock(SymfonyMailerInterface::class);
        $symfonyMailer->expects(self::once())->method('send');

        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())
            ->method('render')
            ->with(
                '@SensioLabsAdmin/User/Email/reset_password.html.twig',
                self::callback(static function (array $context): bool {
                    return 'https://example.com/resetting/reset/my-token' === $context['confirmationUrl']
                        && $context['user'] instanceof User;
                }),
            )
            ->willReturn("Subject\nBody");

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(self::once())
            ->method('generate')
            ->with(
                'sensiolabs_admin_user_resetting_reset',
                ['token' => 'my-token'],
                UrlGeneratorInterface::ABSOLUTE_URL,
            )
            ->willReturn('https://example.com/resetting/reset/my-token');

        $mailer = new Mailer(
            $symfonyMailer,
            $twig,
            $urlGenerator,
            'noreply@example.com',
            '@SensioLabsAdmin/User/Email/reset_password.html.twig',
        );

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setConfirmationToken('my-token');

        $mailer->sendResetPasswordEmail($user);
    }
}
