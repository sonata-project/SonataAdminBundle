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

namespace SensioLabs\AdminBundle\User\Mailer;

use SensioLabs\AdminBundle\User\Model\UserInterface;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final class Mailer implements MailerInterface
{
    public function __construct(
        private readonly SymfonyMailerInterface $mailer,
        private readonly Environment $twig,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $fromEmail,
        private readonly string $emailTemplate,
    ) {
    }

    public function sendResetPasswordEmail(UserInterface $user): void
    {
        $token = $user->getConfirmationToken();

        if (null === $token || '' === $token) {
            throw new \RuntimeException('The user must have a confirmation token set before sending a reset email.');
        }

        $resetUrl = $this->urlGenerator->generate(
            'sensiolabs_admin_user_resetting_reset',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $rendered = $this->twig->render($this->emailTemplate, [
            'user' => $user,
            'confirmationUrl' => $resetUrl,
        ]);

        // First line is the subject, rest is the body
        $lines = explode("\n", trim($rendered), 2);
        $subject = trim($lines[0]);
        $body = trim($lines[1] ?? '');

        $email = (new Email())
            ->from($this->fromEmail)
            ->to($user->getEmail() ?? '')
            ->subject($subject)
            ->html($body);

        $this->mailer->send($email);
    }
}
