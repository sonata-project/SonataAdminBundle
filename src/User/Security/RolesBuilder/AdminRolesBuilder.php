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

use SensioLabs\AdminBundle\Admin\Pool;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdminRolesBuilder implements AdminRolesBuilderInterface
{
    /**
     * @var array<string, string>
     */
    private array $permissionLabels = [];

    /**
     * @var list<string>
     */
    private array $excludedAdmins = [];

    public function __construct(
        private readonly Pool $pool,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getRoles(?string $domain = null): array
    {
        $roles = [];

        foreach ($this->pool->getAdminServiceCodes() as $code) {
            if (\in_array($code, $this->excludedAdmins, true)) {
                continue;
            }

            $admin = $this->pool->getInstance($code);
            $securityHandler = $admin->getSecurityHandler();
            $baseRole = $securityHandler->getBaseRole($admin);
            $isMaster = $admin->isGranted('MASTER');

            foreach (array_keys($securityHandler->buildSecurityInformation($admin)) as $permission) {
                $role = \sprintf($baseRole, $permission);

                $this->permissionLabels[$permission] = $permission;

                $roles[$role] = [
                    'role' => $role,
                    'label' => $permission,
                    'role_translated' => $this->translateRole($role, $domain),
                    'is_granted' => $isMaster || $this->authorizationChecker->isGranted($role),
                    'admin_label' => $admin->getTranslationLabel(
                        $admin->getClassnameLabel(),
                        'breadcrumb',
                        'link',
                    ),
                    'admin_code' => $admin->getCode(),
                    'admin_translation_domain' => $admin->getTranslationDomain(),
                ];
            }
        }

        return $roles;
    }

    public function getPermissionLabels(): array
    {
        // Build roles first to populate the labels
        if ([] === $this->permissionLabels) {
            $this->getRoles();
        }

        return $this->permissionLabels;
    }

    /**
     * @param list<string> $excludedAdmins
     */
    public function setExcludedAdmins(array $excludedAdmins): void
    {
        $this->excludedAdmins = $excludedAdmins;
    }

    private function translateRole(string $role, ?string $domain): string
    {
        if (null !== $domain) {
            return $this->translator->trans($role, [], $domain);
        }

        return $role;
    }
}
