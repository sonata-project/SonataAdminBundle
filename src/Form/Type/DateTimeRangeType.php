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

namespace SensioLabs\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeRangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $options['field_options_start'] = array_merge(
            [
                'label' => 'date_range_start',
                'translation_domain' => 'SensioLabsAdminBundle',
            ],
            $options['field_options_start']
        );

        $options['field_options_end'] = array_merge(
            [
                'label' => 'date_range_end',
                'translation_domain' => 'SensioLabsAdminBundle',
            ],
            $options['field_options_end']
        );

        $builder->add(
            'start',
            $options['field_type'],
            array_merge(['required' => false], $options['field_options'], $options['field_options_start'])
        );
        $builder->add(
            'end',
            $options['field_type'],
            array_merge(['required' => false], $options['field_options'], $options['field_options_end'])
        );
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->children['start']->vars['linked_to'] = $view->children['end']->vars['id'];
    }

    public function getBlockPrefix(): string
    {
        return 'sensiolabs_type_datetime_range';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'field_options' => [],
            'field_options_start' => [],
            'field_options_end' => [],
            'field_type' => DateTimeType::class,
        ]);

        $resolver->setAllowedTypes('field_options', 'array');
        $resolver->setAllowedTypes('field_options_start', 'array');
        $resolver->setAllowedTypes('field_options_end', 'array');
        $resolver->setAllowedTypes('field_type', 'string');
    }
}
