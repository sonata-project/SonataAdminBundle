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

use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes) {
    // Redirect root to dashboard
    $routes->add('sensiolabs_admin_redirect', '/')
        ->controller([RedirectController::class, 'redirectAction'])
        ->defaults([
            'route' => 'sensiolabs_admin_dashboard',
            'permanent' => true,
        ]);

    // Dashboard (uses default controller, can be overridden)
    $routes->add('sensiolabs_admin_dashboard', '/dashboard')
        ->controller('sensiolabs.admin.dashboard.default_controller::index');

    // Core admin routes (form field helpers, autocomplete, etc.)
    $routes->add('sensiolabs_admin_retrieve_form_element', '/core/get-form-field-element')
        ->controller('sensiolabs.admin.action.retrieve_form_field_element');

    $routes->add('sensiolabs_admin_append_form_element', '/core/append-form-field-element')
        ->controller('sensiolabs.admin.action.append_form_field_element');

    $routes->add('sensiolabs_admin_short_object_information', '/core/get-short-object-description.{_format}')
        ->controller('sensiolabs.admin.action.get_short_object_description')
        ->defaults(['_format' => 'html'])
        ->requirements(['_format' => 'html|json']);

    $routes->add('sensiolabs_admin_set_object_field_value', '/core/set-object-field-value')
        ->controller('sensiolabs.admin.action.set_object_field_value');

    $routes->add('sensiolabs_admin_retrieve_autocomplete_items', '/core/get-autocomplete-items')
        ->controller('sensiolabs.admin.action.retrieve_autocomplete_items');
};
