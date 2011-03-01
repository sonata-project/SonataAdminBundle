<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BaseApplicationBundle\Controller;

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
     *
     * @return Response with json encoded data
     */
    public function renderJson($data, $status = 200, $headers = array())
    {

        // fake content-type so browser does not show the download popup when this
        // response is rendered through an iframe (used by the jquery.form.js plugin)
        //  => don't know yet if it is the best solution
        if($this->get('request')->get('_xml_http_request')) {
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

    public function configure()
    {
        $actionName = $this->container->get('request')->get('_bab_action');
        
        $this->admin = $this->container
            ->get('sonata_base_application.admin.pool')
            ->getAdminByActionName($actionName);

        if(!$this->admin) {
            throw new \RuntimeException(sprintf('Unable to find the admin class related to the current controller (%s)', get_class($this)));
        }

        if($this->container->get('request')->get('uniqid')) {
            $this->admin->setUniqid($this->container->get('request')->get('uniqid'));
        }

        if($this->admin->isChild()) {
            $this->admin->setCurrentChild(true);
        }
    }

    /**
     * return the base template name
     * 
     * @return string the template name
     */
    public function getBaseTemplate()
    {
        if ($this->isXmlHttpRequest()) {
            return $this->container->getParameter('sonata_base_application.templates.ajax');
        }

        return $this->container->getParameter('sonata_base_application.templates.layout');
    }

    /**
     * return the Response object associated to the list action
     *
     * @return Response
     */
    public function listAction()
    {

        $datagrid = $this->admin->getDatagrid();

        return $this->render($this->admin->getListTemplate(), array(
            'datagrid'          => $datagrid,
            'list'              => $this->admin->getList(),
            'admin'             => $this->admin,
            'base_template'     => $this->getBaseTemplate(),
            'side_menu'         => $this->getSideMenu('list'),
            'breadcrumbs'       => $this->getBreadcrumbs('list'),
        ));
    }

    public function batchActionDelete($idx)
    {
        $em = $this->admin->getEntityManager();

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
        // todo
    }

    /**
     * return the Response object associated to the edit action
     *
     * @return Response
     */
    public function editAction($id)
    {

        $id = $this->get('request')->get($this->admin->getIdParameter());

        if ($id instanceof Form) {
            $object = $id->getData();
            $form   = $id;

            // todo : refactor the Form Creation
            $this->admin->getForm($object);
        } else {
            $object = $this->admin->getObject($id);

            if (!$object) {
                throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
            }

            $form = $this->admin->getForm($object);
        }

        $this->admin->setSubject($object);

        return $this->render($this->admin->getEditTemplate(), array(
            'form'           => $form,
            'object'         => $object,
            'fields'         => $this->admin->getFormFieldDescriptions(),
            'form_groups'    => $this->admin->getFormGroups(),
            'admin'          => $this->admin,
            'base_template'  => $this->getBaseTemplate(),
            'side_menu'      => $this->getSideMenu('edit'),
            'breadcrumbs'    => $this->getBreadcrumbs('edit'),
        ));
    }

    /**
     * return the Response object associated to the update action
     *
     * @return Response
     */
    public function updateAction()
    {
        if ($this->get('request')->getMethod() != 'POST') {
           throw new \RuntimeException('invalid request type, POST expected');
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());

        if (is_numeric($id)) {
            $object = $this->admin->getObject($id);

            if (!$object) {
                throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
            }

            $action = 'edit';
        } else {
            $object = $this->admin->getNewInstance();

            $action = 'create';
        }

        $this->admin->setSubject($object);
        
        $form = $this->admin->getForm($object);

        $form->bind($this->get('request'));

        if ($form->isValid()) {

            if ($action == 'create') {
                $this->admin->preInsert($object);
            } else {
                $this->admin->preUpdate($object);
            }

            $this->admin->getEntityManager()->persist($object);
            $this->admin->getEntityManager()->flush($object);

            if ($action == 'create') {
                $this->admin->postInsert($object);
            } else {
                $this->admin->postUpdate($object);
            }

            if ($this->isXmlHttpRequest()) {
                return $this->renderJson(array('result' => 'ok', 'objectId' => $object->getId()));
            }

            // redirect to edit mode
            return $this->redirectTo($object);
        }

        return $this->forward(sprintf('%s:%s', $this->admin->getBaseControllerName(), $action), array(
            'id' => $form
        ));
    }

    /**
     * redirect the user depend on this choice
     *
     * @param  $object
     * @return Response
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
     * @return Response
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
     * @return Response
     */
    public function createAction($id = null)
    {
        if ($id instanceof Form) {
            $object = $id->getData();
            $form = $id;
        } else {
            $object = $this->admin->getNewInstance();
            $form = $this->admin->getForm($object);
        }

        $this->admin->setSubject($object);

        return $this->render($this->admin->getEditTemplate(), array(
            'form'          => $form,
            'object'        => $object,
            'fields'        => $this->admin->getFormFieldDescriptions(),
            'form_groups'   => $this->admin->getFormGroups(),
            'admin'         => $this->admin,
            'base_template' => $this->getBaseTemplate(),
            'side_menu'     => $this->getSideMenu('create'),
            'breadcrumbs'   => $this->getBreadcrumbs('create'),
        ));
    }

    /**
     * @param string $action
     * @return Knplabs\MenuBundle\Menu
     */
    public function getSideMenu($action)
    {
        if($this->admin->isChild()) {
            return $this->admin->getParent()->getSideMenu($action, $this->admin);
        }

        return $this->admin->getSideMenu($action);
    }

    /**
     * @param string $action
     * @return array
     */
    public function getBreadcrumbs($action)
    {
        
        if($this->admin->isChild()) {
            return $this->admin->getParent()->getBreadcrumbs($action);
        }

        return $this->admin->getBreadcrumbs($action);
    }
}