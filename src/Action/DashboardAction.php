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
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;


final class DashboardAction
{
    /**
     * @var array
     */
    private $dashboardBlocks;

    /**
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
    private $templatingEngine;

    public function __construct(
        array $dashboardBlocks,
        BreadcrumbsBuilderInterface $breadcrumbsBuilder,
        TemplateRegistryInterface $templateRegistry,
        Pool $pool,
        EngineInterface $templatingEngine
    ) {
        $this->dashboardBlocks = $dashboardBlocks;
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
        $this->templateRegistry = $templateRegistry;
        $this->pool = $pool;
        $this->templatingEngine = $templatingEngine;
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
            'admin_pool' => $this->pool,
            'blocks' => $blocks,
        ];

        if (!$request->isXmlHttpRequest()) {
            $parameters['breadcrumbs_builder'] = $this->breadcrumbsBuilder;
        }

        return new Response($this->templatingEngine->render($this->templateRegistry->getTemplate('dashboard'), $parameters));
    }
}
