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

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;

use Sonata\AdminBundle\Admin\Pool;

class AdminPoolLoader extends FileLoader
{
    /**
     * @var Bundle\Sonata\AdminBundle\Admin\Pool
     */
    protected $pool;

    /**
     * @var array
     */
    protected $adminServiceIds = array();

    /**
     * @param \Sonata\AdminBundle\Admin\Pool $pool
     * @param  $adminServiceIds
     */
    public function __construct(Pool $pool, $adminServiceIds)
    {
        $this->pool = $pool;
        $this->adminServiceIds = $adminServiceIds;
    }

    /**
     * @param string $resource
     * @param null $type
     * @return bool
     */
    function supports($resource, $type = null)
    {
        if ($type == 'sonata_admin') {
            return true;
        }

        return false;
    }

    /**
     * @param string $resource
     * @param null $type
     * @return \Symfony\Component\Routing\RouteCollection
     */
    function load($resource, $type = null)
    {
        $collection = new RouteCollection;
        foreach ($this->adminServiceIds as $id) {

            $admin = $this->pool->getInstance($id);

            foreach ($admin->getRoutes()->getElements() as $code => $route) {
                $collection->add($route->getDefault('_sonata_name'), $route);
            }

            $reflection = new \ReflectionObject($admin);
            $collection->addResource(new FileResource($reflection->getFileName()));
        }

        return $collection;
    }
}
