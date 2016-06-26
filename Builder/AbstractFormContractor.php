<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder;

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\Exception\MissingAssociationAdminException;
use Sonata\AdminBundle\Builder\Exception\MissingTargetModelClassException;
use Sonata\AdminBundle\Builder\Exception\UnhandledAssociationTypeException;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class AbstractFormContractor implements FormContractorInterface
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @return FormFactoryInterface
     */
    final public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * {@inheritdoc}
     */
    final public function getFormBuilder($name, array $options = array())
    {
        // NEXT_MAJOR: Remove this line when drop Symfony <2.8 support
        $formType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Symfony\Component\Form\Extension\Core\Type\FormType' : 'form';

        return $this->getFormFactory()->createNamedBuilder($name, $formType, null, $options);
    }

    /**
     * Returns the default options for a form field according to its form type and associated field description.
     *
     * @param string                    $type             The field's form type.
     * @param FieldDescriptionInterface $fieldDescription The field's field description.
     *
     * @return array The field's default options
     */
    public function getDefaultOptions($type, FieldDescriptionInterface $fieldDescription)
    {
        // NEXT_MAJOR: Check directly against $type when dropping Symfony <2.8 support
        $fqcn = $this->convertSonataFormTypeToFQCN($type);

        $this->ensureFormTypeCanHandleFieldDescription($fqcn, $fieldDescription);

        $options = array(
            'sonata_field_description' => $fieldDescription,
        );

        switch ($fqcn) {
            case 'Sonata\AdminBundle\Form\Type\ModelType':
            case 'Sonata\AdminBundle\Form\Type\ModelAutocompleteType':
                if ($fieldDescription->describesCollectionValuedAssociation()) {
                    $options['multiple'] = true;
                }
            // intentional fallthrough
            case 'Sonata\AdminBundle\Form\Type\ModelTypeList':
            case 'Sonata\AdminBundle\Form\Type\ModelHiddenType':
            case 'Sonata\AdminBundle\Form\Type\ModelReferenceType':
                $options['class'] = $fieldDescription->getTargetEntity();
                $options['model_manager'] = $fieldDescription->getAdmin()->getModelManager();
                break;
            case 'Sonata\AdminBundle\Form\Type\AdminType':
                $options['delete'] = false;
                $options['data_class'] = $fieldDescription->getTargetEntity();
                break;
            case 'Sonata\CoreBundle\Form\Type\CollectionType':
                // NEXT_MAJOR: replace by FQCN when dropping Symfony <2.8 support
                $options['type'] = 'sonata_type_admin';
                $options['modifiable'] = true;
                $options['type_options'] = array(
                    'sonata_field_description' => $fieldDescription,
                    'data_class' => $fieldDescription->getTargetEntity(),
                );
                break;
        }

        return $options;
    }

    /**
     * Ensures that an association target model class is defined for the given field description.
     *
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @throws MissingTargetModelClassException if the field description has no target entity
     */
    final protected function ensureTargetModelClass(FieldDescriptionInterface $fieldDescription)
    {
        if (!$fieldDescription->getTargetEntity()) {
            throw $this->createMissingTargetModelClassException($fieldDescription);
        }
    }

    /**
     * Ensures that an association admin is defined for the given field description.
     *
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @throws MissingAssociationAdminException if the field description has no association admin
     */
    final protected function ensureAssociationAdmin(FieldDescriptionInterface $fieldDescription)
    {
        if (!$fieldDescription->getAssociationAdmin()) {
            throw $this->createMissingAssociationAdminException($fieldDescription);
        }
    }

    /**
     * Ensures a field description describes an association type that can be handled by the given form type.
     *
     * @param FieldDescriptionInterface $fieldDescription The field description to check.
     * @param string                    $type             The association type to ensure.
     * @param string                    $formType         The requested form type.
     *
     * @throws UnhandledAssociationTypeException if the field description has no association admin
     */
    final protected function ensureAssociationType(FieldDescriptionInterface $fieldDescription, $type, $formType)
    {
        if (!in_array($type, array('single-valued', 'collection-valued'), true)) {
            throw new \InvalidArgumentException(sprintf(
                'Second argument to %s must be either "single-valued" or "collection-valued"',
                __METHOD__
            ));
        }
        if ($type === 'single-valued' && !$fieldDescription->describesSingleValuedAssociation()) {
            throw $this->createUnhandledAssociationTypeException($formType, $type, $fieldDescription, array(
                'Sonata\CoreBundle\Form\Type\CollectionType',
                'Sonata\AdminBundle\Form\Type\ModelType',
                'Sonata\AdminBundle\Form\Type\ModelAutocompleteType',
            ));
        } elseif ($type === 'collection-valued' && !$fieldDescription->describesCollectionValuedAssociation()) {
            throw $this->createUnhandledAssociationTypeException($formType, $type, $fieldDescription, array(
                'Sonata\AdminBundle\Form\Type\AdminType',
                'Sonata\AdminBundle\Form\Type\ModelType',
                'Sonata\AdminBundle\Form\Type\ModelTypeList',
                'Sonata\AdminBundle\Form\Type\ModelAutocompleteType',
                'Sonata\AdminBundle\Form\Type\ModelHiddenType',
                'Sonata\AdminBundle\Form\Type\ModelReferenceType',
            ));
        }
    }

    /**
     * Returns a `MissingAssociationAdminException` instance with a developer-friendly message,
     * to be thrown when a field description is missing an association admin.
     *
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @return MissingAssociationAdminException
     */
    protected function createMissingAssociationAdminException(FieldDescriptionInterface $fieldDescription)
    {
        $msg = "The current field `{$fieldDescription->getName()}` is not linked to an admin. Please create one";
        if ($fieldDescription->describesAssociation()) {
            if ($fieldDescription->getTargetEntity()) {
                $msg .= " for the target model: `{$fieldDescription->getTargetEntity()}`";
            }
            $msg .= ', make sure your association mapping is properly configured, or';
        } else {
            $msg .= ', and';
        }
        $msg .= ' use the `admin_code` option to link it.';

        return new MissingAssociationAdminException($msg);
    }

    /**
     * Returns a `MissingTargetModelClassException` instance with a developer-friendly message,
     * to be thrown when a field description is missing an association target model class.
     *
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @return MissingTargetModelClassException
     */
    protected function createMissingTargetModelClassException(FieldDescriptionInterface $fieldDescription)
    {
        return new MissingTargetModelClassException(sprintf(
            'The field `%s` in class `%s` does not have a target model class defined.'
            .' Please make sure your association mapping is properly configured.',
            $fieldDescription->getName(),
            $fieldDescription->getAdmin()->getClass()
        ));
    }

    /**
     * Returns a `UnhandledAssociationTypeException` instance with a developer-friendly message,
     * to be thrown when a form type can't handle the association type described by a field description.
     *
     * @param string                    $formType
     * @param string                    $associationType
     * @param FieldDescriptionInterface $fieldDescription
     * @param string[]                  $alternativeTypes
     *
     * @return UnhandledAssociationTypeException
     */
    protected function createUnhandledAssociationTypeException(
        $formType,
        $associationType,
        FieldDescriptionInterface $fieldDescription,
        array $alternativeTypes = array()
    ) {
        $msg = sprintf('The `%s` type only handles %s associations.', $formType, $associationType);
        if ($alternativeTypes) {
            $msg .= sprintf(
                ' Try using %s%s',
                count($alternativeTypes) > 1 ? 'one of ' : '',
                implode(', ', $alternativeTypes)
            );
        }
        $msg .= sprintf(' for field `%s`', $fieldDescription->getName());

        return new UnhandledAssociationTypeException($msg);
    }

    /**
     * Ensures a field's form type can handle the provided field description.
     *
     * @param string                    $formType
     * @param FieldDescriptionInterface $fieldDescription
     */
    private function ensureFormTypeCanHandleFieldDescription($formType, FieldDescriptionInterface $fieldDescription)
    {
        switch ($formType) {
            case 'Sonata\AdminBundle\Form\Type\ModelType':
                $this->ensureTargetModelClass($fieldDescription);
                break;
            case 'Sonata\AdminBundle\Form\Type\ModelAutocompleteType':
                $this->ensureTargetModelClass($fieldDescription);
                $this->ensureAssociationAdmin($fieldDescription);
                break;
            case 'Sonata\AdminBundle\Form\Type\ModelTypeList':
                $this->ensureTargetModelClass($fieldDescription);
                $this->ensureAssociationType($fieldDescription, 'single-valued', $formType);
                $this->ensureAssociationAdmin($fieldDescription);
                break;
            case 'Sonata\AdminBundle\Form\Type\ModelHiddenType':
                $this->ensureTargetModelClass($fieldDescription);
                $this->ensureAssociationType($fieldDescription, 'single-valued', $formType);
                break;
            case 'Sonata\AdminBundle\Form\Type\ModelReferenceType':
                $this->ensureTargetModelClass($fieldDescription);
                $this->ensureAssociationType($fieldDescription, 'single-valued', $formType);
                break;
            case 'Sonata\AdminBundle\Form\Type\AdminType':
                $this->ensureTargetModelClass($fieldDescription);
                $this->ensureAssociationType($fieldDescription, 'single-valued', $formType);
                $this->ensureAssociationAdmin($fieldDescription);
                break;
            case 'Sonata\CoreBundle\Form\Type\CollectionType':
                $this->ensureTargetModelClass($fieldDescription);
                $this->ensureAssociationType($fieldDescription, 'collection-valued', $formType);
                $this->ensureAssociationAdmin($fieldDescription);
                break;
        }
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function convertSonataFormTypeToFQCN($type)
    {
        switch ($type) {
            case 'sonata_type_model':
                return 'Sonata\AdminBundle\Form\Type\ModelType';
            case 'sonata_type_model_list':
                return 'Sonata\AdminBundle\Form\Type\ModelTypeList';
            case 'sonata_type_model_hidden':
                return 'Sonata\AdminBundle\Form\Type\ModelHiddenType';
            case 'sonata_type_model_autocomplete':
                return 'Sonata\AdminBundle\Form\Type\ModelAutocompleteType';
            case 'sonata_type_admin':
                return 'Sonata\AdminBundle\Form\Type\AdminType';
            case 'sonata_type_collection':
                return 'Sonata\CoreBundle\Form\Type\CollectionType';
            default:
                return $type;
        }
    }
}
