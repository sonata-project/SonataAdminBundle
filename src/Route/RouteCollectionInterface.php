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

use InvalidArgumentException;
use Symfony\Component\Routing\Route;

/**
 * @author Jordi Sala <jordism91@gmail.com>
 */
interface RouteCollectionInterface
{
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
    ): self;

    public function getCode(string $name): string;

    public function addCollection(self $collection): self;

    /**
     * @return Route[]
     */
    public function getElements(): array;

    public function has(string $name): bool;

    public function hasCached(string $name): bool;

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $name): Route;

    public function remove(string $name): self;

    /**
     * @throws InvalidArgumentException
     */
    public function restore(string $name): self;

    /**
     * Remove all routes except routes in $routeList.
     *
     * @param string[]|string $routeList
     */
    public function clearExcept($routeList): self;

    public function clear(): self;

    /**
     * Converts a word into the format required for a controller action. By instance,
     * the argument "list_something" returns "listSomething" if the associated controller is not an action itself,
     * otherwise, it will return "listSomethingAction".
     */
    public function actionify(string $action): string;

    public function getBaseCodeRoute(): string;

    public function getBaseControllerName(): string;

    public function getBaseRouteName(): string;

    public function getBaseRoutePattern(): string;
}
