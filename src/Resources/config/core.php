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

use Psr\Container\ContainerInterface;
use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilder;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Extension\LockExtension;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\ArgumentResolver\AdminValueResolver;
use Sonata\AdminBundle\ArgumentResolver\ProxyQueryResolver;
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
use Sonata\AdminBundle\Search\SearchHandlerInterface;
use Sonata\AdminBundle\SonataConfiguration;
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Sonata\AdminBundle\Translator\BCLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\Extractor\AdminExtractor;
use Sonata\AdminBundle\Translator\FormLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\AdminBundle\Translator\NativeLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\UnderscoreLabelTranslatorStrategy;

return static function (ContainerConfigurator $containerConfigurator): void {
    /**
     * @psalm-suppress DeprecatedClass
     */
    $containerConfigurator->services()

        ->set('sonata.admin.pool', Pool::class)
            ->args([
                abstract_arg('admin service locator'),
                abstract_arg('admin service ids'),
                abstract_arg('admin service groups'),
                abstract_arg('admin service clasess'),
            ])

        ->alias(Pool::class, 'sonata.admin.pool')

        ->set('sonata.admin.configuration', SonataConfiguration::class)
            ->args([
                abstract_arg('title'),
                abstract_arg('logo'),
                abstract_arg('options'),
            ])

        ->set('sonata.admin.route_loader', AdminPoolLoader::class)
            ->tag('routing.loader')
            ->args([
                service('sonata.admin.pool'),
            ])

        ->set('sonata.admin.helper', AdminHelper::class)
            ->args([
                service('property_accessor'),
            ])

        ->set('sonata.admin.builder.filter.factory', FilterFactory::class)
            ->args([
                abstract_arg('service locator'),
            ])

        ->alias(FilterFactoryInterface::class, 'sonata.admin.builder.filter.factory')

        ->set('sonata.admin.breadcrumbs_builder', BreadcrumbsBuilder::class)
            ->args([
                param('sonata.admin.configuration.breadcrumbs'),
            ])

        ->alias(BreadcrumbsBuilderInterface::class, 'sonata.admin.breadcrumbs_builder')

        // Services used to format the label, default is sonata.admin.label.strategy.noop

        // NEXT_MAJOR: Remove this line.
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
                service('sonata.admin.pool'),
                service('sonata.admin.breadcrumbs_builder'),
            ])

        ->set('sonata.admin.audit.manager', AuditManager::class)
            ->args([
                abstract_arg('service locator'),
            ])

        ->alias(AuditManagerInterface::class, 'sonata.admin.audit.manager')

        ->set('sonata.admin.search.handler', SearchHandler::class)

        ->alias(SearchHandlerInterface::class, 'sonata.admin.search.handler')

        ->set('sonata.admin.controller.crud', CRUDController::class)
            ->public()
            ->tag('container.service_subscriber')
            ->call('setContainer', [service(ContainerInterface::class)])

        ->set('sonata.admin.event.extension', AdminEventExtension::class)
            ->tag('sonata.admin.extension', ['global' => true])
            ->args([
                service('event_dispatcher'),
            ])

        ->set('sonata.admin.lock.extension', LockExtension::class)
            ->tag('sonata.admin.extension', ['global' => true])

        ->set('sonata.admin.filter_persister.session', SessionFilterPersister::class)
            ->args([
                service('request_stack'),
            ])

        ->alias(FilterPersisterInterface::class, 'sonata.admin.filter_persister.session')

        ->set('sonata.admin.global_template_registry', TemplateRegistry::class)
            ->args([
                param('sonata.admin.configuration.templates'),
            ])

        ->set('sonata.admin.request.fetcher', AdminFetcher::class)
            ->args([
                service('sonata.admin.pool'),
            ])

        ->alias(AdminFetcherInterface::class, 'sonata.admin.request.fetcher')

        ->set('sonata.admin.argument_resolver.admin', AdminValueResolver::class)
            ->args([
                service('sonata.admin.request.fetcher'),
            ])
            ->tag('controller.argument_value_resolver')

        ->set('sonata.admin.argument_resolver.proxy_query', ProxyQueryResolver::class)
            ->tag('controller.argument_value_resolver');
};
