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

use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\AclInterface;
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
     *
     * @return void
     */
    public function setAdminPermissions(array $permissions);

    /**
     * Return the permissions not related to an object instance and also to be available when objects do not exist.
     *
     * @return string[]
     */
    public function getAdminPermissions();

    /**
     * Set the permissions related to an object instance.
     *
     * @param string[] $permissions
     *
     * @return void
     */
    public function setObjectPermissions(array $permissions);

    /**
     * Return the permissions related to an object instance.
     *
     * @return string[]
     */
    public function getObjectPermissions();

    /**
     * Get the ACL for the passed object identity.
     *
     * @return MutableAclInterface|null
     */
    public function getObjectAcl(ObjectIdentityInterface $objectIdentity);

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
    public function findObjectAcls(\Traversable $oids, array $sids = []);

    /**
     * Add an object owner ACE to the object ACL.
     *
     * NEXT_MAJOR: change signature to `addObjectOwner(MutableAclInterface $acl, ?UserSecurityIdentity $securityIdentity = null): void`.
     *
     * @param MutableAclInterface $acl
     *
     * @return void
     */
    public function addObjectOwner(AclInterface $acl, ?UserSecurityIdentity $securityIdentity = null);

    /**
     * Add the object class ACE's to the object ACL.
     *
     * NEXT_MAJOR: change signature to `addObjectClassAces(MutableAclInterface $acl, array $roleInformation = []): void`.
     *
     * @param MutableAclInterface     $acl
     * @param array<string, string[]> $roleInformation
     *
     * @return void
     */
    public function addObjectClassAces(AclInterface $acl, array $roleInformation = []);

    /**
     * Create an object ACL.
     *
     * NEXT_MAJOR: change signature to `createAcl(ObjectIdentityInterface $objectIdentity): MutableAclInterface`
     *
     * @return MutableAclInterface
     */
    public function createAcl(ObjectIdentityInterface $objectIdentity);

    /**
     * Update the ACL.
     *
     * NEXT_MAJOR: change signature to `updateAcl(MutableAclInterface $acl): void`
     *
     * @param MutableAclInterface $acl
     *
     * @return void
     */
    public function updateAcl(AclInterface $acl);

    /**
     * Delete the ACL.
     *
     * @return void
     */
    public function deleteAcl(ObjectIdentityInterface $objectIdentity);

    /**
     * Helper method to find the index of a class ACE for a role.
     *
     * NEXT_MAJOR: change signature to `findClassAceIndexByRole(MutableAclInterface $acl, string $role): array-key|false`
     *
     * @param string $role
     *
     * @return array-key|false index if found, FALSE if not found
     */
    public function findClassAceIndexByRole(AclInterface $acl, $role);

    /**
     * Helper method to find the index of a class ACE for a username.
     *
     * NEXT_MAJOR: change signature to `findClassAceIndexByUsername(MutableAclInterface $acl, string $username): array-key|false`
     *
     * @param string $username
     *
     * @return array-key|false index if found, FALSE if not found
     */
    public function findClassAceIndexByUsername(AclInterface $acl, $username);
}
