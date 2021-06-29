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
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Request\AdminFetcher;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Sonata\AdminBundle\Search\SearchHandler;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
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
     * @var AdminFetcherInterface
     */
    private $adminFetcher;

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

    // NEXT_MAJOR: Make the last param mandatory.
    public function __construct(
        Pool $pool,
        SearchHandler $searchHandler,
        TemplateRegistryInterface $templateRegistry,
        // NEXT_MAJOR: Remove next line.
        BreadcrumbsBuilderInterface $breadcrumbsBuilder,
        Environment $twig,
        ?AdminFetcherInterface $adminFetcher = null
    ) {
        // NEXT_MAJOR: Remove this.
        if (null === $adminFetcher) {
            @trigger_error(sprintf(
                'Not passing an %s as argument 6 is deprecated since sonata-project/admin-bundle 3.x.',
                AdminFetcherInterface::class,
            ), \E_USER_DEPRECATED);

            $adminFetcher = new AdminFetcher($pool);
        }

        $this->pool = $pool;
        $this->searchHandler = $searchHandler;
        $this->templateRegistry = $templateRegistry;
        // NEXT_MAJOR: Remove next line.
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
        $this->twig = $twig;
        $this->adminFetcher = $adminFetcher;
    }

    /**
     * The search action first render an empty page, if the query is set, then the template generates
     * some ajax request to retrieve results for each admin. The Ajax query returns a JSON response.
     *
     * @return JsonResponse|Response
     */
    public function __invoke(Request $request): Response
    {
        // NEXT_MAJOR: Remove this BC-layer.
        if (null === $request->get('_sonata_admin') && null !== $request->get('admin')) {
            @trigger_error(
                'Not passing the "_sonata_admin" parameter in the request is deprecated since sonata-project/admin-bundle 3.x'
                .' and will throw an exception in 4.0.',
                \E_USER_DEPRECATED
            );

            $request->query->set('_sonata_admin', $request->get('admin'));
        }

        if (null === $request->get('_sonata_admin') || !$request->isXmlHttpRequest()) {
            return new Response($this->twig->render($this->templateRegistry->getTemplate('search'), [
                'base_template' => $request->isXmlHttpRequest() ?
                    $this->templateRegistry->getTemplate('ajax') :
                    $this->templateRegistry->getTemplate('layout'),
                // NEXT_MAJOR: Remove next line.
                'breadcrumbs_builder' => $this->breadcrumbsBuilder,
                // NEXT_MAJOR: Remove next line.
                'admin_pool' => $this->pool,
                'query' => $request->get('q'),
                'groups' => $this->pool->getDashboardGroups(),
            ]));
        }

        $admin = $this->adminFetcher->get($request);

        $results = [];

        $page = false;
        $total = false;
        if ($pager = $this->searchHandler->search(
            $admin,
            $request->get('q'),
            $request->get('page'),
            $request->get('offset')
        )) {
            // NEXT_MAJOR: remove the existence check and just use $pager->getCurrentPageResults()
            if (method_exists($pager, 'getCurrentPageResults')) {
                $pageResults = $pager->getCurrentPageResults();
            } else {
                @trigger_error(sprintf(
                    'Not implementing "%s::getCurrentPageResults()" is deprecated since sonata-project/admin-bundle 3.87 and will fail in 4.0.',
                    PagerInterface::class
                ), \E_USER_DEPRECATED);

                $pageResults = $pager->getResults();
            }

            foreach ($pageResults as $result) {
                $results[] = [
                    'label' => $admin->toString($result),
                    'link' => $admin->getSearchResultLink($result),
                    'id' => $admin->id($result),
                ];
            }
            $page = (int) $pager->getPage();

            // NEXT_MAJOR: remove the existence check and just use $pager->countResults() without casting to int
            if (method_exists($pager, 'countResults')) {
                $total = (int) $pager->countResults();
            } else {
                @trigger_error(sprintf(
                    'Not implementing "%s::countResults()" is deprecated since sonata-project/admin-bundle 3.86 and will fail in 4.0.',
                    'Sonata\AdminBundle\Datagrid\PagerInterface'
                ), \E_USER_DEPRECATED);
                $total = (int) $pager->getNbResults();
            }
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
