<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Action;

use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class DashboardAction
{
    /**
     * @var array
     */
    private $dashboardBlocks = [];

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var BreadcrumbsBuilderInterface
     */
    private $breadcrumbsBuilder;

    /**
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(
        array $dashboardBlocks,
        // NEXT_MAJOR: Remove next line.
        BreadcrumbsBuilderInterface $breadcrumbsBuilder,
        TemplateRegistryInterface $templateRegistry,
        Pool $pool,
        Environment $twig
    ) {
        $this->dashboardBlocks = $dashboardBlocks;
        // NEXT_MAJOR: Remove next line.
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
        $this->templateRegistry = $templateRegistry;
        $this->pool = $pool;
        $this->twig = $twig;
    }

    public function __invoke(Request $request): Response
    {
        $blocks = [
            'top' => [],
            'left' => [],
            'center' => [],
            'right' => [],
            'bottom' => [],
        ];

        foreach ($this->dashboardBlocks as $block) {
            $blocks[$block['position']][] = $block;
        }

        $parameters = [
            'base_template' => $request->isXmlHttpRequest() ?
                $this->templateRegistry->getTemplate('ajax') :
                $this->templateRegistry->getTemplate('layout'),
            // NEXT_MAJOR: Remove next line.
            'admin_pool' => $this->pool,
            'blocks' => $blocks,
        ];

        // NEXT_MAJOR: Remove the entire if block.
        if (!$request->isXmlHttpRequest()) {
            $parameters['breadcrumbs_builder'] = $this->breadcrumbsBuilder;
        }

        return new Response($this->twig->render($this->templateRegistry->getTemplate('dashboard'), $parameters));
    }
}
