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
        ->set('sonata.admin.security.handler.acl.class', AclSecurityHandler::class)

        ->set('sonata.admin.security.mask.builder.class', MaskBuilder::class)

        ->set('sonata.admin.manipulator.acl.admin.class', AdminAclManipulator::class)

        ->set('sonata.admin.object.manipulator.acl.admin.class', AdminObjectAclManipulator::class);

    $containerConfigurator->services()
        ->set('sonata.admin.command.generate_object_acl', GenerateObjectAclCommand::class)
            ->tag('console.command')
            ->args([
                service('sonata.admin.pool'),
                abstract_arg('acl object manipulators'),
            ])

        ->set('sonata.admin.command.setup_acl', SetupAclCommand::class)
            ->tag('console.command')
            ->args([
                service('sonata.admin.pool'),
                service('sonata.admin.manipulator.acl.admin'),
            ])

        ->set('sonata.admin.security.handler.acl', (string) param('sonata.admin.security.handler.acl.class'))
            ->args([
                service('security.token_storage'),
                service('security.authorization_checker'),
                service('security.acl.provider')->nullOnInvalid(),
                param('sonata.admin.security.mask.builder.class'),
                param('sonata.admin.configuration.security.role_super_admin'),
            ])
            ->call('setAdminPermissions', [param('sonata.admin.configuration.security.admin_permissions')])
            ->call('setObjectPermissions', [param('sonata.admin.configuration.security.object_permissions')])

        ->set('sonata.admin.manipulator.acl.admin', (string) param('sonata.admin.manipulator.acl.admin.class'))
            ->args([
                param('sonata.admin.security.mask.builder.class'),
            ])

        ->set('sonata.admin.object.manipulator.acl.admin', (string) param('sonata.admin.object.manipulator.acl.admin.class'))
            ->args([
                service('form.factory'),
                param('sonata.admin.security.mask.builder.class'),
            ])

        ->alias(AdminObjectAclManipulator::class, 'sonata.admin.object.manipulator.acl.admin');
};
