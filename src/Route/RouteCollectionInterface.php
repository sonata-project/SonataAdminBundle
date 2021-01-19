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
 * @author Jordi Sala <jordism91@gmail.com>
 *
 * @method string getRouteName(string $name)
 */
interface RouteCollectionInterface
{
    /**
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
    );

    /**
     * @return string
     */
    public function getCode(string $name);

    /**
     * @return $this
     */
    public function addCollection(RouteCollection $collection);

    /**
     * @return Route[]
     */
    public function getElements();

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name);

    public function hasCached(string $name): bool;

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return Route
     */
    public function get($name);

    /**
     * @return $this
     */
    public function remove(string $name);

    /**
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function restore(string $name);

    /**
     * Remove all routes except routes in $routeList.
     *
     * @param string[]|string $routeList
     *
     * @return $this
     */
    public function clearExcept($routeList);

    /**
     * @return $this
     */
    public function clear();

    /**
     * Converts a word into the format required for a controller action. By instance,
     * the argument "list_something" returns "listSomething" if the associated controller is not an action itself,
     * otherwise, it will return "listSomethingAction".
     *
     * @return string
     */
    public function actionify(string $action);

    /**
     * @return string
     */
    public function getBaseCodeRoute();

    /**
     * @return string
     */
    public function getBaseControllerName();

    /**
     * @return string
     */
    public function getBaseRouteName();

    // NEXT_MAJOR: Uncomment the following line and remove corresponding @method annotation.
//    public function getRouteName(string $name): string;

    /**
     * @return string
     */
    public function getBaseRoutePattern();
}
