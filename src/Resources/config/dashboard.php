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

use SensioLabs\AdminBundle\Dashboard\AbstractDashboardController;
use SensioLabs\AdminBundle\Dashboard\DefaultDashboardController;
use SensioLabs\AdminBundle\Twig\DashboardRuntime;
use SensioLabs\AdminBundle\Twig\Extension\DashboardExtension;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sensiolabs.admin.dashboard.default_controller', DefaultDashboardController::class)
            ->public()
            ->args([
                service('sensiolabs.admin.pool'),
                service('sensiolabs.admin.global_template_registry'),
                service('twig'),
            ])

        ->alias(AbstractDashboardController::class, 'sensiolabs.admin.dashboard.default_controller')

        ->set('sensiolabs.admin.twig.dashboard_runtime', DashboardRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service(AbstractDashboardController::class),
                service('sensiolabs.admin.pool'),
                service('router'),
            ])

        ->set('sensiolabs.admin.twig.dashboard_extension', DashboardExtension::class)
            ->tag('twig.extension');
};
