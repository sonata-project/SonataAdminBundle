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

namespace SensioLabs\AdminBundle\Tests\User\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\User\DependencyInjection\Compiler\RolesMatrixCompilerPass;
use SensioLabs\AdminBundle\User\Security\RolesBuilder\AdminRolesBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class RolesMatrixCompilerPassTest extends TestCase
{
    public function testDoesNothingWhenBuilderNotDefined(): void
    {
        $container = new ContainerBuilder();
        $pass = new RolesMatrixCompilerPass();

        // Should not throw
        $pass->process($container);

        self::assertTrue(true);
    }

    public function testExcludesAdminsMarkedAsHidden(): void
    {
        $container = new ContainerBuilder();

        $builderDef = new Definition(AdminRolesBuilder::class);
        $container->setDefinition('sensiolabs.admin.user.admin_roles_builder', $builderDef);

        $adminDef = new Definition();
        $adminDef->addTag('sensiolabs.admin', ['show_in_roles_matrix' => false]);
        $container->setDefinition('app.admin.hidden', $adminDef);

        $pass = new RolesMatrixCompilerPass();
        $pass->process($container);

        $calls = $builderDef->getMethodCalls();
        self::assertCount(1, $calls);
        self::assertSame('setExcludedAdmins', $calls[0][0]);
        self::assertSame(['app.admin.hidden'], $calls[0][1][0]);
    }

    public function testDoesNotExcludeVisibleAdmins(): void
    {
        $container = new ContainerBuilder();

        $builderDef = new Definition(AdminRolesBuilder::class);
        $container->setDefinition('sensiolabs.admin.user.admin_roles_builder', $builderDef);

        $adminDef = new Definition();
        $adminDef->addTag('sensiolabs.admin', []);
        $container->setDefinition('app.admin.visible', $adminDef);

        $pass = new RolesMatrixCompilerPass();
        $pass->process($container);

        $calls = $builderDef->getMethodCalls();
        self::assertCount(0, $calls);
    }
}
