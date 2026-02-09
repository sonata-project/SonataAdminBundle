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

return static function (RoutingConfigurator $routes) {
    $routes->add('sensiolabs_admin_user_resetting_request', '/resetting/request')
        ->controller('sensiolabs.admin.user.action.request')
        ->methods(['GET']);

    $routes->add('sensiolabs_admin_user_resetting_send_email', '/resetting/send-email')
        ->controller('sensiolabs.admin.user.action.send_email')
        ->methods(['POST']);

    $routes->add('sensiolabs_admin_user_resetting_check_email', '/resetting/check-email')
        ->controller('sensiolabs.admin.user.action.check_email')
        ->methods(['GET']);

    $routes->add('sensiolabs_admin_user_resetting_reset', '/resetting/reset/{token}')
        ->controller('sensiolabs.admin.user.action.reset')
        ->methods(['GET', 'POST']);
};
