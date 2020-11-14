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
use Sonata\Form\Type\DateTimeRangeType as FormDateTimeRangeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class DateTimeRangeType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'sonata_type_filter_datetime_range';
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', DateRangeOperatorType::class, ['required' => false])
            ->add('value', $options['field_type'], $options['field_options'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'field_type' => FormDateTimeRangeType::class,
            'field_options' => ['date_format' => 'yyyy-MM-dd'],
        ]);
    }
}
