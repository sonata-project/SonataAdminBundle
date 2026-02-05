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

use SensioLabs\AdminBundle\Command\GenerateObjectAclCommand;
use SensioLabs\AdminBundle\Command\SetupAclCommand;
use SensioLabs\AdminBundle\Security\Acl\Permission\MaskBuilder;
use SensioLabs\AdminBundle\Security\Handler\AclSecurityHandler;
use SensioLabs\AdminBundle\Util\AdminAclManipulator;
use SensioLabs\AdminBundle\Util\AdminObjectAclManipulator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()
        ->set('sensiolabs.admin.security.handler.acl.class', AclSecurityHandler::class)

        ->set('sensiolabs.admin.security.mask.builder.class', MaskBuilder::class)

        ->set('sensiolabs.admin.manipulator.acl.admin.class', AdminAclManipulator::class)

        ->set('sensiolabs.admin.object.manipulator.acl.admin.class', AdminObjectAclManipulator::class);

    $containerConfigurator->services()
        ->set('sensiolabs.admin.command.generate_object_acl', GenerateObjectAclCommand::class)
            ->tag('console.command')
            ->args([
                service('sensiolabs.admin.pool'),
                abstract_arg('acl object manipulators'),
            ])

        ->set('sensiolabs.admin.command.setup_acl', SetupAclCommand::class)
            ->tag('console.command')
            ->args([
                service('sensiolabs.admin.pool'),
                service('sensiolabs.admin.manipulator.acl.admin'),
            ])

        ->set('sensiolabs.admin.security.handler.acl', (string) param('sensiolabs.admin.security.handler.acl.class'))
            ->args([
                service('security.token_storage'),
                service('security.authorization_checker'),
                service('security.acl.provider')->nullOnInvalid(),
                param('sensiolabs.admin.security.mask.builder.class'),
                param('sensiolabs.admin.configuration.security.role_super_admin'),
            ])
            ->call('setAdminPermissions', [param('sensiolabs.admin.configuration.security.admin_permissions')])
            ->call('setObjectPermissions', [param('sensiolabs.admin.configuration.security.object_permissions')])

        ->set('sensiolabs.admin.manipulator.acl.admin', (string) param('sensiolabs.admin.manipulator.acl.admin.class'))
            ->args([
                param('sensiolabs.admin.security.mask.builder.class'),
            ])

        ->set('sensiolabs.admin.object.manipulator.acl.admin', (string) param('sensiolabs.admin.object.manipulator.acl.admin.class'))
            ->args([
                service('form.factory'),
                param('sensiolabs.admin.security.mask.builder.class'),
            ])

        ->alias(AdminObjectAclManipulator::class, 'sensiolabs.admin.object.manipulator.acl.admin');
};
