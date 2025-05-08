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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sonata\AdminBundle\Action\AppendFormFieldElementAction;
use Sonata\AdminBundle\Action\DashboardAction;
use Sonata\AdminBundle\Action\GetShortObjectDescriptionAction;
use Sonata\AdminBundle\Action\RetrieveAutocompleteItemsAction;
use Sonata\AdminBundle\Action\RetrieveFormFieldElementAction;
use Sonata\AdminBundle\Action\SearchAction;
use Sonata\AdminBundle\Action\SetObjectFieldValueAction;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.admin.action.dashboard', DashboardAction::class)
            ->public()
            ->args([
                param('sonata.admin.configuration.dashboard_blocks'),
                service('sonata.admin.global_template_registry'),
                service('twig'),
            ])

        ->set('sonata.admin.action.search', SearchAction::class)
            ->public()
            ->args([
                service('sonata.admin.pool'),
                service('sonata.admin.global_template_registry'),
                service('twig'),
            ])

        ->set('sonata.admin.action.append_form_field_element', AppendFormFieldElementAction::class)
            ->public()
            ->args([
                service('twig'),
                service('sonata.admin.request.fetcher'),
                service('sonata.admin.helper'),
            ])

        ->set('sonata.admin.action.retrieve_form_field_element', RetrieveFormFieldElementAction::class)
            ->public()
            ->args([
                service('twig'),
                service('sonata.admin.request.fetcher'),
                service('sonata.admin.helper'),
            ])

        ->set('sonata.admin.action.get_short_object_description', GetShortObjectDescriptionAction::class)
            ->public()
            ->args([
                service('twig'),
                service('sonata.admin.request.fetcher'),
            ])

        ->set('sonata.admin.action.set_object_field_value', SetObjectFieldValueAction::class)
            ->public()
            ->args([
                service('twig'),
                service('sonata.admin.request.fetcher'),
                service('validator'),
                service('sonata.admin.form.data_transformer_resolver'),
                service('property_accessor'),
                service('sonata.admin.twig.render_element_runtime'),
            ])

        ->set('sonata.admin.action.retrieve_autocomplete_items', RetrieveAutocompleteItemsAction::class)
            ->public()
            ->args([
                service('sonata.admin.request.fetcher'),
            ]);
};
