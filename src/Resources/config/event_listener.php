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

use Sonata\AdminBundle\EventListener\AdminEventListener;
use Sonata\AdminBundle\EventListener\ConfigureCRUDControllerListener;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set(AdminEventListener::class)
            ->tag('kernel.event_subscriber', [])
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator(AdminFetcherInterface::class),
                new ReferenceConfigurator('sonata.admin.global_template_registry'),
            ])

        ->set('sonata.admin.event_listener.configure_crud_controller', ConfigureCRUDControllerListener::class)
            ->tag('kernel.event_subscriber');
};
