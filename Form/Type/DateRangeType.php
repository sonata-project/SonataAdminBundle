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

namespace Sonata\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DateRangeType extends AbstractType
{
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('start', 'date', array_merge(array('required' => false), $options['field_options']));
        $builder->add('end', 'date', array_merge(array('required' => false), $options['field_options']));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_type_date_range';
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'field_options'    => array()
        ));
    }
}
