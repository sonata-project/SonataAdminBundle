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
use Symfony\Component\Optionsresolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class DateType extends AbstractType
{
    const TYPE_GREATER_EQUAL = 1;

    const TYPE_GREATER_THAN = 2;

    const TYPE_EQUAL = 3;

    const TYPE_LESS_EQUAL = 4;

    const TYPE_LESS_THAN = 5;

    const TYPE_NULL = 6;

    const TYPE_NOT_NULL = 7;

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sonata_type_filter_date';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array(
            'label_date_type_equal' => self::TYPE_EQUAL,
            'label_date_type_greater_equal' => self::TYPE_GREATER_EQUAL,
            'label_date_type_greater_than' => self::TYPE_GREATER_THAN,
            'label_date_type_less_equal' => self::TYPE_LESS_EQUAL,
            'label_date_type_less_than' => self::TYPE_LESS_THAN,
            'label_date_type_null' => self::TYPE_NULL,
            'label_date_type_not_null' => self::TYPE_NOT_NULL,
        );
        $choiceOptions = array(
            'required' => false,
        );

        $choiceOptions['choice_translation_domain'] = 'SonataAdminBundle';

        // NEXT_MAJOR: Remove (when requirement of Symfony is >= 3.0)
        if (method_exists('Symfony\Component\Form\FormTypeInterface', 'setDefaultOptions')) {
            $choiceOptions['choices_as_values'] = true;
        }

        $choiceOptions['choices'] = $choices;

        $builder
            ->add('type', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', $choiceOptions)
            ->add('value', $options['field_type'], array_merge(array('required' => false), $options['field_options']))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'field_type' => 'date',
            'field_options' => array('date_format' => 'yyyy-MM-dd'),
        ));
    }
}
