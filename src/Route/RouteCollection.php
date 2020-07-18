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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class RouteCollection implements RouteCollectionInterface
{
    /**
     * @var Route[]
     */
    private $elements = [];

    /**
     * @var string
     */
    private $baseCodeRoute;

    /**
     * @var string
     */
    private $baseRouteName;

    /**
     * @var string
     */
    private $baseControllerName;

    /**
     * @var string
     */
    private $baseRoutePattern;

    /**
     * @var Route[]
     */
    private $cachedElements = [];

    public function __construct(
        string $baseCodeRoute,
        string $baseRouteName,
        string $baseRoutePattern,
        string $baseControllerName
    ) {
        $this->baseCodeRoute = $baseCodeRoute;
        $this->baseRouteName = $baseRouteName;
        $this->baseRoutePattern = $baseRoutePattern;
        $this->baseControllerName = $baseControllerName;
    }

    public function add(
        string $name,
        ?string $pattern = null,
        array $defaults = [],
        array $requirements = [],
        array $options = [],
        string $host = '',
        array $schemes = [],
        array $methods = [],
        string $condition = ''
    ): RouteCollectionInterface {
        $pattern = sprintf('%s/%s', $this->baseRoutePattern, $pattern ?: $name);
        $code = $this->getCode($name);
        $routeName = sprintf('%s_%s', $this->baseRouteName, $name);

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

        $defaults['_sonata_name'] = $routeName;

        $element = static function () use ($pattern, $defaults, $requirements, $options, $host, $schemes, $methods, $condition) {
            return new Route($pattern, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
        };
        $this->addElement($code, $element);

        return $this;
    }

    public function getCode(string $name): string
    {
        if (false !== strrpos($name, '.')) {
            return $name;
        }

        return sprintf('%s.%s', $this->baseCodeRoute, $name);
    }

    public function addCollection(RouteCollectionInterface $collection): RouteCollectionInterface
    {
        foreach ($collection->getElements() as $code => $element) {
            $this->addElement($code, $element);
        }

        return $this;
    }

    public function getElements(): array
    {
        foreach ($this->elements as $code => $element) {
            $this->resolveElement($code);
        }

        return $this->elements;
    }

    public function has(string $name): bool
    {
        return \array_key_exists($this->getCode($name), $this->elements);
    }

    public function hasCached(string $name): bool
    {
        return \array_key_exists($this->getCode($name), $this->cachedElements);
    }

    public function get(string $name): Route
    {
        if ($this->has($name)) {
            $code = $this->getCode($name);
            $this->resolveElement($code);

            return $this->elements[$code];
        }

        throw new \InvalidArgumentException(sprintf('Element "%s" does not exist.', $name));
    }

    public function remove(string $name): RouteCollectionInterface
    {
        unset($this->elements[$this->getCode($name)]);

        return $this;
    }

    public function restore(string $name): RouteCollectionInterface
    {
        if ($this->hasCached($name)) {
            $code = $this->getCode($name);
            $this->addElement($code, $this->cachedElements[$code]);

            return $this;
        }

        throw new \InvalidArgumentException(sprintf('Element "%s" does not exist in cache.', $name));
    }

    public function clearExcept($routeList): RouteCollectionInterface
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

    public function clear(): RouteCollectionInterface
    {
        $this->elements = [];

        return $this;
    }

    public function actionify(string $action): string
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

    public function getBaseCodeRoute(): string
    {
        return $this->baseCodeRoute;
    }

    public function getBaseControllerName(): string
    {
        return $this->baseControllerName;
    }

    public function getBaseRouteName(): string
    {
        return $this->baseRouteName;
    }

    public function getBaseRoutePattern(): string
    {
        return $this->baseRoutePattern;
    }

    /**
     * @param Route|callable $element
     */
    private function addElement(string $code, $element): void
    {
        $this->elements[$code] = $element;
        $this->updateCachedElement($code);
    }

    private function updateCachedElement(string $code): void
    {
        $this->cachedElements[$code] = $this->elements[$code];
    }

    private function resolveElement(string $code): void
    {
        $element = $this->elements[$code];

        if (\is_callable($element)) {
            $this->elements[$code] = $element();
            $this->updateCachedElement($code);
        }
    }
}
