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
 * Class DateTimeRangeType.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class DateTimeRangeType extends AbstractType
{
    const TYPE_BETWEEN = 1;
    const TYPE_NOT_BETWEEN = 2;

    /**
     * NEXT_MAJOR: remove this property.
     *
     * @deprecated since 3.5, to be removed with 4.0
     *
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
        return 'sonata_type_filter_datetime_range';
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

        // NEXT_MAJOR: Remove (when requirement of Symfony is >= 2.7)
        if (!method_exists('Symfony\Component\Form\AbstractType', 'configureOptions')) {
            $choices = array_flip($choices);
            foreach ($choices as $key => $value) {
                $choices[$key] = $this->translator->trans($value, array(), 'SonataAdminBundle');
            }
        } else {
            $choiceOptions['choice_translation_domain'] = 'SonataAdminBundle';

            // NEXT_MAJOR: Remove (when requirement of Symfony is >= 3.0)
            if (method_exists('Symfony\Component\Form\FormTypeInterface', 'setDefaultOptions')) {
                $choiceOptions['choices_as_values'] = true;
            }
        }

        $choiceOptions['choices'] = $choices;

        $builder
            // NEXT_MAJOR: Remove ternary (when requirement of Symfony is >= 2.8)
            ->add('type', method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
                ? 'Symfony\Component\Form\Extension\Core\Type\ChoiceType'
                : 'choice', $choiceOptions)
            ->add('value', $options['field_type'], $options['field_options'])
        ;
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
        $resolver->setDefaults(array(
            'field_type' => 'sonata_type_datetime_range',
            'field_options' => array('date_format' => 'yyyy-MM-dd'),
        ));
    }
}
