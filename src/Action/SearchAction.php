<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Action;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Search\SearchHandler;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SearchAction extends Controller
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
     * @var BreadcrumbsBuilderInterface
     */
    private $breadcrumbsBuilder;

    public function __construct(
        Pool $pool,
        SearchHandler $searchHandler,
        TemplateRegistryInterface $templateRegistry,
        BreadcrumbsBuilderInterface $breadcrumbsBuilder
    ) {
        $this->pool = $pool;
        $this->searchHandler = $searchHandler;
        $this->templateRegistry = $templateRegistry;
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
    }

    /**
     * The search action first render an empty page, if the query is set, then the template generates
     * some ajax request to retrieve results for each admin. The Ajax query returns a JSON response.
     *
     * @throws \RuntimeException
     *
     * @return JsonResponse|Response
     */
    public function __invoke(Request $request)
    {
        if (!$request->get('admin') || !$request->isXmlHttpRequest()) {
            return $this->render($this->templateRegistry->getTemplate('search'), [
                'base_template' => $request->isXmlHttpRequest() ?
                    $this->templateRegistry->getTemplate('ajax') :
                    $this->templateRegistry->getTemplate('layout'),
                'breadcrumbs_builder' => $this->breadcrumbsBuilder,
                'admin_pool' => $this->pool,
                'query' => $request->get('q'),
                'groups' => $this->pool->getDashboardGroups(),
            ]);
        }

        try {
            $admin = $this->pool->getAdminByAdminCode($request->get('admin'));
        } catch (ServiceNotFoundException $e) {
            throw new \RuntimeException('Unable to find the Admin instance', $e->getCode(), $e);
        }

        if (!$admin instanceof AdminInterface) {
            throw new \RuntimeException('The requested service is not an Admin instance');
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
            foreach ($pager->getResults() as $result) {
                $results[] = [
                    'label' => $admin->toString($result),
                    'link' => $admin->generateObjectUrl('edit', $result),
                    'id' => $admin->id($result),
                ];
            }
            $page = (int) $pager->getPage();
            $total = (int) $pager->getNbResults();
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
