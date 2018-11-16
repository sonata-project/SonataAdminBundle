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

use Doctrine\Common\Annotations\AnnotationReader;
use Sonata\AdminBundle\Annotation\Admin;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class AutoRegisterCompilerPass implements CompilerPassInterface
{
    const DEFAULT_SERVICE_PREFIX = 'app.admin.';

    public function process(ContainerBuilder $container)
    {
        if (!$container->getParameter('sonata.admin.annotations')) {
            return;
        }

        $reader = $container->get('annotation_reader');

        foreach ($this->findFiles($container->getParameter('sonata.admin.annotations.directory')) as $file) {
            if (!($className = $this->getFullyQualifiedClassName($file))) {
                continue;
            }

            if (!\class_exists($className)) {
                continue;
            }

            if (!($annotation = $this->getClassAnnotation($reader, $reflection = new \ReflectionClass($className)))) {
                continue;
            }

            $definition = new Definition(
                $className,
                [$annotation->id, $className, $annotation->baseControllerName]
            );

            $definition->addTag('sonata.admin', $annotation->getTagOptions());

            $container->setDefinition(
                $serviceId = ($annotation->id ?? $this->getServiceId($file)),
                $definition
            );
        }
    }

    /**
     * @param string $directory
     *
     * @return \IteratorAggregate
     */
    private function findFiles($directory)
    {
        return Finder::create()
            ->in($directory)
            ->files()
            ->name('*.php');
    }

    /**
     * @param SplFileInfo $file
     *
     * @return string|null
     */
    private function getFullyQualifiedClassName(SplFileInfo $file)
    {
        if (!($namespace = $this->getNamespace($file->getPathname()))) {
            return null;
        }

        return $namespace.'\\'.$this->getClassName($file->getFilename());
    }

    /**
     * @param string $filePath
     *
     * @return string|null
     */
    private function getNamespace($filePath)
    {
        $namespaceLine = preg_grep('/^namespace /', file($filePath));

        if (!$namespaceLine) {
            return null;
        }

        preg_match('/namespace (.*);$/', reset($namespaceLine), $match);

        return array_pop($match);
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    private function getClassName($fileName)
    {
        return str_replace('.php', '', $fileName);
    }

    /**
     * @return Admin|null
     */
    private function getClassAnnotation(AnnotationReader $reader, \ReflectionClass $class)
    {
        return $reader->getClassAnnotation(
            $class,
            Admin::class
        );
    }

    /**
     * @param SplFileInfo $file
     *
     * @return string
     */
    private function getServiceId(SplFileInfo $file)
    {
        return self::DEFAULT_SERVICE_PREFIX.$this->getClassName($file->getFilename());
    }
}
