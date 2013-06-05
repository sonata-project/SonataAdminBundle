<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Simon Cosandey <simon.cosandey@simseo.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Manipulator;

use Sensio\Bundle\GeneratorBundle\Manipulator\Manipulator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Changes the code of a YAML services file.
 *
 * @author Simon Cosandey <simon.cosandey@simseo.ch>
 */
class ServicesManipulator extends Manipulator
{
    
    private $file;

    /**
     * Constructor.
     *
     * @param string $file The YAML service file path
     * 
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Adds a service definition at the bottom of the existing ones.
     *
     * @param BundleInterface $bundle
     * @param string $entity
     * @param string $adminClass
     *
     * @return Boolean true if it worked, false otherwise
     *
     */
    public function addResource(BundleInterface $bundle, $entity, $adminClass)
    {
        $current = '';
        $parts = explode('\\', $bundle->getNamespace() );
        var_dump($parts);
        if (file_exists($this->file)) {
            $current = file_get_contents($this->file);
            
        } elseif (!is_dir($dir = dirname($this->file))) {
            mkdir($dir, 0777, true);
        }
        
        $code = sprintf("    %s.%s.admin.%s:\n",
                strtolower( $parts[0]),
                strtolower($parts[1]),
                strtolower($entity)
                );
        
        $code .= sprintf("        class: %s\Admin\%s\n", $bundle->getNamespace(), $adminClass);
        $code .= sprintf("        tags:\n");
        $code .= sprintf("            - { name: sonata.admin, manager_type: orm, group: admin, label: %s }\n", $entity);
        $code .= sprintf("        arguments: [null, %s\Entity\%s, %s:%s]\n", $bundle->getNamespace(), $entity, $bundle->getName(), $adminClass);
        $current .= "\n";
        $current .= $code;

        if (false === file_put_contents($this->file, $current)) {
            return false;
        }

        return true;
    }
}
