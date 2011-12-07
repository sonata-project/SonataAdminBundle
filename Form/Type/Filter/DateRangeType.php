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

class DateRangeType extends AbstractType
{
    const TYPE_RANGE = 1;

    protected $translator;

    /**
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'sonata_type_filter_date_range';
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $choices = array(
            self::TYPE_RANGE            => $this->translator->trans('label_date_type_range', array(), 'SonataAdminBundle'),
        );
        
        $builder
            ->add('type', 'choice', array('choices' => $choices, 'required' => false))
            ->add('value', 'sonata_type_date_range', array('field_options' => array_merge(array('format' => 'yyyy-MM-dd'), $options['field_options'])))
        ;
    }

    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'operator_type'    => 'hidden',
            'operator_options' => array(),
            'field_type'       => 'text',
            'field_options'    => array()
        );

        $options = array_replace($options, $defaultOptions);

        return $options;
    }
}