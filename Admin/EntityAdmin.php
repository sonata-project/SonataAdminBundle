<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BaseApplicationBundle\Admin;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Form\Form;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Sonata\BaseApplicationBundle\Tool\Datagrid;

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
    protected function buildFormFields()
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
    protected function buildListFields()
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
    protected function getChoices(FieldDescription $description, $prependChoices = array())
    {

        if (!isset($this->choicesCache[$description->getTargetEntity()])) {
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

            $this->choicesCache[$description->getTargetEntity()] = $choices;
        }

        return $prependChoices + $this->choicesCache[$description->getTargetEntity()];
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
        $transformer = new \Sonata\BaseApplicationBundle\Form\ValueTransformer\ArrayToObjectTransformer(array(
            'em'        => $this->getEntityManager(),
            'className' => $fieldDescription->getTargetEntity()
        ));

        // create the "embedded" field
        if ($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_MANY) {
            $field = new \Sonata\BaseApplicationBundle\Form\EditableFieldGroup($fieldName, array(
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
     * return the class associated to a FieldDescription
     *
     * @throws RuntimeException
     * @param FieldDescription $fieldDescription
     * @return bool
     */
    public function getFormFieldClass(FieldDescription $fieldDescription)
    {

        $class = isset($this->formFieldClasses[$fieldDescription->getType()]) ? $this->formFieldClasses[$fieldDescription->getType()] : false;

        $class = $fieldDescription->getOption('form_field_widget', $class);

        if(!$class) {
            throw new \RuntimeException(sprintf('unknow type `%s`', $fieldDescription->getType()));
        }

        if(!class_exists($class)) {
            throw new \RuntimeException(sprintf('The class `%s` does not exist for field `%s`', $class, $fieldDescription->getType()));
        }

        return $class;
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

        $method = sprintf('add%s', FieldDescription::camelize($mapping['fieldName']));

        $object->$method($instance);
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

        // tweak the widget depend on the edit mode
        if ($fieldDescription->getOption('edit') == 'inline') {

            return $this->getRelatedAssociatedField($object, $fieldDescription);
        }

        $options = array(
            'value_transformer' => new \Symfony\Bundle\DoctrineBundle\Form\ValueTransformer\EntityToIDTransformer(array(
                'em'        =>  $this->getEntityManager(),
                'className' => $fieldDescription->getTargetEntity()
            ))
        );
        $options = array_merge($options, $fieldDescription->getOption('form_field_options', array()));


        if ($fieldDescription->getOption('edit') == 'list') {

            return new \Symfony\Component\Form\TextField($fieldDescription->getFieldName(), $options);
        }

        $class = $fieldDescription->getOption('form_field_widget', 'Symfony\\Component\\Form\\ChoiceField');

        // set valid default value
        if ($class == 'Symfony\\Component\\Form\\ChoiceField') {

            $choices = array();
            if($fieldDescription->getOption('add_empty', false)) {
                $choices = array(
                    $fieldDescription->getOption('add_empty_value', '') => $fieldDescription->getOption('add_empty_value', '')
                );
            }

            $options = array_merge(array(
                'expanded'      => false,
                'choices'       => $this->getChoices($fieldDescription, $choices),
             ), $options);
        }

        return new $class($fieldDescription->getFieldName(), $options);
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
            return new \Sonata\BaseApplicationBundle\Form\EditableCollectionField($prototype);
        }

        return $this->getManyToManyField($object, $fieldDescription);
    }

    protected function getManyToManyField($object, FieldDescription $fieldDescription)
    {

        $options = array(
            'value_transformer' => new \Symfony\Bundle\DoctrineBundle\Form\ValueTransformer\CollectionToChoiceTransformer(array(
                'em'        =>  $this->getEntityManager(),
                'className' => $fieldDescription->getTargetEntity()
            ))
        );

        $options = array_merge($options, $fieldDescription->getOption('form_field_options', array()));

        $class = $fieldDescription->getOption('form_field_widget', 'Symfony\\Component\\Form\\ChoiceField');

        // set valid default value
        if ($class == 'Symfony\\Component\\Form\\ChoiceField') {

            $choices = array();
            if($fieldDescription->getOption('add_empty', false)) {
                $choices = array(
                    $fieldDescription->getOption('add_empty_value', '') => $fieldDescription->getOption('add_empty_value', '')
                );
            }

            $options = array_merge(array(
                'expanded'      => true,
                'multiple'      => true,
                'choices'       => $this->getChoices($fieldDescription, $choices),
             ), $options);
        }

        return new $class($fieldDescription->getFieldName(), $options);
    }

    protected function getManyToOneField($object, FieldDescription $fieldDescription)
    {
                // tweak the widget depend on the edit mode
        if ($fieldDescription->getOption('edit') == 'inline') {

            return $this->getRelatedAssociatedField($object, $fieldDescription);
        }

        $options = array(
            'value_transformer' => new \Symfony\Bundle\DoctrineBundle\Form\ValueTransformer\EntityToIDTransformer(array(
                'em'        =>  $this->getEntityManager(),
                'className' => $fieldDescription->getTargetEntity()
            ))
        );
        $options = array_merge($options, $fieldDescription->getOption('form_field_options', array()));


        if ($fieldDescription->getOption('edit') == 'list') {

            return new \Symfony\Component\Form\TextField($fieldDescription->getFieldName(), $options);
        }

        $class = $fieldDescription->getOption('form_field_widget', 'Symfony\\Component\\Form\\ChoiceField');

        // set valid default value
        if ($class == 'Symfony\\Component\\Form\\ChoiceField') {

            $choices = array();
            if($fieldDescription->getOption('add_empty', false)) {
                $choices = array(
                    $fieldDescription->getOption('add_empty_value', '') => $fieldDescription->getOption('add_empty_value', '')
                );
            }

            $options = array_merge(array(
                'expanded'      => false,
                'choices'       => $this->getChoices($fieldDescription, $choices),
             ), $options);
        }

        return new $class($fieldDescription->getFieldName(), $options);
    }

    protected function getFormFieldInstance($object, FieldDescription $fieldDescription)
    {

        switch ($fieldDescription->getType()) {

            case ClassMetadataInfo::ONE_TO_MANY:

                return $this->getOneToManyField($object, $fieldDescription);

            case ClassMetadataInfo::MANY_TO_MANY:

                return $this->getManyToManyField($object, $fieldDescription);

            case ClassMetadataInfo::MANY_TO_ONE:
                return $this->getManyToOneField($object, $fieldDescription);

            case ClassMetadataInfo::ONE_TO_ONE:

                return $this->getOneToOneField($object, $fieldDescription);

            default:
                $class   = $this->getFormFieldClass($fieldDescription);
                $options = $fieldDescription->getOption('form_field_options', array());

                return new $class($fieldDescription->getFieldName(), $options);
        }
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

        $form = $this->getBaseForm($object);

        foreach ($fields as $fieldDescription) {

            if (!$fieldDescription->getType()) {

                continue;
            }

            $form->add($this->getFormFieldInstance($object, $fieldDescription));
        }

        return $form;
    }
}