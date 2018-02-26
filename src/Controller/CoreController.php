<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Controller;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Search\SearchHandler;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CoreController extends Controller
{
    /**
     * @return Response
     */
    public function dashboardAction()
    {
        $blocks = [
            'top' => [],
            'left' => [],
            'center' => [],
            'right' => [],
            'bottom' => [],
        ];

        foreach ($this->container->getParameter('sonata.admin.configuration.dashboard_blocks') as $block) {
            $blocks[$block['position']][] = $block;
        }

        $parameters = [
            'base_template' => $this->getBaseTemplate(),
            'admin_pool' => $this->container->get('sonata.admin.pool'),
            'blocks' => $blocks,
        ];

        if (!$this->getCurrentRequest()->isXmlHttpRequest()) {
            $parameters['breadcrumbs_builder'] = $this->get('sonata.admin.breadcrumbs_builder');
        }

        return $this->render($this->getTemplateRegistry()->getTemplate('dashboard'), $parameters);
    }

    /**
     * The search action first render an empty page, if the query is set, then the template generates
     * some ajax request to retrieve results for each admin. The Ajax query returns a JSON response.
     *
     * @throws \RuntimeException
     *
     * @return JsonResponse|Response
     */
    public function searchAction(Request $request)
    {
        if ($request->get('admin') && $request->isXmlHttpRequest()) {
            try {
                $admin = $this->getAdminPool()->getAdminByAdminCode($request->get('admin'));
            } catch (ServiceNotFoundException $e) {
                throw new \RuntimeException('Unable to find the Admin instance', $e->getCode(), $e);
            }

            if (!$admin instanceof AdminInterface) {
                throw new \RuntimeException('The requested service is not an Admin instance');
            }

            $handler = $this->getSearchHandler();

            $results = [];

            if ($pager = $handler->search($admin, $request->get('q'), $request->get('page'), $request->get('offset'))) {
                foreach ($pager->getResults() as $result) {
                    $results[] = [
                        'label' => $admin->toString($result),
                        'link' => $admin->generateObjectUrl('edit', $result),
                        'id' => $admin->id($result),
                    ];
                }
            }

            $response = new JsonResponse([
                'results' => $results,
                'page' => $pager ? (int) $pager->getPage() : false,
                'total' => $pager ? (int) $pager->getNbResults() : false,
            ]);
            $response->setPrivate();

            return $response;
        }

        return $this->render($this->getTemplateRegistry()->getTemplate('search'), [
            'base_template' => $this->getBaseTemplate(),
            'breadcrumbs_builder' => $this->get('sonata.admin.breadcrumbs_builder'),
            'admin_pool' => $this->container->get('sonata.admin.pool'),
            'query' => $request->get('q'),
            'groups' => $this->getAdminPool()->getDashboardGroups(),
        ]);
    }

    /**
     * Get the request object from the container.
     *
     * This method is compatible with both Symfony 2.3 and Symfony 3
     *
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since 3.0, to be removed in 4.0 and action methods will be adjusted.
     *             Use Symfony\Component\HttpFoundation\Request as an action argument
     *
     * @return Request
     */
    public function getRequest()
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since 3.0 and will be removed in 4.0.'.
            ' Inject the Symfony\Component\HttpFoundation\Request into the actions instead.',
            E_USER_DEPRECATED
        );

        return $this->getCurrentRequest();
    }

    /**
     * @return Pool
     */
    protected function getAdminPool()
    {
        return $this->container->get('sonata.admin.pool');
    }

    /**
     * @return SearchHandler
     */
    protected function getSearchHandler()
    {
        return $this->get('sonata.admin.search.handler');
    }

    /**
     * @return string
     */
    protected function getBaseTemplate()
    {
        if ($this->getCurrentRequest()->isXmlHttpRequest()) {
            return $this->getTemplateRegistry()->getTemplate('ajax');
        }

        return $this->getTemplateRegistry()->getTemplate('layout');
    }

    /**
     * @return TemplateRegistryInterface
     */
    private function getTemplateRegistry()
    {
        return $this->container->get('sonata.admin.global_template_registry');
    }

    /**
     * Get the request object from the container.
     *
     * @return Request
     */
    private function getCurrentRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }
}
