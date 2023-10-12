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

use Sonata\AdminBundle\EventListener\AdminEventListener;
use Sonata\AdminBundle\EventListener\ConfigureCRUDControllerListener;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.admin.event_listener.admin_event', AdminEventListener::class)
            ->tag('kernel.event_subscriber')
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.admin.request.fetcher'),
            ])

        ->set('sonata.admin.event_listener.configure_crud_controller', ConfigureCRUDControllerListener::class)
            ->tag('kernel.event_subscriber');
};
