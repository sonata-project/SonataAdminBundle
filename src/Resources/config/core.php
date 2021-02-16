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

use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilder;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Extension\LockExtension;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\HelperController;
use Sonata\AdminBundle\DependencyInjection\Compiler\AliasDeprecatedPublicServicesCompilerPass;
use Sonata\AdminBundle\Event\AdminEventExtension;
use Sonata\AdminBundle\Export\Exporter;
use Sonata\AdminBundle\Filter\FilterFactory;
use Sonata\AdminBundle\Filter\FilterFactoryInterface;
use Sonata\AdminBundle\Filter\Persister\FilterPersisterInterface;
use Sonata\AdminBundle\Filter\Persister\SessionFilterPersister;
use Sonata\AdminBundle\Model\AuditManager;
use Sonata\AdminBundle\Model\AuditManagerInterface;
use Sonata\AdminBundle\Request\AdminFetcher;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Sonata\AdminBundle\Route\AdminPoolLoader;
use Sonata\AdminBundle\Search\SearchHandler;
use Sonata\AdminBundle\SonataConfiguration;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Sonata\AdminBundle\Translator\BCLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\Extractor\AdminExtractor;
use Sonata\AdminBundle\Translator\Extractor\JMSTranslatorBundle\AdminExtractor as DeprecatedAdminExtractor;
use Sonata\AdminBundle\Translator\FormLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\AdminBundle\Translator\NativeLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\UnderscoreLabelTranslatorStrategy;
use Sonata\AdminBundle\Twig\GlobalVariables;
use Sonata\AdminBundle\Util\BCDeprecationParameters;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.admin.pool', Pool::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            // NEXT_MAJOR: Remove alias.
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->args([
                new ReferenceConfigurator('service_container'),
                [], // admin service ids
                [], // admin service groups
                [], // admin service classes
            ])
            // NEXT_MAJOR: Remove this call.
            ->call('setTemplateRegistry', [
                new ReferenceConfigurator('sonata.admin.global_template_registry'),
                'sonata_deprecation_mute',
            ])

        ->alias(Pool::class, 'sonata.admin.pool')
        // NEXT_MAJOR: Remove alias.
        ->alias('sonata.admin.pool.do-not-use', 'sonata.admin.pool')
        ->public()

        ->set('sonata.admin.configuration', SonataConfiguration::class)
            ->args([
                '',
                '',
                [],
            ])

        ->alias(SonataConfiguration::class, 'sonata.admin.configuration')
            // NEXT_MAJOR: Remove this alias.
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))

        ->set('sonata.admin.route_loader', AdminPoolLoader::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->tag('routing.loader')
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
            ])

        // NEXT_MAJOR: Remove this alias.
        ->alias(AdminPoolLoader::class, 'sonata.admin.route_loader')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.95 and will be removed in 4.0.',
                '3.95'
            ))

        ->set('sonata.admin.helper', AdminHelper::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->args([
                new ReferenceConfigurator('property_accessor'),
                // NEXT_MAJOR: Remove next line.
                new ReferenceConfigurator('sonata.admin.pool'),
            ])

        ->alias(AdminHelper::class, 'sonata.admin.helper')
            // NEXT_MAJOR: Remove this alias.
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))

        ->set('sonata.admin.builder.filter.factory', FilterFactory::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->args([
                new ReferenceConfigurator('service_container'),
                [],
            ])

        // NEXT_MAJOR: Remove this alias.
        ->alias(FilterFactory::class, 'sonata.admin.builder.filter.factory')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))

        ->alias(FilterFactoryInterface::class, 'sonata.admin.builder.filter.factory')

        ->set('sonata.admin.breadcrumbs_builder', BreadcrumbsBuilder::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->args([
                '%sonata.admin.configuration.breadcrumbs%',
            ])

        // NEXT_MAJOR: Remove this alias.
        ->alias(BreadcrumbsBuilder::class, 'sonata.admin.breadcrumbs_builder')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))
        // NEXT_MAJOR: Remove this alias.
        ->alias('sonata.admin.breadcrumbs_builder.do-not-use', 'sonata.admin.breadcrumbs_builder')
        ->public()

        ->alias(BreadcrumbsBuilderInterface::class, 'sonata.admin.breadcrumbs_builder')

        // Services used to format the label, default is sonata.admin.label.strategy.noop

        ->set('sonata.admin.label.strategy.bc', BCLabelTranslatorStrategy::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])

        // NEXT_MAJOR: Remove this alias.
        ->alias(BCLabelTranslatorStrategy::class, 'sonata.admin.label.strategy.bc')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))

        ->set('sonata.admin.label.strategy.native', NativeLabelTranslatorStrategy::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])

        // NEXT_MAJOR: Remove this alias.
        ->alias(NativeLabelTranslatorStrategy::class, 'sonata.admin.label.strategy.native')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))

        ->alias(LabelTranslatorStrategyInterface::class, 'sonata.admin.label.strategy.native')

        ->set('sonata.admin.label.strategy.noop', NoopLabelTranslatorStrategy::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])

        // NEXT_MAJOR: Remove this alias.
        ->alias(NoopLabelTranslatorStrategy::class, 'sonata.admin.label.strategy.noop')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))

        ->set('sonata.admin.label.strategy.underscore', UnderscoreLabelTranslatorStrategy::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])

        // NEXT_MAJOR: Remove this alias.
        ->alias(UnderscoreLabelTranslatorStrategy::class, 'sonata.admin.label.strategy.underscore')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))

        ->set('sonata.admin.label.strategy.form_component', FormLabelTranslatorStrategy::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])

        // NEXT_MAJOR: Remove this alias.
        ->alias(FormLabelTranslatorStrategy::class, 'sonata.admin.label.strategy.form_component')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))

        // NEXT_MAJOR: Remove this service.
        ->set('sonata.admin.translator.extractor.jms_translator_bundle', DeprecatedAdminExtractor::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->tag('jms_translation.extractor', [
                'alias' => 'sonata_admin',
            ])
            ->deprecate(...BCDeprecationParameters::forConfig(sprintf(
                'The service "%%service_id%%" is deprecated since sonata-project/admin-bundle 3.72 and will be removed in 4.0. Use "%s" service instead.',
                Sonata\AdminBundle\Translator\Extractor\AdminExtractor::class
            ), '3.72'))
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                (new ReferenceConfigurator('logger'))->nullOnInvalid(),
            ])
            ->call('setBreadcrumbsBuilder', [
                new ReferenceConfigurator('sonata.admin.breadcrumbs_builder'),
            ])

        ->set('sonata.admin.translation_extractor', AdminExtractor::class)
            ->tag('translation.extractor', [
                'alias' => 'sonata_admin',
            ])
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                new ReferenceConfigurator('sonata.admin.breadcrumbs_builder'),
            ])

        // NEXT_MAJOR: Remove this alias.
        ->alias(AdminExtractor::class, 'sonata.admin.translation_extractor')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))

        // NEXT_MAJOR: Remove this service.
        ->set('sonata.admin.controller.admin', HelperController::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The controller service "%service_id%" is deprecated in favor of several action services since sonata-project/admin-bundle 3.38.0 and will be removed in 4.0.',
                '3.38.0'
            ))
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.admin.pool'),
                new ReferenceConfigurator('sonata.admin.helper'),
                new ReferenceConfigurator('validator'),
                new ReferenceConfigurator('sonata.admin.form.data_transformer_resolver'),
            ])

        ->set('sonata.admin.audit.manager', AuditManager::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->args([
                // NEXT_MAJOR: Remove next line.
                new ReferenceConfigurator('service_container'),
                null, // Service locator
            ])

        // NEXT_MAJOR: Remove this alias.
        ->alias(AuditManager::class, 'sonata.admin.audit.manager')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))
        // NEXT_MAJOR: Remove this alias.
        ->alias('sonata.admin.audit.manager.do-not-use', 'sonata.admin.audit.manager')
        ->public()

        ->alias(AuditManagerInterface::class, 'sonata.admin.audit.manager')

        // NEXT_MAJOR: Remove this service.
        ->set('sonata.admin.exporter', Exporter::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The service "%service_id%" is deprecated since sonata-project/admin-bundle 3.14.0 and will be removed in 4.0. Use "sonata.exporter.exporter" service instead.',
                '3.14.0'
            ))

        ->set('sonata.admin.search.handler', SearchHandler::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->args([
                '%sonata.admin.configuration.global_search.case_sensitive%',
            ])

        // NEXT_MAJOR: Remove this alias.
        ->alias(SearchHandler::class, 'sonata.admin.search.handler')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))

        ->set('sonata.admin.event.extension', AdminEventExtension::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->tag('sonata.admin.extension', ['global' => true])
            ->args([
                new ReferenceConfigurator('event_dispatcher'),
            ])

        // NEXT_MAJOR: Remove this alias.
        ->alias(AdminEventExtension::class, 'sonata.admin.event.extension')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))

        ->set('sonata.admin.lock.extension', LockExtension::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->tag('sonata.admin.extension', ['global' => true])

        // NEXT_MAJOR: Remove this service definition and alias.
        ->set('sonata.admin.twig.global', GlobalVariables::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                '%sonata.admin.configuration.mosaic_background%',
            ])

        ->alias(GlobalVariables::class, 'sonata.admin.twig.global')

        ->set('sonata.admin.filter_persister.session', SessionFilterPersister::class)
            ->args([
                new ReferenceConfigurator('session'),
            ])

        // NEXT_MAJOR: Remove this alias.
        ->alias(SessionFilterPersister::class, 'sonata.admin.filter_persister.session')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))

        ->alias(FilterPersisterInterface::class, 'sonata.admin.filter_persister.session')

        ->set('sonata.admin.global_template_registry', TemplateRegistry::class)
            // NEXT_MAJOR: Remove public and sonata.container.private tag.
            ->public()
            ->tag(AliasDeprecatedPublicServicesCompilerPass::PRIVATE_TAG_NAME, ['version' => '3.98'])
            ->args([
                '%sonata.admin.configuration.templates%',
            ])

        // NEXT_MAJOR: Remove this alias.
        ->alias(TemplateRegistry::class, 'sonata.admin.global_template_registry')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%alias_id%" alias is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ))

        // NEXT_MAJOR: remove this alias, global template registry SHOULD NOT be mutable
        ->alias(MutableTemplateRegistryInterface::class, 'sonata.admin.global_template_registry')

        ->set('sonata.admin.request.fetcher', AdminFetcher::class)
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
            ])

        ->alias(AdminFetcherInterface::class, 'sonata.admin.request.fetcher');
};
