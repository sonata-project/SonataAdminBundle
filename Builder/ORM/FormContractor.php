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

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * Returns an OneToOne associated field
     *
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @param string
     * @return array();
     */
    protected function getOneToOneFieldOptions($type, FieldDescriptionInterface $fieldDescription)
    {
        $options = array();

        if (!$fieldDescription->hasAssociationAdmin()) {
            return $options;
        }

        if ($type == 'sonata_type_admin') {
            $fieldDescription->setOption('edit', 'inline');
        }

        // tweak the widget depend on the edit mode
        if ($fieldDescription->getOption('edit') == 'inline') {
            return $options;
        }

        if ($fieldDescription->getOption('edit') == 'standard')
        {
            return $options;
        }

        $options['class']         = $fieldDescription->getTargetEntity();
        $options['data_class']    = $fieldDescription->getTargetEntity();
        $options['model_manager'] = $fieldDescription->getAdmin()->getModelManager();

        if ($fieldDescription->getOption('edit') == 'list') {
            $options['parent'] = 'text';
        }

        return $options;
    }

    /**
     * Returns the OneToMany associated field
     *
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return array
     */
    protected function getOneToManyFieldOptions($type, FieldDescriptionInterface $fieldDescription)
    {
        $options = array();

        if (!$fieldDescription->hasAssociationAdmin()) {
            return $options;
        }

        if ($fieldDescription->getOption('edit') == 'inline') {

            // create a collection type with the generated prototype
            $options['type']         = 'sonata_type_admin';
            $options['modifiable']   = true;
            $options['type_options'] = array(
                'sonata_field_description' => $fieldDescription,
                'data_class'               => $fieldDescription->getAssociationAdmin()->getClass()
            );

            return $options;
        }

        return $this->getManyToManyFieldOptions($type, $fieldDescription);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     * @param string
     * @return array
     */
    protected function getManyToManyFieldOptions($type, FieldDescriptionInterface $fieldDescription)
    {
        $options = array();

        if (!$fieldDescription->hasAssociationAdmin()) {
            return $options;
        }

        if ($type == 'sonata_type_model') {
            $options['class']               = $fieldDescription->getTargetEntity();
            $options['multiple']            = true;
            $options['parent']              = 'choice';
            $options['model_manager']       = $fieldDescription->getAdmin()->getModelManager();
        }

        return $options;
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

        if (in_array($fieldDescription->getMappingType(), array(ClassMetadataInfo::ONE_TO_MANY, ClassMetadataInfo::MANY_TO_MANY, ClassMetadataInfo::MANY_TO_ONE, ClassMetadataInfo::ONE_TO_ONE ))) {
            $admin->attachAdminClass($fieldDescription);
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
        return $this->getFormFactory()->createNamedBuilder('form', $name, null, $options);
    }

    public function getDefaultOptions($type, FieldDescriptionInterface $fieldDescription, array $options = array())
    {
        $options['sonata_field_description'] = $fieldDescription;

        if (!is_string($type)) {
            return $options;
        }

        // only add default options to Admin Bundle widget ...
        $types = array('sonata_type_model', 'sonata_type_admin', 'sonata_type_collection');

        if (!in_array($type, $types)) {
            return $options;
        }

        $fieldOptions = array();

        switch ($fieldDescription->getMappingType()) {
            case ClassMetadataInfo::ONE_TO_MANY:

                $fieldOptions = $this->getOneToManyFieldOptions($type, $fieldDescription);
                break;

            case ClassMetadataInfo::MANY_TO_MANY:
                $fieldOptions = $this->getManyToManyFieldOptions($type, $fieldDescription);
                break;

            case ClassMetadataInfo::MANY_TO_ONE:
            case ClassMetadataInfo::ONE_TO_ONE:
                $fieldOptions = $this->getOneToOneFieldOptions($type, $fieldDescription);
                break;
        }

        return array_merge($fieldOptions, $options);
    }
}