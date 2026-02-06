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

namespace SensioLabs\AdminBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Dashboard\DashboardControllerInterface;
use SensioLabs\AdminBundle\Dashboard\DefaultDashboardController;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\DashboardControllerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class DashboardControllerCompilerPassTest extends TestCase
{
    public function testDefaultControllerIsUsedWhenNoCustomControllerRegistered(): void
    {
        $container = new ContainerBuilder();

        $defaultDefinition = new Definition(DefaultDashboardController::class);
        $defaultDefinition->addTag('sensiolabs.admin.dashboard_controller');
        $container->setDefinition('sensiolabs.admin.dashboard.default_controller', $defaultDefinition);

        $compilerPass = new DashboardControllerCompilerPass();
        $compilerPass->process($container);

        static::assertTrue($container->hasAlias('sensiolabs.admin.dashboard.controller'));
        static::assertSame(
            'sensiolabs.admin.dashboard.default_controller',
            (string) $container->getAlias('sensiolabs.admin.dashboard.controller')
        );

        static::assertTrue($container->hasAlias(DashboardControllerInterface::class));
        static::assertSame(
            'sensiolabs.admin.dashboard.default_controller',
            (string) $container->getAlias(DashboardControllerInterface::class)
        );
    }

    public function testCustomControllerIsUsedWhenRegistered(): void
    {
        $container = new ContainerBuilder();

        $defaultDefinition = new Definition(DefaultDashboardController::class);
        $defaultDefinition->addTag('sensiolabs.admin.dashboard_controller');
        $container->setDefinition('sensiolabs.admin.dashboard.default_controller', $defaultDefinition);

        // Register a custom controller
        $customDefinition = new Definition('App\Dashboard\CustomDashboardController');
        $customDefinition->addTag('sensiolabs.admin.dashboard_controller');
        $container->setDefinition('app.dashboard.custom_controller', $customDefinition);

        $compilerPass = new DashboardControllerCompilerPass();
        $compilerPass->process($container);

        static::assertTrue($container->hasAlias('sensiolabs.admin.dashboard.controller'));
        static::assertSame(
            'app.dashboard.custom_controller',
            (string) $container->getAlias('sensiolabs.admin.dashboard.controller')
        );

        static::assertTrue($container->hasAlias(DashboardControllerInterface::class));
        static::assertSame(
            'app.dashboard.custom_controller',
            (string) $container->getAlias(DashboardControllerInterface::class)
        );
    }

    public function testAliasesArePublic(): void
    {
        $container = new ContainerBuilder();

        $defaultDefinition = new Definition(DefaultDashboardController::class);
        $defaultDefinition->addTag('sensiolabs.admin.dashboard_controller');
        $container->setDefinition('sensiolabs.admin.dashboard.default_controller', $defaultDefinition);

        $compilerPass = new DashboardControllerCompilerPass();
        $compilerPass->process($container);

        static::assertTrue($container->getAlias('sensiolabs.admin.dashboard.controller')->isPublic());
        static::assertTrue($container->getAlias(DashboardControllerInterface::class)->isPublic());
    }

    public function testDefaultFallbackWhenNoTaggedServicesExist(): void
    {
        $container = new ContainerBuilder();

        $compilerPass = new DashboardControllerCompilerPass();
        $compilerPass->process($container);

        static::assertTrue($container->hasAlias('sensiolabs.admin.dashboard.controller'));
        static::assertSame(
            'sensiolabs.admin.dashboard.default_controller',
            (string) $container->getAlias('sensiolabs.admin.dashboard.controller')
        );
    }
}
