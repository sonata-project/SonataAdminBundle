<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Generate the controller file for admin
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class ControllerGenerator extends Generator
{
    private $skeletonDir;

    public function __construct($skeletonDir)
    {
        $this->skeletonDir = $skeletonDir;
    }

    public function generate(BundleInterface $bundle, $entityClass, $force = false)
    {
        $controller = $entityClass."AdminController";
        $dirPath    = $bundle->getPath().'/Controller';
        
        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0755);
        }
        
        $controllerPath = $dirPath.'/'.$controller.'.php';
        
        if (file_exists($controllerPath) && !$force) {
            throw new \RuntimeException(sprintf('Controller file %s already exists.', $controllerPath));
        }
        
        $this->renderFile($this->skeletonDir, 'Controller.php', $controllerPath, array(
            'namespace'         => $bundle->getNamespace(),
            'controller'        => $controller,
        ));
    }
}