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

use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Bundle\Sonata\BaseApplicationBundle\Tool\Datagrid;

abstract class EntityAdmin extends Admin
{

    /**
     * make sure the base fields are set in the correct format
     *
     * @param  $selected_fields
     * @return array
     */
    static public function getBaseFields(ClassMetadataInfo $metadata, $selectedFields)
    {

        // if nothing is defined we display all fields
        if(!$selectedFields) {
            $selectedFields = array_keys($metadata->reflFields) + array_keys($metadata->associationMappings);
        }

        $fields = array();

        // make sure we works with array
        foreach($selectedFields as $name => $options) {

            $description = new FieldDescription;

            if(is_array($options)) {

                // remove property value
                if(isset($options['type'])) {
                    $description->setType($options['type']);
                    unset($options['type']);
                }

                // remove property value
                if(isset($options['template'])) {
                    $description->setTemplate($options['template']);
                    unset($options['template']);
                }
                
                $description->setOptions($options);
            } else {
                $name = $options;
            }

            $description->setName($name);

            if(isset($metadata->fieldMappings[$name])) {
                $description->setFieldMapping($metadata->fieldMappings[$name]);
            }

            if(isset($metadata->associationMappings[$name])) {
                $description->setAssociationMapping($metadata->associationMappings[$name]);
            }

            $fields[$name] = $description;
        }

        return $fields;
    }

    /**
     * return the entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->container->get('doctrine.orm.default_entity_manager');
    }

    /**
     * build the fields to use in the form
     *
     * @throws RuntimeException
     * @return
     */
    public function buildFormFields()
    {

        if($this->loaded['form_fields']) {
            return;
        }

        $this->loaded['form_fields'] = true;
        
        $this->formFields = self::getBaseFields($this->getClassMetaData(), $this->formFields);

        $pool = $this->getConfigurationPool();
        
        foreach($this->formFields as $name => $formDescription) {

            if(!$formDescription->getType()) {
                throw new \RuntimeException(sprintf('You must declare a type for the field `%s`', $name));
            }

            $formDescription->setAdmin($this);

            $formDescription->setOption('edit', $formDescription->getOption('edit', 'standard'));

            // fix template value for doctrine association fields
            if(!$formDescription->getTemplate()) {

                $formDescription->setTemplate(sprintf('Sonata\BaseApplicationBundle:CRUD:edit_%s.twig', $formDescription->getType()));
                
                if($formDescription->getType() == ClassMetadataInfo::ONE_TO_ONE) {
                    $formDescription->setTemplate('Sonata\BaseApplicationBundle:CRUD:edit_one_to_one.twig');
                }

                if($formDescription->getType() == ClassMetadataInfo::MANY_TO_ONE) {
                    $formDescription->setTemplate('Sonata\BaseApplicationBundle:CRUD:edit_many_to_one.twig');
                }

                if($formDescription->getType() == ClassMetadataInfo::MANY_TO_MANY) {
                    $formDescription->setTemplate('Sonata\BaseApplicationBundle:CRUD:edit_many_to_many.twig');
                }

                if($formDescription->getType() == ClassMetadataInfo::ONE_TO_MANY) {
                    $formDescription->setTemplate('Sonata\BaseApplicationBundle:CRUD:edit_one_to_many.twig');
                }

            }

            $admin = $pool->getAdminByClass($formDescription->getTargetEntity());
            if($admin) {
                $formDescription->setAssociationAdmin($admin);
            }
            
            // set correct default value
            if($formDescription->getType() == 'datetime') {
                $options = $formDescription->getOption('form_fields', array());
                if(!isset($options['years'])) {
                    $options['years'] = range(1900, 2100);
                }
                $formDescription->setOption('form_field', $options);
            }

            // unset the identifier field as it is not required to update an object
            if($formDescription->isIdentifier()) {
                unset($this->formFields[$name]);
            }
        }

        $this->configureFormFields();

        return $this->formFields;
    }

    /**
     * build the field to use in the list view
     *
     * @return void
     */
    public function buildListFields()
    {

        if($this->loaded['list_fields']) {
            return;
        }

        $this->loaded['list_fields'] = true;
        
        $this->listFields = self::getBaseFields($this->getClassMetaData(), $this->listFields);

        $pool = $this->getConfigurationPool();

        // normalize field
        foreach($this->listFields as $name => $fieldDescription) {

            $fieldDescription->setOption('code', $fieldDescription->getOption('code', $name));
            $fieldDescription->setOption('label', $fieldDescription->getOption('label', $name));

            // set the default type if none is set
            if(!$fieldDescription->getType()) {
                $fieldDescription->setType('string');
            }

            $fieldDescription->setAdmin($this);

            if(!$fieldDescription->getTemplate()) {

                $fieldDescription->setTemplate(sprintf('Sonata/BaseApplicationBundle:CRUD:list_%s.twig', $fieldDescription->getType()));

                if($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_ONE) {
                    $fieldDescription->setTemplate('Sonata/BaseApplicationBundle:CRUD:list_many_to_one.twig');
                }

                if($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_ONE) {
                    $fieldDescription->setTemplate('Sonata/BaseApplicationBundle:CRUD:list_one_to_one.twig');
                }

                if($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_MANY) {
                    $fieldDescription->setTemplate('Sonata/BaseApplicationBundle:CRUD:list_one_to_many.twig');
                }

                if($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_MANY) {
                    $fieldDescription->setTemplate('Sonata/BaseApplicationBundle:CRUD:list_many_to_many.twig');
                }
            }

            $admin = $pool->getAdminByClass($fieldDescription->getTargetEntity());
            if ($admin) {
                $fieldDescription->setAssociationAdmin($admin);
            }
        }

        $this->configureListFields();
        
        if(!isset($this->listFields['_batch'])) {
            $fieldDescription = new FieldDescription();
            $fieldDescription->setOptions(array(
                'label' => 'batch',
                'code'  => '_batch'
            ));
            $fieldDescription->setTemplate('Sonata/BaseApplicationBundle:CRUD:list__batch.twig');
            $this->listFields = array( '_batch' => $fieldDescription ) + $this->listFields;
        }

        return $this->listFields;
    }

    public function getChoices(FieldDescription $description)
    {
        $targets = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('t')
            ->from($description->getTargetEntity(), 't')
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

            if(!$description->getType()) {

                continue;
            }

            switch($description->getType()) {

                case ClassMetadataInfo::ONE_TO_MANY:
                case ClassMetadataInfo::MANY_TO_MANY:

                    $transformer = new \Symfony\Bundle\DoctrineBundle\Form\ValueTransformer\CollectionToChoiceTransformer(array(
                        'em'        =>  $this->getEntityManager(),
                        'className' => $description->getTargetEntity()
                    ));

                    $field = new \Symfony\Component\Form\ChoiceField($name, array_merge(array(
                        'expanded' => true,
                        'multiple' => true,
                        'choices' => $this->getChoices($description),
                        'value_transformer' => $transformer,
                    ), $description->getOption('form_field_options', array())));

                    break;

                case ClassMetadataInfo::MANY_TO_ONE:
                case ClassMetadataInfo::ONE_TO_ONE:

                    if($description->getOption('edit') == 'inline') {

                        if(!$description->getAssociationAdmin()) {
                            throw new \RuntimeException(sprintf('inline mode for field `%s` required an Admin definition', $name));
                        }
                        
                        // retrieve the related object
                        $target_object = $description->getValue($object);

                        if(!$target_object) {
                            $target_object = $description->getAssociationAdmin()->getNewInstance();
                        }

                        // retrieve the related form
                        $target_fields = $description->getAssociationAdmin()->getFormFields();
                        $target_form   = $description->getAssociationAdmin()->getForm($target_object, $target_fields);

                        // create the transformer
                        $transformer = new \Bundle\Sonata\BaseApplicationBundle\Form\ValueTransformer\ArrayToObjectTransformer(array(
                            'em'        => $this->getEntityManager(),
                            'className' => $description->getTargetEntity()
                        ));

                        // create the "embedded" field
                        $field = new \Symfony\Component\Form\FieldGroup($name, array(
                            'value_transformer' => $transformer,
                        ));

                        foreach($target_form->getFields() as $name => $form_field) {
                            if($name == '_token') {
                                continue;
                            }

                            $field->add($form_field);
                        }
                    }
                    else
                    {
                        $transformer = new \Symfony\Bundle\DoctrineBundle\Form\ValueTransformer\EntityToIDTransformer(array(
                            'em'        => $this->getEntityManager(),
                            'className' => $description->getTargetEntity()
                        ));

                        $field = new \Symfony\Component\Form\ChoiceField($name, array_merge(array(
                            'expanded' => false,
                            'choices' => $this->getChoices($description),
                            'value_transformer' => $transformer,
                        ), $description->getOption('form_field_options', array())));
                    }

                    break;

                case 'string':
                    $field = new \Symfony\Component\Form\TextField($name, $description->getOption('form_field_options', array()));
                    break;

                case 'text':
                    $field = new \Symfony\Component\Form\TextareaField($name, $description->getOption('form_field_options', array()));
                    break;

                case 'boolean':
                    $field = new \Symfony\Component\Form\CheckboxField($name, $description->getOption('form_field_options', array()));
                    break;

                case 'integer':
                    $field = new \Symfony\Component\Form\IntegerField($name, $description->getOption('form_field_options', array()));
                    break;

                case 'decimal':
                    $field = new \Symfony\Component\Form\NumberField($name, $description->getOption('form_field_options', array()));
                    break;

                case 'datetime':
                    $field = new \Symfony\Component\Form\DateTimeField($name, $description->getOption('form_field_options', array()));
                    break;

                case 'date':
                    $field = new \Symfony\Component\Form\DateField($name, $description->getOption('form_field_options', array()));
                    break;

                case 'choice':
                    $field = new \Symfony\Component\Form\ChoiceField($name, $description->getOption('form_field_options', array()));
                    break;

                case 'array':
                    $field = new \Symfony\Component\Form\FieldGroup($name, $description->getOption('form_field_options', array()));

                    $values = $description->getValue($object);

                    foreach((array)$values as $k => $v) {
                        $field->add(new \Symfony\Component\Form\TextField($k));
                    }
                    break;

                default:
                    throw new \RuntimeException(sprintf('unknow type `%s`', $description->getType()));
            }

            $form->add($field);

        }

        return $form;
    }
}