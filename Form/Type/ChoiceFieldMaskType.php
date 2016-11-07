<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ChoiceFieldMaskType.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ChoiceFieldMaskType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $allFieldNames = array();
        foreach ($options['map'] as $value => $fieldNames) {
            foreach ($fieldNames as $fieldName) {
                $allFieldNames[$fieldName] = $fieldName;
            }
        }
        $allFieldNames = array_values($allFieldNames);

        $view->vars['all_fields'] = $allFieldNames;
        $view->vars['map'] = $options['map'];

        $options['expanded'] = false;

        parent::buildView($view, $form, $options);
    }

    /**
     * NEXT_MAJOR: Remove method, when bumping requirements to SF 2.7+.
     *
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // NEXT_MAJOR: Remove conditional parent call when bumping requirements to SF 2.7+
        if (method_exists('Symfony\Component\Form\AbstractType', 'configureOptions')) {
            parent::configureOptions($resolver);
        } else {
            parent::setDefaultOptions($resolver);
        }

        $resolver->setDefaults(array(
            'map' => array(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        // NEXT_MAJOR: Remove ternary (when requirement of Symfony is >= 2.8)
        return method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Symfony\Component\Form\Extension\Core\Type\ChoiceType'
            : 'choice';
    }

    /**
     * NEXT_MAJOR: Remove when dropping Symfony <2.8 support.
     *
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sonata_type_choice_field_mask';
    }
}
