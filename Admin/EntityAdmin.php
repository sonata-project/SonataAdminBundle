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

    protected $choices_cache = array();
    
    /**
     * make sure the base fields are set in the correct format
     *
     * @param  $selected_fields
     * @return array
     */
    static public function getBaseFields(ClassMetadataInfo $metadata, $selectedFields)
    {

        // if nothing is defined we display all fields
        if (!$selectedFields) {
            $selectedFields = array_keys($metadata->reflFields) + array_keys($metadata->associationMappings);
        }

        $fields = array();

        // make sure we works with array
        foreach ($selectedFields as $name => $options) {

            $description = new FieldDescription;

            if (is_array($options)) {

                // remove property value
                if (isset($options['type'])) {
                    $description->setType($options['type']);
                    unset($options['type']);
                }

                // remove property value
                if (isset($options['template'])) {
                    $description->setTemplate($options['template']);
                    unset($options['template']);
                }
                
                $description->setOptions($options);
            } else {
                $name = $options;
            }

            $description->setName($name);

            if (isset($metadata->fieldMappings[$name])) {
                $description->setFieldMapping($metadata->fieldMappings[$name]);
            }

            if (isset($metadata->associationMappings[$name])) {
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

        if ($this->loaded['form_fields']) {
            return;
        }

        $this->loaded['form_fields'] = true;
        
        $this->formFields = self::getBaseFields($this->getClassMetaData(), $this->formFields);

        foreach ($this->formFields as $name => $fieldDescription) {

            if (!$fieldDescription->getType()) {
                throw new \RuntimeException(sprintf('You must declare a type for the field `%s`', $name));
            }

            $fieldDescription->setAdmin($this);
            $fieldDescription->setOption('edit', $fieldDescription->getOption('edit', 'standard'));

            // fix template value for doctrine association fields
            if (!$fieldDescription->getTemplate()) {

                $fieldDescription->setTemplate(sprintf('SonataBaseApplicationBundle:CRUD:edit_%s.twig.html', $fieldDescription->getType()));
                
                if ($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_ONE) {
                    $fieldDescription->setTemplate('SonataBaseApplicationBundle:CRUD:edit_one_to_one.twig.html');
                    $this->attachAdminClass($fieldDescription);
                }

                if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_ONE) {
                    $fieldDescription->setTemplate('SonataBaseApplicationBundle:CRUD:edit_many_to_one.twig.html');
                    $this->attachAdminClass($fieldDescription);
                }

                if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_MANY) {
                    $fieldDescription->setTemplate('SonataBaseApplicationBundle:CRUD:edit_many_to_many.twig.html');
                    $this->attachAdminClass($fieldDescription);
                }

                if ($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_MANY) {
                    $fieldDescription->setTemplate('SonataBaseApplicationBundle:CRUD:edit_one_to_many.twig.html');

                    if($fieldDescription->getOption('edit') == 'inline' && !$fieldDescription->getOption('widget')) {
                        $fieldDescription->setOption('widget', 'Bundle\\Sonata\\BaseApplicationBundle\\Form\\EditableGroupField');
                    }

                    $this->attachAdminClass($fieldDescription);
                }
            }
            
            // set correct default value
            if ($fieldDescription->getType() == 'datetime') {
                $options = $fieldDescription->getOption('form_fields', array());
                if (!isset($options['years'])) {
                    $options['years'] = range(1900, 2100);
                }
                $fieldDescription->setOption('form_field', $options);
            }

            // unset the identifier field as it is not required to update an object
            if ($fieldDescription->isIdentifier()) {
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

        if ($this->loaded['list_fields']) {
            return;
        }

        $this->loaded['list_fields'] = true;
        
        $this->listFields = self::getBaseFields($this->getClassMetaData(), $this->listFields);

        // normalize field
        foreach ($this->listFields as $name => $fieldDescription) {

            $fieldDescription->setOption('code', $fieldDescription->getOption('code', $name));
            $fieldDescription->setOption('label', $fieldDescription->getOption('label', $name));

            // set the default type if none is set
            if (!$fieldDescription->getType()) {
                $fieldDescription->setType('string');
            }

            $fieldDescription->setAdmin($this);

            if (!$fieldDescription->getTemplate()) {

                $fieldDescription->setTemplate(sprintf('SonataBaseApplicationBundle:CRUD:list_%s.twig.html', $fieldDescription->getType()));

                if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_ONE) {
                    $fieldDescription->setTemplate('SonataBaseApplicationBundle:CRUD:list_many_to_one.twig.html');
                    $this->attachAdminClass($fieldDescription);
                }

                if ($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_ONE) {
                    $fieldDescription->setTemplate('SonataBaseApplicationBundle:CRUD:list_one_to_one.twig.html');
                    $this->attachAdminClass($fieldDescription);
                }

                if ($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_MANY) {
                    $fieldDescription->setTemplate('SonataBaseApplicationBundle:CRUD:list_one_to_many.twig.html');
                    $this->attachAdminClass($fieldDescription);
                }

                if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_MANY) {
                    $fieldDescription->setTemplate('SonataBaseApplicationBundle:CRUD:list_many_to_many.twig.html');
                    $this->attachAdminClass($fieldDescription);
                }
            }
        }

        $this->configureListFields();
        
        if (!isset($this->listFields['_batch'])) {
            $fieldDescription = new FieldDescription();
            $fieldDescription->setOptions(array(
                'label' => 'batch',
                'code'  => '_batch'
            ));
            $fieldDescription->setTemplate('SonataBaseApplicationBundle:CRUD:list__batch.twig.html');
            $this->listFields = array( '_batch' => $fieldDescription ) + $this->listFields;
        }

        return $this->listFields;
    }

    /**
     * return the list of choices for one entity
     *
     * @param FieldDescription $description
     * @return array
     */
    public function getChoices(FieldDescription $description)
    {

        if (!isset($this->choices_cache[$description->getTargetEntity()])) {
            $targets = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('t')
                ->from($description->getTargetEntity(), 't')
                ->getQuery()
                ->execute();

            $choices = array();
            foreach ($targets as $target) {
                // todo : puts this into a configuration option and use reflection
                foreach (array('getTitle', 'getName', '__toString') as $getter) {
                    if (method_exists($target, $getter)) {
                        $choices[$target->getId()] = $target->$getter();
                        break;
                    }
                }
            }

            $this->choices_cache[$description->getTargetEntity()] = $choices;
        }

        return $this->choices_cache[$description->getTargetEntity()];
    }

    /**
     * return the field associated to a FieldDescription
     *   ie : build the embedded form from the related Admin instance
     *
     * @throws RuntimeException
     * @param  $object
     * @param FieldDescription $fieldDescription
     * @param null $fieldName
     * @return FieldGroup
     */
    protected function getRelatedAssociatedField($object, FieldDescription $fieldDescription, $fieldName = null)
    {
        $fieldName = $fieldName ?: $fieldDescription->getFieldName();
        
        $associatedAdmin = $fieldDescription->getAssociationAdmin();
        
        if (!$associatedAdmin) {
            throw new \RuntimeException(sprintf('inline mode for field `%s` required an Admin definition', $fieldName));
        }
        
        // retrieve the related object
        $targetObject = $associatedAdmin->getNewInstance();

        // retrieve the related form
        $targetFields = $associatedAdmin->getFormFields();
        $targetForm   = $associatedAdmin->getForm($targetObject, $targetFields);

        // create the transformer
        $transformer = new \Bundle\Sonata\BaseApplicationBundle\Form\ValueTransformer\ArrayToObjectTransformer(array(
            'em'        => $this->getEntityManager(),
            'className' => $fieldDescription->getTargetEntity()
        ));

        // create the "embedded" field
        if($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_MANY) {
            $field = new \Bundle\Sonata\BaseApplicationBundle\Form\EditableFieldGroup($fieldName, array(
                'value_transformer' => $transformer,
            ));

        } else {
            $field = new \Symfony\Component\Form\FieldGroup($fieldName, array(
                'value_transformer' => $transformer,
            ));
        }
        

        foreach ($targetForm->getFields() as $name => $formField) {
            if ($name == '_token') {
                continue;
            }

            $field->add($formField);
        }

        return $field;
    }

    /**
     * return an OneToOne associated field
     *
     * @param  $object
     * @param FieldDescription $fieldDescription
     * @return ChoiceField
     */
    protected function getOneToOneField($object, FieldDescription $fieldDescription)
    {
        
        if ($fieldDescription->getOption('edit') == 'inline') {
            return $this->getRelatedAssociatedField($object, $fieldDescription);
        }

        $fieldName = $fieldDescription->getFieldName();

        $transformer = new \Symfony\Bundle\DoctrineBundle\Form\ValueTransformer\EntityToIDTransformer(array(
            'em'        => $this->getEntityManager(),
            'className' => $fieldDescription->getTargetEntity()
        ));

        $field = new \Symfony\Component\Form\ChoiceField($fieldName, array_merge(array(
            'expanded' => false,
            'choices' => $this->getChoices($fieldDescription),
            'value_transformer' => $transformer,
        ), $fieldDescription->getOption('form_field_options', array())));

        return $field;
    }

    /**
     * Add a new instance to the related FieldDescription value
     *
     * @param  $object
     * @param FieldDescription $fieldDescription
     * @return void
     */
    public function addNewInstance($object, FieldDescription $fieldDescription)
    {
        $instance = $fieldDescription->getAssociationAdmin()->getNewInstance();
        $mapping  = $fieldDescription->getAssociationMapping();

        $method = sprintf('add%s', $mapping['fieldName']);

        $object->$method($instance);
    }

    /**
     * return the OneToMany associated field
     *
     * @param  $object
     * @param FieldDescription $fieldDescription
     * @return ChoiceField|CollectionField
     */
    protected function getOneToManyField($object, FieldDescription $fieldDescription)
    {
        $fieldName = $fieldDescription->getFieldName();

        if ($fieldDescription->getOption('edit') == 'inline') {
            $prototype = $this->getRelatedAssociatedField($object, $fieldDescription);

            $value = $fieldDescription->getValue($object);

            // add new instances if the min number is not matched
            if ($fieldDescription->getOption('min', 0) > count($value)) {

                $diff = $fieldDescription->getOption('min', 0) - count($value);
                foreach (range(1, $diff) as $i) {
                    $this->addNewInstance($object, $fieldDescription);
                }
            }

            // use custom one to expose the newfield method
            return new  \Bundle\Sonata\BaseApplicationBundle\Form\EditableCollectionField($prototype);
        }

        $transformer = new \Symfony\Bundle\DoctrineBundle\Form\ValueTransformer\CollectionToChoiceTransformer(array(
            'em'        =>  $this->getEntityManager(),
            'className' => $fieldDescription->getTargetEntity()
        ));

        $field = new \Symfony\Component\Form\ChoiceField($fieldName, array_merge(array(
            'expanded' => true,
            'multiple' => true,
            'choices' => $this->getChoices($fieldDescription),
            'value_transformer' => $transformer,
        ), $fieldDescription->getOption('form_field_options', array())));

        return $field;
    }

    /**
     * return a form depend on the given $object and FieldDescription $fields array
     *
     * @throws RuntimeException
     * @param  $object
     * @param  $fields
     * @return Symfony\Component\Form\Form
     */
    public function getForm($object, $fields)
    {

        $this->container->get('session')->start();

        $form = new Form('data', $object, $this->container->get('validator'));

        foreach ($fields as $name => $description) {

            if (!$description->getType()) {

                continue;
            }

            switch($description->getType()) {

                case ClassMetadataInfo::ONE_TO_MANY:
                    
                    $field = $this->getOneToManyField($object, $description);
                    break;

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

                    $field = $this->getOneToOneField($object, $description);

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
                case 'tinyint';
                case 'smallint':
                case 'mediumint':
                case 'bigint':

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

                    foreach ((array)$values as $k => $v) {
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