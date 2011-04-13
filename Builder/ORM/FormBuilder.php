<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder\ORM;

use Sonata\AdminBundle\Admin\ORM\FieldDescription;
use Sonata\AdminBundle\Form\ValueTransformer\EntityToIDTransformer;
use Sonata\AdminBundle\Form\ValueTransformer\ArrayToObjectTransformer;
use Sonata\AdminBundle\Form\EditableCollectionField;
use Sonata\AdminBundle\Form\EditableFieldGroup;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\FormBuilderInterface;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormContextInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Form\FieldFactory\FieldFactoryInterface;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

class FormBuilder implements FormBuilderInterface
{

    protected $fieldFactory;

    protected $formContext;

    protected $validator;

    /**
     * todo: put this in the DIC
     *
     * built-in definition
     *
     * @var array
     */
    protected $formFieldClasses = array(
        'string'     =>  'Symfony\\Component\\Form\\TextField',
        'text'       =>  'Symfony\\Component\\Form\\TextareaField',
        'boolean'    =>  'Symfony\\Component\\Form\\CheckboxField',
        'integer'    =>  'Symfony\\Component\\Form\\IntegerField',
        'tinyint'    =>  'Symfony\\Component\\Form\\IntegerField',
        'smallint'   =>  'Symfony\\Component\\Form\\IntegerField',
        'mediumint'  =>  'Symfony\\Component\\Form\\IntegerField',
        'bigint'     =>  'Symfony\\Component\\Form\\IntegerField',
        'decimal'    =>  'Symfony\\Component\\Form\\NumberField',
        'datetime'   =>  'Symfony\\Component\\Form\\DateTimeField',
        'date'       =>  'Symfony\\Component\\Form\\DateField',
        'choice'     =>  'Symfony\\Component\\Form\\ChoiceField',
        'array'      =>  'Symfony\\Component\\Form\\FieldGroup',
        'country'    =>  'Symfony\\Component\\Form\\CountryField',
    );

    public function __construct(FieldFactoryInterface $fieldFactory, FormContextInterface $formContext, ValidatorInterface $validator)
    {
        $this->fieldFactory = $fieldFactory;
        $this->formContext  = $formContext;
        $this->validator    = $validator;
    }

    /**
     * Returns the field associated to a FieldDescription
     *   ie : build the embedded form from the related AdminInterface instance
     *
     * @throws RuntimeException
     * @param  $object
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @param null $fieldName
     * @return FieldGroup
     */
    protected function getRelatedAssociatedField($object, FieldDescriptionInterface $fieldDescription, $fieldName = null)
    {
        $fieldName = $fieldName ?: $fieldDescription->getFieldName();

        $associatedAdmin = $fieldDescription->getAssociationAdmin();

        if (!$associatedAdmin) {
            throw new \RuntimeException(sprintf('inline mode for field `%s` required an Admin definition', $fieldName));
        }

        // retrieve the related object
        $targetObject = $associatedAdmin->getNewInstance();

        // retrieve the related form
        $targetForm   = $associatedAdmin->getForm($targetObject);

        // create the transformer
        $transformer = new ArrayToObjectTransformer(array(
            'em'        => $fieldDescription->getAdmin()->getModelManager(),
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
     * Returns the class associated to a FieldDescriptionInterface if any defined
     *
     * @throws RuntimeException
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return bool|string
     */
    public function getFormFieldClass(FieldDescriptionInterface $fieldDescription)
    {
        $class = false;

        // the user redefined the mapping type, use the default built in definition
        if (!$fieldDescription->getFieldMapping() || $fieldDescription->getType() != $fieldDescription->getMappingType()) {
            $class = array_key_exists($fieldDescription->getType(), $this->formFieldClasses) ? $this->formFieldClasses[$fieldDescription->getType()] : false;
        } else if ($fieldDescription->getOption('form_field_widget', false)) {
            $class = $fieldDescription->getOption('form_field_widget', false);
        }

        if ($class && !class_exists($class)) {
            throw new \RuntimeException(sprintf('The class `%s` does not exist for field `%s`', $class, $fieldDescription->getType()));
        }

        return $class;
    }

    /**
     * Add a new instance to the related FieldDescriptionInterface value
     *
     * @param object $object
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function addNewInstance($object, FieldDescriptionInterface $fieldDescription)
    {
        $instance = $fieldDescription->getAssociationAdmin()->getNewInstance();
        $mapping  = $fieldDescription->getAssociationMapping();

        $method = sprintf('add%s', FieldDescription::camelize($mapping['fieldName']));

        $object->$method($instance);
    }

    /**
     * Returns an OneToOne associated field
     *
     * @param object $object
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return ChoiceField
     */
    protected function getOneToOneField($object, FieldDescriptionInterface $fieldDescription)
    {
        // tweak the widget depend on the edit mode
        if ($fieldDescription->getOption('edit') == 'inline') {
            return $this->getRelatedAssociatedField($object, $fieldDescription);
        }

        // TODO : remove this once an EntityField will be available
        $options = array(
            'value_transformer' => new EntityToIDTransformer(array(
                'em'        => $fieldDescription->getAdmin()->getModelManager()->getEntityManager(),
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
            $instance = $this->getFieldFactory()->getInstance(
                $fieldDescription->getAdmin()->getClass(),
                $fieldDescription->getFieldName(),
                $fieldDescription->getOption('form_field_options', array())
            );
        } else {
            $instance = new $class($fieldDescription->getFieldName(), $options);
        }

        return $instance;
    }

    /**
     * Returns the OneToMany associated field
     *
     * @param  $object
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return ChoiceField|CollectionField
     */
    protected function getOneToManyField($object, FieldDescriptionInterface $fieldDescription)
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
            return new \Sonata\AdminBundle\Form\EditableCollectionField($prototype);
        }

        return $this->getManyToManyField($object, $fieldDescription);
    }

    /**
     * @param object $object
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return \Symfony\Component\Form\FieldFactory\FieldInterface
     */
    protected function getManyToManyField($object, FieldDescriptionInterface $fieldDescription)
    {

        $class = $fieldDescription->getOption('form_field_widget', false);

        // set valid default value
        if (!$class) {
            $instance = $this->getFieldFactory()->getInstance(
                $fieldDescription->getAdmin()->getClass(),
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

    /**
     * @param object $object
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return FieldGroup|\Symfony\Component\Form\FieldFactory\FieldInterface|\Symfony\Component\Form\TextField
     */
    protected function getManyToOneField($object, FieldDescriptionInterface $fieldDescription)
    {

        // tweak the widget depend on the edit mode
        if ($fieldDescription->getOption('edit') == 'inline') {

            return $this->getRelatedAssociatedField($object, $fieldDescription);
        }

        $options = array(
            'value_transformer' => new EntityToIDTransformer(array(
                'em'        =>  $fieldDescription->getAdmin()->getModelManager(),
                'className' =>  $fieldDescription->getTargetEntity()
            ))
        );
        $options = array_merge($options, $fieldDescription->getOption('form_field_options', array()));


        if ($fieldDescription->getOption('edit') == 'list') {

            return new \Symfony\Component\Form\TextField($fieldDescription->getFieldName(), $options);
        }

        $class = $fieldDescription->getOption('form_field_widget', false);

        if (!$class) {
            $instance = $this->getFieldFactory()->getInstance(
                $fieldDescription->getAdmin()->getClass(),
                $fieldDescription->getFieldName(),
                $fieldDescription->getOption('form_field_options', array())
            );
        } else {
            $instance = new $class($fieldDescription->getFieldName(), array_merge(array('expanded' => true), $options));
        }

        return $instance;
    }

    /**
     * @param Form $form
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $name
     * @param array $options
     * @return void
     */
    public function addField(Form $form, FieldDescriptionInterface $fieldDescription)
    {

        switch ($fieldDescription->getType()) {

            case ClassMetadataInfo::ONE_TO_MANY:

                $field = $this->getOneToManyField($form->getData(), $fieldDescription);
                break;

            case ClassMetadataInfo::MANY_TO_MANY:

                $field = $this->getManyToManyField($form->getData(), $fieldDescription);
                break;

            case ClassMetadataInfo::MANY_TO_ONE:
                $field = $this->getManyToOneField($form->getData(), $fieldDescription);
                break;

            case ClassMetadataInfo::ONE_TO_ONE:
                $field = $this->getOneToOneField($form->getData(), $fieldDescription);
                break;

            default:
                $class   = $this->getFormFieldClass($fieldDescription);

                // there is no way to use a custom widget with the FieldFactory
                if ($class) {
                    $field = new $class(
                        $fieldDescription->getFieldName(),
                        $fieldDescription->getOption('form_field_options', array())
                    );
                } else {
                    $field = $this->getFieldFactory()->getInstance(
                        $fieldDescription->getAdmin()->getClass(),
                        $fieldDescription->getFieldName(),
                        $fieldDescription->getOption('form_field_options', array())
                    );
                }
        }

        return $form->add($field);
    }

    /**
     * The method define the correct default settings for the provided FieldDescription
     *
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription, array $options = array())
    {

        $fieldDescription->mergeOptions($options);

        if($admin->getModelManager()->hasMetadata($admin->getClass()))
        {
            $metadata = $admin->getModelManager()->getMetadata($admin->getClass());

            // set the default field mapping
            if (isset($metadata->fieldMappings[$fieldDescription->getName()])) {
                $fieldDescription->setFieldMapping($metadata->fieldMappings[$fieldDescription->getName()]);
            }

            // set the default association mapping
            if (isset($metadata->associationMappings[$fieldDescription->getName()])) {
                $fieldDescription->setAssociationMapping($metadata->associationMappings[$fieldDescription->getName()]);
            }
        }

        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), get_class($admin)));
        }

        $fieldDescription->setAdmin($admin);
        $fieldDescription->setOption('edit', $fieldDescription->getOption('edit', 'standard'));

        // fix template value for doctrine association fields
        if (!$fieldDescription->getTemplate()) {
             $fieldDescription->setTemplate(sprintf('SonataAdminBundle:CRUD:edit_%s.html.twig', $fieldDescription->getType()));
        }

        if ($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_ONE) {
            $fieldDescription->setTemplate('SonataAdminBundle:CRUD:edit_orm_one_to_one.html.twig');
            $admin->attachAdminClass($fieldDescription);
        }

        if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_ONE) {
            $fieldDescription->setTemplate('SonataAdminBundle:CRUD:edit_orm_many_to_one.html.twig');
            $admin->attachAdminClass($fieldDescription);
        }

        if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_MANY) {
            $fieldDescription->setTemplate('SonataAdminBundle:CRUD:edit_orm_many_to_many.html.twig');
            $admin->attachAdminClass($fieldDescription);
        }

        if ($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_MANY) {
            $fieldDescription->setTemplate('SonataAdminBundle:CRUD:edit_orm_one_to_many.html.twig');

            if ($fieldDescription->getOption('edit') == 'inline' && !$fieldDescription->getOption('widget_form_field')) {
                $fieldDescription->setOption('widget_form_field', 'Bundle\\Sonata\\AdminBundle\\Form\\EditableFieldGroup');
            }

            $admin->attachAdminClass($fieldDescription);
        }

        // set correct default value
        if ($fieldDescription->getType() == 'datetime') {
            $options = $fieldDescription->getOption('form_field_options', array());
            if (!isset($options['years'])) {
                $options['years'] = range(1900, 2100);
            }
            $fieldDescription->setOption('form_field', $options);
        }
    }

    public function setFieldFactory($fieldFactory)
    {
        $this->fieldFactory = $fieldFactory;
    }

    public function getFieldFactory()
    {
        return $this->fieldFactory;
    }

    public function setFormContext($formContext)
    {
        $this->formContext = $formContext;
    }

    public function getFormContext()
    {
        return $this->formContext;
    }

    public function setFormFieldClasses(array $formFieldClasses)
    {
        $this->formFieldClasses = $formFieldClasses;
    }

    public function getFormFieldClasses()
    {
        return $this->formFieldClasses;
    }

    public function getBaseForm($name, $object, array $options = array())
    {
        return new Form($name, array_merge(array(
            'data'      => $object,
            'validator' => $this->getValidator(),
            'context'   => $this->getFormContext(),
        ), $options));
    }

    public function setValidator($validator)
    {
        $this->validator = $validator;
    }

    public function getValidator()
    {
        return $this->validator;
    }
}