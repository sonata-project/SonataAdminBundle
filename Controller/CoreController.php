<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;


class CoreController extends Controller
{
    /**
     * @return string
     */
    public function getBaseTemplate()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->container->get('sonata.admin.pool')->getTemplate('ajax');
        }

        return $this->container->get('sonata.admin.pool')->getTemplate('layout');
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    public function dashboardAction()
    {
        return $this->render('SonataAdminBundle:Core:dashboard.html.twig', array(
            'groups'          => $this->get('sonata.admin.pool')->getDashboardGroups(),
            'base_template'   => $this->getBaseTemplate(),
            'admin_pool'      => $this->container->get('sonata.admin.pool')
        ));
    }
}