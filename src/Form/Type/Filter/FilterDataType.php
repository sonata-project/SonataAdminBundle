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

use Sonata\AdminBundle\Form\DataTransformer\FilterDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FilterDataType extends AbstractType
{
    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', $options['operator_type'], $options['operator_options'] + [
                'label' => false,
                'required' => false,
            ])
            ->add('value', $options['field_type'], $options['field_options'] + [
                'label' => false,
                'required' => false,
            ]);

        $builder
            ->addModelTransformer(new FilterDataTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'operator_options' => [],
            'field_options' => [],
        ]);
        $resolver
            ->setRequired(['operator_type', 'field_type'])
            ->setAllowedTypes('operator_type', 'string')
            ->setAllowedTypes('field_type', 'string')
            ->setAllowedTypes('operator_options', 'array')
            ->setAllowedTypes('field_options', 'array');
    }
}
