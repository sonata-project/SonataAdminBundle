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
     * @param string $baseRoutePattern
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
     * @param array  $defaults
     * @param array  $requirements
     * @param array  $options
     *
     * @return \Sonata\AdminBundle\Route\RouteCollection
     */
    public function add($name, $pattern = null, array $defaults = array(), array $requirements = array(), array $options = array())
    {
        $pattern    = $this->baseRoutePattern . '/'. ($pattern ?: $name);
        $code       = $this->getCode($name);
        $routeName  = $this->baseRouteName . '_' . $name;

        if (!isset($defaults['_controller'])) {
            $defaults['_controller'] = $this->baseControllerName . ':' . $this->actionify($code);
        }

        if (!isset($defaults['_sonata_admin'])) {
            $defaults['_sonata_admin'] = $this->baseCodeRoute;
        }

        $defaults['_sonata_name'] = $routeName;

        $this->elements[$this->getCode($name)] = function() use ($pattern, $defaults, $requirements, $options) {
            return new Route($pattern, $defaults, $requirements, $options);
        };

        return $this;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getCode($name)
    {
        if (strrpos($name, '.') !== false) {
            return $name;
        }

        return $this->baseCodeRoute . '.' . $name;
    }

    /**
     * @param RouteCollection $collection
     *
     * @return \Sonata\AdminBundle\Route\RouteCollection
     */
    public function addCollection(RouteCollection $collection)
    {
        foreach ($collection->getElements() as $code => $route) {
            $this->elements[$code] = $route;
        }

        return $this;
    }

    /**
     * @param $element
     *
     * @return Route
     */
    private function resolve($element)
    {
        if (is_callable($element)) {
            return call_user_func($element);
        }

        return $element;
    }

    /**
     * @return array
     */
    public function getElements()
    {
        foreach ($this->elements as $name => $element) {
            $this->elements[$name] = $this->resolve($element);
        }

        return $this->elements;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($this->getCode($name), $this->elements);
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return Route
     */
    public function get($name)
    {
        if ($this->has($name)) {
            $code = $this->getCode($name);

            $this->elements[$code] = $this->resolve($this->elements[$code]);

            return $this->elements[$code];
        }

        throw new \InvalidArgumentException(sprintf('Element "%s" does not exist.', $name));
    }

    /**
     * @param string $name
     *
     * @return \Sonata\AdminBundle\Route\RouteCollection
     */
    public function remove($name)
    {
        unset($this->elements[$this->getCode($name)]);

        return $this;
    }

    /**
     * Remove all routes except routes in $routeList
     *
     * @param array $routeList
     *
     * @return \Sonata\AdminBundle\Route\RouteCollection
     */
    public function clearExcept(array $routeList)
    {
        $routeCodeList = array();
        foreach ($routeList as $name) {
            $routeCodeList[] = $this->getCode($name);
        }

        $elements = $this->elements;
        foreach ($elements as $key => $element) {
            if (!in_array($key, $routeCodeList)) {
                unset($this->elements[$key]);
            }
        }

        return $this;
    }

    /**
     * Remove all routes
     *
     * @return \Sonata\AdminBundle\Route\RouteCollection
     */
    public function clear()
    {
        $this->elements = array();

        return $this;
    }

    /**
     * Convert a word in to the format for a symfony action action_name => actionName
     *
     * @param string $action Word to actionify
     *
     * @return string Actionified word
     */
    public function actionify($action)
    {
        if (($pos = strrpos($action, '.')) !== false) {
            $action = substr($action, $pos + 1);
        }

        // if this is a service rather than just a controller name, the suffix
        // Action is not automatically appended to the method name
        if (strpos($this->baseControllerName, ':') === false) {
            $action .= 'Action';
        }

        return lcfirst(str_replace(' ', '', ucwords(strtr($action, '_-', '  '))));
    }

    /**
     * @return string
     */
    public function getBaseCodeRoute()
    {
        return $this->baseCodeRoute;
    }

    /**
     * @return string
     */
    public function getBaseControllerName()
    {
        return $this->baseControllerName;
    }

    /**
     * @return string
     */
    public function getBaseRouteName()
    {
        return $this->baseRouteName;
    }

    /**
     * @return string
     */
    public function getBaseRoutePattern()
    {
        return $this->baseRoutePattern;
    }
}