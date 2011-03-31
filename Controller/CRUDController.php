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

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CRUDController extends Controller
{

    /**
     * The related Admin class
     * 
     * @var Admin
     */
    protected $admin;

    /**
     * @param mixed $data
     * @param integer $status
     * @param array $headers
     *
     * @return Response with json encoded data
     */
    public function renderJson($data, $status = 200, $headers = array())
    {

        // fake content-type so browser does not show the download popup when this
        // response is rendered through an iframe (used by the jquery.form.js plugin)
        //  => don't know yet if it is the best solution
        if ($this->get('request')->get('_xml_http_request')
           && strpos($this->get('request')->headers->get('Content-Type'), 'multipart/form-data') === 0) {
            $headers['Content-Type'] = 'text/plain';
        } else {
            $headers['Content-Type'] = 'application/json';
        }

        return new Response(json_encode($data), $status, $headers);
    }

    /**
     *
     * @return boolean true if the request is done by an ajax like query
     */
    public function isXmlHttpRequest()
    {

        return $this->get('request')->isXmlHttpRequest() || $this->get('request')->get('_xml_http_request');
    }

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->configure();
    }

    /**
     * Contextualize the admin class depends on the current request
     *
     * @throws \RuntimeException
     * @return void
     */
    public function configure()
    {
        $adminCode = $this->container->get('request')->get('_sonata_admin');

        if (!$adminCode) {
            throw new \RuntimeException(sprintf('There is no `_sonata_admin` defined for the controller `%s` and the current route `%s`', get_class($this), $this->container->get('request')->get('_route')));
        }

        $this->admin = $this->container->get('sonata_admin.admin.pool')->getAdminByAdminCode($adminCode);

        if (!$this->admin) {
            throw new \RuntimeException(sprintf('Unable to find the admin class related to the current controller (%s)', get_class($this)));
        }

        $rootAdmin = $this->admin;

        if ($this->admin->isChild()) {
            $this->admin->setCurrentChild(true);
            $rootAdmin = $rootAdmin->getParent();
        }

        $rootAdmin->setRequest($this->container->get('request'));
    }

    /**
     * return the base template name
     * 
     * @return string the template name
     */
    public function getBaseTemplate()
    {
        if ($this->isXmlHttpRequest()) {
            return $this->container->getParameter('sonata_admin.templates.ajax');
        }

        return $this->container->getParameter('sonata_admin.templates.layout');
    }

    /**
     * return the Response object associated to the list action
     *
     * @return Response
     */
    public function listAction()
    {

        return $this->render($this->admin->getListTemplate(), array(
            'datagrid'          => $this->admin->getDatagrid(),
            'list'              => $this->admin->getList(),
            'admin'             => $this->admin,
            'base_template'     => $this->getBaseTemplate(),
            'side_menu'         => $this->admin->getSideMenu('list'),
            'breadcrumbs'       => $this->admin->getBreadcrumbs('list'),
        ));
    }

    /**
     * execute a batch delete
     *
     * @param array $idx
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchActionDelete($idx)
    {
        $em = $this->admin->getModelManager();

        $query_builder = $em->createQueryBuilder();
        $objects = $query_builder
            ->select('o')
            ->from($this->admin->getClass(), 'o')
            ->add('where', $query_builder->expr()->in('o.id', $idx))
            ->getQuery()
            ->execute();

        foreach ($objects as $object) {
            $em->remove($object);
        }

        $em->flush();

        // todo : add confirmation flash var
        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    public function deleteAction($id)
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }
        
        $em = $this->admin->getModelManager();
        $em->remove($object);
        $em->flush();
        
        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * return the Response object associated to the edit action
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @param  $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id)
    {
        $object = $this->admin->getObject($this->get('request')->get($this->admin->getIdParameter()));

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->admin->setSubject($object);

        $form = $this->admin->getForm($object);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            if ($form->isValid()) {
                $this->admin->preUpdate($object);
                $this->admin->getModelManager()->persist($object);
                $this->admin->getModelManager()->flush($object);
                $this->admin->postUpdate($object);

                if ($this->isXmlHttpRequest()) {
                   return $this->renderJson(array('result' => 'ok', 'objectId' => $object->getId()));
                }

                // redirect to edit mode
                return $this->redirectTo($object);
            }
        }

        return $this->render($this->admin->getEditTemplate(), array(
            'form'           => $form,
            'object'         => $object,
            'fields'         => $this->admin->getFormFieldDescriptions(),
            'form_groups'    => $this->admin->getFormGroups(),
            'admin'          => $this->admin,
            'base_template'  => $this->getBaseTemplate(),
            'side_menu'      => $this->admin->getSideMenu('edit'),
            'breadcrumbs'    => $this->admin->getBreadcrumbs('edit'),
        ));
    }

    /**
     * redirect the user depend on this choice
     *
     * @param  $object
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function redirectTo($object) {

        $url = false;

        if ($this->get('request')->get('btn_update_and_list')) {
            $url = $this->admin->generateUrl('list');
        }

        if ($this->get('request')->get('btn_create_and_create')) {
            $url = $this->admin->generateUrl('create');
        }

        if (!$url) {
            $url = $this->admin->generateUrl('edit', array('id' => $object->getId()));
        }

        return new RedirectResponse($url);
    }

    /**
     * return the Response object associated to the batch action
     *
     * @throws \RuntimeException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function batchAction()
    {
        if ($this->get('request')->getMethod() != 'POST') {
           throw new \RuntimeException('invalid request type, POST expected');
        }

        $action = $this->get('request')->get('action');
        $idx    = $this->get('request')->get('idx');

        if (count($idx) == 0) { // no item selected
            // todo : add flash information

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        // execute the action, batchActionXxxxx
        $final_action = sprintf('batchAction%s', ucfirst($action));
        if (!method_exists($this, $final_action)) {
            throw new \RuntimeException(sprintf('A `%s::%s` method must be created', get_class($this), $final_action));
        }

        return call_user_func(array($this, $final_action), $idx);
    }

    /**
     * return the Response object associated to the create action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $object = $this->admin->getNewInstance();
        $form = $this->admin->getForm($object);

        $this->admin->setSubject($object);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            if ($form->isValid()) {
                $this->admin->prePersist($object);
                $this->admin->getModelManager()->persist($object);
                $this->admin->getModelManager()->flush($object);
                $this->admin->postPersist($object);

                if ($this->isXmlHttpRequest()) {
                   return $this->renderJson(array('result' => 'ok', 'objectId' => $object->getId()));
                }

                // redirect to edit mode
                return $this->redirectTo($object);
            }
        }

        return $this->render($this->admin->getEditTemplate(), array(
            'form'          => $form,
            'object'        => $object,
            'fields'        => $this->admin->getFormFieldDescriptions(),
            'form_groups'   => $this->admin->getFormGroups(),
            'admin'         => $this->admin,
            'base_template' => $this->getBaseTemplate(),
            'side_menu'     => $this->admin->getSideMenu('create'),
            'breadcrumbs'   => $this->admin->getBreadcrumbs('create'),
        ));
    }
}