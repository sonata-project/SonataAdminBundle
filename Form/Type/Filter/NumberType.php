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
 * Class NumberType.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class NumberType extends AbstractType
{
    const TYPE_GREATER_EQUAL = 1;

    const TYPE_GREATER_THAN = 2;

    const TYPE_EQUAL = 3;

    const TYPE_LESS_EQUAL = 4;

    const TYPE_LESS_THAN = 5;

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
        return 'sonata_type_filter_number';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array(
            $this->translator->trans('label_type_equal', array(), 'SonataAdminBundle')         => self::TYPE_EQUAL,
            $this->translator->trans('label_type_greater_equal', array(), 'SonataAdminBundle') => self::TYPE_GREATER_EQUAL,
            $this->translator->trans('label_type_greater_than', array(), 'SonataAdminBundle')  => self::TYPE_GREATER_THAN,
            $this->translator->trans('label_type_less_equal', array(), 'SonataAdminBundle')    => self::TYPE_LESS_EQUAL,
            $this->translator->trans('label_type_less_than', array(), 'SonataAdminBundle')     => self::TYPE_LESS_THAN,
        );

        $builder
            ->add('type', 'choice', array('choices' => $choices, 'choices_as_values' => true, 'required' => false))
            ->add('value', $options['field_type'], array_merge(array('required' => false), $options['field_options']))
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
            'field_type'    => 'number',
            'field_options' => array(),
        ));
    }
}
