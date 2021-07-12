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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class RoleSecurityHandler implements SecurityHandlerInterface
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var array
     */
    protected $superAdminRoles = [];

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct($authorizationChecker, array $superAdminRoles)
    {
        // NEXT_MAJOR: Move AuthorizationCheckerInterface check to method signature
        if (!$authorizationChecker instanceof AuthorizationCheckerInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 should be an instance of %s',
                AuthorizationCheckerInterface::class
            ));
        }

        $this->authorizationChecker = $authorizationChecker;
        $this->superAdminRoles = $superAdminRoles;
    }

    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        if (!\is_array($attributes)) {
            $attributes = [$attributes];
        }

        $useAll = false;
        foreach ($attributes as $pos => $attribute) {
            // If the attribute is not already a ROLE_ we generate the related role.
            if (0 !== strpos($attribute, 'ROLE_')) {
                $attributes[$pos] = sprintf($this->getBaseRole($admin), $attribute);
                // All the admin related role are available when you have the `_ALL` role.
                $useAll = true;
            }
        }

        $allRole = sprintf($this->getBaseRole($admin), 'ALL');

        try {
            return $this->isAnyGranted($this->superAdminRoles)
                || $this->isAnyGranted($attributes, $object)
                || $useAll && $this->isAnyGranted([$allRole], $object);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        }
    }

    public function getBaseRole(AdminInterface $admin)
    {
        return sprintf('ROLE_%s_%%s', str_replace('.', '_', strtoupper($admin->getCode())));
    }

    public function buildSecurityInformation(AdminInterface $admin)
    {
        return [];
    }

    public function createObjectSecurity(AdminInterface $admin, $object)
    {
    }

    public function deleteObjectSecurity(AdminInterface $admin, $object)
    {
    }

    private function isAnyGranted(array $attributes, $subject = null): bool
    {
        foreach ($attributes as $attribute) {
            if ($this->authorizationChecker->isGranted($attribute, $subject)) {
                return true;
            }
        }

        return false;
    }
}
