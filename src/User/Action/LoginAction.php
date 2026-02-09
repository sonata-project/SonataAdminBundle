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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

final readonly class LoginAction
{
    public function __construct(
        private Environment $twig,
        private AuthenticationUtils $authenticationUtils,
        private Pool $pool,
        private TemplateRegistryInterface $templateRegistry,
        private TokenStorageInterface $tokenStorage,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (null !== $this->tokenStorage->getToken() && null !== $this->tokenStorage->getToken()->getUser()) {
            return new RedirectResponse($this->urlGenerator->generate('sensiolabs_admin_dashboard'));
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return new Response($this->twig->render('@SensioLabsAdmin/User/Security/login.html.twig', [
            'admin_pool' => $this->pool,
            'error' => $error,
            'last_username' => $lastUsername,
            'reset_route' => 'sensiolabs_admin_user_resetting_request',
        ]));
    }
}
