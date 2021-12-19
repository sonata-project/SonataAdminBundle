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

use Sonata\AdminBundle\Twig\BreadcrumbsRuntime;
use Sonata\AdminBundle\Twig\CanonicalizeRuntime;
use Sonata\AdminBundle\Twig\Extension\BreadcrumbsExtension;
use Sonata\AdminBundle\Twig\Extension\CanonicalizeExtension;
use Sonata\AdminBundle\Twig\Extension\GroupExtension;
use Sonata\AdminBundle\Twig\Extension\IconExtension;
use Sonata\AdminBundle\Twig\Extension\RenderElementExtension;
use Sonata\AdminBundle\Twig\Extension\SecurityExtension;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Sonata\AdminBundle\Twig\Extension\TemplateRegistryExtension;
use Sonata\AdminBundle\Twig\Extension\XEditableExtension;
use Sonata\AdminBundle\Twig\GroupRuntime;
use Sonata\AdminBundle\Twig\IconRuntime;
use Sonata\AdminBundle\Twig\RenderElementRuntime;
use Sonata\AdminBundle\Twig\SecurityRuntime;
use Sonata\AdminBundle\Twig\SonataAdminRuntime;
use Sonata\AdminBundle\Twig\TemplateRegistryRuntime;
use Sonata\AdminBundle\Twig\XEditableRuntime;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()

        ->set('sonata.admin.twig.extension.x_editable_type_mapping', XEditableRuntime::FIELD_DESCRIPTION_MAPPING);

    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        // NEXT_MAJOR: Remove the `args()` call.
        ->set('sonata.admin.twig.extension', SonataAdminExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('sonata.admin.twig.runtime'),
            ])

        ->set('sonata.admin.twig.runtime', SonataAdminRuntime::class)
            ->tag('twig.runtime')
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
            ])

        // NEXT_MAJOR: Remove the `args()` call.
        ->set('sonata.templates.twig.extension', TemplateRegistryExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('sonata.templates.twig.runtime'),
            ])

        ->set('sonata.templates.twig.runtime', TemplateRegistryRuntime::class)
            ->tag('twig.runtime')
            ->args([
                new ReferenceConfigurator('sonata.admin.global_template_registry'),
                new ReferenceConfigurator('sonata.admin.pool'),
            ])

        // NEXT_MAJOR: Remove the `args()` call.
        ->set('sonata.admin.group.extension', GroupExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('sonata.admin.group.runtime'),
            ])

        ->set('sonata.admin.group.runtime', GroupRuntime::class)
            ->tag('twig.runtime')
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
            ])

        // NEXT_MAJOR: Remove the `args()` call.
        ->set('sonata.admin.twig.icon_extension', IconExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('sonata.admin.twig.icon_runtime'),
            ])

        ->set('sonata.admin.twig.icon_runtime', IconRuntime::class)
            ->tag('twig.runtime')

        // NEXT_MAJOR: Remove the `args()` call.
        ->set('sonata.security.twig.extension', SecurityExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('sonata.security.twig.runtime'),
            ])

        ->set('sonata.security.twig.runtime', SecurityRuntime::class)
            ->tag('twig.runtime')
            ->args([
                new ReferenceConfigurator('security.authorization_checker'),
            ])

        // NEXT_MAJOR: Remove the `args()` call.
        ->set('sonata.canonicalize.twig.extension', CanonicalizeExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('sonata.canonicalize.twig.runtime'),
            ])

        ->set('sonata.canonicalize.twig.runtime', CanonicalizeRuntime::class)
            ->tag('twig.runtime')
            ->args([
                new ReferenceConfigurator('request_stack'),
            ])

        // NEXT_MAJOR: Remove the `args()` call.
        ->set('sonata.xeditable.twig.extension', XEditableExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('sonata.xeditable.twig.runtime'),
            ])

        ->set('sonata.xeditable.twig.runtime', XEditableRuntime::class)
            ->tag('twig.runtime')
            ->args([
                new ReferenceConfigurator('translator'),
                '%sonata.admin.twig.extension.x_editable_type_mapping%',
            ])

        // NEXT_MAJOR: Remove the `args()` call.
        ->set('sonata.render_element.twig.extension', RenderElementExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('sonata.render_element.twig.runtime'),
            ])

        ->set('sonata.render_element.twig.runtime', RenderElementRuntime::class)
            ->tag('twig.runtime')
            ->args([
                new ReferenceConfigurator('property_accessor'),
            ])

        // NEXT_MAJOR: Remove the `args()` call.
        ->set('sonata.admin.twig.breadcrumbs_extension', BreadcrumbsExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('sonata.admin.twig.breadcrumbs_runtime'),
            ])

        ->set('sonata.admin.twig.breadcrumbs_runtime', BreadcrumbsRuntime::class)
            ->tag('twig.runtime')
            ->args([
                new ReferenceConfigurator('sonata.admin.breadcrumbs_builder'),
            ]);
};
