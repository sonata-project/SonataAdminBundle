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
use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Sonata\AdminBundle\Admin\AdminInterface;

class AclSecurityHandler implements SecurityHandlerInterface
{
    protected $securityContext;
    protected $aclProvider;
    protected $superAdminRoles;
    protected $adminPermissions;

    /**
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     * @param array $superAdminRoles
     */
    public function __construct(SecurityContextInterface $securityContext, AclProviderInterface $aclProvider, array $superAdminRoles)
    {
        $this->securityContext = $securityContext;
        $this->aclProvider = $aclProvider;
        $this->superAdminRoles = $superAdminRoles;
    }

    /**
     * Set the permissions not related to an object instance and also to be available when objects do not exist
     *
     * @param array $permissions
     */
    public function setAdminPermissions(array $permissions)
    {
        $this->adminPermissions = $permissions;
    }

    /**
     * Return the permissions not related to an object instance and also to be available when objects do not exist
     *
     * @return array
     */
    public function getAdminPermissions()
    {
        return $this->adminPermissions;
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        if (!is_array($attributes)) {
            $attributes = array($attributes);
        }

        try {
            return $this->securityContext->isGranted($this->superAdminRoles) || $this->securityContext->isGranted($attributes, $object);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseRole(AdminInterface $admin)
    {
        return 'ROLE_'.str_replace('.', '_', strtoupper($admin->getCode())).'_%s';
    }

    /**
     * {@inheritDoc}
     */
    public function buildSecurityInformation(AdminInterface $admin)
    {
        $baseRole = $this->getBaseRole($admin);

        $results = array();
        foreach ($admin->getSecurityInformation() as $role => $permissions) {
            $results[sprintf($baseRole, $role)] = $permissions;
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function createObjectOwner(AdminInterface $admin, $object)
    {
        $acl = $this->getNewObjectOwnerAcl($admin, $object);
        $this->updateAcl($acl);
    }

    /**
     * Get a new ACL with an object ACE where the currently logged in user is set as owner
     *
     * @param AdminInterface $admin
     * @param object $object
     * @return Symfony\Component\Security\Acl\Model\AclInterface
     */
    public function getNewObjectOwnerAcl(AdminInterface $admin, $object)
    {
        // creating the ACL, fe.
        // Comment 1 ACL
        $objectIdentity = ObjectIdentity::fromDomainObject($object);
        $acl = $this->aclProvider->createAcl($objectIdentity);

        // inherit class ACE's from the admin ACL, fe.
        // Comment admin ACL
        //  - Comment 1 ACL
        $parentOid = ObjectIdentity::fromDomainObject($admin);
        $parentAcl = $this->aclProvider->findAcl($parentOid);
        $acl->setParentAcl($parentAcl);

        // retrieving the security identity of the currently logged-in user
        $user = $this->securityContext->getToken()->getUser();
        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        // grant owner access
        $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);

        return $acl;
    }

    /**
     * Update the ACL
     */
    public function updateAcl(AclInterface $acl)
    {
        $this->aclProvider->updateAcl($acl);
    }
}