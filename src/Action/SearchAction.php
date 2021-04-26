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
use Sonata\AdminBundle\Search\SearchHandler;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class SearchAction
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var SearchHandler
     */
    private $searchHandler;

    /**
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var BreadcrumbsBuilderInterface
     */
    private $breadcrumbsBuilder;

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(
        Pool $pool,
        SearchHandler $searchHandler,
        TemplateRegistryInterface $templateRegistry,
        // NEXT_MAJOR: Remove next line.
        BreadcrumbsBuilderInterface $breadcrumbsBuilder,
        Environment $twig
    ) {
        $this->pool = $pool;
        $this->searchHandler = $searchHandler;
        $this->templateRegistry = $templateRegistry;
        // NEXT_MAJOR: Remove next line.
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
        $this->twig = $twig;
    }

    /**
     * The search action first render an empty page, if the query is set, then the template generates
     * some ajax request to retrieve results for each admin. The Ajax query returns a JSON response.
     *
     * @return JsonResponse|Response
     */
    public function __invoke(Request $request): Response
    {
        if (!$request->get('admin') || !$request->isXmlHttpRequest()) {
            return new Response($this->twig->render($this->templateRegistry->getTemplate('search'), [
                'base_template' => $request->isXmlHttpRequest() ?
                    $this->templateRegistry->getTemplate('ajax') :
                    $this->templateRegistry->getTemplate('layout'),
                // NEXT_MAJOR: Remove next line.
                'breadcrumbs_builder' => $this->breadcrumbsBuilder,
                'query' => $request->get('q'),
                'groups' => $this->pool->getDashboardGroups(),
            ]));
        }

        try {
            $admin = $this->pool->getAdminByAdminCode($request->get('admin'));
        } catch (ServiceNotFoundException $e) {
            throw new \RuntimeException('Unable to find the Admin instance', (int) $e->getCode(), $e);
        }

        $results = [];

        $page = false;
        $total = false;
        if ($pager = $this->searchHandler->search(
            $admin,
            $request->get('q'),
            $request->get('page'),
            $request->get('offset')
        )) {
            $pageResults = $pager->getCurrentPageResults();

            foreach ($pageResults as $result) {
                $results[] = [
                    'label' => $admin->toString($result),
                    'link' => $admin->getSearchResultLink($result),
                    'id' => $admin->id($result),
                ];
            }
            $page = $pager->getPage();
            $total = $pager->countResults();
        }

        $response = new JsonResponse([
            'results' => $results,
            'page' => $page,
            'total' => $total,
        ]);
        $response->setPrivate();

        return $response;
    }
}
