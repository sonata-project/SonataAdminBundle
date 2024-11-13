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

namespace Sonata\AdminBundle\Security\Handler;

use Sonata\AdminBundle\Admin\AdminInterface;
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
    private array $superAdminRoles = [];

    /**
     * @param string|string[] $superAdminRoles
     */
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        $superAdminRoles,
    ) {
        // NEXT_MAJOR: Keep only the elseif part and add typehint.
        if (\is_array($superAdminRoles)) {
            @trigger_error(\sprintf(
                'Passing an array as argument 1 of "%s()" is deprecated since sonata-project/admin-bundle 4.6'
                .' and will throw an error in 5.0. You MUST pass a string instead.',
                __METHOD__
            ), \E_USER_DEPRECATED);

            $this->superAdminRoles = $superAdminRoles;
        } elseif (\is_string($superAdminRoles)) {
            $this->superAdminRoles = [$superAdminRoles];
        } else {
            throw new \TypeError(\sprintf(
                'Argument 1 passed to "%s()" must be of type "array" or "string", "%s" given.',
                __METHOD__,
                \gettype($superAdminRoles)
            ));
        }
    }

    public function isGranted(AdminInterface $admin, $attributes, ?object $object = null): bool
    {
        // NEXT_MAJOR: Remove this and add string typehint to $attributes and rename it $attribute.
        if (\is_array($attributes)) {
            @trigger_error(\sprintf(
                'Passing an array as argument 1 of "%s()" is deprecated since sonata-project/admin-bundle 4.6'
                .' and will throw an error in 5.0. You MUST pass a string instead.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        // NEXT_MAJOR: Remove this check.
        if (!\is_array($attributes)) {
            $attributes = [$attributes];
        }

        $useAll = $this->hasOnlyAdminRoles($attributes);
        $attributes = $this->mapAttributes($attributes, $admin);
        $allRole = \sprintf($this->getBaseRole($admin), 'ALL');

        try {
            // NEXT_MAJOR: Remove the method isAnyGranted and use $this->authorizationChecker->isGranted instead.
            return $this->isAnyGranted($this->superAdminRoles)
                || $this->isAnyGranted($attributes, $object)
                || $useAll && $this->isAnyGranted([$allRole], $object);
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

    /**
     * @param array<string|Expression> $attributes
     */
    private function hasOnlyAdminRoles(array $attributes): bool
    {
        // NEXT_MAJOR: Change the foreach to a single check.
        foreach ($attributes as $attribute) {
            // If the attribute is not already a ROLE_ we generate the related role.
            if (\is_string($attribute) && !str_starts_with($attribute, 'ROLE_')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string|Expression> $attributes
     * @param AdminInterface<object>   $admin
     *
     * @return array<string|Expression>
     */
    private function mapAttributes(array $attributes, AdminInterface $admin): array
    {
        $mappedAttributes = [];

        foreach ($attributes as $attribute) {
            if (!\is_string($attribute) || str_starts_with($attribute, 'ROLE_')) {
                $mappedAttributes[] = $attribute;

                continue;
            }

            $baseRole = $this->getBaseRole($admin);

            $mappedAttributes[] = \sprintf($baseRole, $attribute);

            foreach ($admin->getSecurityInformation() as $role => $permissions) {
                if (\in_array($attribute, $permissions, true)) {
                    $mappedAttributes[] = \sprintf($baseRole, $role);
                }
            }
        }

        return array_unique($mappedAttributes);
    }
}
