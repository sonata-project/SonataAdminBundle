<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\Sonata\BaseApplicationBundle\Admin;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Form\Form;

abstract class Admin extends ContainerAware
{
    protected $class;

    protected $list_fields = false;

    protected $form_fields = false;

    protected $filter_fields = array(); // by default there is no filter

    protected $filter_datagrid;

    protected $max_per_page = 25;

    protected $base_route = '';

    protected $base_controller_name;

    // note : don't like this, but havn't find a better way to do it
    protected $configuration_pool;

    protected $code;

    protected $label;
    
    public function configure()
    {
        $this->buildFormFields();
        $this->buildListFields();
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getEntityManager()
    {
        return $this->container->get('doctrine.orm.default_entity_manager');
    }

    public function getClassMetaData()
    {
        $em             = $this->getEntityManager();

        return $em->getClassMetaData($this->getClass());
    }

    public function getBatchActions()
    {

        return array(
            'delete' => 'action_delete'
        );
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
            ),
            'batch'   => array(
                'url'       => $this->base_route.'_batch',
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

    public function generateUrl($name, $params = array())
    {
        $url = $this->getUrl($name);

        if(!$url) {
            throw new \RuntimeException(sprintf('unable to find the url `%s`', $name));
        }

        if(!is_array($params)) {
            $params = array();
        }

        return $this->container->get('router')->generate($url['url'], array_merge($url['params'], $params));
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
    static public function getBaseFields($metadata, $selected_fields)
    {
        // if nothing is defined we display all fields
        if(!$selected_fields) {
            $selected_fields = array_keys($metadata->reflFields) + array_keys($metadata->associationMappings);
        }

        // make sure we works with array
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


            if(isset($metadata->associationMappings[$name])) {
                $fields[$name] = array_merge(
                    $metadata->associationMappings[$name],
                    $fields[$name]
                );
            }

            if(isset($metadata->reflFields[$name])) {
                $fields[$name]['reflection']  =& $metadata->reflFields[$name];
            }
        }

        return $fields;
    }

    /**
     * @return void
     */
    public function configureFormFields()
    {

    }

    /**
     * return the target objet
     *
     * @param  $id
     * @return
     */
    public function getObject($id)
    {
        
        return $this->getEntityManager()
            ->find($this->getClass(), $id);
    }

    /**
     * build the fields to use in the form
     *
     * @throws RuntimeException
     * @return
     */
    public function buildFormFields()
    {
        $this->form_fields = self::getBaseFields($this->getClassMetaData(), $this->form_fields);

        foreach($this->form_fields as $name => $options) {

            if(!isset($this->form_fields[$name]['type'])) {
                throw new \RuntimeException(sprintf('You must declare a type for the field `%s`', $name));
            }

            // make sure the options field is set
            if(!isset($this->form_fields[$name]['options'])) {
                $this->form_fields[$name]['options'] = array();
            }

            // fix template value for doctrine association fields
            if(!isset($this->form_fields[$name]['template']) && isset($this->form_fields[$name]['type'])) {
                $this->form_fields[$name]['template'] = sprintf('BaseApplicationBundle:CRUD:edit_%s.twig', $this->form_fields[$name]['type']);

                if($this->form_fields[$name]['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_ONE)
                {
                    $this->form_fields[$name]['template'] = 'BaseApplicationBundle:CRUD:edit_one_to_one.twig';
                }

                if($this->form_fields[$name]['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE)
                {
                    $this->form_fields[$name]['template'] = 'BaseApplicationBundle:CRUD:edit_many_to_one.twig';
                    $this->form_fields[$name]['configuration']  = $this->getConfigurationPool()
                        ->getConfigurationByClass($this->form_fields[$name]['targetEntity']);
                }

                if($this->form_fields[$name]['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY)
                {
                    $this->form_fields[$name]['template'] = 'BaseApplicationBundle:CRUD:edit_many_to_many.twig';
                    $this->form_fields[$name]['configuration']  = $this->getConfigurationPool()
                        ->getConfigurationByClass($this->form_fields[$name]['targetEntity']);
                }
            }

            // set correct default value
            if($this->form_fields[$name]['type'] == 'datetime') {

                if(!isset($this->form_fields[$name]['options']['date_widget'])) {
                    $this->form_fields[$name]['options']['date_widget'] = \Symfony\Component\Form\DateField::CHOICE;
                }

                if(!isset($this->form_fields[$name]['options']['years'])) {
                    $this->form_fields[$name]['options']['years'] = range(1900, 2100);
                }

            }

            // unset the identifier field as it is not required to update an object
            if(isset($this->form_fields[$name]['id'])) {
                unset($this->form_fields[$name]);
            }
        }

        $this->configureFormFields();

        return $this->form_fields;
    }

    /**
     * build the field to use in the list view
     *
     * @return void
     */
    public function buildListFields()
    {
        $this->list_fields = self::getBaseFields($this->getClassMetaData(), $this->list_fields);

        foreach($this->list_fields as $name => $options) {

            $this->list_fields[$name]['code'] = $name;

            // set the label if none is set
            if(!isset($this->list_fields[$name]['label']))
            {
                $this->list_fields[$name]['label'] = $name;
            }

            // set the default type if none is set
            if(!isset($this->list_fields[$name]['type'])) {
                $this->list_fields[$name]['type'] = 'string';
            }

            // fix template for mapping
            if($this->list_fields[$name]['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE) {
                $this->list_fields[$name]['template']       = 'BaseApplicationBundle:CRUD:list_many_to_one.twig';
                $this->list_fields[$name]['configuration']  = $this->getConfigurationPool()
                    ->getConfigurationByClass($this->list_fields[$name]['targetEntity']);
            }

            if($this->list_fields[$name]['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_MANY) {
                $this->list_fields[$name]['template']       = 'BaseApplicationBundle:CRUD:list_one_to_many.twig';
            }

            if($this->list_fields[$name]['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY) {
                $this->list_fields[$name]['template']       = 'BaseApplicationBundle:CRUD:list_many_to_many.twig';
            }

            // define the default template
            if(!isset($this->list_fields[$name]['template'])) {
                $this->list_fields[$name]['template'] = sprintf('BaseApplicationBundle:CRUD:list_%s.twig', $this->list_fields[$name]['type']);
            }

            // define the default template for identifier field
            if(isset($this->list_fields[$name]['id'])) {
                $this->list_fields[$name]['template'] = 'BaseApplicationBundle:CRUD:list_identifier.twig';
            }

        }

        if(!isset($this->list_fields['_batch'])) {
            $this->list_fields = array('_batch' => array(
                'code'     => '_batch',
                'template' => 'BaseApplicationBundle:CRUD:list__batch.twig',
                'label'    => 'batch',
            ) ) + $this->list_fields;
        }

         $this->configureListFields();

        return $this->list_fields;
    }

    public function configureListFields()
    {

    }

    public function configureFilterFields()
    {
        
    }

    public function getFilterDatagrid()
    {
        if(!$this->filter_datagrid) {

            $this->filter_datagrid = new \Bundle\BaseApplicationBundle\Tool\Datagrid(
                $this->getClass(),
                $this->getEntityManager()
            );

            $this->filter_datagrid->setMaxPerPage($this->max_per_page);

            $this->configureFilterFields();
            
            $this->filter_datagrid->setFilterFields($this->filter_fields);

            $this->filter_datagrid->buildFilterFields();
        }

        return $this->filter_datagrid;
    }

    /**
     * Construct and build the form field definitions
     *
     * @return list form field definition
     */
    public function getFormFields()
    {
        return $this->form_fields;
    }

    public function getListFields()
    {
        return $this->list_fields;
    }

    public function getChoices($description)
    {
        $targets = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('t')
            ->from($description['targetEntity'], 't')
            ->getQuery()
            ->execute();

        $choices = array();
        foreach($targets as $target) {
            // todo : puts this into a configuration option and use reflection
            foreach(array('getTitle', 'getName', '__toString') as $getter) {
                if(method_exists($target, $getter)) {
                    $choices[$target->getId()] = $target->$getter();
                    break;
                }
            }
        }

        return $choices;
    }

    public function getForm($object, $fields)
    {

        $this->container->get('session')->start();

        $form = new Form('data', $object, $this->container->get('validator'));

        foreach($fields as $name => $description) {

            if(!isset($description['type'])) {

                continue;
            }

            switch($description['type']) {

                case \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY:

                    $transformer = new \Symfony\Bundle\DoctrineBundle\Form\ValueTransformer\CollectionToChoiceTransformer(array(
                        'em'        =>  $this->getEntityManager(),
                        'className' => $description['targetEntity']
                    ));

                    $field = new \Symfony\Component\Form\ChoiceField($name, array_merge(array(
                        'expanded' => true,
                        'multiple' => true,
                        'choices' => $this->getChoices($description),
                        'value_transformer' => $transformer,
                    ), $description['options']));

                    break;

                case \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE:
                case \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_ONE:

                    $transformer = new \Symfony\Bundle\DoctrineBundle\Form\ValueTransformer\EntityToIDTransformer(array(
                        'em'        =>  $this->getEntityManager(),
                        'className' => $description['targetEntity']
                    ));

                    $field = new \Symfony\Component\Form\ChoiceField($name, array_merge(array(
                        'expanded' => false,
                        'choices' => $this->getChoices($description),
                        'value_transformer' => $transformer,
                    ), $description['options']));

                    break;

                case 'string':
                    $field = new \Symfony\Component\Form\TextField($name, $description['options']);
                    break;

                case 'text':
                    $field = new \Symfony\Component\Form\TextareaField($name, $description['options']);
                    break;

                case 'boolean':
                    $field = new \Symfony\Component\Form\CheckboxField($name, $description['options']);
                    break;

                case 'integer':
                    $field = new \Symfony\Component\Form\IntegerField($name, $description['options']);
                    break;

                case 'decimal':
                    $field = new \Symfony\Component\Form\NumberField($name, $description['options']);
                    break;

                case 'datetime':
                    $field = new \Symfony\Component\Form\DateTimeField($name, $description['options']);
                    break;

                case 'date':
                    $field = new \Symfony\Component\Form\DateField($name, $description['options']);
                    break;

                case 'choice':
                    $field = new \Symfony\Component\Form\ChoiceField($name, $description['options']);
                    break;

                case 'array':
                    $field = new \Symfony\Component\Form\FieldGroup($name, $description['options']);

                    $values = $description['reflection']->getValue($object);

                    foreach((array)$values as $k => $v) {
                        $field->add(new \Symfony\Component\Form\TextField($k));
                    }
                    break;

                default:
                    throw new \RuntimeException(sprintf('unknow type `%s`', $description['type']));
            }

            $form->add($field);

        }

        return $form;
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

    public function setConfigurationPool($configuration_pool)
    {
        $this->configuration_pool = $configuration_pool;
    }

    public function getConfigurationPool()
    {
        return $this->configuration_pool;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setFilterFields($filter_fields)
    {
        $this->filter_fields = $filter_fields;
    }

    public function getFilterFields()
    {
        return $this->filter_fields;
    }

    public function setMaxPerPage($max_per_page)
    {
        $this->max_per_page = $max_per_page;
    }

    public function getMaxPerPage()
    {
        return $this->max_per_page;
    }
}