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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SensioLabs\AdminBundle\Security\Handler\NoopSecurityHandler;
use SensioLabs\AdminBundle\Security\Handler\RoleSecurityHandler;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()

        ->set('sensiolabs.admin.security.handler.noop.class', NoopSecurityHandler::class)

        ->set('sensiolabs.admin.security.handler.role.class', RoleSecurityHandler::class);

    $containerConfigurator->services()

        ->set('sensiolabs.admin.security.handler.noop', (string) param('sensiolabs.admin.security.handler.noop.class'))

        ->set('sensiolabs.admin.security.handler.role', (string) param('sensiolabs.admin.security.handler.role.class'))
            ->args([
                service('security.authorization_checker'),
                param('sensiolabs.admin.configuration.security.role_super_admin'),
            ]);
};
