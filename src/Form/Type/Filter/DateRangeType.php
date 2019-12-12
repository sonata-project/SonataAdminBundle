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

use Sonata\CoreBundle\Form\Type\DateRangeType as FormDateRangeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as FormChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class DateRangeType extends AbstractType
{
    public const TYPE_BETWEEN = 1;
    public const TYPE_NOT_BETWEEN = 2;

    public function getBlockPrefix()
    {
        return 'sonata_type_filter_date_range';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [
            'label_date_type_between' => self::TYPE_BETWEEN,
            'label_date_type_not_between' => self::TYPE_NOT_BETWEEN,
        ];
        $choiceOptions = [
            'required' => false,
        ];

        $choiceOptions['choice_translation_domain'] = 'SonataAdminBundle';

        $choiceOptions['choices'] = $choices;

        $builder
            ->add('type', FormChoiceType::class, $choiceOptions)
            ->add('value', $options['field_type'], $options['field_options'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'field_type' => FormDateRangeType::class,
            'field_options' => ['format' => 'yyyy-MM-dd'],
        ]);
    }
}
