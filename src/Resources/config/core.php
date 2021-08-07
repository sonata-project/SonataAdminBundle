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

use Psr\Container\ContainerInterface;
use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilder;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Extension\LockExtension;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\ArgumentResolver\AdminValueResolver;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Event\AdminEventExtension;
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
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Sonata\AdminBundle\Translator\BCLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\Extractor\AdminExtractor;
use Sonata\AdminBundle\Translator\FormLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\AdminBundle\Translator\NativeLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\UnderscoreLabelTranslatorStrategy;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.admin.pool', Pool::class)
            ->args([
                null, // admin service locator
                [], // admin service ids
                [], // admin service groups
                [], // admin service classes
            ])

        ->alias(Pool::class, 'sonata.admin.pool')

        ->set('sonata.admin.configuration', SonataConfiguration::class)
            ->args([
                '',
                '',
                [],
            ])

        ->set('sonata.admin.route_loader', AdminPoolLoader::class)
            ->tag('routing.loader')
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
            ])

        ->set('sonata.admin.helper', AdminHelper::class)
            ->args([
                new ReferenceConfigurator('property_accessor'),
            ])

        ->set('sonata.admin.builder.filter.factory', FilterFactory::class)
            ->args([
                null, // Service locator
            ])

        ->alias(FilterFactoryInterface::class, 'sonata.admin.builder.filter.factory')

        ->set('sonata.admin.breadcrumbs_builder', BreadcrumbsBuilder::class)
            ->args([
                '%sonata.admin.configuration.breadcrumbs%',
            ])

        ->alias(BreadcrumbsBuilderInterface::class, 'sonata.admin.breadcrumbs_builder')

        // Services used to format the label, default is sonata.admin.label.strategy.noop

        ->set('sonata.admin.label.strategy.bc', BCLabelTranslatorStrategy::class)

        ->set('sonata.admin.label.strategy.native', NativeLabelTranslatorStrategy::class)

        ->alias(LabelTranslatorStrategyInterface::class, 'sonata.admin.label.strategy.native')

        ->set('sonata.admin.label.strategy.noop', NoopLabelTranslatorStrategy::class)

        ->set('sonata.admin.label.strategy.underscore', UnderscoreLabelTranslatorStrategy::class)

        ->set('sonata.admin.label.strategy.form_component', FormLabelTranslatorStrategy::class)

        ->set('sonata.admin.translation_extractor', AdminExtractor::class)
            ->tag('translation.extractor', [
                'alias' => 'sonata_admin',
            ])
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
                new ReferenceConfigurator('sonata.admin.breadcrumbs_builder'),
            ])

        ->set('sonata.admin.audit.manager', AuditManager::class)
            ->args([
                null, // Service locator
            ])

        ->alias(AuditManagerInterface::class, 'sonata.admin.audit.manager')

        ->set('sonata.admin.search.handler', SearchHandler::class)

        ->set('sonata.admin.controller.crud', CRUDController::class)
            ->public()
            ->tag('container.service_subscriber')
            ->call('setContainer', [new ReferenceConfigurator(ContainerInterface::class)])

        ->set('sonata.admin.event.extension', AdminEventExtension::class)
            ->tag('sonata.admin.extension', ['global' => true])
            ->args([
                new ReferenceConfigurator('event_dispatcher'),
            ])

        ->set('sonata.admin.lock.extension', LockExtension::class)
            ->tag('sonata.admin.extension', ['global' => true])

        ->set('sonata.admin.filter_persister.session', SessionFilterPersister::class)
            ->args([
                new ReferenceConfigurator('request_stack'),
            ])

        ->alias(FilterPersisterInterface::class, 'sonata.admin.filter_persister.session')

        ->set('sonata.admin.global_template_registry', TemplateRegistry::class)
            ->args([
                '%sonata.admin.configuration.templates%',
            ])

        ->set('sonata.admin.request.fetcher', AdminFetcher::class)
            ->args([
                new ReferenceConfigurator('sonata.admin.pool'),
            ])

        ->alias(AdminFetcherInterface::class, 'sonata.admin.request.fetcher')

        ->set('sonata.admin.argument_resolver.admin', AdminValueResolver::class)
            ->args([
                new ReferenceConfigurator('sonata.admin.request.fetcher'),
            ])
            ->tag('controller.argument_value_resolver');
};
