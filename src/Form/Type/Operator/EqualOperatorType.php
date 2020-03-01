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

final class EqualOperatorType extends AbstractType
{
    /**
     * @deprecated since sonata-project/admin-bundle 3.60, to be removed with 4.0: Use EqualOperatorType::TYPE_EQUAL instead
     */
    public const TYPE_YES = 1;
    /**
     * @deprecated since sonata-project/admin-bundle 3.60, to be removed with 4.0: Use EqualOperatorType::TYPE_NOT_EQUAL instead
     */
    public const TYPE_NO = 2;

    public const TYPE_EQUAL = 1;
    public const TYPE_NOT_EQUAL = 2;

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_translation_domain' => 'SonataAdminBundle',
            'choices' => [
                'label_type_equals' => self::TYPE_EQUAL,
                'label_type_not_equals' => self::TYPE_NOT_EQUAL,
            ],
        ]);
    }

    public function getParent()
    {
        return FormChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return 'sonata_type_operator_equal';
    }
}
