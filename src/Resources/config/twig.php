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
use Sonata\AdminBundle\Twig\Extension\BreadcrumbsExtension;
use Sonata\AdminBundle\Twig\Extension\CanonicalizeExtension;
use Sonata\AdminBundle\Twig\Extension\GroupExtension;
use Sonata\AdminBundle\Twig\Extension\IconExtension;
use Sonata\AdminBundle\Twig\Extension\PaginationExtension;
use Sonata\AdminBundle\Twig\Extension\RenderElementExtension;
use Sonata\AdminBundle\Twig\Extension\SecurityExtension;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Sonata\AdminBundle\Twig\Extension\TemplateRegistryExtension;
use Sonata\AdminBundle\Twig\Extension\XEditableExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()

        ->set('sonata.admin.twig.extension.x_editable_type_mapping', XEditableExtension::FIELD_DESCRIPTION_MAPPING);

    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.admin.twig.extension', SonataAdminExtension::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                // NEXT_MAJOR: Remove next line.
                (new ReferenceConfigurator('logger'))->nullOnInvalid(),
                // NEXT_MAJOR: Remove next line.
                new ReferenceConfigurator('translator'),
                new ReferenceConfigurator('service_container'),
                new ReferenceConfigurator('property_accessor'),
                // NEXT_MAJOR: Remove next line.
                new ReferenceConfigurator('security.authorization_checker'),
            ])
             // NEXT_MAJOR: Remove next call.
            ->call('setXEditableTypeMapping', [
                '%sonata.admin.twig.extension.x_editable_type_mapping%',
                'sonata_deprecation_mute',
            ])

        ->set('sonata.templates.twig.extension', TemplateRegistryExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('sonata.admin.global_template_registry'),
                // NEXT_MAJOR: Remove next line.
                new ReferenceConfigurator('service_container'),
                new ReferenceConfigurator('sonata.admin.pool'),
            ])

        ->set('sonata.admin.group.extension', GroupExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
            ])

        ->set('sonata.admin.twig.icon_extension', IconExtension::class)
            ->tag('twig.extension')

        // NEXT_MAJOR: Remove this service.
        ->set('sonata.pagination.twig.extension', PaginationExtension::class)
            ->tag('twig.extension')

        ->set('sonata.security.twig.extension', SecurityExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('security.authorization_checker'),
            ])

        ->set('sonata.canonicalize.twig.extension', CanonicalizeExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('request_stack'),
            ])

        ->set('sonata.xeditable.twig.extension', XEditableExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('translator'),
                '%sonata.admin.twig.extension.x_editable_type_mapping%',
            ])

        ->set('sonata.render_element.twig.extension', RenderElementExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('property_accessor'),
                new ReferenceConfigurator('service_container'),
                (new ReferenceConfigurator('logger'))->nullOnInvalid(),
            ])

        ->set('sonata.admin.twig.breadcrumbs_extension', BreadcrumbsExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('sonata.admin.breadcrumbs_builder'),
            ]);
};
