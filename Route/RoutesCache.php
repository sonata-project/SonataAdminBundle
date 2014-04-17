<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Route;

use Sonata\AdminBundle\Admin\AdminInterface;

class RoutesCache
{
    /**
     * @var string
     */
    protected $cacheFolder;

    /**
     * @param string $cacheFolder
     */
    public function __construct($cacheFolder)
    {
        $this->cacheFolder = $cacheFolder;
    }

    /**
     * @param AdminInterface $admin
     *
     * @return array
     */
    public function dump(AdminInterface $admin)
    {
        if (!is_dir($this->cacheFolder)) {
            mkdir($this->cacheFolder, 0755, true);
        }

        $filename = sprintf("%s/route_%s", $this->cacheFolder, md5($admin->getCode()));
        $routes = array();

        if (!$admin->getRoutes()) {
            throw new \RuntimeException('Invalid data type, Admin::getRoutes must return a RouteCollection');
        }

        foreach ($admin->getRoutes()->getElements() as $code => $route) {
            $routes[$code] = $route->getDefault('_sonata_name');
        }

        file_put_contents($filename, serialize($routes));

        return $routes;
    }

    /**
     * @param AdminInterface $admin
     *
     * @return array|null
     */
    public function load(AdminInterface $admin)
    {
        $filename = sprintf("%s/route_%s", $this->cacheFolder, md5($admin->getCode()));

        // we don't care about error here ...
        $content = @file_get_contents($filename);

        if (!$content) {
            return $this->dump($admin);
        }

        return unserialize($content);
    }
}
