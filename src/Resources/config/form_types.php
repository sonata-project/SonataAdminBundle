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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SensioLabs\AdminBundle\Form\DataTransformer\BooleanToStringTransformer;
use SensioLabs\AdminBundle\Form\DataTransformerResolver;
use SensioLabs\AdminBundle\Form\Extension\ChoiceTypeExtension;
use SensioLabs\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension;
use SensioLabs\AdminBundle\Form\Extension\Field\Type\MopaCompatibilityTypeFieldExtension;
use SensioLabs\AdminBundle\Form\Type\AdminType;
use SensioLabs\AdminBundle\Form\Type\BooleanType;
use SensioLabs\AdminBundle\Form\Type\ChoiceFieldMaskType;
use SensioLabs\AdminBundle\Form\Type\CollectionType;
use SensioLabs\AdminBundle\Form\Type\DateRangeType;
use SensioLabs\AdminBundle\Form\Type\DateTimeRangeType;
use SensioLabs\AdminBundle\Form\Type\ImmutableArrayType;
use SensioLabs\AdminBundle\Form\Type\ModelAutocompleteType;
use SensioLabs\AdminBundle\Form\Type\ModelHiddenType;
use SensioLabs\AdminBundle\Form\Type\ModelListType;
use SensioLabs\AdminBundle\Form\Type\ModelReferenceType;
use SensioLabs\AdminBundle\Form\Type\ModelType;
use SensioLabs\AdminBundle\Form\Type\SensioLabsCollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as SymfonyChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sensiolabs.admin.form.type.admin', AdminType::class)
            ->tag('form.type', ['alias' => 'sensiolabs_type_admin'])
            ->args([
                service('sensiolabs.admin.helper'),
            ])

        ->set('sensiolabs.admin.form.type.model_choice', ModelType::class)
            ->tag('form.type', ['alias' => 'sensiolabs_type_model'])
            ->args([
                service('property_accessor'),
            ])

        ->set('sensiolabs.admin.form.type.model_list', ModelListType::class)
            ->tag('form.type', ['alias' => 'sensiolabs_type_model_list'])

        ->set('sensiolabs.admin.form.type.model_reference', ModelReferenceType::class)
            ->tag('form.type', ['alias' => 'sensiolabs_type_model_reference'])

        ->set('sensiolabs.admin.form.type.model_hidden', ModelHiddenType::class)
            ->tag('form.type', ['alias' => 'sensiolabs_type_model_hidden'])

        ->set('sensiolabs.admin.form.type.model_autocomplete', ModelAutocompleteType::class)
            ->tag('form.type', ['alias' => 'sensiolabs_type_model_autocomplete'])

        ->set('sensiolabs.admin.form.type.collection', CollectionType::class)
            ->tag('form.type', ['alias' => 'sensiolabs_type_native_collection'])

        ->set('sensiolabs.admin.form.type.immutable_array', ImmutableArrayType::class)
            ->tag('form.type', ['alias' => 'sensiolabs_type_immutable_array'])

        ->set('sensiolabs.admin.form.type.boolean', BooleanType::class)
            ->tag('form.type', ['alias' => 'sensiolabs_type_boolean'])

        ->set('sensiolabs.admin.form.type.sensiolabs_collection', SensioLabsCollectionType::class)
            ->tag('form.type', ['alias' => 'sensiolabs_type_collection'])

        ->set('sensiolabs.admin.form.type.date_range', DateRangeType::class)
            ->tag('form.type', ['alias' => 'sensiolabs_type_date_range'])

        ->set('sensiolabs.admin.form.type.datetime_range', DateTimeRangeType::class)
            ->tag('form.type', ['alias' => 'sensiolabs_type_datetime_range'])

        ->set('sensiolabs.admin.doctrine_orm.form.type.choice_field_mask', ChoiceFieldMaskType::class)
            ->tag('form.type', ['alias' => 'sensiolabs_type_choice_field_mask'])

        ->set('sensiolabs.admin.form.extension.field', FormTypeFieldExtension::class)
            ->tag('form.type_extension', [
                'alias' => 'form',
                'extended_type' => FormType::class,
            ])
            ->args([
                abstract_arg('default classes'),
                abstract_arg('default options'),
            ])

        ->set('sensiolabs.admin.form.extension.field.mopa', MopaCompatibilityTypeFieldExtension::class)
            ->tag('form.type_extension', [
                'alias' => 'form',
                'extended_type' => FormType::class,
            ])

        ->set('sensiolabs.admin.form.extension.choice', ChoiceTypeExtension::class)
            ->tag('form.type_extension', [
                'alias' => 'choice',
                'extended_type' => SymfonyChoiceType::class,
            ])

        ->set('sensiolabs.admin.form.data_transformer.boolean_to_string', BooleanToStringTransformer::class)
            ->args([
                1,
            ])

        ->set('sensiolabs.admin.form.data_transformer_resolver', DataTransformerResolver::class)
            ->call('addCustomGlobalTransformer', [
                'boolean',
                service('sensiolabs.admin.form.data_transformer.boolean_to_string'),
            ]);
};
