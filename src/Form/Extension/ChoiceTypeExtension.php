<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Amine Zaghdoudi <amine.zaghdoudi@ekino.com>
 */
class ChoiceTypeExtension extends AbstractTypeExtension
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
        $optionalOptions = ['sortable'];

        $resolver->setDefined($optionalOptions);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['sortable'] = array_key_exists('sortable', $options) && $options['sortable'];
    }

    public function getExtendedType()
    {
        return ChoiceType::class;
    }
}
