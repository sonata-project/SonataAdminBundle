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

use Sonata\AdminBundle\Command\CreateClassCacheCommand;
use Sonata\AdminBundle\Command\ExplainAdminCommand;
use Sonata\AdminBundle\Command\GenerateObjectAclCommand;
use Sonata\AdminBundle\Command\ListAdminCommand;
use Sonata\AdminBundle\Command\SetupAclCommand;
use Sonata\AdminBundle\DependencyInjection\Compiler\AliasDeprecatedPublicServicesCompilerPass;
use Sonata\AdminBundle\Util\BCDeprecationParameters;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        // NEXT_MAJOR: Remove this service.
        ->set(CreateClassCacheCommand::class, CreateClassCacheCommand::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.x'])
            ->tag('console.command', [
                'command' => 'cache:create-cache-class',
            ])
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%service_id%" service is deprecated since sonata-project/admin-bundle 3.39.0 and will be removed in 4.0.',
                '3.39.0'
            ))
            ->args([
                '%kernel.cache_dir%',
                '%kernel.debug%',
            ])

        ->set('sonata.admin.command.explain', ExplainAdminCommand::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.x'])
            ->tag('console.command')
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                new ReferenceConfigurator('validator'),
            ])

        // NEXT_MAJOR: Remove this alias.
        ->alias(ExplainAdminCommand::class, 'sonata.admin.command.explain')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))

        // NEXT_MAJOR: Remove the "setRegistry" call.
        ->set('sonata.admin.command.generate_object_acl', GenerateObjectAclCommand::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.x'])
            ->tag('console.command')
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                [],
            ])
            ->call('setRegistry', [(new ReferenceConfigurator('doctrine'))->nullOnInvalid()])

        ->alias(GenerateObjectAclCommand::class, 'sonata.admin.command.generate_object_acl')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))

        ->set('sonata.admin.command.list', ListAdminCommand::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.x'])
            ->tag('console.command')
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
            ])

        // NEXT_MAJOR: Remove this alias.
        ->alias(ListAdminCommand::class, 'sonata.admin.command.list')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))

        ->set('sonata.admin.command.setup_acl', SetupAclCommand::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.x'])
            ->tag('console.command')
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                new ReferenceConfigurator('sonata.admin.manipulator.acl.admin'),
            ])

        // NEXT_MAJOR: Remove this alias.
        ->alias(SetupAclCommand::class, 'sonata.admin.command.setup_acl')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ));
};
