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

use SensioLabs\AdminBundle\Action\AppendFormFieldElementAction;
use SensioLabs\AdminBundle\Action\GetShortObjectDescriptionAction;
use SensioLabs\AdminBundle\Action\RetrieveAutocompleteItemsAction;
use SensioLabs\AdminBundle\Action\RetrieveFormFieldElementAction;
use SensioLabs\AdminBundle\Action\SetObjectFieldValueAction;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sensiolabs.admin.action.append_form_field_element', AppendFormFieldElementAction::class)
            ->public()
            ->args([
                service('twig'),
                service('sensiolabs.admin.request.fetcher'),
                service('sensiolabs.admin.helper'),
            ])

        ->set('sensiolabs.admin.action.retrieve_form_field_element', RetrieveFormFieldElementAction::class)
            ->public()
            ->args([
                service('twig'),
                service('sensiolabs.admin.request.fetcher'),
                service('sensiolabs.admin.helper'),
            ])

        ->set('sensiolabs.admin.action.get_short_object_description', GetShortObjectDescriptionAction::class)
            ->public()
            ->args([
                service('twig'),
                service('sensiolabs.admin.request.fetcher'),
            ])

        ->set('sensiolabs.admin.action.set_object_field_value', SetObjectFieldValueAction::class)
            ->public()
            ->args([
                service('twig'),
                service('sensiolabs.admin.request.fetcher'),
                service('validator'),
                service('sensiolabs.admin.form.data_transformer_resolver'),
                service('property_accessor'),
                service('sensiolabs.admin.twig.render_element_runtime'),
            ])

        ->set('sensiolabs.admin.action.retrieve_autocomplete_items', RetrieveAutocompleteItemsAction::class)
            ->public()
            ->args([
                service('sensiolabs.admin.request.fetcher'),
            ]);
};
