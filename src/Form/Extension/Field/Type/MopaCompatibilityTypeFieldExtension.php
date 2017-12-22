<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form\Extension\Field\Type;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * This class is built to allow AdminInterface to work properly
 * if the MopaBootstrapBundle is not installed.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class MopaCompatibilityTypeFieldExtension extends AbstractTypeExtension
{
    /**
     * NEXT_MAJOR: Remove method, when bumping requirements to SF 2.7+.
     *
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'horizontal_label_class' => '',
            'horizontal_label_offset_class' => '',
            'horizontal_input_wrapper_class' => '',
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['horizontal_label_class'] = $options['horizontal_label_class'];
        $view->vars['horizontal_label_offset_class'] = $options['horizontal_label_offset_class'];
        $view->vars['horizontal_input_wrapper_class'] = $options['horizontal_input_wrapper_class'];
    }

    public function getExtendedType()
    {
        return FormType::class;
    }
}
