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

use Sonata\AdminBundle\DependencyInjection\Admin\TaggedAdminInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 *
 * @phpstan-type ExtensionMap = array<string, array{
 *     global: bool,
 *     excludes: array<string, string>,
 *     admins: array<string, string>,
 *     implements: array<class-string, string>,
 *     extends: array<class-string, string>,
 *     instanceof: array<class-string, string>,
 *     uses: array<class-string, string>,
 *     admin_implements: array<class-string, string>,
 *     admin_extends: array<class-string, string>,
 *     admin_instanceof: array<class-string, string>,
 *     admin_uses: array<class-string, string>,
 *     priority: int,
 * }>
 * @phpstan-type FlattenExtensionMap = array{
 *     global: array<string, array<string, array{priority: int}>>,
 *     excludes: array<string, array<string, array{priority: int}>>,
 *     admins: array<string, array<string, array{priority: int}>>,
 *     implements: array<string, array<class-string, array{priority: int}>>,
 *     extends: array<string, array<class-string, array{priority: int}>>,
 *     instanceof: array<string, array<class-string, array{priority: int}>>,
 *     uses: array<string, array<class-string, array{priority: int}>>,
 *     admin_implements: array<string, array<class-string, array{priority: int}>>,
 *     admin_extends: array<string, array<class-string, array{priority: int}>>,
 *     admin_instanceof: array<string, array<class-string, array{priority: int}>>,
 *     admin_uses: array<string, array<class-string, array{priority: int}>>,
 * }
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class ExtensionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $universalExtensions = [];
        $targets = [];

        foreach ($container->findTaggedServiceIds('sonata.admin.extension') as $id => $tags) {
            $adminExtension = $container->getDefinition($id);

            // Trim possible parameter delimiters ("%") from the class name.
            $adminExtensionClass = trim($adminExtension->getClass() ?? '', '%');
            if (!class_exists($adminExtensionClass, false) && $container->hasParameter($adminExtensionClass)) {
                $adminExtensionClass = $container->getParameter($adminExtensionClass);
                \assert(\is_string($adminExtensionClass));
            }
            \assert(class_exists($adminExtensionClass));

            foreach ($tags as $attributes) {
                $target = false;

                if (isset($attributes['target'])) {
                    $target = $attributes['target'];
                    unset($attributes['target']);
                }

                if (isset($attributes['global'])) {
                    if ($attributes['global']) {
                        $attributes['global'] = $adminExtensionClass;
                    } else {
                        unset($attributes['global']);
                    }
                }
                $universalExtensions[$id][] = $attributes;

                if (false === $target || !$container->hasDefinition($target)) {
                    continue;
                }

                $this->addExtension($targets, $target, $id, $attributes);
            }
        }

        /**
         * @phpstan-var ExtensionMap $extensionConfig
         */
        $extensionConfig = $container->getParameter('sonata.admin.extension.map');
        $extensionMap = $this->flattenExtensionConfiguration($extensionConfig);

        foreach ($container->findTaggedServiceIds(TaggedAdminInterface::ADMIN_TAG) as $id => $tags) {
            $admin = $container->getDefinition($id);

            // Trim possible parameter delimiters ("%") from the class name.
            $adminClass = trim($admin->getClass() ?? '', '%');
            if (!class_exists($adminClass, false) && $container->hasParameter($adminClass)) {
                $adminClass = $container->getParameter($adminClass);
                \assert(\is_string($adminClass));
            }
            \assert(class_exists($adminClass));

            if (!isset($targets[$id])) {
                $targets[$id] = new \SplPriorityQueue();
            }

            // NEXT_MAJOR: Remove this line.
            $defaultModelClass = $admin->getArguments()[1] ?? null;
            foreach ($tags as $attributes) {
                // NEXT_MAJOR: Remove the fallback to $defaultModelClass and use null instead.
                $modelClass = $attributes['model_class'] ?? $defaultModelClass;
                if (null === $modelClass) {
                    throw new InvalidArgumentException(\sprintf('Missing tag attribute "model_class" on service "%s".', $id));
                }

                $class = $container->getParameterBag()->resolveValue($modelClass);
                if (!\is_string($class)) {
                    throw new \TypeError(\sprintf(
                        'Tag attribute "model_class" for service "%s" must be of type string, %s given.',
                        $id,
                        get_debug_type($class)
                    ));
                }

                if (!class_exists($class)) {
                    continue;
                }

                foreach ($universalExtensions as $extension => $extensionsAttributes) {
                    foreach ($extensionsAttributes as $extensionAttributes) {
                        if (isset($extensionAttributes['excludes'][$id])) {
                            continue;
                        }

                        foreach ($extensionAttributes as $type => $subject) {
                            if ($this->shouldApplyExtension($type, $subject, $class, $adminClass)) {
                                $this->addExtension($targets, $id, $extension, $extensionAttributes);
                                break;
                            }
                        }
                    }
                }
            }

            $extensions = $this->getExtensionsForAdmin($id, $tags, $admin, $container, $extensionMap);

            foreach ($extensions as $extension => $attributes) {
                if (!$container->has($extension)) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Unable to find extension service for id %s',
                        $extension
                    ));
                }

                $this->addExtension($targets, $id, $extension, $attributes);
            }
        }

        foreach ($targets as $target => $extensions) {
            $extensions = iterator_to_array($extensions);
            krsort($extensions);
            $admin = $container->getDefinition($target);

            foreach (array_values($extensions) as $extension) {
                $admin->addMethodCall('addExtension', [$extension]);
            }
        }
    }

    /**
     * @param array<string, mixed>                                              $tags
     * @param array<string, array<string, array<string, array<string, mixed>>>> $extensionMap
     *
     * @return array<string, array<string, mixed>>
     *
     * @phpstan-param FlattenExtensionMap $extensionMap
     */
    private function getExtensionsForAdmin(string $id, array $tags, Definition $admin, ContainerBuilder $container, array $extensionMap): array
    {
        // Trim possible parameter delimiters ("%") from the class name.
        $adminClass = trim($admin->getClass() ?? '', '%');
        if (!class_exists($adminClass, false) && $container->hasParameter($adminClass)) {
            $adminClass = $container->getParameter($adminClass);
            \assert(\is_string($adminClass));
        }
        \assert(class_exists($adminClass));

        $extensions = [];

        $excludes = $extensionMap['excludes'];
        unset($extensionMap['excludes']);

        foreach ($extensionMap as $type => $subjects) {
            foreach ($subjects as $subject => $extensionList) {
                if ('admins' === $type) {
                    if ($id === $subject) {
                        $extensions = array_merge($extensions, $extensionList);
                    }

                    continue;
                }

                // NEXT_MAJOR: Remove this line.
                $defaultModelClass = $admin->getArguments()[1] ?? null;
                foreach ($tags as $attributes) {
                    // NEXT_MAJOR: Remove the fallback to $defaultModelClass and use null instead.
                    $modelClass = $attributes['model_class'] ?? $defaultModelClass;
                    if (null === $modelClass) {
                        throw new InvalidArgumentException(\sprintf('Missing tag attribute "model_class" on service "%s".', $id));
                    }

                    $class = $container->getParameterBag()->resolveValue($modelClass);
                    if (!\is_string($class)) {
                        throw new \TypeError(\sprintf(
                            'Tag attribute "model_class" for service "%s" must be of type string, %s given.',
                            $id,
                            get_debug_type($class)
                        ));
                    }

                    if (!class_exists($class)) {
                        continue;
                    }

                    if ($this->shouldApplyExtension($type, $subject, $class, $adminClass)) {
                        $extensions = array_merge($extensions, $extensionList);
                    }
                }
            }
        }

        if (isset($excludes[$id])) {
            $extensions = array_diff_key($extensions, $excludes[$id]);
        }

        return $extensions;
    }

    /**
     * @param array<string, array<string, array<string, string>|int|bool>> $config
     *
     * @return array<string, array<string, array<string, array<string, int>>>> an array with the following structure
     *
     * @phpstan-param ExtensionMap $config
     * @phpstan-return FlattenExtensionMap
     */
    private function flattenExtensionConfiguration(array $config): array
    {
        /** @phpstan-var FlattenExtensionMap $extensionMap */
        $extensionMap = [
            'global' => [],
            'excludes' => [],
            'admins' => [],
            'implements' => [],
            'extends' => [],
            'instanceof' => [],
            'uses' => [],
            'admin_implements' => [],
            'admin_extends' => [],
            'admin_instanceof' => [],
            'admin_uses' => [],
        ];

        foreach ($config as $extension => $options) {
            if (true === $options['global']) {
                $options['global'] = [$extension];
            } else {
                $options['global'] = [];
            }

            /**
             * @phpstan-var array{
             *     global: array<string, string>,
             *     excludes: array<string, string>,
             *     admins: array<string, string>,
             *     implements: array<class-string, string>,
             *     extends: array<class-string, string>,
             *     instanceof: array<class-string, string>,
             *     uses: array<class-string, string>,
             *     admin_implements: array<class-string, string>,
             *     admin_extends: array<class-string, string>,
             *     admin_instanceof: array<class-string, string>,
             *     admin_uses: array<class-string, string>,
             * } $optionsMap
             */
            $optionsMap = array_intersect_key($options, $extensionMap);

            foreach ($extensionMap as $key => &$value) {
                foreach ($optionsMap[$key] as $source) {
                    $value[$source][$extension]['priority'] = $options['priority'];
                }
            }
        }

        return $extensionMap;
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private function hasTrait(\ReflectionClass $class, string $traitName): bool
    {
        if (\in_array($traitName, $class->getTraitNames(), true)) {
            return true;
        }

        $parentClass = $class->getParentClass();
        if (false === $parentClass) {
            return false;
        }

        return $this->hasTrait($parentClass, $traitName);
    }

    /**
     * @phpstan-param class-string $class
     * @phpstan-param class-string $adminClass
     */
    private function shouldApplyExtension(string $type, mixed $subject, string $class, string $adminClass): bool
    {
        $classReflection = new \ReflectionClass($class);
        $adminClassReflection = new \ReflectionClass($adminClass);

        switch ($type) {
            case 'global':
                return true;
            case 'instanceof':
                if (!\is_string($subject) || !class_exists($subject)) {
                    return false;
                }

                $subjectReflection = new \ReflectionClass($subject);

                return $classReflection->isSubclassOf($subject) || $subjectReflection->getName() === $classReflection->getName();
            case 'implements':
                return \is_string($subject) && interface_exists($subject) && $classReflection->implementsInterface($subject);
            case 'extends':
                return \is_string($subject) && class_exists($subject) && $classReflection->isSubclassOf($subject);
            case 'uses':
                return \is_string($subject) && trait_exists($subject) && $this->hasTrait($classReflection, $subject);
            case 'admin_instanceof':
                if (!\is_string($subject) || !class_exists($subject)) {
                    return false;
                }

                $subjectReflection = new \ReflectionClass($subject);

                return $adminClassReflection->isSubclassOf($subject) || $subjectReflection->getName() === $adminClassReflection->getName();
            case 'admin_implements':
                return \is_string($subject) && interface_exists($subject) && $adminClassReflection->implementsInterface($subject);
            case 'admin_extends':
                return \is_string($subject) && class_exists($subject) && $adminClassReflection->isSubclassOf($subject);
            case 'admin_uses':
                return \is_string($subject) && trait_exists($subject) && $this->hasTrait($adminClassReflection, $subject);
            default:
                return false;
        }
    }

    /**
     * Add extension configuration to the targets array.
     *
     * @param array<string, \SplPriorityQueue<int, Reference>> $targets
     * @param array<string, mixed>                             $attributes
     */
    private function addExtension(
        array &$targets,
        string $target,
        string $extension,
        array $attributes,
    ): void {
        if (!isset($targets[$target])) {
            /** @phpstan-var \SplPriorityQueue<int, Reference> $queue */
            $queue = new \SplPriorityQueue();
            $targets[$target] = $queue;
        }

        $priority = $attributes['priority'] ?? 0;
        $targets[$target]->insert(new Reference($extension), $priority);
    }
}
