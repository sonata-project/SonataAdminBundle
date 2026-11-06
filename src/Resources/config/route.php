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

use Sonata\AdminBundle\DependencyInjection\Compiler\AliasDeprecatedPublicServicesCompilerPass;
use Sonata\AdminBundle\Route\DefaultRouteGenerator;
use Sonata\AdminBundle\Route\PathInfoBuilder;
use Sonata\AdminBundle\Route\QueryStringBuilder;
use Sonata\AdminBundle\Route\RoutesCache;
use Sonata\AdminBundle\Route\RoutesCacheWarmUp;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.admin.route.path_info', PathInfoBuilder::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->args([
                new ReferenceConfigurator('sonata.admin.audit.manager'),
            ])

        ->set('sonata.admin.route.query_string', QueryStringBuilder::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->args([
                new ReferenceConfigurator('sonata.admin.audit.manager'),
            ])

        ->set('sonata.admin.route.default_generator', DefaultRouteGenerator::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->args([
                new ReferenceConfigurator('router'),
                new ReferenceConfigurator('sonata.admin.route.cache'),
            ])

        ->set('sonata.admin.route.cache', RoutesCache::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->args([
                '%kernel.cache_dir%/sonata/admin',
                '%kernel.debug%',
            ])

        ->set('sonata.admin.route.cache_warmup', RoutesCacheWarmUp::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->tag('kernel.cache_warmer')
            ->args([
                new ReferenceConfigurator('sonata.admin.route.cache'),
                new ReferenceConfigurator('sonata.admin.pool'),
            ]);
};
