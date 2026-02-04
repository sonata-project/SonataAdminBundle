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

namespace SensioLabs\AdminBundle\Action;

use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\BCLayer\BCHelper;
use SensioLabs\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class SearchAction
{
    public function __construct(
        private Pool $pool,
        private TemplateRegistryInterface $templateRegistry,
        private Environment $twig,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        return new Response($this->twig->render($this->templateRegistry->getTemplate('search'), [
            'base_template' => $request->isXmlHttpRequest() ?
                $this->templateRegistry->getTemplate('ajax') :
                $this->templateRegistry->getTemplate('layout'),
            'query' => BCHelper::getFromRequest($request, 'q', ''),
            'groups' => $this->pool->getDashboardGroups(),
        ]));
    }
}
