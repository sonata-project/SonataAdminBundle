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

use Sonata\AdminBundle\Security\Acl\Permission\MaskBuilder;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandler;
use Sonata\AdminBundle\Security\Handler\NoopSecurityHandler;
use Sonata\AdminBundle\Security\Handler\RoleSecurityHandler;
use Sonata\AdminBundle\Util\AdminAclManipulator;
use Sonata\AdminBundle\Util\AdminObjectAclManipulator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()

        ->set('sonata.admin.security.handler.noop.class', NoopSecurityHandler::class)

        ->set('sonata.admin.security.handler.role.class', RoleSecurityHandler::class)

        ->set('sonata.admin.security.handler.acl.class', AclSecurityHandler::class)

        ->set('sonata.admin.security.mask.builder.class', MaskBuilder::class)

        ->set('sonata.admin.manipulator.acl.admin.class', AdminAclManipulator::class)

        ->set('sonata.admin.object.manipulator.acl.admin.class', AdminObjectAclManipulator::class);

    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.admin.security.handler.noop', '%sonata.admin.security.handler.noop.class%')

        ->set('sonata.admin.security.handler.role', '%sonata.admin.security.handler.role.class%')
            ->args([
                new ReferenceConfigurator('security.authorization_checker'),
                [
                    '%sonata.admin.configuration.security.role_super_admin%',
                ],
            ])

        ->set('sonata.admin.security.handler.acl', '%sonata.admin.security.handler.acl.class%')
            ->args([
                new ReferenceConfigurator('security.token_storage'),
                new ReferenceConfigurator('security.authorization_checker'),
                (new ReferenceConfigurator('security.acl.provider'))->nullOnInvalid(),
                '%sonata.admin.security.mask.builder.class%',
                [
                    '%sonata.admin.configuration.security.role_super_admin%',
                ],
            ])
            ->call('setAdminPermissions', ['%sonata.admin.configuration.security.admin_permissions%'])
            ->call('setObjectPermissions', ['%sonata.admin.configuration.security.object_permissions%'])

        ->set('sonata.admin.manipulator.acl.admin', '%sonata.admin.manipulator.acl.admin.class%')
            ->args([
                '%sonata.admin.security.mask.builder.class%',
            ])

        ->set('sonata.admin.object.manipulator.acl.admin', '%sonata.admin.object.manipulator.acl.admin.class%')
            ->args([
                new ReferenceConfigurator('form.factory'),
                '%sonata.admin.security.mask.builder.class%',
            ])

        ->alias(AdminObjectAclManipulator::class, 'sonata.admin.object.manipulator.acl.admin');
};
