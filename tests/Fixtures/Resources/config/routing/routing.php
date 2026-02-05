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

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('sensiolabs_admin_foo', '/foo')
        ->controller('SensioLabsAdminBundle:RouteAdminController:foo');

    $routes->add('sensiolabs_admin_foo_param', '/foo/{param1}/{param2}')
        ->controller('SensioLabsAdminBundle:RouteAdminController:fooParam');

    $routes->add('sensiolabs_admin_foo_object', '/foo/obj/{param1}/{barId}/{param2}')
        ->controller('SensioLabsAdminBundle:RouteAdminController:fooObject');
};
