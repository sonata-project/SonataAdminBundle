<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ChoiceType extends AbstractType
{
    const TYPE_CONTAINS = 1;

    const TYPE_NOT_CONTAINS = 2;

    const TYPE_EQUAL = 3;

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sonata_type_filter_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array(
            'label_type_contains' => self::TYPE_CONTAINS,
            'label_type_not_contains' => self::TYPE_NOT_CONTAINS,
            'label_type_equals' => self::TYPE_EQUAL,
        );
        $operatorChoices = array();

        $operatorChoices['choice_translation_domain'] = 'SonataAdminBundle';

        // NEXT_MAJOR: Remove (when requirement of Symfony is >= 3.0)
        if (method_exists('Symfony\Component\Form\FormTypeInterface', 'setDefaultOptions')) {
            $operatorChoices['choices_as_values'] = true;
        }

        $operatorChoices['choices'] = $choices;

        $builder
            ->add('type', $options['operator_type'], array_merge(array('required' => false), $options['operator_options'], $operatorChoices))
            ->add('value', $options['field_type'], array_merge(array('required' => false), $options['field_options']))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'field_type' => 'choice',
            'field_options' => array(),
            'operator_type' => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
            'operator_options' => array(),
        ));
    }
}
