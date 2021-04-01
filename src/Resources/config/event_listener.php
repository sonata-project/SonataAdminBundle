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

use Sonata\AdminBundle\EventListener\AssetsInstallCommandListener;
use Sonata\AdminBundle\Util\BCDeprecationParameters;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.admin.listener.assets_install', AssetsInstallCommandListener::class)
            ->tag('kernel.event_listener', [
                'event' => ConsoleEvents::TERMINATE,
                'method' => 'copySonataCoreBundleAssets',
            ])
            ->args([
                new ReferenceConfigurator('filesystem'),
                '%kernel.project_dir%',
            ])

        // NEXT_MAJOR: Remove this alias.
        ->alias(AssetsInstallCommandListener::class, 'sonata.admin.listener.assets_install')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.x and will be removed in 4.0.',
                '3.x'
            ));
};
