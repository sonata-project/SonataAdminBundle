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
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface AclSecurityHandlerInterface extends SecurityHandlerInterface
{
    /**
     * Set the permissions not related to an object instance and also to be available when objects do not exist.
     *
     * @param string[] $permissions
     */
    public function setAdminPermissions(array $permissions): void;

    /**
     * Return the permissions not related to an object instance and also to be available when objects do not exist.
     *
     * @return string[]
     */
    public function getAdminPermissions(): array;

    /**
     * Set the permissions related to an object instance.
     *
     * @param string[] $permissions
     */
    public function setObjectPermissions(array $permissions): void;

    /**
     * Return the permissions related to an object instance.
     *
     * @return string[]
     */
    public function getObjectPermissions(): array;

    /**
     * Get the ACL for the passed object identity.
     */
    public function getObjectAcl(ObjectIdentityInterface $objectIdentity): ?MutableAclInterface;

    /**
     * Find the ACLs for the passed object identities.
     *
     * @param \Traversable<ObjectIdentityInterface> $oids
     * @param SecurityIdentityInterface[]           $sids
     *
     * @throws \Exception
     *
     * @return \SplObjectStorage<ObjectIdentityInterface, MutableAclInterface> mapping the passed object identities to ACLs
     */
    public function findObjectAcls(\Traversable $oids, array $sids = []): \SplObjectStorage;

    /**
     * Add an object owner ACE to the object ACL.
     */
    public function addObjectOwner(MutableAclInterface $acl, UserSecurityIdentity $securityIdentity): void;

    /**
     * Add the object class ACE's to the object ACL.
     *
     * @param AdminInterface<object> $admin
     */
    public function addObjectClassAces(MutableAclInterface $acl, AdminInterface $admin): void;

    /**
     * Create an object ACL.
     */
    public function createAcl(ObjectIdentityInterface $objectIdentity): MutableAclInterface;

    /**
     * Update the ACL.
     */
    public function updateAcl(MutableAclInterface $acl): void;

    /**
     * Delete the ACL.
     */
    public function deleteAcl(ObjectIdentityInterface $objectIdentity): void;

    /**
     * Helper method to find the index of a class ACE for a role.
     *
     * @return int|false index if found, FALSE if not found
     */
    public function findClassAceIndexByRole(MutableAclInterface $acl, string $role);

    /**
     * Helper method to find the index of a class ACE for a username.
     *
     * @return int|false index if found, FALSE if not found
     */
    public function findClassAceIndexByUsername(MutableAclInterface $acl, string $username);
}
