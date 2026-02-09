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

namespace SensioLabs\AdminBundle\User\Security;

use SensioLabs\AdminBundle\Admin\Pool;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class EditableRolesBuilder
{
    /**
     * @param array<string, list<string>> $rolesHierarchy
     */
    public function __construct(
        private readonly Pool $pool,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly array $rolesHierarchy = [],
    ) {
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getRoles(): array
    {
        $roles = [];
        $isMaster = $this->hasMasterAccess();

        // Security roles from hierarchy
        foreach ($this->rolesHierarchy as $name => $children) {
            $roles[$name] = [
                'role' => $name,
                'children' => $children,
                'is_granted' => $isMaster || $this->authorizationChecker->isGranted($name),
            ];
        }

        // Admin roles
        foreach ($this->pool->getAdminServiceCodes() as $code) {
            $admin = $this->pool->getInstance($code);
            $securityHandler = $admin->getSecurityHandler();
            $baseRole = $securityHandler->getBaseRole($admin);

            foreach (array_keys($securityHandler->buildSecurityInformation($admin)) as $permission) {
                $role = \sprintf($baseRole, $permission);
                $roles[$role] = [
                    'role' => $role,
                    'label' => $permission,
                    'admin_label' => $admin->getClassnameLabel(),
                    'is_granted' => $isMaster || $this->authorizationChecker->isGranted($role),
                ];
            }
        }

        return $roles;
    }

    /**
     * @return list<string>
     */
    public function getRolesReadOnly(): array
    {
        $rolesReadOnly = [];

        foreach ($this->getRoles() as $role => $attributes) {
            if (false === ($attributes['is_granted'] ?? false)) {
                $rolesReadOnly[] = $role;
            }
        }

        return $rolesReadOnly;
    }

    private function hasMasterAccess(): bool
    {
        return null !== $this->tokenStorage->getToken()
            && $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN');
    }
}
