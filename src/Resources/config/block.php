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

use Sonata\AdminBundle\Block\AdminListBlockService;
use Sonata\AdminBundle\Block\AdminPreviewBlockService;
use Sonata\AdminBundle\Block\AdminSearchBlockService;
use Sonata\AdminBundle\Block\AdminStatsBlockService;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.admin.block.admin_list', AdminListBlockService::class)
            ->public()
            ->tag('sonata.block')
            ->args([
                service('twig'),
                service('sonata.admin.pool'),
                service('sonata.admin.global_template_registry'),
            ])

        ->set('sonata.admin.block.search_result', AdminSearchBlockService::class)
            ->public()
            ->tag('sonata.block')
            ->args([
                service('twig'),
                service('sonata.admin.pool'),
                service('sonata.admin.search.handler'),
                service('sonata.admin.global_template_registry'),
                param('sonata.admin.configuration.global_search.empty_boxes'),
                param('sonata.admin.configuration.global_search.admin_route'),
            ])

        ->set('sonata.admin.block.stats', AdminStatsBlockService::class)
            ->public()
            ->tag('sonata.block')
            ->args([
                service('twig'),
                service('sonata.admin.pool'),
            ])

        ->set('sonata.admin.block.admin_preview', AdminPreviewBlockService::class)
            ->public()
            ->tag('sonata.block')
            ->args([
                service('twig'),
                service('sonata.admin.pool'),
            ]);
};
