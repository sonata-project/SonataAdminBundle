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

use Sonata\AdminBundle\Action\AppendFormFieldElementAction;
use Sonata\AdminBundle\Action\DashboardAction;
use Sonata\AdminBundle\Action\GetShortObjectDescriptionAction;
use Sonata\AdminBundle\Action\RetrieveAutocompleteItemsAction;
use Sonata\AdminBundle\Action\RetrieveFormFieldElementAction;
use Sonata\AdminBundle\Action\SearchAction;
use Sonata\AdminBundle\Action\SetObjectFieldValueAction;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.admin.action.dashboard', DashboardAction::class)
            ->public()
            ->args([
                '%sonata.admin.configuration.dashboard_blocks%',
                new ReferenceConfigurator('sonata.admin.global_template_registry'),
                new ReferenceConfigurator('twig'),
            ])

        ->set('sonata.admin.action.search', SearchAction::class)
            ->public()
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                new ReferenceConfigurator('sonata.admin.global_template_registry'),
                new ReferenceConfigurator('twig'),
            ])

        ->set('sonata.admin.action.append_form_field_element', AppendFormFieldElementAction::class)
            ->public()
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.admin.request.fetcher'),
                new ReferenceConfigurator('sonata.admin.helper'),
            ])

        ->set('sonata.admin.action.retrieve_form_field_element', RetrieveFormFieldElementAction::class)
            ->public()
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.admin.request.fetcher'),
                new ReferenceConfigurator('sonata.admin.helper'),
            ])

        ->set('sonata.admin.action.get_short_object_description', GetShortObjectDescriptionAction::class)
            ->public()
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.admin.request.fetcher'),
            ])

        ->set('sonata.admin.action.set_object_field_value', SetObjectFieldValueAction::class)
            ->public()
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.admin.request.fetcher'),
                new ReferenceConfigurator('validator'),
                new ReferenceConfigurator('sonata.admin.form.data_transformer_resolver'),
                new ReferenceConfigurator('property_accessor'),
            ])

        ->set('sonata.admin.action.retrieve_autocomplete_items', RetrieveAutocompleteItemsAction::class)
            ->public()
            ->args([
                new ReferenceConfigurator('sonata.admin.request.fetcher'),
            ]);
};
