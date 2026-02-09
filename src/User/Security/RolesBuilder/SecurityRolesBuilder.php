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

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SecurityRolesBuilder implements ExpandableRolesBuilderInterface
{
    /**
     * @param array<string, list<string>> $rolesHierarchy
     */
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly TranslatorInterface $translator,
        private readonly array $rolesHierarchy = [],
    ) {
    }

    public function getRoles(?string $domain = null): array
    {
        $roles = [];

        foreach ($this->rolesHierarchy as $name => $children) {
            $roles[$name] = [
                'role' => $name,
                'role_translated' => $this->translateRole($name, $domain),
                'is_granted' => $this->authorizationChecker->isGranted($name),
                'children' => $children,
            ];
        }

        return $roles;
    }

    public function getExpandedRoles(?string $domain = null): array
    {
        $roles = [];

        foreach ($this->rolesHierarchy as $name => $children) {
            $roles[$name] = [
                'role' => $name,
                'role_translated' => $this->translateRole($name, $domain),
                'is_granted' => $this->authorizationChecker->isGranted($name),
            ];

            foreach ($children as $child) {
                if (!isset($roles[$child])) {
                    $roles[$child] = [
                        'role' => $child,
                        'role_translated' => $this->translateRole($child, $domain),
                        'is_granted' => $this->authorizationChecker->isGranted($child),
                    ];
                }
            }
        }

        return $roles;
    }

    private function translateRole(string $role, ?string $domain): string
    {
        if (null !== $domain) {
            return $this->translator->trans($role, [], $domain);
        }

        return $role;
    }
}
