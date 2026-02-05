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

        ->set('sensiolabs.admin.assets.public_dir', '/public')
        ->set('sensiolabs.admin.assets.base_path', '/');

    $containerConfigurator->services()

        ->set('sensiolabs.admin.assets.version_strategy', LastModifiedVersionStrategy::class)
            ->args([
                param('kernel.project_dir'),
                param('sensiolabs.admin.assets.public_dir'),
            ])

        ->set('sensiolabs.admin.assets.package', PathPackage::class)
            ->tag('assets.package', ['package' => 'sensiolabs_admin'])
            ->args([
                param('sensiolabs.admin.assets.base_path'),
                service('sensiolabs.admin.assets.version_strategy'),
                service('assets.context'),
            ])

        ->set('sensiolabs.admin.pool', Pool::class)
            ->args([
                abstract_arg('admin service locator'),
                abstract_arg('admin service ids'),
                abstract_arg('admin service classes'),
            ])

        ->alias(Pool::class, 'sensiolabs.admin.pool')

        // Backward compatibility aliases for external Sonata bundles
        ->alias('sonata.admin.pool', 'sensiolabs.admin.pool')
            ->public()
        ->alias('Sonata\AdminBundle\Admin\Pool', 'sensiolabs.admin.pool')

        ->set('sensiolabs.admin.configuration', SensioLabsConfiguration::class)
            ->args([
                abstract_arg('title'),
                abstract_arg('logo'),
                abstract_arg('options'),
            ])

        ->set('sensiolabs.admin.route_loader', AdminPoolLoader::class)
            ->tag('routing.loader')
            ->args([
                service('sensiolabs.admin.pool'),
            ])

        // @phpstan-ignore-next-line classConstant.internalClass
        ->set('sensiolabs.admin.helper', AdminHelper::class)
            ->args([
                service('property_accessor'),
            ])

        ->set('sensiolabs.admin.builder.filter.factory', FilterFactory::class)
            ->args([
                abstract_arg('service locator'),
            ])

        ->alias(FilterFactoryInterface::class, 'sensiolabs.admin.builder.filter.factory')

        ->set('sensiolabs.admin.breadcrumbs_builder', BreadcrumbsBuilder::class)
            ->args([
                param('sensiolabs.admin.configuration.breadcrumbs'),
            ])

        ->alias(BreadcrumbsBuilderInterface::class, 'sensiolabs.admin.breadcrumbs_builder')

        // Services used to format the label, default is sensiolabs.admin.label.strategy.noop

        ->set('sensiolabs.admin.label.strategy.native', NativeLabelTranslatorStrategy::class)

        ->alias(LabelTranslatorStrategyInterface::class, 'sensiolabs.admin.label.strategy.native')

        ->set('sensiolabs.admin.label.strategy.noop', NoopLabelTranslatorStrategy::class)

        ->set('sensiolabs.admin.label.strategy.underscore', UnderscoreLabelTranslatorStrategy::class)

        ->set('sensiolabs.admin.label.strategy.form_component', FormLabelTranslatorStrategy::class)

        // @phpstan-ignore-next-line classConstant.internalClass
        ->set('sensiolabs.admin.translation_extractor', AdminExtractor::class)
            ->tag('translation.extractor', [
                'alias' => 'sensiolabs_admin',
            ])
            ->args([
                service('sensiolabs.admin.pool'),
                service('sensiolabs.admin.breadcrumbs_builder'),
            ])

        ->set('sensiolabs.admin.audit.manager', AuditManager::class)
            ->args([
                abstract_arg('service locator'),
            ])

        ->alias(AuditManagerInterface::class, 'sensiolabs.admin.audit.manager')

        ->set('sensiolabs.admin.controller.crud', CRUDController::class)
            ->public()
            ->tag('container.service_subscriber')
            ->call('setContainer', [service(ContainerInterface::class)])

        ->set('sensiolabs.admin.event.extension', AdminEventExtension::class)
            ->tag('sensiolabs.admin.extension', ['global' => true])
            ->args([
                service('event_dispatcher'),
            ])

        ->set('sensiolabs.admin.lock.extension', LockExtension::class)
            ->tag('sensiolabs.admin.extension', ['global' => true])

        ->set('sensiolabs.admin.filter_persister.session', SessionFilterPersister::class)
            ->args([
                service('request_stack'),
            ])

        ->alias(FilterPersisterInterface::class, 'sensiolabs.admin.filter_persister.session')

        ->set('sensiolabs.admin.global_template_registry', TemplateRegistry::class)
            ->args([
                param('sensiolabs.admin.configuration.templates'),
            ])

        ->set('sensiolabs.admin.request.fetcher', AdminFetcher::class)
            ->args([
                service('sensiolabs.admin.pool'),
            ])

        ->alias(AdminFetcherInterface::class, 'sensiolabs.admin.request.fetcher')

        ->set('sensiolabs.admin.argument_resolver.admin', AdminValueResolver::class)
            ->args([
                service('sensiolabs.admin.request.fetcher'),
            ])
            ->tag('controller.argument_value_resolver')

        ->set('sensiolabs.admin.argument_resolver.proxy_query', ProxyQueryResolver::class)
            ->tag('controller.argument_value_resolver');
};
