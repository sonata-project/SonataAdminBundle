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

use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface AclSecurityHandlerInterface extends SecurityHandlerInterface
{
    /**
     * Set the permissions not related to an object instance and also to be available when objects do not exist.
     *
     * @abstract
     *
     * @param array $permissions
     */
    public function setAdminPermissions(array $permissions);

    /**
     * Return the permissions not related to an object instance and also to be available when objects do not exist.
     *
     * @abstract
     *
     * @return array
     */
    public function getAdminPermissions();

    /**
     * Set the permissions related to an object instance.
     *
     * @abstract
     *
     * @param array $permissions
     */
    public function setObjectPermissions(array $permissions);

    /**
     * Return the permissions related to an object instance.
     *
     * @abstract
     *
     * @return array
     */
    public function getObjectPermissions();

    /**
     * Get the ACL for the passed object identity.
     *
     * @abstract
     *
     * @param ObjectIdentityInterface $objectIdentity
     *
     * @return null|AclInterface or NULL if not found
     */
    public function getObjectAcl(ObjectIdentityInterface $objectIdentity);

    /**
     * Find the ACLs for the passed object identities.
     *
     * @abstract
     *
     * @param \Traversable $oids a collection of ObjectIdentityInterface implementations
     * @param array        $sids an array of SecurityIdentityInterface implementations
     *
     * @throws \Exception
     *
     * @return \SplObjectStorage mapping the passed object identities to ACLs
     */
    public function findObjectAcls(\Traversable $oids, array $sids = array());

    /**
     * Add an object owner ACE to the object ACL.
     *
     * @abstract
     *
     * @param AclInterface         $acl
     * @param UserSecurityIdentity $securityIdentity
     */
    public function addObjectOwner(AclInterface $acl, UserSecurityIdentity $securityIdentity = null);

    /**
     * Add the object class ACE's to the object ACL.
     *
     * @param AclInterface $acl
     * @param array        $roleInformation
     */
    public function addObjectClassAces(AclInterface $acl, array $roleInformation = array());

    /**
     * Create an object ACL.
     *
     * @abstract
     *
     * @param ObjectIdentityInterface $objectIdentity
     *
     * @return AclInterface
     */
    public function createAcl(ObjectIdentityInterface $objectIdentity);

    /**
     * Update the ACL.
     *
     * @abstract
     *
     * @param AclInterface $acl
     */
    public function updateAcl(AclInterface $acl);

    /**
     * Delete the ACL.
     *
     * @abstract
     *
     * @param ObjectIdentityInterface $objectIdentity
     */
    public function deleteAcl(ObjectIdentityInterface $objectIdentity);

    /**
     * Helper method to find the index of a class ACE for a role.
     *
     * @param AclInterface $acl
     * @param string       $role
     *
     * @return mixed index if found, FALSE if not found
     */
    public function findClassAceIndexByRole(AclInterface $acl, $role);

    /**
     * Helper method to find the index of a class ACE for a username.
     *
     * @param AclInterface $acl
     * @param string       $username
     *
     * @return mixed index if found, FALSE if not found
     */
    public function findClassAceIndexByUsername(AclInterface $acl, $username);
}
