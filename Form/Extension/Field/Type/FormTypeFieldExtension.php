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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormViewInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
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
     * @param FormBuilderInterface $formBuilder
     *
     * @return string
     */
    protected function getClass(FormBuilderInterface $formBuilder)
    {
        foreach ($formBuilder->getTypes() as $type) {
            if (isset($this->defaultClasses[$type->getName()])) {
                return $this->defaultClasses[$type->getName()];
            }
        }

        return '';
    }

    /**
     * @param FormViewInterface $view
     * @param FormInterface     $form
     * @param array             $options
     */
    public function buildView(FormViewInterface $view, FormInterface $form, array $options)
    {
        $sonataAdmin = $form->getConfig()->getAttribute('sonata_admin');

        // avoid to add extra information not required by non admin field
        if ($form->getConfig()->getAttribute('sonata_admin_enabled', true)) {
            $sonataAdmin['value'] = $form->getData();

            // add a new block types, so the Admin Form element can be tweaked based on the admin code
            $types    = $view->getVar('types');
            $baseName = str_replace('.', '_', $sonataAdmin['field_description']->getAdmin()->getCode());
            $baseType = $types[count($types) - 1];

            $types[] = sprintf('%s_%s', $baseName, $baseType);
            $types[] = sprintf('%s_%s_%s', $baseName, $sonataAdmin['field_description']->getName(), $baseType);
            
            if ($sonataAdmin['block_name']) {
                $types[] = $sonataAdmin['block_name'];
            }

            $view->setVar('types', $types);
            $view->setVar('sonata_admin_enabled', true);
            $view->setVar('sonata_admin', $sonataAdmin);

            $attr = $view->getVar('attr', array());

            if (!isset($attr['class'])) {
                $attr['class'] = $sonataAdmin['class'];
            }

            $view->setVar('attr', $attr);

        } else {
            $view->setVar('sonata_admin_enabled', false);
        }

        $view->setVar('sonata_admin', $sonataAdmin);
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
     * Sets the default options
     * 
     * @param OptionsResolverInterface $resolver Options Resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'sonata_admin'             => null,
            'sonata_field_description' => null,
        ));
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