<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ExtensionCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $universalExtensions = array();
        $targets = array();

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

        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $attributes) {
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
                    throw new \InvalidArgumentException(
                        sprintf('Unable to find extension service for id %s', $extension)
                    );
                }

                $this->addExtension($targets, $id, $extension, $attributes);
            }
        }

        foreach ($targets as $target => $extensions) {
            $extensions = iterator_to_array($extensions);
            krsort($extensions);
            $admin = $container->getDefinition($target);

            foreach (array_values($extensions) as $extension) {
                $admin->addMethodCall('addExtension', array($extension));
            }
        }
    }

    /**
     * @param string           $id
     * @param Definition       $admin
     * @param ContainerBuilder $container
     * @param array            $extensionMap
     *
     * @return array
     */
    protected function getExtensionsForAdmin($id, Definition $admin, ContainerBuilder $container, array $extensionMap)
    {
        $extensions = array();
        $classReflection = $subjectReflection = null;

        $excludes = $extensionMap['excludes'];
        unset($extensionMap['excludes']);

        foreach ($extensionMap as $type => $subjects) {
            foreach ($subjects as $subject => $extensionList) {
                if ('admins' == $type) {
                    if ($id == $subject) {
                        $extensions = array_merge($extensions, $extensionList);
                    }
                } else {
                    $class = $this->getManagedClass($admin, $container);
                    if (!class_exists($class)) {
                        continue;
                    }
                    $classReflection = new \ReflectionClass($class);
                    $subjectReflection = new \ReflectionClass($subject);
                }

                if ('instanceof' == $type) {
                    if ($subjectReflection->getName() == $classReflection->getName() || $classReflection->isSubclassOf($subject)) {
                        $extensions = array_merge($extensions, $extensionList);
                    }
                }

                if ('implements' == $type) {
                    if ($classReflection->implementsInterface($subject)) {
                        $extensions = array_merge($extensions, $extensionList);
                    }
                }

                if ('extends' == $type) {
                    if ($classReflection->isSubclassOf($subject)) {
                        $extensions = array_merge($extensions, $extensionList);
                    }
                }

                if ('uses' == $type) {
                    if ($this->hasTrait($classReflection, $subject)) {
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
     * Resolves the class argument of the admin to an actual class (in case of %parameter%).
     *
     * @param Definition       $admin
     * @param ContainerBuilder $container
     *
     * @return string
     */
    protected function getManagedClass(Definition $admin, ContainerBuilder $container)
    {
        return $container->getParameterBag()->resolveValue($admin->getArgument(1));
    }

    /**
     * @param array $config
     *
     * @return array An array with the following structure.
     *
     * array(
     *     'excludes'   => array('<admin_id>'  => array('<extension_id>' => array('priority' => <int>))),
     *     'admins'     => array('<admin_id>'  => array('<extension_id>' => array('priority' => <int>))),
     *     'implements' => array('<interface>' => array('<extension_id>' => array('priority' => <int>))),
     *     'extends'    => array('<class>'     => array('<extension_id>' => array('priority' => <int>))),
     *     'instanceof' => array('<class>'     => array('<extension_id>' => array('priority' => <int>))),
     *     'uses'       => array('<trait>'     => array('<extension_id>' => array('priority' => <int>))),
     * )
     */
    protected function flattenExtensionConfiguration(array $config)
    {
        $extensionMap = array(
            'excludes' => array(),
            'admins' => array(),
            'implements' => array(),
            'extends' => array(),
            'instanceof' => array(),
            'uses' => array(),
        );

        foreach ($config as $extension => $options) {
            $optionsMap = array_intersect_key($options, $extensionMap);

            foreach ($optionsMap as $key => $value) {
                foreach ($value as $source) {
                    if (!isset($extensionMap[$key][$source])) {
                        $extensionMap[$key][$source] = array();
                    }
                    $extensionMap[$key][$source][$extension]['priority'] = $options['priority'];
                }
            }
        }

        return $extensionMap;
    }

    /**
     * @param \ReflectionClass $class
     * @param                  $traitName
     *
     * @return bool
     */
    protected function hasTrait(\ReflectionClass $class, $traitName)
    {
        if (in_array($traitName, $class->getTraitNames())) {
            return true;
        }

        if (!$parentClass = $class->getParentClass()) {
            return false;
        }

        return $this->hasTrait($parentClass, $traitName);
    }

    /**
     * Add extension configuration to the targets array.
     *
     * @param array  $targets
     * @param string $target
     * @param string $extension
     * @param array  $attributes
     */
    private function addExtension(array &$targets, $target, $extension, array $attributes)
    {
        if (!isset($targets[$target])) {
            $targets[$target] = new \SplPriorityQueue();
        }

        $priority = isset($attributes['priority']) ? $attributes['priority'] : 0;
        $targets[$target]->insert(new Reference($extension), $priority);
    }
}
