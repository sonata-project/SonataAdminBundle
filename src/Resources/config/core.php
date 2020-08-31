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
use Sonata\AdminBundle\Event\AdminEventExtension;
use Sonata\AdminBundle\Export\Exporter;
use Sonata\AdminBundle\Filter\FilterFactory;
use Sonata\AdminBundle\Filter\FilterFactoryInterface;
use Sonata\AdminBundle\Filter\Persister\FilterPersisterInterface;
use Sonata\AdminBundle\Filter\Persister\SessionFilterPersister;
use Sonata\AdminBundle\Model\AuditManager;
use Sonata\AdminBundle\Model\AuditManagerInterface;
use Sonata\AdminBundle\Route\AdminPoolLoader;
use Sonata\AdminBundle\Search\SearchHandler;
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
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.admin.pool', Pool::class)
            ->public()
            ->args([
                new ReferenceConfigurator('service_container'),
                '',
                '',
                [],
                new ReferenceConfigurator('property_accessor'),
            ])
            ->call('setTemplateRegistry', [
                new ReferenceConfigurator('sonata.admin.global_template_registry'),
            ])

        ->alias(Pool::class, 'sonata.admin.pool')

        ->set('sonata.admin.route_loader', AdminPoolLoader::class)
            ->public()
            ->tag('routing.loader')
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                [],
                new ReferenceConfigurator('service_container'),
            ])

        ->alias(AdminPoolLoader::class, 'sonata.admin.route_loader')

        ->set('sonata.admin.helper', AdminHelper::class)
            ->public()
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
            ])

        ->alias(AdminHelper::class, 'sonata.admin.helper')

        ->set('sonata.admin.builder.filter.factory', FilterFactory::class)
            ->public()
            ->args([
                new ReferenceConfigurator('service_container'),
                [],
            ])

        ->alias(FilterFactory::class, 'sonata.admin.builder.filter.factory')

        ->alias(FilterFactoryInterface::class, 'sonata.admin.builder.filter.factory')

        ->set('sonata.admin.breadcrumbs_builder', BreadcrumbsBuilder::class)
            ->public()
            ->args([
                '%sonata.admin.configuration.breadcrumbs%',
            ])

        ->alias(BreadcrumbsBuilder::class, 'sonata.admin.breadcrumbs_builder')

        ->alias(BreadcrumbsBuilderInterface::class, 'sonata.admin.breadcrumbs_builder')

        // Services used to format the label, default is sonata.admin.label.strategy.noop

        ->set('sonata.admin.label.strategy.bc', BCLabelTranslatorStrategy::class)
            ->public()

        ->alias(BCLabelTranslatorStrategy::class, 'sonata.admin.label.strategy.bc')

        ->set('sonata.admin.label.strategy.native', NativeLabelTranslatorStrategy::class)
            ->public()

        ->alias(NativeLabelTranslatorStrategy::class, 'sonata.admin.label.strategy.native')

        ->alias(LabelTranslatorStrategyInterface::class, 'sonata.admin.label.strategy.native')

        ->set('sonata.admin.label.strategy.noop', NoopLabelTranslatorStrategy::class)
            ->public()

        ->alias(NoopLabelTranslatorStrategy::class, 'sonata.admin.label.strategy.noop')

        ->set('sonata.admin.label.strategy.underscore', UnderscoreLabelTranslatorStrategy::class)
            ->public()

        ->alias(UnderscoreLabelTranslatorStrategy::class, 'sonata.admin.label.strategy.underscore')

        ->set('sonata.admin.label.strategy.form_component', FormLabelTranslatorStrategy::class)
            ->public()

        ->alias(FormLabelTranslatorStrategy::class, 'sonata.admin.label.strategy.form_component')

        // NEXT_MAJOR: Remove this service.
        ->set('sonata.admin.translator.extractor.jms_translator_bundle', DeprecatedAdminExtractor::class)
            ->public()
            ->tag('jms_translation.extractor', [
                'alias' => 'sonata_admin',
            ])
            ->deprecate(sprintf(
                'The service "%%service_id%%" is deprecated since sonata-project/admin-bundle 3.72 and will be removed in 4.0. Use "%s" service instead.',
                Sonata\AdminBundle\Translator\Extractor\AdminExtractor::class
            ))
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                (new ReferenceConfigurator('logger'))->nullOnInvalid(),
            ])
            ->call('setBreadcrumbsBuilder', [
                new ReferenceConfigurator('sonata.admin.breadcrumbs_builder'),
            ])

        ->set(AdminExtractor::class)
            ->tag('translation.extractor', [
                'alias' => 'sonata_admin',
            ])
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                new ReferenceConfigurator('sonata.admin.breadcrumbs_builder'),
            ])

        // NEXT_MAJOR: Remove this service.
        ->set('sonata.admin.controller.admin', HelperController::class)
            ->public()
            ->deprecate('The controller service "%service_id%" is deprecated in favor of several action services since sonata-project/admin-bundle 3.38.0 and will be removed in 4.0.')
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.admin.pool'),
                new ReferenceConfigurator('sonata.admin.helper'),
                new ReferenceConfigurator('validator'),
            ])

        ->set('sonata.admin.audit.manager', AuditManager::class)
            ->public()
            ->args([
                new ReferenceConfigurator('service_container'),
            ])

        ->alias(AuditManager::class, 'sonata.admin.audit.manager')

        ->alias(AuditManagerInterface::class, 'sonata.admin.audit.manager')

        // NEXT_MAJOR: Remove this service.
        ->set('sonata.admin.exporter', Exporter::class)
            ->public()
            ->deprecate('The service "%service_id%" is deprecated since sonata-project/admin-bundle 3.14.0 and will be removed in 4.0. Use "sonata.exporter.exporter" service instead.')

        ->set('sonata.admin.search.handler', SearchHandler::class)
            ->public()
            ->args([
                '%sonata.admin.configuration.global_search.case_sensitive%',
            ])

        ->alias(SearchHandler::class, 'sonata.admin.search.handler')

        ->set('sonata.admin.event.extension', AdminEventExtension::class)
            ->public()
            ->tag('sonata.admin.extension', ['global' => true])
            ->args([
                new ReferenceConfigurator('event_dispatcher'),
            ])

        ->alias(AdminEventExtension::class, 'sonata.admin.event.extension')

        ->set('sonata.admin.lock.extension', LockExtension::class)
            ->public()
            ->tag('sonata.admin.extension', ['global' => true])

        ->set('sonata.admin.twig.global', GlobalVariables::class)
            ->public()
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                '%sonata.admin.configuration.mosaic_background%',
            ])

        ->alias(GlobalVariables::class, 'sonata.admin.twig.global')

        ->set('sonata.admin.filter_persister.session', SessionFilterPersister::class)
            ->args([
                new ReferenceConfigurator('session'),
            ])

        ->alias(SessionFilterPersister::class, 'sonata.admin.filter_persister.session')

        ->alias(FilterPersisterInterface::class, 'sonata.admin.filter_persister.session')

        ->set('sonata.admin.global_template_registry', TemplateRegistry::class)
            ->public()
            ->args([
                '%sonata.admin.configuration.templates%',
            ])

        ->alias(TemplateRegistry::class, 'sonata.admin.global_template_registry')

        ->alias(MutableTemplateRegistryInterface::class, 'sonata.admin.global_template_registry')
    ;
};
