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
     * {@inheritdoc}
     *
     * @todo Remove when dropping Symfony <2.8 support
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
            self::TYPE_BETWEEN => $this->translator->trans('label_date_type_between', array(), 'SonataAdminBundle'),
            self::TYPE_NOT_BETWEEN => $this->translator->trans('label_date_type_not_between', array(), 'SonataAdminBundle'),
        );

        if (!method_exists('Symfony\Component\Form\FormTypeInterface', 'setDefaultOptions')) {
            $choices = array_flip($choices);
        }

        $builder
            ->add('type', 'choice', array('choices' => $choices, 'required' => false))
            ->add('value', $options['field_type'], $options['field_options'])
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @todo Remove it when bumping requirements to SF 2.7+
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
