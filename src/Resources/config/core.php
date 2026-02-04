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

use Psr\Container\ContainerInterface;
use SensioLabs\AdminBundle\Admin\AdminHelper;
use SensioLabs\AdminBundle\Admin\BreadcrumbsBuilder;
use SensioLabs\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use SensioLabs\AdminBundle\Admin\Extension\LockExtension;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\ArgumentResolver\AdminValueResolver;
use SensioLabs\AdminBundle\ArgumentResolver\ProxyQueryResolver;
use SensioLabs\AdminBundle\Asset\LastModifiedVersionStrategy;
use SensioLabs\AdminBundle\Controller\CRUDController;
use SensioLabs\AdminBundle\Event\AdminEventExtension;
use SensioLabs\AdminBundle\Filter\FilterFactory;
use SensioLabs\AdminBundle\Filter\FilterFactoryInterface;
use SensioLabs\AdminBundle\Filter\Persister\FilterPersisterInterface;
use SensioLabs\AdminBundle\Filter\Persister\SessionFilterPersister;
use SensioLabs\AdminBundle\Model\AuditManager;
use SensioLabs\AdminBundle\Model\AuditManagerInterface;
use SensioLabs\AdminBundle\Request\AdminFetcher;
use SensioLabs\AdminBundle\Request\AdminFetcherInterface;
use SensioLabs\AdminBundle\Route\AdminPoolLoader;
use SensioLabs\AdminBundle\Search\SearchHandler;
use SensioLabs\AdminBundle\Search\SearchHandlerInterface;
use SensioLabs\AdminBundle\SensioLabsConfiguration;
use SensioLabs\AdminBundle\Templating\TemplateRegistry;
use SensioLabs\AdminBundle\Translator\Extractor\AdminExtractor;
use SensioLabs\AdminBundle\Translator\FormLabelTranslatorStrategy;
use SensioLabs\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use SensioLabs\AdminBundle\Translator\NativeLabelTranslatorStrategy;
use SensioLabs\AdminBundle\Translator\NoopLabelTranslatorStrategy;
use SensioLabs\AdminBundle\Translator\UnderscoreLabelTranslatorStrategy;
use Symfony\Component\Asset\PathPackage;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()

        ->set('sonata.admin.assets.public_dir', '/public')
        ->set('sonata.admin.assets.base_path', '/');

    $containerConfigurator->services()

        ->set('sonata.admin.assets.version_strategy', LastModifiedVersionStrategy::class)
            ->args([
                param('kernel.project_dir'),
                param('sonata.admin.assets.public_dir'),
            ])

        ->set('sonata.admin.assets.package', PathPackage::class)
            ->tag('assets.package', ['package' => 'sonata_admin'])
            ->args([
                param('sonata.admin.assets.base_path'),
                service('sonata.admin.assets.version_strategy'),
                service('assets.context'),
            ])

        ->set('sonata.admin.pool', Pool::class)
            ->args([
                abstract_arg('admin service locator'),
                abstract_arg('admin service ids'),
                abstract_arg('admin service groups'),
                abstract_arg('admin service clasess'),
            ])

        ->alias(Pool::class, 'sonata.admin.pool')

        ->set('sonata.admin.configuration', SensioLabsConfiguration::class)
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

        // @phpstan-ignore-next-line classConstant.internalClass
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

        ->set('sonata.admin.label.strategy.native', NativeLabelTranslatorStrategy::class)

        ->alias(LabelTranslatorStrategyInterface::class, 'sonata.admin.label.strategy.native')

        ->set('sonata.admin.label.strategy.noop', NoopLabelTranslatorStrategy::class)

        ->set('sonata.admin.label.strategy.underscore', UnderscoreLabelTranslatorStrategy::class)

        ->set('sonata.admin.label.strategy.form_component', FormLabelTranslatorStrategy::class)

        // @phpstan-ignore-next-line classConstant.internalClass
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
