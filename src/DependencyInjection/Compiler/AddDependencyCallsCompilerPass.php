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

namespace SensioLabs\AdminBundle\DependencyInjection\Compiler;

use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Datagrid\Pager;
use SensioLabs\AdminBundle\DependencyInjection\Admin\TaggedAdminInterface;
use SensioLabs\AdminBundle\Templating\MutableTemplateRegistry;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\String\UnicodeString;

/**
 * Add all dependencies to the Admin class, this avoids writing too many lines
 * in the configuration files.
 *
 * @internal
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class AddDependencyCallsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('sensiolabs.admin.pool')) {
            return;
        }

        // check if translator service exist
        if (!$container->has('translator')) {
            throw new \RuntimeException('The "translator" service is not yet enabled.
                It\'s required by SonataAdmin to display all labels properly.
                To learn how to enable the translator service please visit:
                http://symfony.com/doc/current/translation.html#configuration
             ');
        }

        $parameterBag = $container->getParameterBag();
        $admins = $adminServices = $classes = [];

        $pool = $container->getDefinition('sensiolabs.admin.pool');

        foreach ($container->findTaggedServiceIds(TaggedAdminInterface::ADMIN_TAG) as $id => $tags) {
            if (\count($tags) > 1) {
                throw new \RuntimeException(\sprintf(
                    'Found multiple sensiolabs.admin tags in service %s. Tagging a service with sensiolabs.admin more than once is not supported. Consider defining multiple services with different sensiolabs.admin tag parameters if this is really needed.',
                    $id
                ));
            }

            foreach ($tags as $attributes) {
                $code = $attributes['code'] ?? $id;

                $adminServices[$code] = new Reference($id);

                $definition = $container->getDefinition($id);
                $parentDefinition = null;

                if ($definition instanceof ChildDefinition) {
                    $parentDefinition = $container->getDefinition($definition->getParent());
                }

                if (!isset($attributes['model_class'])) {
                    throw new \RuntimeException(\sprintf(
                        'The admin service "%s" must define the "model_class" attribute on its "sensiolabs.admin" tag.',
                        $id
                    ));
                }

                $definition->setMethodCalls(array_merge(
                    $this->getDefaultMethodCalls($container, $id, $attributes),
                    $definition->getMethodCalls()
                ));

                $this->fixTemplates($id, $container, $definition);

                $admins[] = $code;

                $modelClass = $attributes['model_class'];
                if (!isset($classes[$modelClass])) {
                    $classes[$modelClass] = [];
                }

                $default = (bool) (isset($attributes['default']) ? $parameterBag->resolveValue($attributes['default']) : false);
                if ($default) {
                    if (isset($classes[$modelClass][Pool::DEFAULT_ADMIN_KEY])) {
                        throw new \RuntimeException(\sprintf(
                            'The class %s has two admins %s and %s with the "default" attribute set to true. Only one is allowed.',
                            $modelClass,
                            $classes[$modelClass][Pool::DEFAULT_ADMIN_KEY],
                            $code
                        ));
                    }

                    $classes[$modelClass][Pool::DEFAULT_ADMIN_KEY] = $code;
                } else {
                    $classes[$modelClass][] = $code;
                }

            }
        }

        $pool->replaceArgument(0, ServiceLocatorTagPass::register($container, $adminServices));
        $pool->replaceArgument(1, $admins);
        $pool->replaceArgument(2, $classes);
    }

    /**
     * Apply the default values required by the AdminInterface to the Admin service definition.
     *
     * @param array<string, mixed> $attributes
     *
     * @return array<array{string, array<mixed>}>
     */
    private function getDefaultMethodCalls(ContainerBuilder $container, string $serviceId, array $attributes = []): array
    {
        $definition = $container->getDefinition($serviceId);
        $methodCalls = [];

        $definition->setShared(false);

        $managerType = $attributes['manager_type'] ?? null;
        if (!\is_string($managerType)) {
            throw new InvalidArgumentException(\sprintf('Missing tag information "manager_type" on service "%s".', $serviceId));
        }

        $overwriteAdminConfiguration = $container->getParameter('sensiolabs.admin.configuration.default_admin_services');
        \assert(\is_array($overwriteAdminConfiguration));

        $defaultAddServices = [
            'model_manager' => \sprintf('sensiolabs.admin.manager.%s', $managerType),
            'data_source' => \sprintf('sensiolabs.admin.data_source.%s', $managerType),
            'field_description_factory' => \sprintf('sensiolabs.admin.field_description_factory.%s', $managerType),
            'form_contractor' => \sprintf('sensiolabs.admin.builder.%s_form', $managerType),
            'show_builder' => \sprintf('sensiolabs.admin.builder.%s_show', $managerType),
            'list_builder' => \sprintf('sensiolabs.admin.builder.%s_list', $managerType),
            'datagrid_builder' => \sprintf('sensiolabs.admin.builder.%s_datagrid', $managerType),
            'translator' => 'translator',
            'configuration_pool' => 'sensiolabs.admin.pool',
            'route_generator' => 'sensiolabs.admin.route.default_generator',
            'security_handler' => 'sensiolabs.admin.security.handler',
            'menu_factory' => 'knp_menu.factory',
            'route_builder' => 'sensiolabs.admin.route.path_info',
            'label_translator_strategy' => 'sensiolabs.admin.label.strategy.native',
        ];

        $methodCalls[] = ['setManagerType', [$managerType]];

        foreach ($defaultAddServices as $attr => $addServiceId) {
            $method = $this->generateSetterMethodName($attr);
            if ($definition->hasMethodCall($method)) {
                continue;
            }

            $args = [new Reference($attributes[$attr] ?? $overwriteAdminConfiguration[$attr] ?? $addServiceId)];
            if ('translator' === $attr) {
                $args[] = false;
            }

            $methodCalls[] = [$method, $args];
        }

        $defaultController = $container->getParameter('sensiolabs.admin.configuration.default_controller');
        \assert(\is_string($defaultController));

        $modelClass = $attributes['model_class'];
        $methodCalls[] = ['setModelClass', [$modelClass]];

        $controller = $attributes['controller'] ?? $defaultController;
        $methodCalls[] = ['setBaseControllerName', [$controller]];

        $code = $attributes['code'] ?? $serviceId;
        $methodCalls[] = ['setCode', [$code]];

        $pagerType = $overwriteAdminConfiguration['pager_type'] ?? $attributes['pager_type'] ?? Pager::TYPE_DEFAULT;
        $methodCalls[] = ['setPagerType', [$pagerType]];

        $label = $attributes['label'] ?? null;
        $methodCalls[] = ['setLabel', [$label]];

        $defaultTranslationDomain = $container->getParameter('sensiolabs.admin.configuration.default_translation_domain');
        \assert(\is_string($defaultTranslationDomain));

        $translationDomain = $attributes['translation_domain'] ?? $defaultTranslationDomain;
        $methodCalls[] = ['setTranslationDomain', [$translationDomain]];

        $persistFilters = $attributes['persist_filters']
            ?? $container->getParameter('sensiolabs.admin.configuration.filters.persist');
        \assert(\is_bool($persistFilters));
        $filtersPersister = $attributes['filter_persister']
            ?? $container->getParameter('sensiolabs.admin.configuration.filters.persister');
        \assert(\is_string($filtersPersister));

        // configure filters persistence, if configured to
        if ($persistFilters) {
            $methodCalls[] = ['setFilterPersister', [new Reference($filtersPersister)]];
        }

        $showMosaicButton = $attributes['show_mosaic_button']
            ?? $container->getParameter('sensiolabs.admin.configuration.show.mosaic.button');
        \assert(\is_bool($showMosaicButton));

        $listModes = TaggedAdminInterface::DEFAULT_LIST_MODES;
        if (!$showMosaicButton) {
            unset($listModes['mosaic']);
        }
        $methodCalls[] = ['setListModes', [$listModes]];

        if ($container->hasParameter('sensiolabs.admin.configuration.security.information') && !$definition->hasMethodCall('setSecurityInformation')) {
            $methodCalls[] = ['setSecurityInformation', ['%sensiolabs.admin.configuration.security.information%']];
        }

        $defaultTemplates = $container->getParameter('sensiolabs.admin.configuration.templates');
        \assert(\is_array($defaultTemplates));

        if (!$definition->hasMethodCall('setFormTheme')) {
            $formTheme = $defaultTemplates['form_theme'] ?? [];
            $methodCalls[] = ['setFormTheme', [$formTheme]];
        }
        if (!$definition->hasMethodCall('setFilterTheme')) {
            $filterTheme = $defaultTemplates['filter_theme'] ?? [];
            $methodCalls[] = ['setFilterTheme', [$filterTheme]];
        }

        return $methodCalls;
    }

    private function fixTemplates(
        string $serviceId,
        ContainerBuilder $container,
        Definition $definition,
    ): void {
        $definedTemplates = $container->getParameter('sensiolabs.admin.configuration.templates');
        \assert(\is_array($definedTemplates));

        $methods = [];
        $pos = 0;
        foreach ($definition->getMethodCalls() as [$method, $args]) {
            if ('setTemplates' === $method) {
                $definedTemplates = array_merge($definedTemplates, $args[0]);

                continue;
            }

            if ('setTemplate' === $method) {
                $definedTemplates[$args[0]] = $args[1];

                continue;
            }

            // set template for simple pager if it is not already overwritten
            if ('setPagerType' === $method
                && Pager::TYPE_SIMPLE === $args[0]
                && (
                    !isset($definedTemplates['pager_results'])
                    || '@SensioLabsAdmin/Pager/results.html.twig' === $definedTemplates['pager_results']
                )
            ) {
                $definedTemplates['pager_results'] = '@SensioLabsAdmin/Pager/simple_pager_results.html.twig';
            }

            $methods[$pos] = [$method, $args];
            ++$pos;
        }

        $definition->setMethodCalls($methods);

        $templateRegistryId = \sprintf('%s.template_registry', $serviceId);
        $templateRegistryDefinition = $container
            ->register($templateRegistryId, MutableTemplateRegistry::class)
            ->addTag('sensiolabs.admin.template_registry')
            ->setPublic(true); // Temporary fix until we can support service locators

        if ($container->getParameter('sensiolabs.admin.configuration.templates') !== $definedTemplates) {
            $templateRegistryDefinition->addArgument($definedTemplates);
        } else {
            $templateRegistryDefinition->addArgument('%sensiolabs.admin.configuration.templates%');
        }

        $definition->addMethodCall('setTemplateRegistry', [new Reference($templateRegistryId)]);
    }

    private function generateSetterMethodName(string $key): string
    {
        return 'set'.(new UnicodeString($key))->camel()->title(true)->toString();
    }
}
