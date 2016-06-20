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
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @author ju1ius
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
     * @param string                    $type
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @return array
     *
     * @throws \LogicException If an invalid option was provided.
     * @throws \LogicException If an association widget has no related admin class.
     * @throws \LogicException If an association widget has no related model.
     * @throws \LogicException If an association widget's related model cannot be handled.
     */
    public function getDefaultOptions($type, FieldDescriptionInterface $fieldDescription)
    {
        $options = array();
        $options['sonata_field_description'] = $fieldDescription;

        if (in_array($type, array(
            'sonata_type_model', 'sonata_type_model_list',
            'sonata_type_model_hidden', 'sonata_type_model_autocomplete',
        ), true)) {
            if ($fieldDescription->getOption('edit', 'standard') !== 'standard') {
                throw new \LogicException(sprintf(
                    'The `%s` type does not accept an `edit` option anymore,'
                    .' please review the UPGRADE-2.1.md file from the SonataAdminBundle',
                    $type
                ));
            }
            if (!$fieldDescription->getTargetEntity()) {
                throw new \LogicException(sprintf(
                    'The field "%s" in class "%s" does not have a target model defined.'
                    .' Please make sure your association mapping is properly configured.',
                    $fieldDescription->getName(),
                    $fieldDescription->getAdmin()->getClass()
                ));
            }
        }

        if (in_array($type, array(
            'sonata_type_admin',
            'sonata_type_model_autocomplete',
            'sonata_type_collection',
        ), true)) {
            if (!$fieldDescription->getAssociationAdmin()) {
                throw $this->createMissingAssociationAdminException($fieldDescription);
            }
        }

        if (in_array($type, array(
            'sonata_type_model', 'sonata_type_model_list',
            'sonata_type_model_hidden', 'sonata_type_model_autocomplete',
        ), true)) {
            $options['class'] = $fieldDescription->getTargetEntity();
            $options['model_manager'] = $fieldDescription->getAdmin()->getModelManager();
        } elseif ($type === 'sonata_type_admin') {
            if (!$fieldDescription->describesSingleValuedAssociation()) {
                throw new \LogicException(sprintf(
                    'The `sonata_type_admin` type only handles single-valued associations.'
                    .' Try using `sonata_type_collection` or one of the `sonata_type_model` variants'
                    .' for field `%s`.',
                    $fieldDescription->getName()
                ));
            }

            // set sensitive default value to have a component working fine out of the box
            $options['delete'] = false;

            $options['data_class'] = $fieldDescription->getAssociationAdmin()->getClass();
            $fieldDescription->setOption('edit', $fieldDescription->getOption('edit', 'admin'));
        } elseif ($type === 'sonata_type_collection') {
            if (!$fieldDescription->describesCollectionValuedAssociation()) {
                throw new \LogicException(sprintf(
                    'The `sonata_type_collection` type only handles collection-valued associations.'
                    .' Try using `sonata_type_admin` or one of the `sonata_type_model` variants'
                    .' for field `%s`',
                    $fieldDescription->getName()
                ));
            }
            $options['type'] = 'sonata_type_admin';
            $options['modifiable'] = true;
            $options['type_options'] = array(
                'sonata_field_description' => $fieldDescription,
                'data_class' => $fieldDescription->getAssociationAdmin()->getClass(),
            );
        }

        return $options;
    }

    /**
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @return \LogicException
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

        return new \LogicException($msg);
    }
}
