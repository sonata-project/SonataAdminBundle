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

use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AdminPoolLoader extends Loader
{
    const ROUTE_TYPE_NAME = 'sonata_admin';

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var array
     */
    protected $adminServiceIds = [];

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(Pool $pool, array $adminServiceIds, ContainerInterface $container)
    {
        $this->pool = $pool;
        $this->adminServiceIds = $adminServiceIds;
        $this->container = $container;
    }

    public function supports($resource, $type = null)
    {
        return self::ROUTE_TYPE_NAME === $type;
    }

    public function load($resource, $type = null)
    {
        $collection = new SymfonyRouteCollection();
        foreach ($this->adminServiceIds as $id) {
            $admin = $this->pool->getInstance($id);

            foreach ($admin->getRoutes()->getElements() as $code => $route) {
                $collection->add($route->getDefault('_sonata_name'), $route);
            }

            $reflection = new \ReflectionObject($admin);
            if (file_exists($reflection->getFileName())) {
                $collection->addResource(new FileResource($reflection->getFileName()));
            }
        }

        $reflection = new \ReflectionObject($this->container);
        if (file_exists($reflection->getFileName())) {
            $collection->addResource(new FileResource($reflection->getFileName()));
        }

        return $collection;
    }
}
