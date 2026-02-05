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
    $routes->import('@SensioLabsAdminBundle/Resources/config/routing/sensiolabs_admin.php')
        ->prefix('/admin');

    $routes->import('.', 'sensiolabs_admin')
        ->prefix('/admin');
};
