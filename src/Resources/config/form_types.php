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

use Sonata\AdminBundle\Form\DataTransformer\BooleanToStringTransformer;
use Sonata\AdminBundle\Form\DataTransformerResolver;
use Sonata\AdminBundle\Form\Extension\ChoiceTypeExtension;
use Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension;
use Sonata\AdminBundle\Form\Extension\Field\Type\MopaCompatibilityTypeFieldExtension;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeType;
use Sonata\AdminBundle\Form\Type\Filter\DateType;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelHiddenType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelReferenceType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as SymfonyChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.admin.form.type.admin', AdminType::class)
            ->tag('form.type', ['alias' => 'sonata_type_admin'])
            ->args([
                new ReferenceConfigurator('sonata.admin.helper'),
            ])

        ->set('sonata.admin.form.type.model_choice', ModelType::class)
            ->tag('form.type', ['alias' => 'sonata_type_model'])
            ->args([
                new ReferenceConfigurator('property_accessor'),
            ])

        ->set('sonata.admin.form.type.model_list', ModelListType::class)
            ->tag('form.type', ['alias' => 'sonata_type_model_list'])

        ->set('sonata.admin.form.type.model_reference', ModelReferenceType::class)
            ->tag('form.type', ['alias' => 'sonata_type_model_reference'])

        ->set('sonata.admin.form.type.model_hidden', ModelHiddenType::class)
            ->tag('form.type', ['alias' => 'sonata_type_model_hidden'])

        ->set('sonata.admin.form.type.model_autocomplete', ModelAutocompleteType::class)
            ->tag('form.type', ['alias' => 'sonata_type_model_autocomplete'])

        ->set('sonata.admin.form.type.collection', CollectionType::class)
            ->tag('form.type', ['alias' => 'sonata_type_native_collection'])

        ->set('sonata.admin.doctrine_orm.form.type.choice_field_mask', ChoiceFieldMaskType::class)
            ->tag('form.type', ['alias' => 'sonata_type_choice_field_mask'])

        ->set('sonata.admin.form.extension.field', FormTypeFieldExtension::class)
            ->tag('form.type_extension', [
                'alias' => 'form',
                'extended_type' => FormType::class,
            ])
            ->args(['', ''])

        ->set('sonata.admin.form.extension.field.mopa', MopaCompatibilityTypeFieldExtension::class)
            ->tag('form.type_extension', [
                'alias' => 'form',
                'extended_type' => FormType::class,
            ])

        ->set('sonata.admin.form.extension.choice', ChoiceTypeExtension::class)
            ->tag('form.type_extension', [
                'alias' => 'choice',
                'extended_type' => SymfonyChoiceType::class,
            ])

        ->set('sonata.admin.form.filter.type.number', NumberType::class)
            ->tag('form.type', ['alias' => 'sonata_type_filter_number'])

        ->set('sonata.admin.form.filter.type.choice', ChoiceType::class)
            ->tag('form.type', ['alias' => 'sonata_type_filter_choice'])

        ->set('sonata.admin.form.filter.type.default', DefaultType::class)
            ->tag('form.type', ['alias' => 'sonata_type_filter_default'])

        ->set('sonata.admin.form.filter.type.date', DateType::class)
            ->tag('form.type', ['alias' => 'sonata_type_filter_date'])

        ->set('sonata.admin.form.filter.type.daterange', DateRangeType::class)
            ->tag('form.type', ['alias' => 'sonata_type_filter_date_range'])

        ->set('sonata.admin.form.filter.type.datetime', DateTimeType::class)
            ->tag('form.type', ['alias' => 'sonata_type_filter_datetime'])

        ->set('sonata.admin.form.filter.type.datetime_range', DateTimeRangeType::class)
            ->tag('form.type', ['alias' => 'sonata_type_filter_datetime_range'])

        ->set('sonata.admin.form.data_transformer.boolean_to_string', BooleanToStringTransformer::class)
            ->args([
                1,
            ])

        ->set('sonata.admin.form.data_transformer_resolver', DataTransformerResolver::class)
            ->call('addCustomGlobalTransformer', [
                'boolean',
                new ReferenceConfigurator('sonata.admin.form.data_transformer.boolean_to_string'),
            ]);
};
