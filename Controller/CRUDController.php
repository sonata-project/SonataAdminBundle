<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\Sonata\BaseApplicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Form\Form;


use Bundle\Sonata\BaseApplicationBundle\Tool\DoctrinePager as Pager;

class CRUDController extends Controller
{

    protected $configuration;

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->configure();
    }

    public function configure()
    {

       $this->configuration = $this->container
           ->get('base_application.admin.pool')
           ->getAdminConfigurationByControllerName(get_class($this));
    }

    public function getBaseTemplate()
    {

        if($this->get('request')->isXmlHttpRequest()) {
            return 'Sonata\BaseApplicationBundle::ajax_layout.twig';
        }

        return 'Sonata\BaseApplicationBundle::standard_layout.twig';
    }

    public function listAction()
    {

        $datagrid = $this->configuration->getFilterDatagrid();
        $datagrid->setValues($this->get('request')->query->all());

        return $this->render($this->configuration->getListTemplate(), array(
            'datagrid'          => $datagrid,
            'fields'            => $this->configuration->getListFields(),
            'class_meta_data'   => $this->configuration->getClassMetaData(),
            'configuration'     => $this->configuration,
            'batch_actions'     => $this->configuration->getBatchActions(),
            'base_template'     => $this->getBaseTemplate(),
        ));

    }


    public function batchActionDelete($idx)
    {
        $em = $this->configuration->getEntityManager();

        $query_builder = $em->createQueryBuilder();
        $objects = $query_builder
            ->select('o')
            ->from($this->configuration->getClass(), 'o')
            ->add('where', $query_builder->expr()->in('o.id', $idx))
            ->getQuery()
            ->execute();

        
        foreach($objects as $object) {
            $em->remove($object);
        }

        $em->flush();

        // todo : add confirmation flash var
        return $this->redirect($this->configuration->generateUrl('list'));
    }

    public function deleteAction($id)
    {
        // todo
    }

    public function editAction($id)
    {

        $this->get('session')->start();

        $fields = $this->configuration->getFormFields();

        if($id instanceof Form) {
            $object = $id->getData();
            $form   = $id;
        } else {
            $object = $this->configuration->getObject($id);

            if(!$object) {
                throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
            }

            $form   = $this->configuration->getForm($object, $fields);
        }

        return $this->render($this->configuration->getEditTemplate(), array(
            'form'   => $form,
            'object' => $object,
            'fields' => $fields,
            'form_groups'    => $this->configuration->getFormGroups(),
            'configuration'  => $this->configuration,
            'base_template'  => $this->getBaseTemplate(),
        ));
    }

    public function updateAction()
    {

        $this->get('session')->start();

        if($this->get('request')->getMethod() != 'POST') {
           throw new \RuntimeException('invalid request type, POST expected');
        }

        $id = $this->get('request')->get('id');

        if(is_numeric($id)) {
            $object = $this->configuration->getObject($id);

            if(!$object) {
                throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
            }

            $action = 'edit';
        } else {
            $object = $this->configuration->getNewInstance();

            $action = 'create';
        }

        $fields = $this->configuration->getFormFields();
        $form   = $this->configuration->getForm($object, $fields);

        $form->bind($this->get('request')->get('data'));

        if($form->isValid()) {

            if($action == 'create') {
                $this->configuration->preInsert($object);
            } else {
                $this->configuration->preUpdate($object);
            }
            
            $this->configuration->getEntityManager()->persist($object);
            $this->configuration->getEntityManager()->flush($object);

            if($action == 'create') {
                $this->configuration->postInsert($object);
            } else {
                $this->configuration->postUpdate($object);
            }

            if($this->get('request')->isXmlHttpRequest()) {
                return $this->createResponse('ok');
            }

            // redirect to edit mode
            return $this->redirect($this->configuration->generateUrl('edit', array('id' => $object->getId())));
        }

        return $this->forward(sprintf('%s:%s', $this->configuration->getBaseControllerName(), $action), array(
            'id' => $form
        ));
    }

    public function batchAction()
    {
        if($this->get('request')->getMethod() != 'POST') {
           throw new \RuntimeException('invalid request type, POST expected');
        }

        $action = $this->get('request')->get('action');
        $idx    = $this->get('request')->get('idx');

        if(count($idx) == 0) { // no item selected
            // todo : add flash information

            return $this->redirect($this->configuration->generateUrl('list'));
        }

        // execute the action, batchActionXxxxx
        $final_action = sprintf('batchAction%s', ucfirst($action));
        if(!method_exists($this, $final_action)) {
            throw new \RuntimeException(sprintf('A `%s::%s` method must be created', get_class($this), $final_action));
        }

        return call_user_func(array($this, $final_action), $idx);
    }

    public function createAction($id = null)
    {
        $this->get('session')->start();

        $fields = $this->configuration->getFormFields();

        if($id instanceof Form) {
            $object = $id->getData();
            $form   = $id;
        } else {
            $object = $this->configuration->getNewInstance();

            $form   = $this->configuration->getForm($object, $fields);
        }

        return $this->render($this->configuration->getEditTemplate(), array(
            'form'   => $form,
            'object' => $object,
            'fields' => $fields,
            'form_groups'    => $this->configuration->getFormGroups(),
            'configuration'     => $this->configuration,
            'base_template'     => $this->getBaseTemplate(),
        ));
    }

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }
}