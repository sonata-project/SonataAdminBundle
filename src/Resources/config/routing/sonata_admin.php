<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;

return function (RoutingConfigurator $routes) {
    $routes->add('sonata_admin_redirect', '/')
        ->controller([RedirectController::class, 'redirectAction'])
        ->defaults([
            'route' => 'sonata_admin_dashboard',
            'permanent' => true,
        ]);

    $routes->add('sonata_admin_dashboard', '/dashboard')
        ->controller('sonata.admin.action.dashboard');

    $routes->add('sonata_admin_retrieve_form_element', '/core/get-form-field-element')
        ->controller('sonata.admin.action.retrieve_form_field_element');

    $routes->add('sonata_admin_append_form_element', '/core/append-form-field-element')
        ->controller('sonata.admin.action.append_form_field_element');

    $routes->add('sonata_admin_short_object_information', '/core/get-short-object-description.{_format}')
        ->controller('sonata.admin.action.get_short_object_description')
        ->defaults(['_format' => 'html'])
        ->requirements(['_format' => 'html|json']);

    $routes->add('sonata_admin_set_object_field_value', '/core/set-object-field-value')
        ->controller('sonata.admin.action.set_object_field_value');

    $routes->add('sonata_admin_search', '/search')
        ->controller('sonata.admin.action.search');

    $routes->add('sonata_admin_retrieve_autocomplete_items', '/core/get-autocomplete-items')
        ->controller('sonata.admin.action.retrieve_autocomplete_items');
};
