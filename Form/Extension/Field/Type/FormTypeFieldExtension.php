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

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Exception\NoValueException;

class FormTypeFieldExtension extends AbstractTypeExtension
{
    protected $defaultClasses = array();

    /**
     * @param array $defaultClasses
     */
    public function __construct(array $defaultClasses = array())
    {
        $this->defaultClasses = $defaultClasses;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilder $builder
     * @param array                               $options
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $sonataAdmin = array(
            'name'              => null,
            'admin'             => null,
            'value'             => null,
            'edit'              => 'standard',
            'inline'            => 'natural',
            'field_description' => null,
            'block_name'        => false
        );

        $builder->setAttribute('sonata_admin_enabled', false);

        if ($options['sonata_field_description'] instanceof FieldDescriptionInterface) {
            $fieldDescription = $options['sonata_field_description'];

            $sonataAdmin['admin']             = $fieldDescription->getAdmin();
            $sonataAdmin['field_description'] = $fieldDescription;
            $sonataAdmin['name']              = $fieldDescription->getName();
            $sonataAdmin['edit']              = $fieldDescription->getOption('edit', 'standard');
            $sonataAdmin['inline']            = $fieldDescription->getOption('inline', 'natural');
            $sonataAdmin['class']             = $this->getClass($builder);

            $builder->setAttribute('sonata_admin_enabled', true);
        }

        $builder->setAttribute('sonata_admin', $sonataAdmin);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     *
     * @return string
     */
    protected function getClass(FormBuilder $formBuilder)
    {
        foreach ($formBuilder->getTypes() as $type) {
            if (isset($this->defaultClasses[$type->getName()])) {
                return $this->defaultClasses[$type->getName()];
            }
        }

        return '';
    }

    /**
     * @param \Symfony\Component\Form\FormView      $view
     * @param \Symfony\Component\Form\FormInterface $form
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        $sonataAdmin = $form->getAttribute('sonata_admin');

        // avoid to add extra information not required by non admin field
        if ($form->getAttribute('sonata_admin_enabled', true)) {
            $sonataAdmin['value'] = $form->getData();

            // add a new block types, so the Admin Form element can be tweaked based on the admin code
            $types    = $view->get('types');
            $baseName = str_replace('.', '_', $sonataAdmin['field_description']->getAdmin()->getCode());
            $baseType = $types[count($types) - 1];

            $types[] = sprintf('%s_%s', $baseName, $baseType);
            $types[] = sprintf('%s_%s_%s', $baseName, $sonataAdmin['field_description']->getName(), $baseType);
            if ($sonataAdmin['block_name']) {
                $types[] = $sonataAdmin['block_name'];
            }

            $view->set('types', $types);
            $view->set('sonata_admin_enabled', true);
            $view->set('sonata_admin', $sonataAdmin);

            $attr = $view->get('attr', array());

            if (!isset($attr['class'])) {
                $attr['class'] = $sonataAdmin['class'];
            }

            $view->set('attr', $attr);

        } else {
            $view->set('sonata_admin_enabled', false);
        }

        $view->set('sonata_admin', $sonataAdmin);
    }

    /**
     * Returns the name of the type being extended
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return 'field';
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
            'sonata_admin'             => null,
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
     * @param object                                              $object
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     *
     * @return mixed
     */
    public function getValueFromFieldDescription($object, FieldDescriptionInterface $fieldDescription)
    {
        $value = null;

        if (!$object) {
            return $value;
        }

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