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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * This class is built to allow AdminInterface to work properly
 * if the MopaBootstrapBundle is not installed
 *
 * Class MopaCompatibilityTypeFieldExtension
 *
 * @package Sonata\AdminBundle\Form\Extension\Field\Type
 */
class MopaCompatibilityTypeFieldExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'horizontal_label_class'         => '',
            'horizontal_label_offset_class'  => '',
            'horizontal_input_wrapper_class' => '',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['horizontal_label_class']         = $options['horizontal_label_class'];
        $view->vars['horizontal_label_offset_class']  = $options['horizontal_label_offset_class'];
        $view->vars['horizontal_input_wrapper_class'] = $options['horizontal_input_wrapper_class'];
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return 'form';
    }
}