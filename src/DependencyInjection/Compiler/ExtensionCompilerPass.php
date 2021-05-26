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
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
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
            foreach ($tags as $attributes) {
                $target = false;

                if (isset($attributes['target'])) {
                    $target = $attributes['target'];
                }

                if (isset($attributes['global']) && $attributes['global']) {
                    $universalExtensions[$id] = $attributes;
                }

                if (!$target || !$container->hasDefinition($target)) {
                    continue;
                }

                $this->addExtension($targets, $target, $id, $attributes);
            }
        }

        $extensionConfig = $container->getParameter('sonata.admin.extension.map');
        $extensionMap = $this->flattenExtensionConfiguration($extensionConfig);

        foreach ($container->findTaggedServiceIds(TaggedAdminInterface::ADMIN_TAG) as $id => $attributes) {
            $admin = $container->getDefinition($id);

            if (!isset($targets[$id])) {
                $targets[$id] = new \SplPriorityQueue();
            }

            foreach ($universalExtensions as $extension => $extensionAttributes) {
                $this->addExtension($targets, $id, $extension, $extensionAttributes);
            }

            $extensions = $this->getExtensionsForAdmin($id, $admin, $container, $extensionMap);

            foreach ($extensions as $extension => $attributes) {
                if (!$container->has($extension)) {
                    throw new \InvalidArgumentException(sprintf(
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
     * @param array<string, array<string, array<string, array<string, mixed>>>> $extensionMap
     *
     * @return array<string, array<string, mixed>>
     */
    private function getExtensionsForAdmin(string $id, Definition $admin, ContainerBuilder $container, array $extensionMap): array
    {
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

                $class = $this->getManagedClass($admin, $container);

                if (!class_exists($class)) {
                    continue;
                }

                if ($this->isSubtypeOf($type, $subject, $class)) {
                    $extensions = array_merge($extensions, $extensionList);
                }
            }
        }

        if (isset($excludes[$id])) {
            $extensions = array_diff_key($extensions, $excludes[$id]);
        }

        return $extensions;
    }

    /**
     * Resolves the class argument of the admin to an actual class (in case of %parameter%).
     *
     * @phpstan-return class-string
     */
    private function getManagedClass(Definition $admin, ContainerBuilder $container): string
    {
        $argument = $admin->getArgument(1);
        $class = $container->getParameterBag()->resolveValue($argument);

        if (null === $class) {
            throw new \DomainException(sprintf('The admin "%s" does not have a valid manager.', $admin->getClass()));
        }

        if (!\is_string($class)) {
            throw new \TypeError(sprintf(
                'Argument "%s" for admin class "%s" must be of type string, %s given.',
                $argument,
                $admin->getClass(),
                \is_object($class) ? \get_class($class) : \gettype($class)
            ));
        }

        return $class;
    }

    /**
     * @param array<string, array<string, array<string, string>|int|bool>> $config
     *
     * @return array<string, array<string, array<string, array<string, int>>>> an array with the following structure
     *
     * @phpstan-param array<string, array{
     *     global: bool,
     *     excludes: array<string, string>,
     *     admins: array<string, string>,
     *     implements: array<class-string, string>,
     *     extends: array<class-string, string>,
     *     instanceof: array<class-string, string>,
     *     uses: array<class-string, string>,
     *     priority: int,
     * }> $config
     * @phpstan-return array{
     *     global: array<string, array<string, array{priority: int}>>,
     *     excludes: array<string, array<string, array{priority: int}>>,
     *     admins: array<string, array<string, array{priority: int}>>,
     *     implements: array<string, array<class-string, array{priority: int}>>,
     *     extends: array<string, array<class-string, array{priority: int}>>,
     *     instanceof: array<string, array<class-string, array{priority: int}>>,
     *     uses: array<string, array<class-string, array{priority: int}>>,
     * }
     */
    private function flattenExtensionConfiguration(array $config): array
    {
        $extensionMap = [
            'global' => [],
            'excludes' => [],
            'admins' => [],
            'implements' => [],
            'extends' => [],
            'instanceof' => [],
            'uses' => [],
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
             * } $optionsMap
             */
            $optionsMap = array_intersect_key($options, $extensionMap);

            foreach ($optionsMap as $key => $value) {
                foreach ($value as $source) {
                    if (!isset($extensionMap[$key][$source])) {
                        $extensionMap[$key][$source] = [];
                    }
                    $extensionMap[$key][$source][$extension]['priority'] = $options['priority'];
                }
            }
        }

        return $extensionMap;
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @phpstan-param class-string $traitName
     */
    private function hasTrait(\ReflectionClass $class, string $traitName): bool
    {
        if (\in_array($traitName, $class->getTraitNames(), true)) {
            return true;
        }

        if (!$parentClass = $class->getParentClass()) {
            return false;
        }

        return $this->hasTrait($parentClass, $traitName);
    }

    /**
     * @phpstan-param class-string $class
     * @phpstan-param class-string $subject
     */
    private function isSubtypeOf(string $type, string $subject, string $class): bool
    {
        $classReflection = new \ReflectionClass($class);

        switch ($type) {
            case 'global':
                return true;
            case 'instanceof':
                $subjectReflection = new \ReflectionClass($subject);

                return $classReflection->isSubclassOf($subject) || $subjectReflection->getName() === $classReflection->getName();
            case 'implements':
                return $classReflection->implementsInterface($subject);
            case 'extends':
                return $classReflection->isSubclassOf($subject);
            case 'uses':
                return $this->hasTrait($classReflection, $subject);
        }

        return false;
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
        array $attributes
    ): void {
        if (!isset($targets[$target])) {
            $targets[$target] = new \SplPriorityQueue();
        }

        $priority = $attributes['priority'] ?? 0;
        $targets[$target]->insert(new Reference($extension), $priority);
    }
}
