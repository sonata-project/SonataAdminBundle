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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final readonly class CheckEmailAction
{
    public function __construct(
        private Environment $twig,
        private Pool $pool,
        private TemplateRegistryInterface $templateRegistry,
        private int $tokenTtl,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        return new Response($this->twig->render('@SensioLabsAdmin/User/Security/Resetting/checkEmail.html.twig', [
            'admin_pool' => $this->pool,
            'token_ttl' => $this->tokenTtl,
        ]));
    }
}
