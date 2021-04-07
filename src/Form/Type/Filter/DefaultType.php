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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class DefaultType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'sonata_type_filter_default';
    }

    public function getParent(): string
    {
        return FilterDataType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'operator_type' => HiddenType::class,
            'field_type' => TextType::class,
        ]);
    }
}
