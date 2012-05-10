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
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

class DateTimeRangeType extends AbstractType
{
    const TYPE_BETWEEN = 1;
    const TYPE_NOT_BETWEEN = 2;

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
        return 'sonata_type_filter_datetime_range';
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $choices = array(
            self::TYPE_BETWEEN    => $this->translator->trans('label_date_type_between', array(), 'SonataAdminBundle'),
            self::TYPE_NOT_BETWEEN    => $this->translator->trans('label_date_type_not_between', array(), 'SonataAdminBundle'),
        );

        $builder
            ->add('type', 'choice', array('choices' => $choices, 'required' => false))
            ->add('value', 'sonata_type_datetime_range', array('field_options' => $options['field_options']))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'field_type'       => 'sonata_type_datetime_range',
            'field_options'    => array('date_format' => 'yyyy-MM-dd'),
        );

        $options = array_replace($options, $defaultOptions);

        return $options;
    }
}