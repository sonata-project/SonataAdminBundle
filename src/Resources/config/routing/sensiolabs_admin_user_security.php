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
    $routes->add('sensiolabs_admin_user_security_login', '/login')
        ->controller('sensiolabs.admin.user.action.login');

    $routes->add('sensiolabs_admin_user_security_check', '/login_check')
        ->controller('sensiolabs.admin.user.action.check_login')
        ->methods(['POST']);

    $routes->add('sensiolabs_admin_user_security_logout', '/logout')
        ->controller('sensiolabs.admin.user.action.logout');
};
