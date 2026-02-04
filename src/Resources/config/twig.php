<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SensioLabs\AdminBundle\Twig\BreadcrumbsRuntime;
use SensioLabs\AdminBundle\Twig\CanonicalizeRuntime;
use SensioLabs\AdminBundle\Twig\Extension\BreadcrumbsExtension;
use SensioLabs\AdminBundle\Twig\Extension\CanonicalizeExtension;
use SensioLabs\AdminBundle\Twig\Extension\GroupExtension;
use SensioLabs\AdminBundle\Twig\Extension\IconExtension;
use SensioLabs\AdminBundle\Twig\Extension\RenderElementExtension;
use SensioLabs\AdminBundle\Twig\Extension\SecurityExtension;
use SensioLabs\AdminBundle\Twig\Extension\SensioLabsAdminExtension;
use SensioLabs\AdminBundle\Twig\Extension\TemplateRegistryExtension;
use SensioLabs\AdminBundle\Twig\Extension\XEditableExtension;
use SensioLabs\AdminBundle\Twig\GroupRuntime;
use SensioLabs\AdminBundle\Twig\IconRuntime;
use SensioLabs\AdminBundle\Twig\RenderElementRuntime;
use SensioLabs\AdminBundle\Twig\SecurityRuntime;
use SensioLabs\AdminBundle\Twig\SensioLabsAdminRuntime;
use SensioLabs\AdminBundle\Twig\TemplateRegistryRuntime;
use SensioLabs\AdminBundle\Twig\XEditableRuntime;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()

        ->set('sonata.admin.twig.extension.x_editable_type_mapping', XEditableRuntime::FIELD_DESCRIPTION_MAPPING);

    $containerConfigurator->services()

        ->set('sonata.admin.twig.sonata_admin_extension', SensioLabsAdminExtension::class)
            ->tag('twig.extension')

        ->set('sonata.admin.twig.sonata_admin_runtime', SensioLabsAdminRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service('sonata.admin.pool'),
            ])

        ->set('sonata.admin.twig.template_registry_extension', TemplateRegistryExtension::class)
            ->tag('twig.extension')

        ->set('sonata.admin.twig.template_registry_runtime', TemplateRegistryRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service('sonata.admin.global_template_registry'),
                service('sonata.admin.pool'),
            ])

        ->set('sonata.admin.twig.group_extension', GroupExtension::class)
            ->tag('twig.extension')

        ->set('sonata.admin.twig.group_runtime', GroupRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service('sonata.admin.pool'),
            ])

        ->set('sonata.admin.twig.icon_extension', IconExtension::class)
            ->tag('twig.extension')

        ->set('sonata.admin.twig.icon_runtime', IconRuntime::class)
            ->tag('twig.runtime')

        ->set('sonata.admin.twig.security_extension', SecurityExtension::class)
            ->tag('twig.extension')

        ->set('sonata.admin.twig.security_runtime', SecurityRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service('security.authorization_checker'),
            ])

        ->set('sonata.admin.twig.canonicalize_extension', CanonicalizeExtension::class)
            ->tag('twig.extension')

        ->set('sonata.admin.twig.canonicalize_runtime', CanonicalizeRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service('request_stack'),
            ])

        ->set('sonata.admin.twig.xeditable_extension', XEditableExtension::class)
            ->tag('twig.extension')

        ->set('sonata.admin.twig.xeditable_runtime', XEditableRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service('translator'),
                '%sonata.admin.twig.extension.x_editable_type_mapping%',
            ])

        ->set('sonata.admin.twig.render_element_extension', RenderElementExtension::class)
            ->tag('twig.extension')

        ->set('sonata.admin.twig.render_element_runtime', RenderElementRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service('property_accessor'),
            ])

        ->set('sonata.admin.twig.breadcrumbs_extension', BreadcrumbsExtension::class)
            ->tag('twig.extension')

        ->set('sonata.admin.twig.breadcrumbs_runtime', BreadcrumbsRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service('sonata.admin.breadcrumbs_builder'),
            ]);
};
