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

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('sonata_admin_foo', '/foo')
        ->controller('SonataAdminBundle:RouteAdminController:foo');

    $routes->add('sonata_admin_foo_param', '/foo/{param1}/{param2}')
        ->controller('SonataAdminBundle:RouteAdminController:fooParam');

    $routes->add('sonata_admin_foo_object', '/foo/obj/{param1}/{barId}/{param2}')
        ->controller('SonataAdminBundle:RouteAdminController:fooObject');
};
