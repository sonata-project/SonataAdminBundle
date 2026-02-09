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

namespace SensioLabs\AdminBundle\User\Action;

use SensioLabs\AdminBundle\User\Mailer\MailerInterface;
use SensioLabs\AdminBundle\User\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class SendEmailAction
{
    public function __construct(
        private UserManagerInterface $userManager,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private int $tokenTtl,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $email = (string) $request->request->get('email', '');

        if ('' === $email) {
            return new RedirectResponse($this->urlGenerator->generate('sensiolabs_admin_user_resetting_request'));
        }

        $user = $this->userManager->findUserByEmail($email);

        if (null !== $user) {
            if (!$user->isPasswordRequestNonExpired($this->tokenTtl)) {
                $user->setConfirmationToken(bin2hex(random_bytes(32)));
                $user->setPasswordRequestedAt(new \DateTimeImmutable());
                $this->userManager->updateUser($user);
                $this->mailer->sendResetPasswordEmail($user);
            }
        }

        // Always redirect to check-email to avoid user enumeration
        return new RedirectResponse($this->urlGenerator->generate('sensiolabs_admin_user_resetting_check_email'));
    }
}
