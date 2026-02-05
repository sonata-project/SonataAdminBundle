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
use SensioLabs\AdminBundle\Twig\Extension\RenderElementExtension;
use SensioLabs\AdminBundle\Twig\Extension\SecurityExtension;
use SensioLabs\AdminBundle\Twig\Extension\SensioLabsAdminExtension;
use SensioLabs\AdminBundle\Twig\Extension\TemplateRegistryExtension;
use SensioLabs\AdminBundle\Twig\Extension\XEditableExtension;
use SensioLabs\AdminBundle\Twig\RenderElementRuntime;
use SensioLabs\AdminBundle\Twig\SecurityRuntime;
use SensioLabs\AdminBundle\Twig\SensioLabsAdminRuntime;
use SensioLabs\AdminBundle\Twig\TemplateRegistryRuntime;
use SensioLabs\AdminBundle\Twig\XEditableRuntime;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()

        ->set('sensiolabs.admin.twig.extension.x_editable_type_mapping', XEditableRuntime::FIELD_DESCRIPTION_MAPPING);

    $containerConfigurator->services()

        ->set('sensiolabs.admin.twig.sensiolabs_admin_extension', SensioLabsAdminExtension::class)
            ->tag('twig.extension')

        ->set('sensiolabs.admin.twig.sensiolabs_admin_runtime', SensioLabsAdminRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service('sensiolabs.admin.pool'),
            ])

        ->set('sensiolabs.admin.twig.template_registry_extension', TemplateRegistryExtension::class)
            ->tag('twig.extension')

        ->set('sensiolabs.admin.twig.template_registry_runtime', TemplateRegistryRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service('sensiolabs.admin.global_template_registry'),
                service('sensiolabs.admin.pool'),
            ])

        ->set('sensiolabs.admin.twig.security_extension', SecurityExtension::class)
            ->tag('twig.extension')

        ->set('sensiolabs.admin.twig.security_runtime', SecurityRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service('security.authorization_checker'),
            ])

        ->set('sensiolabs.admin.twig.canonicalize_extension', CanonicalizeExtension::class)
            ->tag('twig.extension')

        ->set('sensiolabs.admin.twig.canonicalize_runtime', CanonicalizeRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service('request_stack'),
            ])

        ->set('sensiolabs.admin.twig.xeditable_extension', XEditableExtension::class)
            ->tag('twig.extension')

        ->set('sensiolabs.admin.twig.xeditable_runtime', XEditableRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service('translator'),
                '%sensiolabs.admin.twig.extension.x_editable_type_mapping%',
            ])

        ->set('sensiolabs.admin.twig.render_element_extension', RenderElementExtension::class)
            ->tag('twig.extension')

        ->set('sensiolabs.admin.twig.render_element_runtime', RenderElementRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service('property_accessor'),
            ])

        ->set('sensiolabs.admin.twig.breadcrumbs_extension', BreadcrumbsExtension::class)
            ->tag('twig.extension')

        ->set('sensiolabs.admin.twig.breadcrumbs_runtime', BreadcrumbsRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service('sensiolabs.admin.breadcrumbs_builder'),
            ]);
};
