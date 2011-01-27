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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Form\Form;


use Sonata\BaseApplicationBundle\Tool\DoctrinePager as Pager;

class CRUDController extends Controller
{

    protected $admin;

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

       $this->admin = $this->container
           ->get('base_application.admin.pool')
           ->getAdminByControllerName(get_class($this));
    }

    public function getBaseTemplate()
    {
        if ($this->get('request')->isXmlHttpRequest()) {
            return $this->container->getParameter('base_application.templates.ajax');
        }

        return $this->container->getParameter('base_application.templates.layout');
    }

    public function listAction()
    {

        $datagrid = $this->admin->getFilterDatagrid();
        $datagrid->setValues($this->get('request')->query->all());

        return $this->render($this->admin->getListTemplate(), array(
            'datagrid'          => $datagrid,
            'fields'            => $this->admin->getListFields(),
            'class_meta_data'   => $this->admin->getClassMetaData(),
            'admin'             => $this->admin,
            'batch_actions'     => $this->admin->getBatchActions(),
            'base_template'     => $this->getBaseTemplate(),
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
        return $this->redirect($this->admin->generateUrl('list'));
    }

    public function deleteAction($id)
    {
        // todo
    }

    public function editAction($id)
    {
        if ($id instanceof Form) {
            $object = $id->getData();
            $form   = $id;
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
            'fields'         => $this->admin->getFormFields($form),
            'form_groups'    => $this->admin->getFormGroups($form),
            'admin'          => $this->admin,
            'base_template'  => $this->getBaseTemplate(),
        ));
    }

    public function updateAction()
    {
        if ($this->get('request')->getMethod() != 'POST') {
           throw new \RuntimeException('invalid request type, POST expected');
        }

        $id = $this->get('request')->get('id');

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

        $form = $this->admin->getForm($object);
        $form->bind($this->get('request')->get('data'));

        var_dump($this->get('request')->get('data'));
        var_dump($object);
        var_dump($this->get('validator')->validate($form));
        die;

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

            if ($this->get('request')->isXmlHttpRequest()) {
                return $this->createResponse(json_encode(array('result' => 'ok', 'object_id' => $object->getId())));
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

        return $this->redirect($url);
    }

    public function batchAction()
    {
        if ($this->get('request')->getMethod() != 'POST') {
           throw new \RuntimeException('invalid request type, POST expected');
        }

        $action = $this->get('request')->get('action');
        $idx    = $this->get('request')->get('idx');

        if (count($idx) == 0) { // no item selected
            // todo : add flash information

            return $this->redirect($this->admin->generateUrl('list'));
        }

        // execute the action, batchActionXxxxx
        $final_action = sprintf('batchAction%s', ucfirst($action));
        if (!method_exists($this, $final_action)) {
            throw new \RuntimeException(sprintf('A `%s::%s` method must be created', get_class($this), $final_action));
        }

        return call_user_func(array($this, $final_action), $idx);
    }

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
            'form' => $form,
            'object' => $object,
            'fields' => $this->admin->getFormFields($form),
            'form_groups' => $this->admin->getFormGroups($form),
            'admin' => $this->admin,
            'base_template' => $this->getBaseTemplate(),
        ));
    }

    public function setConfiguration($configuration)
    {
        $this->admin = $configuration;
    }

    public function getConfiguration()
    {
        return $this->admin;
    }
}