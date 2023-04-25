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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sonata\AdminBundle\Route\DefaultRouteGenerator;
use Sonata\AdminBundle\Route\PathInfoBuilder;
use Sonata\AdminBundle\Route\RoutesCache;
use Sonata\AdminBundle\Route\RoutesCacheWarmUp;

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
                param('kernel.cache_dir').'/sonata/admin',
                param('kernel.debug'),
            ])

        ->set('sonata.admin.route.cache_warmup', RoutesCacheWarmUp::class)
            ->tag('kernel.cache_warmer')
            ->args([
                service('sonata.admin.route.cache'),
                service('sonata.admin.pool'),
            ]);
};
