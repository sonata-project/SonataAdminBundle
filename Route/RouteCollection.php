<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Sonata\AdminBundle\Route;

use Symfony\Component\Routing\Route;

class RouteCollection
{

    protected $elements = array();

    protected $baseCodeRoute;

    protected $baseRouteName;

    protected $baseControllerName;

    protected $baseRoutePattern;

    /**
     * @param string $baseCodeRoute
     * @param string $baseRouteName
     * @param string $baseControllerName
     */
    public function __construct($baseCodeRoute, $baseRouteName, $baseRoutePattern, $baseControllerName)
    {
        $this->baseCodeRoute        = $baseCodeRoute;
        $this->baseRouteName        = $baseRouteName;
        $this->baseRoutePattern     = $baseRoutePattern;
        $this->baseControllerName   = $baseControllerName;
    }

    /**
     * @param string $name
     * @param string $pattern
     * @param array $defaults
     * @param array $requirements
     * @param array $options
     * @return void
     */
    public function add($name, $pattern = null, array $defaults = array(), array $requirements = array(), array $options = array())
    {

        $pattern    = sprintf('%s/%s', $this->baseRoutePattern, $pattern ?: $name);
        $code       = sprintf('%s.%s', $this->baseCodeRoute, $name);
        $name       = sprintf('%s_%s', $this->baseRouteName, $name);

        if (!isset($defaults['_controller'])) {
            $defaults['_controller'] = sprintf('%s:%s', $this->baseControllerName, $this->actionify($code));
        }

        if (!isset($defaults['_sonata_admin'])) {
            $defaults['_sonata_admin'] = $this->baseCodeRoute;
        }

        $defaults['_sonata_name'] = $name;

        $this->elements[$code] = new Route($pattern, $defaults, $requirements, $options);;
    }

    /**
     * @param RouteCollection
     */
    public function addCollection(RouteCollection $collection)
    {
        foreach($collection->getElements() as $name => $route) {
            $this->elements[$name] = $route;
        }
    }

    /**
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->elements);
    }

    /**
     * @param string $name
     * @return Route
     */
    public function get($name)
    {
        if ($this->has($name)) {
            return $this->elements[$name];
        }

        throw new \InvalidArgumentException(sprintf('Element "%s" does not exist.', $name));
    }

    /**
     * Convert a word in to the format for a symfony action action_name => actionName
     *
     * @param string  $word Word to actionify
     * @return string $word Actionified word
     */
    public function actionify($action)
    {
        if(($pos = strrpos($action, '.')) !== false) {

          $action = substr($action, $pos + 1);
        }

        return lcfirst(str_replace(' ', '', ucwords(strtr($action, '_-', '  '))));
    }
}