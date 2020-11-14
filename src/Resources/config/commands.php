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

use Sonata\AdminBundle\Command\ExplainAdminCommand;
use Sonata\AdminBundle\Command\GenerateObjectAclCommand;
use Sonata\AdminBundle\Command\ListAdminCommand;
use Sonata\AdminBundle\Command\SetupAclCommand;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set(ExplainAdminCommand::class, ExplainAdminCommand::class)
            ->public()
            ->tag('console.command')
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                new ReferenceConfigurator('validator'),
            ])

        ->set(GenerateObjectAclCommand::class, GenerateObjectAclCommand::class)
            ->public()
            ->tag('console.command')
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                [],
            ])

        ->set(ListAdminCommand::class, ListAdminCommand::class)
            ->public()
            ->tag('console.command')
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
            ])

        ->set(SetupAclCommand::class, SetupAclCommand::class)
            ->public()
            ->tag('console.command')
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                new ReferenceConfigurator('sonata.admin.manipulator.acl.admin'),
            ])
    ;
};
