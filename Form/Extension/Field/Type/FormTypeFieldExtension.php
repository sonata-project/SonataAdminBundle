<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form\Extension\Field\Type;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use Sonata\AdminBundle\Admin\NoValueException;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;

class FormTypeFieldExtension extends AbstractTypeExtension
{
    protected $type;

    /**
     * @param srting $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $sonataAdmin = array(
            'admin'  => null,
            'object'    => null,
            'value'     => null,
            'edit'      => 'standard',
            'inline'    => 'natual',
            'field_description' => null,
        );

        if ($options['sonata_field_description'] instanceof FieldDescriptionInterface) {
            $fieldDescription = $options['sonata_field_description'];

            $sonataAdmin['object']            = $fieldDescription->getAdmin()->getSubject();
            $sonataAdmin['value']             = $this->getValueFromFieldDescription($sonataAdmin['object'], $fieldDescription);
            $sonataAdmin['admin']             = $fieldDescription->getAdmin();
            $sonataAdmin['field_description'] = $fieldDescription;

            $parentFieldDescription = $fieldDescription->getAdmin()->getParentFieldDescription();

            if ($parentFieldDescription) {
                $sonataAdmin['edit']    = $parentFieldDescription->getOption('edit', 'standard');
                $sonataAdmin['inline']  = $parentFieldDescription->getOption('inline', 'natural');
            } else {
                $sonataAdmin['edit']    = $fieldDescription->getOption('edit', 'standard');
                $sonataAdmin['inline']  = $fieldDescription->getOption('inline', 'natural');
            }
        }

        $builder->setAttribute('sonata_admin', $sonataAdmin);
    }

    public function buildView(FormView $view, FormInterface $form)
    {
        $view->set('sonata_admin', $form->getAttribute('sonata_admin'));
    }

    /**
     * Returns the name of the type being extended
     *
     * @return string The name of the type being extended
     */
    function getExtendedType()
    {
        return $this->type;
    }

    /**
     * Overrides the default options form the extended type.
     *
     * @param array $options
     *
     * @return array
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'sonata_admin'     => null,
            'sonata_object'    => null,
            'sonata_value'     => null,
            'sonata_field_description' => null,
        );
    }

    /**
     * Returns the allowed option values for each option (if any).
     *
     * @param array $options
     *
     * @return array The allowed option values
     */
    public function getAllowedOptionValues(array $options)
    {
        return array();
    }

    /**
     * return the value related to FieldDescription, if the associated object does no
     * exists => a temporary one is created
     *
     * @param object $object
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return mixed
     */
    public function getValueFromFieldDescription($object, FieldDescriptionInterface $fieldDescription)
    {
        if (!$object) {
            return null;
            throw new \RunTimeException(sprintf('Object cannot be null - id: %s, field: %s ', $fieldDescription->getAdmin()->getCode(), $fieldDescription->getName()));
        }

        $value = null;
        try {
          $value = $fieldDescription->getValue($object);
        } catch (NoValueException $e) {
            if ($fieldDescription->getAssociationAdmin()) {
                $value = $fieldDescription->getAssociationAdmin()->getNewInstance();
            }
        }

        return $value;
    }
}