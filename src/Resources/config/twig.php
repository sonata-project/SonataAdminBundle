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

use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Sonata\AdminBundle\Twig\Extension\TemplateRegistryExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()

        ->set('sonata.admin.twig.extension.x_editable_type_mapping', [
            'choice' => 'select',
            'boolean' => 'select',
            'text' => 'text',
            'textarea' => 'textarea',
            'html' => 'textarea',
            'email' => 'email',
            'string' => 'text',
            'smallint' => 'text',
            'bigint' => 'text',
            'integer' => 'number',
            'decimal' => 'number',
            'currency' => 'number',
            'percent' => 'number',
            'url' => 'url',
            'date' => 'date',
        ])
    ;

    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.admin.twig.extension', SonataAdminExtension::class)
            ->public()
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                (new ReferenceConfigurator('logger'))->nullOnInvalid(),
                new ReferenceConfigurator('translator'),
                new ReferenceConfigurator('service_container'),
                new ReferenceConfigurator('security.authorization_checker'),
            ])
            ->call('setXEditableTypeMapping', [
                '%sonata.admin.twig.extension.x_editable_type_mapping%',
            ])

        ->set('sonata.templates.twig.extension', TemplateRegistryExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('sonata.admin.global_template_registry'),
                new ReferenceConfigurator('service_container'),
            ])
    ;
};
