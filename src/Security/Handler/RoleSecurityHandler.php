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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class RoleSecurityHandler implements SecurityHandlerInterface
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var string
     */
    private $superAdminRole;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, string $superAdminRole)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->superAdminRole = $superAdminRole;
    }

    public function isGranted(AdminInterface $admin, string $attribute, ?object $object = null): bool
    {
        $useAll = false;
        // If the attribute is not already a ROLE_ we generate the related role.
        if (0 !== strpos($attribute, 'ROLE_')) {
            $attribute = sprintf($this->getBaseRole($admin), $attribute);
            // All the admin related role are available when you have the `_ALL` role.
            $useAll = true;
        }

        $allRole = sprintf($this->getBaseRole($admin), 'ALL');

        try {
            return $this->authorizationChecker->isGranted($this->superAdminRole)
                || $this->authorizationChecker->isGranted($attribute, $object)
                || $useAll && $this->authorizationChecker->isGranted($allRole, $object);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        }
    }

    public function getBaseRole(AdminInterface $admin): string
    {
        return sprintf('ROLE_%s_%%s', str_replace('.', '_', strtoupper($admin->getCode())));
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
}
