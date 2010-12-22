<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\BaseApplicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Form\Form;


use Bundle\BaseApplicationBundle\Tool\DoctrinePager as Pager;

class CRUDController extends Controller
{
    protected $class;

    protected $list_fields = false;

    protected $form_fields = false;

    protected $base_route = '';

    protected $base_controller_name;

    public function getClass()
    {
        return $this->class;
    }

    public function getEntityManager()
    {
        return $this->get('doctrine.orm.default_entity_manager');
    }
    
    public function getClassMetaData()
    {
        $em             = $this->getEntityManager();

        return $em->getClassMetaData($this->getClass());
    }

    public function getListQueryBuilder()
    {
        $em             = $this->getEntityManager();
        $repository     = $em->getRepository($this->getClass());

        $query_buidler = $repository
            ->createQueryBuilder('c');

        return $query_buidler;
    }

    public function getUrls()
    {
        return array(
            'list' => array(
                'url'       => $this->base_route.'_list',
                'params'    => array(),
            ),
            'create' => array(
                'url'       => $this->base_route.'_create',
                'params'    => array(),
            ),
            'update' => array(
                'url'       => $this->base_route.'_update',
                'params'    => array()
            ),
            'delete' => array(
                'url'       => $this->base_route.'_delete',
                'params'    => array()
            ),
            'edit'   => array(
                'url'       => $this->base_route.'_edit',
                'params'    => array()
            )
        );
    }

    public function getUrl($name)
    {
        $urls = $this->getUrls();

        if(!isset($urls[$name])) {
            return false;
        }
        
        return $urls[$name];
    }

    public function listAction()
    {

        $pager = new Pager($this->getClass());

        $url = $this->getUrl('list');

        $pager->setRouter($this->get('router'));
        $pager->setRoute($url['url']);

        $pager->setQueryBuilder($this->getListQueryBuilder());
        $pager->setPage($this->get('request')->get('page', 1));
        $pager->init();

        return $this->render($this->getListTemplate(), array(
            'pager'             => $pager,
            'fields'            => $this->getListFields(),
            'class_meta_data'   => $this->getClassMetaData(),
            'urls'              => $this->getUrls()
        ));

    }

    public function getListTemplate()
    {
        return 'BaseApplicationBundle:CRUD:list.twig';
    }

    public function getEditTemplate()
    {
        return 'BaseApplicationBundle:CRUD:edit.twig';
    }

    public function getReflectionFields()
    {
        return $this->getClassMetaData()->reflFields;
    }

    /**
     * make sure the base field are set in the correct format
     *
     * @param  $selected_fields
     * @return array
     */
    public function getBaseFields($selected_fields)
    {
        // if nothing is defined we display all fields
        if(!$selected_fields) {
            $selected_fields = array_keys($this->getClassMetaData()->reflFields);
        }

        $metadata = $this->getClassMetaData();
        
        // make sure we works with array
        $fields = array();
        foreach($selected_fields as $name => $options) {
            if(is_array($options)) {
                $fields[$name] = $options;
            } else {
                $fields[$options] = array();
                $name = $options;
            }

            if(isset($metadata->fieldMappings[$name])) {
                $fields[$name] = array_merge(
                    $metadata->fieldMappings[$name],
                    $fields[$name]
                );
            }

            if(isset($metadata->reflFields[$name])) {
                $fields[$name]['reflection']  =& $metadata->reflFields[$name];
            }
        }

        return $fields;
    }

    public function getFormFields()
    {
        $this->form_fields = $this->getBaseFields($this->form_fields);

        foreach($this->form_fields as $name => $options) {
            if(!isset($this->form_fields[$name]['template'])) {
                $this->form_fields[$name]['template'] = sprintf('BaseApplicationBundle:CRUD:edit_%s.twig', $this->form_fields[$name]['type']);
            }

            if(isset($this->form_fields[$name]['id'])) {
                unset($this->form_fields[$name]);
            }
        }

        return $this->form_fields;
    }

    public function getListFields()
    {
        
        $this->list_fields = $this->getBaseFields($this->list_fields);

        foreach($this->list_fields as $name => $options) {
            if(!isset($this->list_fields[$name]['type'])) {
                $this->list_fields[$name]['type'] = 'string';
            }

            if(!isset($this->list_fields[$name]['template'])) {
                $this->list_fields[$name]['template'] = sprintf('BaseApplicationBundle:CRUD:list_%s.twig', $this->list_fields[$name]['type']);
            }

            if(isset($this->list_fields[$name]['id'])) {
                $this->list_fields[$name]['template'] = 'BaseApplicationBundle:CRUD:list_identifier.twig';
            }
        }

        return $this->list_fields;
    }

    public function deleteAction($id)
    {

    }

    public function editAction($id)
    {

        $this->get('session')->start();

        $fields = $this->getFormFields();

        if($id instanceof Form) {
            $object = $id->getData();
            $form   = $id;
        } else {
            $object = $this->get('doctrine.orm.default_entity_manager')->find($this->getClass(), $id);

            if(!$object) {
                throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
            }

            $form   = $this->getForm($object, $fields);
        }

        return $this->render($this->getEditTemplate(), array(
            'form'   => $form,
            'object' => $object,
            'fields' => $fields,
            'urls'   => $this->getUrls()
        ));
    }

    public function getForm($object, $fields)
    {

        $form = new Form('data', $object, $this->get('validator'));

        foreach($fields as $name => $description) {

            switch($description['type']) {
                case 'string':
                    $field = new \Symfony\Component\Form\TextField($name);
                    break;

                case 'text':
                    $field = new \Symfony\Component\Form\TextareaField($name);
                    break;

                case 'boolean':
                    $field = new \Symfony\Component\Form\CheckboxField($name);
                    break;

                case 'integer':
                    $field = new \Symfony\Component\Form\IntegerField($name);
                    break;

                case 'decimal':
                    $field = new \Symfony\Component\Form\NumberField($name);
                    break;

                case 'datetime':
                    $field = new \Symfony\Component\Form\DateTimeField($name);
                    break;

                case 'date':
                    $field = new \Symfony\Component\Form\DateField($name);
                    break;

                case 'array':
                    $field = new \Symfony\Component\Form\FieldGroup($name);

                    $values = $description['reflection']->getValue($object);

                    foreach((array)$values as $k => $v) {
                        $field->add(new \Symfony\Component\Form\TextField($k));
                    }
            }

            $form->add($field);

        }

        return $form;
    }

    public function updateAction()
    {

        $this->get('session')->start();

        if($this->get('request')->getMethod() != 'POST') {
           throw new \RuntimeException('invalid request type, POST expected');
        }

        $id = $this->get('request')->get('id');

        if(is_numeric($id)) {
            $object = $this->get('doctrine.orm.default_entity_manager')->find($this->getClass(), $id);

            if(!$object) {
                throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
            }

            $action = 'edit';
        } else {
            $class = $this->getClass();
            $object = new $class;

            $action = 'create';
        }

        $fields = $this->getFormFields();
        $form   = $this->getForm($object, $fields);

        $form->bind($this->get('request')->get('data'));

        if($form->isValid()) {

            $this->getEntityManager()->persist($object);
            $this->getEntityManager()->flush($object);

            // redirect to edit mode
            $url = $this->getUrl('edit');

            return $this->redirect($this->generateUrl($url['url'], array('id' => $object->getId())));
        }

        return $this->forward(sprintf('%s:%s', $this->getBaseControllerName(), $action), array(
            'id' => $form
        ));
    }

    public function createAction($form = null)
    {
        $this->get('session')->start();

        $fields = $this->getFormFields();

        if($form instanceof Form) {
            $object = $form->getData();
        } else {
            $class = $this->getClass();
            $object = new $class;

            $form   = $this->getForm($object, $fields);
        }

        return $this->render($this->getEditTemplate(), array(
            'form'   => $form,
            'object' => $object,
            'fields' => $fields,
            'urls'   => $this->getUrls()
        ));
    }

    public function setBaseControllerName($base_controller_name)
    {
        $this->base_controller_name = $base_controller_name;
    }

    public function getBaseControllerName()
    {
        return $this->base_controller_name;
    }

    public function setBaseRoute($base_route)
    {
        $this->base_route = $base_route;
    }

    public function getBaseRoute()
    {
        return $this->base_route;
    }
}