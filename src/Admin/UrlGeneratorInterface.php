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

use Sonata\AdminBundle\Route\RouteCollectionInterface;
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
     */
    public function getRoutes(): RouteCollectionInterface;

    /**
     * Return the parameter name used to represent the id in the url.
     */
    public function getRouterIdParameter(): string;

    public function setRouteGenerator(RouteGeneratorInterface $routeGenerator): void;

    /**
     * Generates the object url with the given $name.
     *
     * @param array<string, mixed> $parameters
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
     * @param array<string, mixed> $parameters
     *
     * @return string return a complete url
     */
    public function generateUrl(string $name, array $parameters = [], int $referenceType = RoutingUrlGeneratorInterface::ABSOLUTE_PATH): string;

    /**
     * Generates a url for the given parameters.
     *
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     * @phpstan-return array{route: string, routeParameters: array<string, mixed>, routeAbsolute: bool}
     */
    public function generateMenuUrl(string $name, array $parameters = [], int $referenceType = RoutingUrlGeneratorInterface::ABSOLUTE_PATH): array;

    /**
     * @phpstan-param T $model
     */
    public function getUrlSafeIdentifier(object $model): ?string;
}
