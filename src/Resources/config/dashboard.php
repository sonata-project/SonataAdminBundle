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

use SensioLabs\AdminBundle\Dashboard\DashboardControllerInterface;
use SensioLabs\AdminBundle\Dashboard\DefaultDashboardController;
use SensioLabs\AdminBundle\Twig\DashboardRuntime;
use SensioLabs\AdminBundle\Twig\Extension\DashboardExtension;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()
        // Default dashboard controller (used when no custom one is defined)
        ->set('sensiolabs.admin.dashboard.default_controller', DefaultDashboardController::class)
            ->public()
            ->tag('sensiolabs.admin.dashboard_controller')
            ->call('setPool', [service('sensiolabs.admin.pool')])
            ->call('setTemplateRegistry', [service('sensiolabs.admin.global_template_registry')])
            ->call('setTwig', [service('twig')])

        // Twig runtime for dashboard helpers
        ->set('sensiolabs.admin.twig.dashboard_runtime', DashboardRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service(DashboardControllerInterface::class),
                service('sensiolabs.admin.pool'),
                service('router'),
                service('security.authorization_checker'),
            ])

        // Twig extension
        ->set('sensiolabs.admin.twig.dashboard_extension', DashboardExtension::class)
            ->tag('twig.extension');
};
