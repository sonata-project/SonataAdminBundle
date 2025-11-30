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

use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\XmlFileLoader;

return static function (RoutingConfigurator $routes) {
    foreach (debug_backtrace() as $trace) {
        /* @phpstan-ignore class.notFound */
        if (isset($trace['object']) && $trace['object'] instanceof XmlFileLoader && 'doImport' === $trace['function'] && isset($trace['args'])) {
            $realpath = realpath($trace['args'][3]);

            if (false !== $realpath && __DIR__ === dirname($realpath)) {
                @trigger_error(
                    'The "sonata_admin.xml" routing configuration is deprecated since sonata-project/admin-bundle 4.39'
                    .' and will throw an error in 5.0. Import "sonata_admin.php" instead.',
                    \E_USER_DEPRECATED
                );

                break;
            }
        }
    }

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
