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
     * @param AdminInterface $admin
     * @param string $name
     * @param array<string, mixed> $parameters
     * @param int $referenceType
     *
     * @return string
     */
    public function generateUrl(AdminInterface $admin, $name, array $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string;

    /**
     * @param AdminInterface $admin
     * @param string $name
     * @param array<string, mixed> $parameters
     * @param int $referenceType
     *
     * @return array
     */
    public function generateMenuUrl(AdminInterface $admin, $name, array $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): array;

    /**
     * @param AdminInterface $admin
     * @param string $name
     * @param array<string, mixed> $parameters
     * @param int $referenceType
     *
     * @return string
     */
    public function generate($name, array $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string;

    /**
     * @param AdminInterface $admin
     * @param string $name
     *
     * @return bool
     */
    public function hasAdminRoute(AdminInterface $admin, $name): bool;
}
