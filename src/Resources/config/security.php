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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sonata\AdminBundle\Security\Handler\NoopSecurityHandler;
use Sonata\AdminBundle\Security\Handler\RoleSecurityHandler;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()

        ->set('sonata.admin.security.handler.noop.class', NoopSecurityHandler::class)

        ->set('sonata.admin.security.handler.role.class', RoleSecurityHandler::class);

    $containerConfigurator->services()

        ->set('sonata.admin.security.handler.noop', (string) param('sonata.admin.security.handler.noop.class'))

        ->set('sonata.admin.security.handler.role', (string) param('sonata.admin.security.handler.role.class'))
            ->args([
                service('security.authorization_checker'),
                param('sonata.admin.configuration.security.role_super_admin'),
            ]);
};
