<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Simon Cosandey <simon.cosandey@simseo.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;

/**
 * Generates an controller class based on a Doctrine entity.
 *
 * @author Simon Cosandey <simon.cosandey@simseo.ch>
 */
class ControllerGenerator extends Generator
{
    private $filesystem;
    private $skeletonDir;
    private $className;
    private $classPath;

    public function __construct(Filesystem $filesystem, $skeletonDir)
    {
        $this->filesystem = $filesystem;
        $this->skeletonDir = $skeletonDir;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getClassPath()
    {
        return $this->classPath;
    }

    /**
     * Generates the controller class if it does not exist.
     *
     * @param BundleInterface   $bundle   The bundle in which to create the class
     * @param string            $entity   The entity relative class name
     * @param ClassMetadataInfo $metadata The entity metadata class
     */
    public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata)
    {
        $parts       = explode('\\', $entity);
        $entityClass = array_pop($parts);

        $this->className = $entityClass.'AdminController';
        $dirPath         = $bundle->getPath().'/Controller';
        $this->classPath = $dirPath.'/'.str_replace('\\', '/', $entity).'AdminController.php';

        if (file_exists($this->classPath)) {
            throw new \RuntimeException(sprintf('Unable to generate the %s controller class as it already exists under the %s file', $this->className, $this->classPath));
        }

        if (count($metadata->identifier) > 1) {
            throw new \RuntimeException('The controller generator does not support entity classes with multiple primary keys.');
        }


        $this->renderFile($this->skeletonDir, 'AdminController.php', $this->classPath, array(
            'dir'              => $this->skeletonDir,
            'namespace'        => $bundle->getNamespace(),
            'controller_class'       => $this->className,
        ));
    }
}
