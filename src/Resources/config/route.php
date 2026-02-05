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

use SensioLabs\AdminBundle\Route\DefaultRouteGenerator;
use SensioLabs\AdminBundle\Route\PathInfoBuilder;
use SensioLabs\AdminBundle\Route\RoutesCache;
use SensioLabs\AdminBundle\Route\RoutesCacheWarmUp;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sensiolabs.admin.route.path_info', PathInfoBuilder::class)
            ->args([
                service('sensiolabs.admin.audit.manager'),
            ])

        ->set('sensiolabs.admin.route.default_generator', DefaultRouteGenerator::class)
            ->args([
                service('router'),
                service('sensiolabs.admin.route.cache'),
            ])

        ->set('sensiolabs.admin.route.cache', RoutesCache::class)
            ->args([
                param('kernel.cache_dir')->__toString().'/sensiolabs/admin',
                param('kernel.debug'),
            ])

        ->set('sensiolabs.admin.route.cache_warmup', RoutesCacheWarmUp::class)
            ->tag('kernel.cache_warmer')
            ->args([
                service('sensiolabs.admin.route.cache'),
                service('sensiolabs.admin.pool'),
            ]);
};
