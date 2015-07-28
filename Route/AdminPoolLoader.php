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

class AdminPoolLoader extends FileLoader
{
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
    public function __construct(Pool $pool, $adminServiceIds, ContainerInterface $container)
    {
        $this->pool             = $pool;
        $this->adminServiceIds  = $adminServiceIds;
        $this->container        = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        if ($type == 'sonata_admin') {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
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
