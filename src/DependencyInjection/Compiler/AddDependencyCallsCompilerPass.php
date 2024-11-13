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

namespace Sonata\AdminBundle\DependencyInjection\Compiler;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\DependencyInjection\Admin\TaggedAdminInterface;
use Sonata\AdminBundle\Templating\MutableTemplateRegistry;
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
        if (!$container->has('sonata.admin.pool')) {
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
        $groupDefaults = $admins = $adminServices = $classes = [];

        $pool = $container->getDefinition('sonata.admin.pool');
        $defaultController = $container->getParameter('sonata.admin.configuration.default_controller');
        \assert(\is_string($defaultController));

        $defaultGroup = $container->getParameter('sonata.admin.configuration.default_group');
        \assert(\is_string($defaultGroup));
        // NEXT_MAJOR: Remove this variable.
        $defaultLabelCatalogue = $container->getParameter('sonata.admin.configuration.default_label_catalogue');
        \assert(\is_string($defaultLabelCatalogue));
        // NEXT_MAJOR: Remove the fallback.
        $defaultTranslationDomain = $container->getParameter('sonata.admin.configuration.default_translation_domain') ?? $defaultLabelCatalogue;
        \assert(\is_string($defaultTranslationDomain));
        $defaultIcon = $container->getParameter('sonata.admin.configuration.default_icon');
        \assert(\is_string($defaultIcon));

        $defaultValues = [
            'group' => $defaultGroup,
            'translation_domain' => $defaultTranslationDomain,
            'label_catalogue' => $defaultLabelCatalogue, // NEXT_MAJOR: Remove this line.
            'icon' => $defaultIcon,
        ];

        foreach ($container->findTaggedServiceIds(TaggedAdminInterface::ADMIN_TAG) as $id => $tags) {
            if (\count($tags) > 1) {
                // NEXT_MAJOR: Remove deprecation error with the exception below.
                @trigger_error(\sprintf(
                    'Found multiple sonata.admin tags in service %s. Tagging a service with sonata.admin more
                    than once is not supported, and will result in a RuntimeException in 5.0.',
                    $id
                ), \E_USER_DEPRECATED);

                // NEXT_MAJOR: Enable this exception.
                // throw new \RuntimeException(sprintf(
                //    'Found multiple sonata.admin tags in service %s. Tagging a service with sonata.admin more
                //    than once is not supported. Consider defining multiple services with different sonata.admin tag
                //    parameters if this is really needed.',
                //    $id
                // ));
            }

            foreach ($tags as $attributes) {
                $code = $attributes['code'] ?? $id;

                $adminServices[$code] = new Reference($id);

                $definition = $container->getDefinition($id);
                $parentDefinition = null;

                if ($definition instanceof ChildDefinition) {
                    $parentDefinition = $container->getDefinition($definition->getParent());
                }

                // NEXT_MAJOR: Remove the following code.
                if (!isset($attributes['model_class'])) {
                    // Since the model_class attribute will be mandatory we're assuming that
                    // - if it's used the new syntax is used, so we don't need to replace the arguments
                    // - if it's not used, the old syntax is used, so we still need to

                    $this->replaceDefaultArguments([
                        0 => $code,
                        2 => $defaultController,
                    ], $definition, $parentDefinition);
                }

                $definition->setMethodCalls(array_merge(
                    $this->getDefaultMethodCalls($container, $id, $attributes),
                    $definition->getMethodCalls()
                ));

                $this->fixTemplates($id, $container, $definition);

                $arguments = null !== $parentDefinition ?
                    array_merge($parentDefinition->getArguments(), $definition->getArguments()) :
                    $definition->getArguments();

                $admins[] = $code;

                // NEXT_MAJOR: Remove the fallback to $arguments[1].
                $modelClass = $attributes['model_class'] ?? $arguments[1];
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

                $showInDashboard = (bool) (isset($attributes['show_in_dashboard']) ? $parameterBag->resolveValue($attributes['show_in_dashboard']) : true);
                if (!$showInDashboard) {
                    continue;
                }

                $resolvedGroupName = isset($attributes['group']) ?
                    $parameterBag->resolveValue($attributes['group']) :
                    $defaultValues['group'];
                \assert(\is_string($resolvedGroupName));

                // NEXT_MAJOR: Remove this deprecation and the $labelCatalogue variable.
                if (isset($attributes['label_catalogue'])) {
                    @trigger_error(
                        'The "label_catalogue" attribute is deprecated'
                        .' since sonata-project/admin-bundle 4.9 and will throw an error in 5.0.',
                        \E_USER_DEPRECATED
                    );
                }
                $labelCatalogue = $attributes['label_catalogue'] ?? $defaultValues['label_catalogue'];

                // NEXT_MAJOR: Remove the `label_catalogue` fallback.
                $groupTranslationDomain = $attributes['translation_domain'] ?? $attributes['label_catalogue'] ?? $defaultValues['translation_domain'];
                $icon = $attributes['icon'] ?? $defaultValues['icon'];
                $onTop = $attributes['on_top'] ?? false;
                $keepOpen = $attributes['keep_open'] ?? false;

                if (!isset($groupDefaults[$resolvedGroupName])) {
                    $groupDefaults[$resolvedGroupName] = [
                        'label' => $resolvedGroupName,
                        'translation_domain' => $groupTranslationDomain,
                        'label_catalogue' => $labelCatalogue, // NEXT_MAJOR: Remove this line.
                        'icon' => $icon,
                        'items' => [],
                        'roles' => [],
                        'on_top' => false,
                        'keep_open' => false,
                    ];
                }

                $groupDefaults[$resolvedGroupName]['priority'] = max($groupDefaults[$resolvedGroupName]['priority'] ?? 0, $attributes['priority'] ?? 0);
                $groupDefaults[$resolvedGroupName]['items'][] = [
                    'admin' => $code,
                    'label' => $attributes['label'] ?? '', // NEXT_MAJOR: Remove this line.
                    'route' => '', // NEXT_MAJOR: Remove this line.
                    'route_params' => [],
                    'route_absolute' => false,
                    'priority' => $attributes['priority'] ?? 0,
                ];

                if (isset($groupDefaults[$resolvedGroupName]['on_top']) && true === $groupDefaults[$resolvedGroupName]['on_top']
                    || true === $onTop && (\count($groupDefaults[$resolvedGroupName]['items']) > 1)) {
                    throw new \RuntimeException('You can\'t use "on_top" option with multiple same name groups.');
                }
                $groupDefaults[$resolvedGroupName]['on_top'] = $onTop;

                $groupDefaults[$resolvedGroupName]['keep_open'] = $keepOpen;
            }
        }

        $dashboardGroupsSettings = $container->getParameter('sonata.admin.configuration.dashboard_groups');
        \assert(\is_array($dashboardGroupsSettings));
        $sortAdmins = $container->getParameter('sonata.admin.configuration.sort_admins');
        \assert(\is_bool($sortAdmins));

        $sortAdminsByPriority = true;

        if ([] !== $dashboardGroupsSettings) {
            $groups = $dashboardGroupsSettings;

            foreach ($dashboardGroupsSettings as $groupName => $group) {
                $resolvedGroupName = $parameterBag->resolveValue($groupName);
                \assert(\is_string($resolvedGroupName));

                if (!isset($groupDefaults[$resolvedGroupName])) {
                    $groupDefaults[$resolvedGroupName] = [
                        'items' => [],
                        'label' => $resolvedGroupName,
                        'translation_domain' => $defaultValues['translation_domain'],
                        'label_catalogue' => $defaultValues['label_catalogue'], // NEXT_MAJOR: Remove this line.
                        'icon' => $defaultValues['icon'],
                        'roles' => [],
                        'on_top' => false,
                        'keep_open' => false,
                    ];
                }

                if (!isset($group['items']) || [] === $group['items']) {
                    $groups[$resolvedGroupName]['items'] = $groupDefaults[$resolvedGroupName]['items'];
                } else {
                    $sortAdminsByPriority = false;
                }

                if (!isset($group['label']) || '' === $group['label']) {
                    $groups[$resolvedGroupName]['label'] = $groupDefaults[$resolvedGroupName]['label'];
                }

                if (!isset($group['translation_domain']) || '' === $group['translation_domain']) {
                    $groups[$resolvedGroupName]['translation_domain'] = $groupDefaults[$resolvedGroupName]['translation_domain'];
                }

                // NEXT_MAJOR: Remove the whole if/else.
                if (!isset($group['label_catalogue']) || '' === $group['label_catalogue']) {
                    $groups[$resolvedGroupName]['label_catalogue'] = $groupDefaults[$resolvedGroupName]['label_catalogue'];
                } elseif (!isset($group['translation_domain']) || '' === $group['translation_domain']) {
                    // BC-layer if label_catalogue is provided.
                    $groups[$resolvedGroupName]['translation_domain'] = $group['label_catalogue'];
                }

                if (!isset($group['icon']) || '' === $group['icon']) {
                    $groups[$resolvedGroupName]['icon'] = $groupDefaults[$resolvedGroupName]['icon'];
                }

                if (!isset($group['roles']) || [] === $group['roles']) {
                    $groups[$resolvedGroupName]['roles'] = $groupDefaults[$resolvedGroupName]['roles'];
                }

                if (
                    isset($groups[$resolvedGroupName]['on_top'])
                    && true === ($group['on_top'] ?? false)
                    && \count($groups[$resolvedGroupName]['items']) > 1
                ) {
                    throw new \RuntimeException('You can\'t use "on_top" option with multiple same name groups.');
                }
                if (!isset($group['on_top'])) {
                    $groups[$resolvedGroupName]['on_top'] = $groupDefaults[$resolvedGroupName]['on_top'];
                }

                if (!isset($group['keep_open'])) {
                    $groups[$resolvedGroupName]['keep_open'] = $groupDefaults[$resolvedGroupName]['keep_open'];
                }
            }
        } elseif ($sortAdmins) {
            $groups = $groupDefaults;

            $elementSort = static function (array &$element): void {
                usort(
                    $element['items'],
                    static function (array $a, array $b): int {
                        $labelA = isset($a['label']) && '' !== $a['label'] ? $a['label'] : $a['admin'];
                        $labelB = isset($b['label']) && '' !== $b['label'] ? $b['label'] : $b['admin'];

                        return $labelA <=> $labelB;
                    }
                );
            };

            /*
             * 1) sort the groups by their index
             * 2) sort the elements within each group by label/admin
             */
            ksort($groups);
            array_walk($groups, $elementSort);

            $sortAdminsByPriority = false;
        } else {
            $groups = $groupDefaults;

            uasort($groups, static fn (array $a, array $b): int => $b['priority'] <=> $a['priority']);
        }

        if ($sortAdminsByPriority) {
            $elementSort = static function (array &$element): void {
                usort(
                    $element['items'],
                    static fn (array $a, array $b): int => ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0)
                );
            };

            array_walk($groups, $elementSort);
        }

        $pool->replaceArgument(0, ServiceLocatorTagPass::register($container, $adminServices));
        $pool->replaceArgument(1, $admins);
        $pool->replaceArgument(2, $groups);
        $pool->replaceArgument(3, $classes);
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

        $overwriteAdminConfiguration = $container->getParameter('sonata.admin.configuration.default_admin_services');
        \assert(\is_array($overwriteAdminConfiguration));

        $defaultAddServices = [
            'model_manager' => \sprintf('sonata.admin.manager.%s', $managerType),
            'data_source' => \sprintf('sonata.admin.data_source.%s', $managerType),
            'field_description_factory' => \sprintf('sonata.admin.field_description_factory.%s', $managerType),
            'form_contractor' => \sprintf('sonata.admin.builder.%s_form', $managerType),
            'show_builder' => \sprintf('sonata.admin.builder.%s_show', $managerType),
            'list_builder' => \sprintf('sonata.admin.builder.%s_list', $managerType),
            'datagrid_builder' => \sprintf('sonata.admin.builder.%s_datagrid', $managerType),
            'translator' => 'translator',
            'configuration_pool' => 'sonata.admin.pool',
            'route_generator' => 'sonata.admin.route.default_generator',
            'security_handler' => 'sonata.admin.security.handler',
            'menu_factory' => 'knp_menu.factory',
            'route_builder' => 'sonata.admin.route.path_info',
            'label_translator_strategy' => 'sonata.admin.label.strategy.native',
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

        $defaultController = $container->getParameter('sonata.admin.configuration.default_controller');
        \assert(\is_string($defaultController));

        $modelClass = $attributes['model_class'] ?? null;
        if (null === $modelClass) {
            @trigger_error(
                'Not setting the "model_class" attribute is deprecated'
                .' since sonata-project/admin-bundle 4.8 and will throw an error in 5.0.',
                \E_USER_DEPRECATED
            );

        // NEXT_MAJOR: Uncomment the exception instead of the deprecation.
        // throw new InvalidArgumentException(sprintf('Missing tag information "model_class" on service "%s".', $serviceId));
        } else {
            $methodCalls[] = ['setModelClass', [$modelClass]];

            $controller = $attributes['controller'] ?? $defaultController;
            $methodCalls[] = ['setBaseControllerName', [$controller]];

            $code = $attributes['code'] ?? $serviceId;
            $methodCalls[] = ['setCode', [$code]];
        }

        $pagerType = $overwriteAdminConfiguration['pager_type'] ?? $attributes['pager_type'] ?? Pager::TYPE_DEFAULT;
        $methodCalls[] = ['setPagerType', [$pagerType]];

        $label = $attributes['label'] ?? null;
        $methodCalls[] = ['setLabel', [$label]];

        // NEXT_MAJOR: Remove the fallback.
        $defaultTranslationDomain = $container->getParameter('sonata.admin.configuration.default_translation_domain') ?? 'messages';
        \assert(\is_string($defaultTranslationDomain));

        $translationDomain = $attributes['translation_domain'] ?? $defaultTranslationDomain;
        $methodCalls[] = ['setTranslationDomain', [$translationDomain]];

        $persistFilters = $attributes['persist_filters']
            ?? $container->getParameter('sonata.admin.configuration.filters.persist');
        \assert(\is_bool($persistFilters));
        $filtersPersister = $attributes['filter_persister']
            ?? $container->getParameter('sonata.admin.configuration.filters.persister');
        \assert(\is_string($filtersPersister));

        // configure filters persistence, if configured to
        if ($persistFilters) {
            $methodCalls[] = ['setFilterPersister', [new Reference($filtersPersister)]];
        }

        $showMosaicButton = $attributes['show_mosaic_button']
            ?? $container->getParameter('sonata.admin.configuration.show.mosaic.button');
        \assert(\is_bool($showMosaicButton));

        $listModes = TaggedAdminInterface::DEFAULT_LIST_MODES;
        if (!$showMosaicButton) {
            unset($listModes['mosaic']);
        }
        $methodCalls[] = ['setListModes', [$listModes]];

        if ($container->hasParameter('sonata.admin.configuration.security.information') && !$definition->hasMethodCall('setSecurityInformation')) {
            $methodCalls[] = ['setSecurityInformation', ['%sonata.admin.configuration.security.information%']];
        }

        $defaultTemplates = $container->getParameter('sonata.admin.configuration.templates');
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
        $definedTemplates = $container->getParameter('sonata.admin.configuration.templates');
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
                    || '@SonataAdmin/Pager/results.html.twig' === $definedTemplates['pager_results']
                )
            ) {
                $definedTemplates['pager_results'] = '@SonataAdmin/Pager/simple_pager_results.html.twig';
            }

            $methods[$pos] = [$method, $args];
            ++$pos;
        }

        $definition->setMethodCalls($methods);

        $templateRegistryId = \sprintf('%s.template_registry', $serviceId);
        $templateRegistryDefinition = $container
            ->register($templateRegistryId, MutableTemplateRegistry::class)
            ->addTag('sonata.admin.template_registry')
            ->setPublic(true); // Temporary fix until we can support service locators

        if ($container->getParameter('sonata.admin.configuration.templates') !== $definedTemplates) {
            $templateRegistryDefinition->addArgument($definedTemplates);
        } else {
            $templateRegistryDefinition->addArgument('%sonata.admin.configuration.templates%');
        }

        $definition->addMethodCall('setTemplateRegistry', [new Reference($templateRegistryId)]);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * Replace the empty arguments required by the Admin service definition.
     *
     * @param string[] $defaultArguments
     */
    private function replaceDefaultArguments(
        array $defaultArguments,
        Definition $definition,
        ?Definition $parentDefinition = null,
    ): void {
        $arguments = $definition->getArguments();
        $parentArguments = null !== $parentDefinition ? $parentDefinition->getArguments() : [];

        foreach ($defaultArguments as $index => $value) {
            $declaredInParent = null !== $parentDefinition && \array_key_exists($index, $parentArguments);
            $argumentValue = $declaredInParent ? $parentArguments[$index] : $arguments[$index] ?? null;

            if (null === $argumentValue || '' === $argumentValue) {
                $arguments[$declaredInParent ? \sprintf('index_%s', $index) : $index] = $value;
            }
        }

        $definition->setArguments($arguments);
    }

    private function generateSetterMethodName(string $key): string
    {
        return 'set'.(new UnicodeString($key))->camel()->title(true)->toString();
    }
}
