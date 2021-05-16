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
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContainsOperatorType extends AbstractType
{
    public const TYPE_CONTAINS = 1;
    public const TYPE_NOT_CONTAINS = 2;
    public const TYPE_EQUAL = 3;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choice_translation_domain' => 'SonataAdminBundle',
            'choices' => [
                'label_type_contains' => self::TYPE_CONTAINS,
                'label_type_not_contains' => self::TYPE_NOT_CONTAINS,
                'label_type_equals' => self::TYPE_EQUAL,
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
        return 'sonata_type_operator_contains';
    }
}
