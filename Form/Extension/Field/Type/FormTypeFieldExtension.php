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
use Symfony\Component\Form\FormView;

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
            $sonataAdmin['block_name']        = $fieldDescription->getOption('block_name', false);
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
        foreach ($this->getTypes($formBuilder) as $type) {
            if (isset($this->defaultClasses[$type->getName()])) {
                return $this->defaultClasses[$type->getName()];
            }
        }

        return '';
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $formBuilder
     *
     * @return array
     */
    protected function getTypes(FormBuilderInterface $formBuilder)
    {
        $types = array();

        for ($type = $formBuilder->getType(); null !== $type; $type = $type->getParent()) {
            array_unshift($types, $type->getInnerType());
        }

        return $types;
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $sonataAdmin = $form->getConfig()->getAttribute('sonata_admin');

        // avoid to add extra information not required by non admin field
        if ($sonataAdmin && $form->getConfig()->getAttribute('sonata_admin_enabled', true)) {
            $sonataAdmin['value'] = $form->getData();

            // add a new block types, so the Admin Form element can be tweaked based on the admin code
            $block_prefixes    = $view->vars['block_prefixes'];
            $baseName = str_replace('.', '_', $sonataAdmin['admin']->getCode());
            $baseType = $block_prefixes[count($block_prefixes) - 2];
            $blockSuffix = preg_replace("#^_([a-z0-9]{14})_(.++)$#", "\$2", array_pop($block_prefixes));

            $block_prefixes[] = sprintf('%s_%s', $baseName, $baseType);
            $block_prefixes[] = sprintf('%s_%s_%s', $baseName, $sonataAdmin['name'], $baseType);
            $block_prefixes[] = sprintf('%s_%s_%s_%s', $baseName, $sonataAdmin['name'], $baseType, $blockSuffix);

            if (isset($sonataAdmin['block_name']) && $sonataAdmin['block_name'] !== false) {
                $block_prefixes[] = $sonataAdmin['block_name'];
            }

            $view->vars['block_prefixes'] = $block_prefixes;
            $view->vars['sonata_admin_enabled'] = true;
            $view->vars['sonata_admin'] = $sonataAdmin;

            $attr = $view->vars['attr'];

            if (!isset($attr['class']) && isset($sonataAdmin['class'])) {
                $attr['class'] = $sonataAdmin['class'];
            }

            $view->vars['attr'] = $attr;
        } else {
            $view->vars['sonata_admin_enabled'] = false;
        }

        $view->vars['sonata_admin'] = $sonataAdmin;
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
