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

use Symfony\Component\Routing\Route;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class RouteCollection
{
    /**
     * @var Route[]
     */
    protected $elements = [];

    /**
     * @var string
     */
    protected $baseCodeRoute;

    /**
     * @var string
     */
    protected $baseRouteName;

    /**
     * @var string
     */
    protected $baseControllerName;

    /**
     * @var string
     */
    protected $baseRoutePattern;

    /**
     * @param string $baseCodeRoute
     * @param string $baseRouteName
     * @param string $baseRoutePattern
     * @param string $baseControllerName
     */
    public function __construct($baseCodeRoute, $baseRouteName, $baseRoutePattern, $baseControllerName)
    {
        $this->baseCodeRoute = $baseCodeRoute;
        $this->baseRouteName = $baseRouteName;
        $this->baseRoutePattern = $baseRoutePattern;
        $this->baseControllerName = $baseControllerName;
    }

    /**
     * Add route.
     *
     * @param string $name
     * @param string $pattern   Pattern (will be automatically combined with @see $this->baseRoutePattern and $name
     * @param string $host
     * @param string $condition
     *
     * @return RouteCollection
     */
    public function add(
        $name,
        $pattern = null,
        array $defaults = [],
        array $requirements = [],
        array $options = [],
        $host = '',
        array $schemes = [],
        array $methods = [],
        $condition = ''
    ) {
        $pattern = $this->baseRoutePattern.'/'.($pattern ?: $name);
        $code = $this->getCode($name);
        $routeName = $this->baseRouteName.'_'.$name;

        if (!isset($defaults['_controller'])) {
            $actionJoiner = false === \strpos($this->baseControllerName, '\\') ? ':' : '::';
            $defaults['_controller'] = $this->baseControllerName.$actionJoiner.$this->actionify($code);
        }

        if (!isset($defaults['_sonata_admin'])) {
            $defaults['_sonata_admin'] = $this->baseCodeRoute;
        }

        $defaults['_sonata_name'] = $routeName;

        $this->elements[$this->getCode($name)] = function () use (
            $pattern, $defaults, $requirements, $options, $host, $schemes, $methods, $condition) {
            return new Route($pattern, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
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
        if (false !== strrpos($name, '.')) {
            return $name;
        }

        return $this->baseCodeRoute.'.'.$name;
    }

    /**
     * @return RouteCollection
     */
    public function addCollection(self $collection)
    {
        foreach ($collection->getElements() as $code => $route) {
            $this->elements[$code] = $route;
        }

        return $this;
    }

    /**
     * @return Route[]
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
     * @return RouteCollection
     */
    public function remove($name)
    {
        unset($this->elements[$this->getCode($name)]);

        return $this;
    }

    /**
     * Remove all routes except routes in $routeList.
     *
     * @param string[]|string $routeList
     *
     * @return RouteCollection
     */
    public function clearExcept($routeList)
    {
        if (!\is_array($routeList)) {
            $routeList = [$routeList];
        }

        $routeCodeList = [];
        foreach ($routeList as $name) {
            $routeCodeList[] = $this->getCode($name);
        }

        $elements = $this->elements;
        foreach ($elements as $key => $element) {
            if (!\in_array($key, $routeCodeList)) {
                unset($this->elements[$key]);
            }
        }

        return $this;
    }

    /**
     * Remove all routes.
     *
     * @return RouteCollection
     */
    public function clear()
    {
        $this->elements = [];

        return $this;
    }

    /**
     * Convert a word in to the format for a symfony action action_name => actionName.
     *
     * @param string $action Word to actionify
     *
     * @return string Actionified word
     */
    public function actionify($action)
    {
        if (false !== ($pos = strrpos($action, '.'))) {
            $action = substr($action, $pos + 1);
        }

        // if this is a service rather than just a controller name, the suffix
        // Action is not automatically appended to the method name
        if (false === strpos($this->baseControllerName, ':')) {
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

    /**
     * @return Route
     */
    private function resolve($element)
    {
        if (\is_callable($element)) {
            return \call_user_func($element);
        }

        return $element;
    }
}
