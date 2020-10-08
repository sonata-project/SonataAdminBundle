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
     * @param string               $name
     * @param array<string, mixed> $parameters
     * @param int                  $referenceType
     */
    public function generateUrl(AdminInterface $admin, $name, array $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string;

    /**
     * @param string               $name
     * @param array<string, mixed> $parameters
     * @param int                  $referenceType
     */
    public function generateMenuUrl(AdminInterface $admin, $name, array $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): array;

    /**
     * @param string               $name
     * @param array<string, mixed> $parameters
     * @param int                  $referenceType
     */
    public function generate($name, array $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string;

    /**
     * @param string $name
     */
    public function hasAdminRoute(AdminInterface $admin, $name): bool;
}
