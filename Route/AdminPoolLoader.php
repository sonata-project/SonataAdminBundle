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
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

/**
 * Class AdminPoolLoader.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AdminPoolLoader extends FileLoader
{
    const ROUTE_TYPE_NAME = 'sonata_admin';

    /**
     * @var \Sonata\AdminBundle\Admin\Pool
     */
    protected $pool;

    /**
     * @var array
     */
    protected $adminServiceIds = array();

    protected $container;

    /**
     * @param \Sonata\AdminBundle\Admin\Pool                            $pool
     * @param array                                                     $adminServiceIds
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(Pool $pool, array $adminServiceIds, ContainerInterface $container)
    {
        $this->pool             = $pool;
        $this->adminServiceIds  = $adminServiceIds;
        $this->container        = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($resource, $type = null)
    {
        return $type === self::ROUTE_TYPE_NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function load($resource, $type = null)
    {
        $collection = new SymfonyRouteCollection();
        foreach ($this->adminServiceIds as $id) {
            $admin = $this->pool->getInstance($id);

            foreach ($admin->getRoutes()->getElements() as $code => $route) {
                $collection->add($route->getDefault('_sonata_name'), $route);
            }

            $reflection = new \ReflectionObject($admin);
            $collection->addResource(new FileResource($reflection->getFileName()));
        }

        $reflection = new \ReflectionObject($this->container);
        $collection->addResource(new FileResource($reflection->getFileName()));

        return $collection;
    }
}
