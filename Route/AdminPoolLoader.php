<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\BaseApplicationBundle\Route;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Loader\Loader;

use Symfony\Component\Routing\Resource\FileResource;
use Sonata\BaseApplicationBundle\Admin\Pool;

class AdminPoolLoader extends Loader
{
    /**
     * @var Bundle\Soanta\BaseApplicationBundle\Admin\Pool
     */
    protected $pool;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    function supports($resource, $type = null)
    {

        if (substr($resource, -22) == 'base_application.admin') {
            return true;
        }

        return false;
    }

    function load($resource, $type = null)
    {

        $collection = new RouteCollection;
        foreach ($this->pool->getInstances() as $admin) {
            foreach ($admin->getUrls() as $action => $configuration) {

                $default = isset($configuration['defaults'])       ? $configuration['defaults'] : array();

                if(!isset($default['_controller'])) {
                    $default['_controller'] = sprintf('%s:%s', $admin->getBaseControllerName(), $action);
                }
                
                $collection->add($configuration['name'], new Route(
                    $configuration['pattern'],
                    isset($configuration['defaults'])       ? $configuration['defaults'] : array('_controller'),
                    isset($configuration['requirements'])   ? $configuration['requirements'] : array(),
                    isset($configuration['options'])        ? $configuration['options'] : array()
                ));
            }

            $reflection = new \ReflectionObject($admin);
            $collection->addResource(new FileResource($reflection->getFileName()));
        }

        return $collection;
    }
}
