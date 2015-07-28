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

        foreach ($container->findTaggedServiceIds('sonata.admin.extension') as $id => $tags) {
            foreach ($tags as $attributes) {
                $target = false;

                if (isset($attributes['target'])) {
                    $target = $attributes['target'];
                }

                if (isset($attributes['global']) && $attributes['global']) {
                    $universalExtensions[] = $id;
                }

                if (!$target || !$container->hasDefinition($target)) {
                    continue;
                }

                $container
                    ->getDefinition($target)
                    ->addMethodCall('addExtension', array(new Reference($id)))
                ;
            }
        }

        $extensionConfig = $container->getParameter('sonata.admin.extension.map');
        $extensionMap = $this->flattenExtensionConfiguration($extensionConfig);

        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $attributes) {
            $admin = $container->getDefinition($id);

            foreach ($universalExtensions as $extension) {
                $admin->addMethodCall('addExtension', array(new Reference($extension)));
            }

            $extensions = $this->getExtensionsForAdmin($id, $admin, $container, $extensionMap);

            foreach ($extensions as $extension) {
                if (!$container->has($extension)) {
                    throw new \InvalidArgumentException(sprintf('Unable to find extension service for id %s', $extension));
                }
                $admin->addMethodCall('addExtension', array(new Reference($extension)));
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
        $class = $classReflection = $subjectReflection = null;

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
            }
        }

        if (isset($excludes[$id])) {
            $extensions = array_diff($extensions, $excludes[$id]);
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
     * @return array
     */
    protected function flattenExtensionConfiguration(array $config)
    {
        $extensionMap = array(
            'excludes'      => array(),
            'admins'        => array(),
            'implements'    => array(),
            'extends'       => array(),
            'instanceof'    => array(),
        );

        foreach ($config as $extension => $options) {
            foreach ($options as $key => $value) {
                foreach ($value as $source) {
                    if (!isset($extensionMap[$key][$source])) {
                        $extensionMap[$key][$source] = array();
                    }
                    array_push($extensionMap[$key][$source], $extension);
                }
            }
        }

        return $extensionMap;
    }
}
