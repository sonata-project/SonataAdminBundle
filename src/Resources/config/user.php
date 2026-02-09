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

use SensioLabs\AdminBundle\User\Entity\GroupManager;
use SensioLabs\AdminBundle\User\Entity\UserManager;
use SensioLabs\AdminBundle\User\Form\Type\ResetPasswordType;
use SensioLabs\AdminBundle\User\Form\Type\RolesMatrixType;
use SensioLabs\AdminBundle\User\Form\Type\SecurityRolesType;
use SensioLabs\AdminBundle\User\Model\GroupManagerInterface;
use SensioLabs\AdminBundle\User\Model\UserManagerInterface;
use SensioLabs\AdminBundle\User\Security\EditableRolesBuilder;
use SensioLabs\AdminBundle\User\Security\RolesBuilder\AdminRolesBuilder;
use SensioLabs\AdminBundle\User\Security\RolesBuilder\AdminRolesBuilderInterface;
use SensioLabs\AdminBundle\User\Security\RolesBuilder\ExpandableRolesBuilderInterface;
use SensioLabs\AdminBundle\User\Security\RolesBuilder\MatrixRolesBuilder;
use SensioLabs\AdminBundle\User\Security\RolesBuilder\MatrixRolesBuilderInterface;
use SensioLabs\AdminBundle\User\Security\RolesBuilder\SecurityRolesBuilder;
use SensioLabs\AdminBundle\User\Twig\Extension\RolesMatrixExtension;
use SensioLabs\AdminBundle\User\Twig\RolesMatrixRuntime;
use SensioLabs\AdminBundle\User\Twig\UserGlobalVariables;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        // Managers
        ->set('sensiolabs.admin.user.manager', UserManager::class)
            ->args([
                service('doctrine.orm.entity_manager'),
                service('security.user_password_hasher'),
                param('sensiolabs.admin.user.class.user'),
            ])
        ->alias(UserManagerInterface::class, 'sensiolabs.admin.user.manager')

        ->set('sensiolabs.admin.user.group_manager', GroupManager::class)
            ->args([
                service('doctrine.orm.entity_manager'),
                param('sensiolabs.admin.user.class.group'),
            ])
        ->alias(GroupManagerInterface::class, 'sensiolabs.admin.user.group_manager')

        // Editable roles builder (legacy, used by SecurityRolesType)
        ->set('sensiolabs.admin.user.editable_role_builder', EditableRolesBuilder::class)
            ->args([
                service('sensiolabs.admin.pool'),
                service('security.token_storage'),
                service('security.authorization_checker'),
                param('security.role_hierarchy.roles'),
            ])

        // Form types
        ->set('sensiolabs.admin.user.form.type.security_roles', SecurityRolesType::class)
            ->args([
                service('sensiolabs.admin.user.editable_role_builder'),
            ])
            ->tag('form.type')

        // Admin roles builder
        ->set('sensiolabs.admin.user.admin_roles_builder', AdminRolesBuilder::class)
            ->args([
                service('sensiolabs.admin.pool'),
                service('security.authorization_checker'),
                service('translator'),
            ])
        ->alias(AdminRolesBuilderInterface::class, 'sensiolabs.admin.user.admin_roles_builder')

        // Security roles builder
        ->set('sensiolabs.admin.user.security_roles_builder', SecurityRolesBuilder::class)
            ->args([
                service('security.authorization_checker'),
                service('translator'),
                param('security.role_hierarchy.roles'),
            ])
        ->alias(ExpandableRolesBuilderInterface::class, 'sensiolabs.admin.user.security_roles_builder')

        // Matrix roles builder
        ->set('sensiolabs.admin.user.matrix_roles_builder', MatrixRolesBuilder::class)
            ->args([
                service('sensiolabs.admin.user.admin_roles_builder'),
                service('sensiolabs.admin.user.security_roles_builder'),
            ])
        ->alias(MatrixRolesBuilderInterface::class, 'sensiolabs.admin.user.matrix_roles_builder')

        // Roles matrix form type
        ->set('sensiolabs.admin.user.form.type.roles_matrix', RolesMatrixType::class)
            ->args([
                service('sensiolabs.admin.user.matrix_roles_builder'),
            ])
            ->tag('form.type')

        // Reset password form type
        ->set('sensiolabs.admin.user.form.type.reset_password', ResetPasswordType::class)
            ->tag('form.type')

        // Twig extensions
        ->set('sensiolabs.admin.user.twig.roles_matrix_extension', RolesMatrixExtension::class)
            ->tag('twig.extension')

        ->set('sensiolabs.admin.user.twig.roles_matrix_runtime', RolesMatrixRuntime::class)
            ->args([
                service('twig'),
                service('sensiolabs.admin.user.matrix_roles_builder'),
            ])
            ->tag('twig.runtime')

        // User global variables
        ->set('sensiolabs.admin.user.twig.global', UserGlobalVariables::class)
            ->args([
                service('sensiolabs.admin.pool'),
                service('security.token_storage'),
                param('sensiolabs.admin.user.profile.default_avatar'),
            ]);
};
