<?php
/**
 * Created by JetBrains PhpStorm.
 * User: firesnake
 * Date: 01.03.12
 * Time: 17:42
 */

namespace Sonata\AdminBundle\Form\Type\Filter;

use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Symfony\Component\Form\FormBuilder;

class DatepickerType extends NumberType
{

    const TYPE_GREATER_EQUAL = 1;

    const TYPE_GREATER_THAN = 2;

    const TYPE_EQUAL = 3;

    const TYPE_LESS_EQUAL = 4;

    const TYPE_LESS_THAN = 5;

    /**
     * @param \Symfony\Component\Form\FormBuilder $builder
     * @param array $options
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $choices = array(
            self::TYPE_EQUAL            => $this->translator->trans('label_type_equal', array(), 'SonataAdminBundle'),
            self::TYPE_GREATER_EQUAL    => $this->translator->trans('label_type_greater_equal', array(), 'SonataAdminBundle'),
            self::TYPE_GREATER_THAN     => $this->translator->trans('label_type_greater_than', array(), 'SonataAdminBundle'),
            self::TYPE_LESS_EQUAL       => $this->translator->trans('label_type_less_equal', array(), 'SonataAdminBundle'),
            self::TYPE_LESS_THAN        => $this->translator->trans('label_type_less_than', array(), 'SonataAdminBundle'),
        );

        $builder
            ->add('type', 'choice', array('choices' => $choices, 'required' => false))
            ->add('value', 'sonata_type_datepicker', array('required' => false))
        ;
    }

}
