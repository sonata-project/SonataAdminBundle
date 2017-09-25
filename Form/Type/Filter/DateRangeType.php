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
class DateRangeType extends AbstractType
{
    const TYPE_BETWEEN = 1;
    const TYPE_NOT_BETWEEN = 2;

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sonata_type_filter_date_range';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array(
            'label_date_type_between' => self::TYPE_BETWEEN,
            'label_date_type_not_between' => self::TYPE_NOT_BETWEEN,
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
            ->add('value', $options['field_type'], $options['field_options'])
        ;
    }

    /**
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
        $resolver->setDefaults(array(
            'field_type' => 'sonata_type_date_range',
            'field_options' => array('format' => 'yyyy-MM-dd'),
        ));
    }
}
