<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\User\Security\RolesBuilder;

final class MatrixRolesBuilder implements MatrixRolesBuilderInterface
{
    public function __construct(
        private readonly AdminRolesBuilderInterface $adminRolesBuilder,
        private readonly ExpandableRolesBuilderInterface $securityRolesBuilder,
    ) {
    }

    public function getRoles(?string $domain = null): array
    {
        return array_merge(
            $this->adminRolesBuilder->getRoles($domain),
            $this->securityRolesBuilder->getRoles($domain),
        );
    }

    public function getExpandedRoles(?string $domain = null): array
    {
        return array_merge(
            $this->adminRolesBuilder->getRoles($domain),
            $this->securityRolesBuilder->getExpandedRoles($domain),
        );
    }

    public function getPermissionLabels(): array
    {
        return $this->adminRolesBuilder->getPermissionLabels();
    }
}
