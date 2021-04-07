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

use Sonata\AdminBundle\Form\Type\Operator\DateOperatorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType as FormDateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class DateType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'sonata_type_filter_date';
    }

    public function getParent(): string
    {
        return FilterDataType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'operator_type' => DateOperatorType::class,
            'field_type' => FormDateType::class,
            'field_options' => ['format' => FormDateType::HTML5_FORMAT],
        ]);
    }
}
