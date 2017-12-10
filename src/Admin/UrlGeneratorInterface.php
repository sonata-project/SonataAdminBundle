<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as RoutingUrlGeneratorInterface;

/**
 * Contains url generation logic related to an admin.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface UrlGeneratorInterface
{
    /**
     * Returns the list of available urls.
     *
     * @return RouteCollection the list of available urls
     */
    public function getRoutes();

    /**
     * Return the parameter name used to represent the id in the url.
     *
     * @return string
     */
    public function getRouterIdParameter();

    public function setRouteGenerator(RouteGeneratorInterface $routeGenerator);

    /**
     * Generates the object url with the given $name.
     *
     * @param string $name
     * @param mixed  $object
     * @param int    $absolute
     *
     * @return string return a complete url
     */
    public function generateObjectUrl(
        $name,
        $object,
        array $parameters = [],
        $absolute = RoutingUrlGeneratorInterface::ABSOLUTE_PATH
    );

    /**
     * Generates a url for the given parameters.
     *
     * @param string $name
     * @param int    $absolute
     *
     * @return string return a complete url
     */
    public function generateUrl($name, array $parameters = [], $absolute = RoutingUrlGeneratorInterface::ABSOLUTE_PATH);

    /**
     * Generates a url for the given parameters.
     *
     * @param string $name
     * @param int    $absolute
     *
     * @return array return url parts: 'route', 'routeParameters', 'routeAbsolute'
     */
    public function generateMenuUrl($name, array $parameters = [], $absolute = RoutingUrlGeneratorInterface::ABSOLUTE_PATH);

    /**
     * @param mixed $entity
     *
     * @return string a string representation of the id that is safe to use in a url
     */
    public function getUrlsafeIdentifier($entity);
}
