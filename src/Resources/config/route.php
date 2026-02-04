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

        ->set('sonata.admin.route.path_info', PathInfoBuilder::class)
            ->args([
                service('sonata.admin.audit.manager'),
            ])

        ->set('sonata.admin.route.default_generator', DefaultRouteGenerator::class)
            ->args([
                service('router'),
                service('sonata.admin.route.cache'),
            ])

        ->set('sonata.admin.route.cache', RoutesCache::class)
            ->args([
                param('kernel.cache_dir')->__toString().'/sonata/admin',
                param('kernel.debug'),
            ])

        ->set('sonata.admin.route.cache_warmup', RoutesCacheWarmUp::class)
            ->tag('kernel.cache_warmer')
            ->args([
                service('sonata.admin.route.cache'),
                service('sonata.admin.pool'),
            ]);
};
