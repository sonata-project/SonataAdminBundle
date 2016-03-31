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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CoreController extends Controller
{
    /**
     * @return \Sonata\AdminBundle\Admin\Pool
     */
    protected function getAdminPool()
    {
        return $this->container->get('sonata.admin.pool');
    }

    /**
     * @return \Sonata\AdminBundle\Search\SearchHandler
     */
    protected function getSearchHandler()
    {
        return $this->get('sonata.admin.search.handler');
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    protected function getBaseTemplate()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->getAdminPool()->getTemplate('ajax');
        }

        return $this->getAdminPool()->getTemplate('layout');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function dashboardAction()
    {
        $blocks = array(
            'top'    => array(),
            'left'   => array(),
            'center' => array(),
            'right'  => array(),
            'bottom' => array(),
        );

        foreach ($this->container->getParameter('sonata.admin.configuration.dashboard_blocks') as $block) {
            $blocks[$block['position']][] = $block;
        }

        return $this->render($this->getAdminPool()->getTemplate('dashboard'), array(
            'base_template'   => $this->getBaseTemplate(),
            'admin_pool'      => $this->container->get('sonata.admin.pool'),
            'blocks'          => $blocks,
        ));
    }

    /**
     * The search action first render an empty page, if the query is set, then the template generates
     * some ajax request to retrieve results for each admin. The Ajax query returns a JSON response.
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     *
     * @throws \RuntimeException
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

            $results = array();

            if ($pager = $handler->search($admin, $request->get('q'), $request->get('page'), $request->get('offset'))) {
                foreach ($pager->getResults() as $result) {
                    $results[] = array(
                        'label' => $admin->toString($result),
                        'link'  => $admin->generateObjectUrl('edit', $result),
                        'id'    => $admin->id($result),
                    );
                }
            }

            $response = new JsonResponse(array(
                'results' => $results,
                'page'    => $pager ? (int) $pager->getPage() : false,
                'total'   => $pager ? (int) $pager->getNbResults() : false,
            ));
            $response->setPrivate();

            return $response;
        }

        return $this->render($this->container->get('sonata.admin.pool')->getTemplate('search'), array(
            'base_template' => $this->getBaseTemplate(),
            'admin_pool'    => $this->container->get('sonata.admin.pool'),
            'query'         => $request->get('q'),
            'groups'        => $this->getAdminPool()->getDashboardGroups(),
        ));
    }

    /**
     * Get the request object from the container.
     *
     * This method is compatible with both Symfony 2.3 and Symfony 3
     *
     * @deprecated Use the Request action argument. This method will be removed
     *             in SonataAdminBundle 3.0 and the action methods adjusted.
     *
     * @return Request
     */
    public function getRequest()
    {
        if ($this->container->has('request_stack')) {
            return $this->container->get('request_stack')->getCurrentRequest();
        }

        return $this->container->get('request');
    }
}
