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

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\ORM\FieldDescription;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Admin\NoValueException;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactoryInterface;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

class FormContractor implements FormContractorInterface
{
    protected $fieldFactory;

    protected $validator;

    /**
     * built-in definition
     *
     * @var array
     */
    protected $formTypes = array(
        'string'     =>  array('text', array()),
        'text'       =>  array('textarea', array()),
        'boolean'    =>  array('checkbox', array()),
        'checkbox'   =>  array('checkbox', array()),
        'integer'    =>  array('integer', array()),
        'tinyint'    =>  array('integer', array()),
        'smallint'   =>  array('integer', array()),
        'mediumint'  =>  array('integer', array()),
        'bigint'     =>  array('integer', array()),
        'decimal'    =>  array('number', array()),
        'datetime'   =>  array('datetime', array()),
        'date'       =>  array('date', array()),
        'choice'     =>  array('choice', array()),
        'array'      =>  array('collection', array()),
        'country'    =>  array('country', array()),
    );

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * Returns the field associated to a FieldDescriptionInterface
     *   ie : build the embedded form from the related AdminInterface instance
     *
     * @throws RuntimeException
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @param null $fieldName
     * @return FieldGroup
     */
    protected function defineChildFormBuilder(FormBuilder $formBuilder, FieldDescriptionInterface $fieldDescription, $fieldName = null)
    {
        $fieldName = $fieldName ?: $fieldDescription->getFieldName();

        $associatedAdmin = $fieldDescription->getAssociationAdmin();

        if (!$associatedAdmin) {
            throw new \RuntimeException(sprintf('inline mode for field `%s` required an Admin definition', $fieldName));
        }

        // retrieve the related object
        $childBuilder = $formBuilder->create($fieldName, 'sonata_type_admin', array(
            'field_description' => $fieldDescription
        ));

        $formBuilder->add($childBuilder);

        $associatedAdmin->defineFormBuilder($childBuilder);
    }


    /**
     * Returns the class associated to a FieldDescriptionInterface if any defined
     *
     * @throws RuntimeException
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return bool|string
     */
    public function getFormTypeName(FieldDescriptionInterface $fieldDescription)
    {
        $typeName = false;

        // the user redefined the mapping type, use the default built in definition
        if (!$fieldDescription->getFieldMapping() || $fieldDescription->getType() != $fieldDescription->getMappingType()) {
            $typeName = array_key_exists($fieldDescription->getType(), $this->formTypes) ? $this->formTypes[$fieldDescription->getType()] : false;
        } else if ($fieldDescription->getOption('form_field_type', false)) {
            $typeName = $fieldDescription->getOption('form_field_type', false);
        } else if (array_key_exists($fieldDescription->getType(), $this->formTypes)) {
            $typeName = $this->formTypes[$fieldDescription->getType()];
        }

        if (!$typeName) {
            throw new \RuntimeException(sprintf('No known form type for field `%s` (`%s`) is implemented.', $fieldDescription->getFieldName(), $fieldDescription->getType()));
        }

        return $typeName;
    }

    /**
     * Returns an OneToOne associated field
     *
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return \Symfony\Component\Form\Type\FormTypeInterface
     */
    protected function defineOneToOneField(FormBuilder $formBuilder, FieldDescriptionInterface $fieldDescription)
    {
        if (!$fieldDescription->hasAssociationAdmin()) {
            return;
        }

        // tweak the widget depend on the edit mode
        if ($fieldDescription->getOption('edit') == 'inline') {
            return $this->defineChildFormBuilder($formBuilder, $fieldDescription);
        }

        $type = 'sonata_type_model';

        $options = $fieldDescription->getOption('form_field_options', array());
        $options['class']         = $fieldDescription->getTargetEntity();
        $options['model_manager'] = $fieldDescription->getAdmin()->getModelManager();

        if ($fieldDescription->getOption('edit') == 'list') {
            $options['parent'] = 'text';
        }

        $formBuilder->add($fieldDescription->getFieldName(), $type, $options);
    }

    /**
     * Returns the OneToMany associated field
     *
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return \Symfony\Component\Form\Type\FormTypeInterface
     */
    protected function getOneToManyField(FormBuilder $formBuilder, FieldDescriptionInterface $fieldDescription)
    {
        if (!$fieldDescription->hasAssociationAdmin()) {
            return;
        }

        if ($fieldDescription->getOption('edit') == 'inline') {

            // create a collection type with the generated prototype
            $options = $fieldDescription->getOption('form_field_options', array());
            $options['type'] = 'sonata_type_admin';
            $options['modifiable'] = true;
            $options['type_options'] = array(
                'field_description' => $fieldDescription,
            );

            $formBuilder->add($fieldDescription->getFieldName(), 'sonata_type_collection', $options);

            return;
//            $value = $fieldDescription->getValue($formBuilder->getData());
//
//            // add new instances if the min number is not matched
//            if ($fieldDescription->getOption('min', 0) > count($value)) {
//
//                $diff = $fieldDescription->getOption('min', 0) - count($value);
//                foreach (range(1, $diff) as $i) {
//                    $this->addNewInstance($formBuilder->getData(), $fieldDescription);
//                }
//            }

            // use custom one to expose the newfield method
//            return new \Sonata\AdminBundle\Form\EditableCollectionField($prototype);
        }

        return $this->defineManyToManyField($formBuilder, $fieldDescription);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return \Symfony\Component\Form\Type\FormTypeInterface
     */
    protected function defineManyToManyField(FormBuilder $formBuilder, FieldDescriptionInterface $fieldDescription)
    {
        if (!$fieldDescription->hasAssociationAdmin()) {
            return;
        }

        $type     = $fieldDescription->getOption('form_field_type', 'sonata_type_model');
        $options  = $fieldDescription->getOption('form_field_options', array());

        if ($type == 'sonata_type_model') {
            $options['class']               = $fieldDescription->getTargetEntity();
            $options['multiple']            = true;
            $options['field_description']   = $fieldDescription;
            $options['parent']              = 'choice';
            $options['model_manager']       = $fieldDescription->getAdmin()->getModelManager();
        }

        $formBuilder->add($fieldDescription->getName(), $type, $options);
    }

    /**
     * Add a new field type into the provided FormBuilder
     *
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function addField(FormBuilder $formBuilder, FieldDescriptionInterface $fieldDescription)
    {
        // There is a bug in the GraphWalker, so for now we always load related associations
        // for more information : https://github.com/symfony/symfony/pull/1056
        if ($formBuilder->getData() && in_array($fieldDescription->getType(), array(ClassMetadataInfo::ONE_TO_MANY, ClassMetadataInfo::MANY_TO_MANY, ClassMetadataInfo::MANY_TO_ONE, ClassMetadataInfo::ONE_TO_ONE ))) {
            try {
                $value = $fieldDescription->getValue($formBuilder->getData());
            } catch (NoValueException $e) {
                $value = null;
            }

            $infos = $fieldDescription->getAssociationMapping();
            if ($value instanceof $infos['targetEntity'] && $value instanceof \Doctrine\ORM\Proxy\Proxy) {
                $relatedId = 'get'.current($fieldDescription->getAdmin()->getModelManager()->getIdentifierFieldNames($infos['targetEntity']));
                $value->{$relatedId}(); // force to load the lazy loading method __load in the proxy methode
            }
        }

        switch ($fieldDescription->getType()) {
            case ClassMetadataInfo::ONE_TO_MANY:
                $this->getOneToManyField($formBuilder, $fieldDescription);
                break;

            case ClassMetadataInfo::MANY_TO_MANY:
                $this->defineManyToManyField($formBuilder, $fieldDescription);
                break;

            case ClassMetadataInfo::MANY_TO_ONE:
            case ClassMetadataInfo::ONE_TO_ONE:
                $this->defineOneToOneField($formBuilder, $fieldDescription);
                break;

            default:
                list($type, $default_options) = $this->getFormTypeName($fieldDescription);
                $formBuilder->add(
                    $fieldDescription->getFieldName(),
                    $type,
                    array_merge($default_options, $fieldDescription->getOption('form_field_options', array()))
                );
        }
    }

    /**
     * The method defines the correct default settings for the provided FieldDescription
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @param array $options
     * @return void
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription, array $options = array())
    {
        $fieldDescription->mergeOptions($options);

        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
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

    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param string $name
     * @param array $options
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function getFormBuilder($name, array $options = array())
    {
        return $this->getFormFactory()->createNamedBuilder('form', $name, $options);
    }
}