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

namespace Sonata\AdminBundle\Form\Type\Operator;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as FormChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DateOperatorType extends AbstractType
{
    public const TYPE_GREATER_EQUAL = 1;
    public const TYPE_GREATER_THAN = 2;
    public const TYPE_EQUAL = 3;
    public const TYPE_LESS_EQUAL = 4;
    public const TYPE_LESS_THAN = 5;
    public const TYPE_NULL = 6;
    public const TYPE_NOT_NULL = 7;

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_translation_domain' => 'SonataAdminBundle',
            'choices' => [
                'label_date_type_equal' => self::TYPE_EQUAL,
                'label_date_type_greater_equal' => self::TYPE_GREATER_EQUAL,
                'label_date_type_greater_than' => self::TYPE_GREATER_THAN,
                'label_date_type_less_equal' => self::TYPE_LESS_EQUAL,
                'label_date_type_less_than' => self::TYPE_LESS_THAN,
                'label_date_type_null' => self::TYPE_NULL,
                'label_date_type_not_null' => self::TYPE_NOT_NULL,
            ],
        ]);
    }

    public function getParent()
    {
        return FormChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return 'sonata_type_operator_date';
    }
}
