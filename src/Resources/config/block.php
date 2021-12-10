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

use Sonata\AdminBundle\Block\AdminListBlockService;
use Sonata\AdminBundle\Block\AdminPreviewBlockService;
use Sonata\AdminBundle\Block\AdminSearchBlockService;
use Sonata\AdminBundle\Block\AdminStatsBlockService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.admin.block.admin_list', AdminListBlockService::class)
            ->public()
            ->tag('sonata.block')
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.admin.pool'),
                new ReferenceConfigurator('sonata.admin.global_template_registry'),
            ])

        ->set('sonata.admin.block.search_result', AdminSearchBlockService::class)
            ->public()
            ->tag('sonata.block')
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.admin.pool'),
                new ReferenceConfigurator('sonata.admin.search.handler'),
                new ReferenceConfigurator('sonata.admin.global_template_registry'),
                '%sonata.admin.configuration.global_search.empty_boxes%',
                '%sonata.admin.configuration.global_search.admin_route%',
            ])

        ->set('sonata.admin.block.stats', AdminStatsBlockService::class)
            ->public()
            ->tag('sonata.block')
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.admin.pool'),
            ])

        ->set('sonata.admin.block.admin_preview', AdminPreviewBlockService::class)
            ->public()
            ->tag('sonata.block')
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.admin.pool'),
            ]);
};
