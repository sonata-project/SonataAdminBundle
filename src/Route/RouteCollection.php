<?php

declare(strict_types=1);

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
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class RouteCollection implements RouteCollectionInterface
{
    /**
     * @var array<string, Route|callable():Route>
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
     * @var array<string, Route|callable():Route>
     */
    private $cachedElements = [];

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

    public function getRouteName(string $name): string
    {
        return sprintf('%s_%s', $this->baseRouteName, $name);
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
        $pattern = sprintf('%s/%s', $this->baseRoutePattern, $pattern ?: $name);
        $code = $this->getCode($name);

        if (!isset($defaults['_controller'])) {
            $actionJoiner = false === strpos($this->baseControllerName, '\\') ? ':' : '::';
            if (':' !== $actionJoiner && false !== strpos($this->baseControllerName, ':')) {
                $actionJoiner = ':';
            }

            $defaults['_controller'] = $this->baseControllerName.$actionJoiner.$this->actionify($code);
        }

        if (!isset($defaults['_sonata_admin'])) {
            $defaults['_sonata_admin'] = $this->baseCodeRoute;
        }

        $defaults['_sonata_name'] = $this->getRouteName($name);

        $element = static function () use ($pattern, $defaults, $requirements, $options, $host, $schemes, $methods, $condition) {
            return new Route($pattern, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
        };
        $this->addElement($code, $element);

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

        return sprintf('%s.%s', $this->baseCodeRoute, $name);
    }

    /**
     * @return RouteCollection
     */
    public function addCollection(self $collection)
    {
        foreach ($collection->getElements() as $code => $element) {
            $this->addElement($code, $element);
        }

        return $this;
    }

    /**
     * @return Route[]
     */
    public function getElements()
    {
        foreach ($this->elements as $code => $element) {
            $this->resolveElement($code);
        }
        /** @var array<string, Route> $elements */
        $elements = $this->elements;

        return $elements;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return \array_key_exists($this->getCode($name), $this->elements);
    }

    final public function hasCached(string $name): bool
    {
        return \array_key_exists($this->getCode($name), $this->cachedElements);
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
            $this->resolveElement($code);
            \assert($this->elements[$code] instanceof Route);

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
     * @throws \InvalidArgumentException
     */
    final public function restore(string $name): self
    {
        if ($this->hasCached($name)) {
            $code = $this->getCode($name);
            $this->addElement($code, $this->cachedElements[$code]);

            return $this;
        }

        throw new \InvalidArgumentException(sprintf('Element "%s" does not exist in cache.', $name));
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
        foreach ($elements as $code => $element) {
            if (!\in_array($code, $routeCodeList, true)) {
                unset($this->elements[$code]);
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
     * @param Route|callable():Route $element
     */
    final protected function addElement(string $code, $element): void
    {
        $this->elements[$code] = $element;
        $this->updateCachedElement($code);
    }

    final protected function updateCachedElement(string $code): void
    {
        $this->cachedElements[$code] = $this->elements[$code];
    }

    private function resolveElement(string $code): void
    {
        $element = $this->elements[$code];

        if (\is_callable($element)) {
            $resolvedElement = $element();
            if (!$resolvedElement instanceof Route) {
                @trigger_error(sprintf(
                    'Element resolved by code "%s" is not instance of "%s"; This is deprecated since sonata-project/admin-bundle 3.75 and will be removed in 4.0.',
                    $code,
                    Route::class
                ), \E_USER_DEPRECATED);
                // NEXT_MAJOR : remove the previous `trigger_error()` and throw exception
            }

            $this->elements[$code] = $resolvedElement;
            $this->updateCachedElement($code);
        }
    }
}
