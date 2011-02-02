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
use Sonata\BaseApplicationBundle\Form\ValueTransformer\EntityToIDTransformer;
use Sonata\BaseApplicationBundle\Form\ValueTransformer\ArrayToObjectTransformer;
use Sonata\BaseApplicationBundle\Form\EditableCollectionField;
use Sonata\BaseApplicationBundle\Form\EditableFieldGroup;


    
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
        $transformer = new ArrayToObjectTransformer(array(
            'em'        => $this->getEntityManager(),
            'className' => $fieldDescription->getTargetEntity()
        ));

        // create the "embedded" field
        if ($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_MANY) {
            $field = new EditableFieldGroup($fieldName, array(
                'value_transformer' => $transformer,
            ));

        } else {
            $field = new \Symfony\Component\Form\Form($fieldName, array(
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
     * return the class associated to a FieldDescription if any defined
     *
     * @throws RuntimeException
     * @param FieldDescription $fieldDescription
     * @return bool|string
     */
    public function getFormFieldClass(FieldDescription $fieldDescription)
    {

        $class = false;
        
        // the user redefined the mapping type, use the default built in definition
        if ($fieldDescription->getType() != $fieldDescription->getMappingType()) {

            $class = array_key_exists($fieldDescription->getType(), $this->formFieldClasses) ? $this->formFieldClasses[$fieldDescription->getType()] : false;

        } else if($fieldDescription->getOption('form_field_widget', false)) {

            $class = $fieldDescription->getOption('form_field_widget', false);
            
        }

        if ($class && !class_exists($class)) {
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

        // TODO : remove this once an EntityField will be available
        $options = array(
            'value_transformer' => new EntityToIDTransformer(array(
                'em'        =>  $this->getEntityManager(),
                'className' => $fieldDescription->getTargetEntity()
            ))
        );
        $options = array_merge($options, $fieldDescription->getOption('form_field_options', array()));

        if ($fieldDescription->getOption('edit') == 'list') {

            return new \Symfony\Component\Form\TextField($fieldDescription->getFieldName(), $options);
        }

        $class = $fieldDescription->getOption('form_field_widget', false);

        // set valid default value
        if (!$class) {
            $instance = $this->container->get('form.field_factory')->getInstance(
                $this->getClass(),
                $fieldDescription->getFieldName(),
                $fieldDescription->getOption('form_field_options', array())
            );
        } else {
            $instance = new $class($fieldDescription->getFieldName(), $options);
        }

        return $instance;
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

        $class = $fieldDescription->getOption('form_field_widget', false);

        // set valid default value
        if (!$class) {
            $instance = $this->container->get('form.field_factory')->getInstance(
                $this->getClass(),
                $fieldDescription->getFieldName(),
                $fieldDescription->getOption('form_field_options', array())
            );
        } else {
            $instance = new $class(
                $fieldDescription->getFieldName(),
                $fieldDescription->getOption('form_field_options', array())
            );
        }

        return $instance;
    }

    protected function getManyToOneField($object, FieldDescription $fieldDescription)
    {

        // tweak the widget depend on the edit mode
        if ($fieldDescription->getOption('edit') == 'inline') {

            return $this->getRelatedAssociatedField($object, $fieldDescription);
        }

        $options = array(
            'value_transformer' => new EntityToIDTransformer(array(
                'em'        =>  $this->getEntityManager(),
                'className' => $fieldDescription->getTargetEntity()
            ))
        );
        $options = array_merge($options, $fieldDescription->getOption('form_field_options', array()));


        if ($fieldDescription->getOption('edit') == 'list') {

            return new \Symfony\Component\Form\TextField($fieldDescription->getFieldName(), $options);
        }

        $class = $fieldDescription->getOption('form_field_widget', false);

        if (!$class) {
            $instance = $this->container->get('form.field_factory')->getInstance(
                $this->getClass(),
                $fieldDescription->getFieldName(),
                $fieldDescription->getOption('form_field_options', array())
            );
        } else {
            $instance = new $class($fieldDescription->getFieldName(), array_merge(array('expanded' => true), $options));
        }

        return $instance;
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
            }

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
                    $fieldDescription->setOption('widget_form_field', 'Bundle\\Sonata\\BaseApplicationBundle\\Form\\EditableFieldGroup');
                }

                $this->attachAdminClass($fieldDescription);
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

    protected function addFormFieldInstance($form, $object, FieldDescription $fieldDescription)
    {

        switch ($fieldDescription->getType()) {

            case ClassMetadataInfo::ONE_TO_MANY:

                $instance = $this->getOneToManyField($object, $fieldDescription);
                break;

            case ClassMetadataInfo::MANY_TO_MANY:

                $instance = $this->getManyToManyField($object, $fieldDescription);
                break;

            case ClassMetadataInfo::MANY_TO_ONE:
                $instance = $this->getManyToOneField($object, $fieldDescription);
                break;

            case ClassMetadataInfo::ONE_TO_ONE:
                $instance = $this->getOneToOneField($object, $fieldDescription);
                break;

            default:
                $class   = $this->getFormFieldClass($fieldDescription);
                $options = $fieldDescription->getOption('form_field_options', array());

                // there is no way to use a custom widget with the FieldFactory
                if($class) {
                    $instance = new $class($fieldDescription->getFieldName(), $options);
                } else {
                    $instance = $this->container->get('form.field_factory')->getInstance($this->getClass(), $fieldDescription->getFieldName(), $options);
                }
        }

        $form->add($instance);
    }

    /**
     * return a form depend on the given $object and FieldDescription $fields array
     *
     * @throws RuntimeException
     * @param  $object
     * @return Symfony\Component\Form\Form
     */
    public function getForm($object)
    {

        $form = $this->getBaseForm($object);

        foreach ($this->getFormFields() as $fieldDescription) {

            if (!$fieldDescription->getType()) {

                continue;
            }

            $this->addFormFieldInstance($form, $object, $fieldDescription);
        }

        return $form;
    }
}