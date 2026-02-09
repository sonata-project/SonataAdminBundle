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

use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Templating\TemplateRegistryInterface;
use SensioLabs\AdminBundle\User\Form\Type\ResetPasswordType;
use SensioLabs\AdminBundle\User\Model\UserManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final readonly class ResetAction
{
    public function __construct(
        private Environment $twig,
        private FormFactoryInterface $formFactory,
        private UserManagerInterface $userManager,
        private Pool $pool,
        private TemplateRegistryInterface $templateRegistry,
        private UrlGeneratorInterface $urlGenerator,
        private int $tokenTtl,
    ) {
    }

    public function __invoke(Request $request, string $token): Response
    {
        $user = $this->userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(\sprintf('The user with confirmation token "%s" does not exist.', $token));
        }

        if (!$user->isPasswordRequestNonExpired($this->tokenTtl)) {
            return new RedirectResponse($this->urlGenerator->generate('sensiolabs_admin_user_resetting_request'));
        }

        $form = $this->formFactory->create(ResetPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setConfirmationToken(null);
            $user->setPasswordRequestedAt(null);
            $this->userManager->updateUser($user);

            return new RedirectResponse($this->urlGenerator->generate('sensiolabs_admin_user_security_login'));
        }

        return new Response($this->twig->render('@SensioLabsAdmin/User/Security/Resetting/reset.html.twig', [
            'admin_pool' => $this->pool,
            'token' => $token,
            'form' => $form->createView(),
        ]));
    }
}
