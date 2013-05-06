<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ExtensionCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('sonata.admin.extension') as $id => $attributes) {

            $target = false;
            if (isset($attributes[0]['target'])) {
                $target = $attributes[0]['target'];
            }

            if (!$target || !$container->hasDefinition($target)) {
                continue;
            }

            $container->getDefinition($target)
                ->addMethodCall('addExtension', array(new Reference($id)));
        }

        $extensionConfig = $container->getParameter('sonata.admin.extension.map');
        $extensionMap = $this->flattenExtensionConfiguration($extensionConfig);

        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $attributes) {
            $admin = $container->getDefinition($id);
            $extensions = $this->getExtensionsForAdmin($id, $container, $extensionMap);

            foreach ($extensions as $extension) {

                echo "adding extension to admin\n";
                echo "admin: $id - extension $extension\n\n";

                $admin->addMethodCall('addExtension', array(new Reference($extension)));
            }
        }
    }

    /**
     * @param string $id
     * @param ContainerBuilder $container
     * @param array $extensionMap
     * @return array
     */
    protected function getExtensionsForAdmin($id, ContainerBuilder $container, array $extensionMap)
    {
        $extensions = array();
        $excludes = $extensionMap['excludes'];
        unset($extensionMap['excludes']);
        $admin = $container->getDefinition($id);

        foreach ($extensionMap as $type => $subjects) {
            foreach ($subjects as $subject => $extensionList) {
                if('admins' == $type && $id == $subject){
                    $extensions = array_merge($extensions, $extensionList);
                }

                $class = $this->getManagedClass($admin, $container);
                if('instanceof' == $type && $class instanceof $subject){
                    $extensions = array_merge($extensions, $extensionList);
                }

                if('implements' == $type){
                    $classReflection = new \ReflectionClass($class);
                    if($classReflection->implementsInterface($subject)){
                        $extensions = array_merge($extensions, $extensionList);
                    }
                }

                if('extends' == $type){
                    $subjReflection = new \ReflectionClass($subject);
                    if($subjReflection->isSubclassOf($class)){
                        $extensions = array_merge($extensions, $extensionList);
                    }
                }
            }
        }

        if(isset($excludes[$id])){
            $extensions = array_diff($extensions, $excludes);
        }
        return $extensions;
    }

    /**
     * Resolves the class argument of the admin to an actual class (in case of %parameter%)
     *
     * @param Definition $admin
     * @param ContainerBuilder $container
     * @return string
     */
    protected function getManagedClass(Definition $admin, ContainerBuilder $container)
    {
        return $container->getParameterBag()->resolveValue($admin->getArgument(1));
    }

    /**
     * @param array $config
     * @return array
     */
    protected function flattenExtensionConfiguration(array $config)
    {
        $extensionMap = array();
        foreach ($config as $extension => $options) {
            foreach ($options as $key => $value) {
                foreach ($value as $source) {
                    if(!isset($extensionMap[$key][$source])){
                        $extensionMap[$key][$source] = array();
                    }
                    array_push($extensionMap[$key][$source], $extension);
                }
            }
        }
        return $extensionMap;
    }
}
