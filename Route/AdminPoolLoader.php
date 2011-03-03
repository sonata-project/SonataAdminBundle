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

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    function supports($resource, $type = null)
    {
        if ($type == 'sonata_admin') {
            return true;
        }

        return false;
    }

    function load($resource, $type = null)
    {
        $collection = new RouteCollection;
        foreach ($this->pool->getInstances() as $admin) {
            foreach ($admin->getUrls() as $action => $configuration) {

                $defaults = isset($configuration['defaults'])       ? $configuration['defaults'] : array();

                if(!isset($defaults['_controller'])) {
                    $defaults['_controller'] = sprintf('%s:%s', $admin->getBaseControllerName(), $this->actionify($action));
                }
                $defaults['_bab_action'] = sprintf('%s.%s', $admin->getCode(), $action);

                $collection->add($configuration['name'], new Route(
                    $configuration['pattern'],
                    $defaults,
                    isset($configuration['requirements'])   ? $configuration['requirements'] : array(),
                    isset($configuration['options'])        ? $configuration['options'] : array()
                ));
            }

            $reflection = new \ReflectionObject($admin);
            $collection->addResource(new FileResource($reflection->getFileName()));
        }

        return $collection;
    }


    /**
     * Convert a word in to the format for a symfony action action_name => actionName
     *
     * @param string  $word Word to actionify
     * @return string $word Actionified word
     */
    public static function actionify($word)
    {
        return lcfirst(str_replace(' ', '', ucwords(strtr($word, '_-', '  '))));
    }
}
