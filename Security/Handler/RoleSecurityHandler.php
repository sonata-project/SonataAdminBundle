<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Security\Handler;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Sonata\AdminBundle\Admin\AdminInterface;

class RoleSecurityHandler implements SecurityHandlerInterface
{
    protected $securityContext;

    protected $superAdminRoles;

    /**
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     * @param array                                                     $superAdminRoles
     */
    public function __construct(SecurityContextInterface $securityContext, array $superAdminRoles)
    {
        $this->securityContext = $securityContext;
        $this->superAdminRoles = $superAdminRoles;
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        if (!is_array($attributes)) {
            $attributes = array($attributes);
        }

        $roles = $this->getAttributeRoles($admin, $attributes);

        try {
            return $this->securityContext->isGranted($this->superAdminRoles) || $this->securityContext->isGranted($roles);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Get roles for attributes from admin security infomation
     */
    private function getAttributeRoles(AdminInterface $admin, array $attributes)
    {
        $baseRole = $this->getBaseRole($admin);

        $results = array();
        foreach ($admin->getSecurityInformation() as $role => $permissions) {
            if (count(array_intersect($attributes, $permissions)) > 0) {
                $results[] = sprintf($baseRole, $role);
            }
        }

        return $results;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getBaseRole(AdminInterface $admin)
    {
        return 'ROLE_' . str_replace('.', '_', strtoupper($admin->getCode())) . '_%s';
    }

    /**
     * {@inheritDoc}
     */
    public function buildSecurityInformation(AdminInterface $admin)
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function createObjectSecurity(AdminInterface $admin, $object)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function deleteObjectSecurity(AdminInterface $admin, $object)
    {
    }
}
