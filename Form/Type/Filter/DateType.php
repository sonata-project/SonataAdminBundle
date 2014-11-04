<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sonata\AdminBundle\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Symfony\Component\Optionsresolver\OptionsResolverInterface;

class DateType extends AbstractType
{
    const TYPE_GREATER_EQUAL = 1;

    const TYPE_GREATER_THAN = 2;

    const TYPE_EQUAL = 3;

    const TYPE_LESS_EQUAL = 4;

    const TYPE_LESS_THAN = 5;

    const TYPE_NULL = 6;

    const TYPE_NOT_NULL = 7;

    protected $translator;

    /**
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_type_filter_date';
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array(
            self::TYPE_EQUAL            => $this->translator->trans('label_date_type_equal', array(), 'SonataAdminBundle'),
            self::TYPE_GREATER_EQUAL    => $this->translator->trans('label_date_type_greater_equal', array(), 'SonataAdminBundle'),
            self::TYPE_GREATER_THAN     => $this->translator->trans('label_date_type_greater_than', array(), 'SonataAdminBundle'),
            self::TYPE_LESS_EQUAL       => $this->translator->trans('label_date_type_less_equal', array(), 'SonataAdminBundle'),
            self::TYPE_LESS_THAN        => $this->translator->trans('label_date_type_less_than', array(), 'SonataAdminBundle'),
            self::TYPE_NULL             => $this->translator->trans('label_date_type_null', array(), 'SonataAdminBundle'),
            self::TYPE_NOT_NULL         => $this->translator->trans('label_date_type_not_null', array(), 'SonataAdminBundle'),
        );

        $builder
            ->add('type', 'choice', array('choices' => $choices, 'required' => false))
            ->add('value', $options['field_type'], array_merge(array('required' => false), $options['field_options']))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'field_type'       => 'date',
            'field_options'    => array('date_format' => 'yyyy-MM-dd')
        ));
    }
}
