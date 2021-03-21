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
 */
interface RouteCollectionInterface
{
    /**
     * @param array<string, mixed>  $defaults
     * @param array<string, string> $requirements
     * @param array<string, mixed>  $options
     * @param string[]              $schemes
     * @param string[]              $methods
     *
     * @return static
     */
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

    /**
     * @return static
     */
    public function addCollection(self $collection): self;

    /**
     * @return array<string, Route>
     */
    public function getElements(): array;

    public function has(string $name): bool;

    public function hasCached(string $name): bool;

    /**
     * @throws \InvalidArgumentException
     */
    public function get(string $name): Route;

    /**
     * @return static
     */
    public function remove(string $name): self;

    /**
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public function restore(string $name): self;

    /**
     * Remove all routes except routes in $routeList.
     *
     * @param string[]|string $routeList
     *
     * @return static
     */
    public function clearExcept($routeList): self;

    /**
     * @return static
     */
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

    public function getRouteName(string $name): string;

    public function getBaseRoutePattern(): string;
}
