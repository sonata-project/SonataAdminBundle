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

namespace SensioLabs\AdminBundle\Security\Handler;

use SensioLabs\AdminBundle\Admin\AdminInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class RoleSecurityHandler implements SecurityHandlerInterface
{
    /**
     * @var string[]
     */
    private array $superAdminRoles;

    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        string $superAdminRole,
    ) {
        $this->superAdminRoles = [$superAdminRole];
    }

    public function isGranted(AdminInterface $admin, string $attribute, ?object $object = null): bool
    {
        $useAll = $this->hasOnlyAdminRole($attribute);
        $mappedAttributes = $this->mapAttribute($attribute, $admin);
        $allRole = \sprintf($this->getBaseRole($admin), 'ALL');

        try {
            return $this->isAnyGranted($this->superAdminRoles)
                || $this->isAnyGranted($mappedAttributes, $object)
                || $useAll && $this->authorizationChecker->isGranted($allRole, $object);
        } catch (AuthenticationCredentialsNotFoundException) {
            return false;
        }
    }

    public function getBaseRole(AdminInterface $admin): string
    {
        return \sprintf('ROLE_%s_%%s', str_replace('.', '_', strtoupper($admin->getCode())));
    }

    public function buildSecurityInformation(AdminInterface $admin): array
    {
        return [];
    }

    public function createObjectSecurity(AdminInterface $admin, object $object): void
    {
    }

    public function deleteObjectSecurity(AdminInterface $admin, object $object): void
    {
    }

    /**
     * @param array<string|Expression> $attributes
     */
    private function isAnyGranted(array $attributes, ?object $subject = null): bool
    {
        foreach ($attributes as $attribute) {
            if ($this->authorizationChecker->isGranted($attribute, $subject)) {
                return true;
            }
        }

        return false;
    }

    private function hasOnlyAdminRole(string $attribute): bool
    {
        // If the attribute is not already a ROLE_ we generate the related role.
        return !str_starts_with($attribute, 'ROLE_');
    }

    /**
     * @param AdminInterface<object> $admin
     *
     * @return array<string|Expression>
     */
    private function mapAttribute(string $attribute, AdminInterface $admin): array
    {
        if (str_starts_with($attribute, 'ROLE_')) {
            return [$attribute];
        }

        $mappedAttributes = [];
        $baseRole = $this->getBaseRole($admin);

        $mappedAttributes[] = \sprintf($baseRole, $attribute);

        foreach ($admin->getSecurityInformation() as $role => $permissions) {
            if (\in_array($attribute, $permissions, true)) {
                $mappedAttributes[] = \sprintf($baseRole, $role);
            }
        }

        return array_unique($mappedAttributes);
    }
}
