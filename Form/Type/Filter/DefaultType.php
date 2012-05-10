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

class DefaultType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_type_filter_default';
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('type', $options['operator_type'], array_merge(array('required' => false), $options['operator_options']))
            ->add('value', $options['field_type'], array_merge(array('required' => false), $options['field_options']))
        ;
    }

    /**
     * {@inheritDoc}
     */
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