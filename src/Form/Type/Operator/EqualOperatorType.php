<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Form\Type\Operator;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as FormChoiceType;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EqualOperatorType extends AbstractType
{
    public const TYPE_EQUAL = 1;
    public const TYPE_NOT_EQUAL = 2;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choice_translation_domain' => 'SensioLabsAdminBundle',
            'choices' => [
                'label_type_equals' => self::TYPE_EQUAL,
                'label_type_not_equals' => self::TYPE_NOT_EQUAL,
            ],
        ]);
    }

    /**
     * @phpstan-return class-string<FormTypeInterface>
     */
    public function getParent(): string
    {
        return FormChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'sonata_type_operator_equal';
    }
}
