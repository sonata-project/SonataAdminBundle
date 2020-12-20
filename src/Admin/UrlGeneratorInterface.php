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

namespace Sonata\AdminBundle\Admin;

use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as RoutingUrlGeneratorInterface;

/**
 * Contains url generation logic related to an admin.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of object
 */
interface UrlGeneratorInterface
{
    /**
     * Returns the list of available urls.
     *
     * @return RouteCollection the list of available urls
     */
    public function getRoutes(): RouteCollection;

    /**
     * Return the parameter name used to represent the id in the url.
     *
     * @return string
     */
    public function getRouterIdParameter(): string;

    public function setRouteGenerator(RouteGeneratorInterface $routeGenerator);

    /**
     * Generates the object url with the given $name.
     *
     * @param string               $name
     * @param object               $object
     * @param array<string, mixed> $parameters
     * @param int                  $referenceType
     *
     * @return string return a complete url
     *
     * @phpstan-param T $object
     */
    public function generateObjectUrl(
        string $name,
        object $object,
        array $parameters = [],
        int $referenceType = RoutingUrlGeneratorInterface::ABSOLUTE_PATH
    ): string;

    /**
     * Generates a url for the given parameters.
     *
     * @param string               $name
     * @param array<string, mixed> $parameters
     * @param int                  $referenceType
     *
     * @return string return a complete url
     */
    public function generateUrl(string $name, array $parameters = [], int $referenceType = RoutingUrlGeneratorInterface::ABSOLUTE_PATH): string;

    /**
     * Generates a url for the given parameters.
     *
     * @param string               $name
     * @param array<string, mixed> $parameters
     * @param int                  $referenceType
     *
     * @return array return url parts: 'route', 'routeParameters', 'routeAbsolute'
     */
    public function generateMenuUrl(string $name, array $parameters = [], int $referenceType = RoutingUrlGeneratorInterface::ABSOLUTE_PATH): array;

    /**
     * @param object $model
     *
     * @return string a string representation of the id that is safe to use in a url
     *
     * @phpstan-param T $model
     */
    public function getUrlSafeIdentifier(object $model): string;
}
