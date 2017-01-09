<?php

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
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class RoleSecurityHandler implements SecurityHandlerInterface
{
    /**
     * @var AuthorizationCheckerInterface|SecurityContextInterface
     */
    protected $authorizationChecker;

    /**
     * @var array
     */
    protected $superAdminRoles;

    /**
     * NEXT_MAJOR: Go back to signature class check when bumping requirements to SF 2.6+.
     *
     * @param AuthorizationCheckerInterface|SecurityContextInterface $authorizationChecker
     * @param array                                                  $superAdminRoles
     */
    public function __construct($authorizationChecker, array $superAdminRoles)
    {
        if (!$authorizationChecker instanceof AuthorizationCheckerInterface && !$authorizationChecker instanceof SecurityContextInterface) {
            throw new \InvalidArgumentException('Argument 1 should be an instance of Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface or Symfony\Component\Security\Core\SecurityContextInterface');
        }

        $this->authorizationChecker = $authorizationChecker;
        $this->superAdminRoles = $superAdminRoles;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        if (!is_array($attributes)) {
            $attributes = array($attributes);
        }

        foreach ($attributes as $pos => $attribute) {
            $attributes[$pos] = sprintf($this->getBaseRole($admin), $attribute);
        }

        $allRole = sprintf($this->getBaseRole($admin), 'ALL');

        try {
            return $this->authorizationChecker->isGranted($this->superAdminRoles)
                || $this->authorizationChecker->isGranted($attributes, $object)
                || $this->authorizationChecker->isGranted(array($allRole), $object);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRole(AdminInterface $admin)
    {
        return 'ROLE_'.str_replace('.', '_', strtoupper($admin->getCode())).'_%s';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSecurityInformation(AdminInterface $admin)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function createObjectSecurity(AdminInterface $admin, $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteObjectSecurity(AdminInterface $admin, $object)
    {
    }
}
