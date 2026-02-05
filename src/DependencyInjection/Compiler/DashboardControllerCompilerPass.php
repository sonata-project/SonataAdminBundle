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

namespace SensioLabs\AdminBundle\DependencyInjection\Compiler;

use SensioLabs\AdminBundle\Dashboard\DashboardControllerInterface;
use SensioLabs\AdminBundle\Dashboard\DefaultDashboardController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Detects custom dashboard controllers and registers them.
 *
 * If a user creates a class implementing DashboardControllerInterface,
 * it will automatically be used instead of the DefaultDashboardController.
 */
final class DashboardControllerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Find all services implementing DashboardControllerInterface
        $taggedServices = $container->findTaggedServiceIds('sensiolabs.admin.dashboard_controller');

        $customControllerServiceId = null;

        foreach ($taggedServices as $serviceId => $tags) {
            $definition = $container->getDefinition($serviceId);
            $class = $definition->getClass();

            // Skip the default controller
            if (null !== $class && is_a($class, DefaultDashboardController::class, true)) {
                continue;
            }

            // Use the first custom controller found
            $customControllerServiceId = $serviceId;
            break;
        }

        // If a custom controller was found, use it as the main dashboard controller
        if (null !== $customControllerServiceId) {
            $container->setAlias('sensiolabs.admin.dashboard.controller', $customControllerServiceId)
                ->setPublic(true);
            $container->setAlias(DashboardControllerInterface::class, $customControllerServiceId)
                ->setPublic(true);
        } else {
            // Use the default controller
            $container->setAlias('sensiolabs.admin.dashboard.controller', 'sensiolabs.admin.dashboard.default_controller')
                ->setPublic(true);
            $container->setAlias(DashboardControllerInterface::class, 'sensiolabs.admin.dashboard.default_controller')
                ->setPublic(true);
        }
    }
}
