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

use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface RouteGeneratorInterface
{
    /**
     * @param AdminInterface<object> $admin
     * @param array<string, mixed>   $parameters
     */
    public function generateUrl(AdminInterface $admin, string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string;

    /**
     * @param AdminInterface<object> $admin
     * @param array<string, mixed>   $parameters
     *
     * @return array<string, mixed>
     *
     * @phpstan-return array{route: string, routeParameters: array<string, mixed>, routeAbsolute: bool}
     */
    public function generateMenuUrl(AdminInterface $admin, string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): array;

    /**
     * @param array<string, mixed> $parameters
     */
    public function generate(string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string;

    /**
     * @param AdminInterface<object> $admin
     */
    public function hasAdminRoute(AdminInterface $admin, string $name): bool;
}
