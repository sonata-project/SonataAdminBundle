<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form\Type\Filter;

use Sonata\AdminBundle\Form\Type\Operator\DateRangeOperatorType;
use Sonata\CoreBundle\Form\Type\DateTimeRangeType as FormDateTimeRangeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class DateTimeRangeType extends AbstractType
{
    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use DateRangeOperatorType::TYPE_BETWEEN instead
     */
    public const TYPE_BETWEEN = 1;

    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use DateRangeOperatorType::TYPE_NOT_BETWEEN instead
     */
    public const TYPE_NOT_BETWEEN = 2;

    /**
     * NEXT_MAJOR: remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.5, to be removed with 4.0
     *
     * @var TranslatorInterface
     */
    protected $translator;

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

    public function getBlockPrefix()
    {
        return 'sonata_type_filter_datetime_range';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', DateRangeOperatorType::class, ['required' => false])
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'field_type' => FormDateTimeRangeType::class,
            'field_options' => ['date_format' => 'yyyy-MM-dd'],
        ]);
    }
}
