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
 * Generate the admin class file for admin
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class AdminGenerator extends Generator
{
    private $skeletonDir;
    private $adminClass;

    public function __construct($skeletonDir)
    {
        $this->skeletonDir = $skeletonDir;
    }

    public function generate(BundleInterface $bundle, $entityClass, $fields_show, $fields_form, $fields_list, $fields_filter, $force = false)
    {
        $admin = $entityClass."Admin";
        $dirPath    = $bundle->getPath().'/Admin';
        
        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0755);
        }
        
        $adminPath = $dirPath.'/'.$admin.'.php';
        
        if (file_exists($adminPath) && !$force) {
            throw new \RuntimeException(sprintf('Admin file %s already exists.', $adminPath));
        }
        
        $this->adminClass = $bundle->getNamespace()."\\Admin\\".$admin;
        
        $this->renderFile($this->skeletonDir, 'Admin.php', $adminPath, array(
            'namespace'         => $bundle->getNamespace(),
            'admin'             => $admin,
            'fields_show'       => $fields_show,
            'fields_form'       => $fields_form,
            'fields_list'       => $fields_list,
            'fields_filter'     => $fields_filter
        ));
    }
    
    public function getAdminClass()
    {
        return $this->adminClass;
    }
}