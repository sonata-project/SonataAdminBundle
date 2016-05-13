<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Route;

use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;

/**
 * Class RoutesCache.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class RoutesCache
{
    /**
     * @var string
     */
    protected $cacheFolder;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @param string $cacheFolder
     * @param bool   $debug
     */
    public function __construct($cacheFolder, $debug)
    {
        $this->cacheFolder = $cacheFolder;
        $this->debug = $debug;
    }

    /**
     * @param AdminInterface $admin
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public function load(AdminInterface $admin)
    {
        $filename = $this->cacheFolder.'/route_'.md5($admin->getCode());

        $cache = new ConfigCache($filename, $this->debug);
        if (!$cache->isFresh()) {
            $resources = array();
            $routes = array();

            $reflection = new \ReflectionObject($admin);
            $resources[] = new FileResource($reflection->getFileName());

            if (!$admin->getRoutes()) {
                throw new \RuntimeException('Invalid data type, AdminInterface::getRoutes must return a RouteCollection');
            }

            foreach ($admin->getRoutes()->getElements() as $code => $route) {
                $routes[$code] = $route->getDefault('_sonata_name');
            }

            if (!is_array($admin->getExtensions())) {
                throw new \RuntimeException('extensions must be an array');
            }

            foreach ($admin->getExtensions() as $extension) {
                $reflection = new \ReflectionObject($extension);
                $resources[] = new FileResource($reflection->getFileName());
            }

            $cache->write(serialize($routes), $resources);
        }

        return unserialize(file_get_contents($filename));
    }
}
