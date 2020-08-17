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
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AclSecurityHandler implements AclSecurityHandlerInterface
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var MutableAclProviderInterface
     */
    protected $aclProvider;

    /**
     * @var array
     */
    protected $superAdminRoles = [];

    /**
     * @var array
     */
    protected $adminPermissions = [];

    /**
     * @var array
     */
    protected $objectPermissions = [];

    /**
     * @var string
     */
    protected $maskBuilderClass;

    /**
     * @param string $maskBuilderClass
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        MutableAclProviderInterface $aclProvider,
        $maskBuilderClass,
        array $superAdminRoles
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->aclProvider = $aclProvider;
        $this->maskBuilderClass = $maskBuilderClass;
        $this->superAdminRoles = $superAdminRoles;
    }

    public function setAdminPermissions(array $permissions)
    {
        $this->adminPermissions = $permissions;
    }

    public function getAdminPermissions()
    {
        return $this->adminPermissions;
    }

    public function setObjectPermissions(array $permissions)
    {
        $this->objectPermissions = $permissions;
    }

    public function getObjectPermissions()
    {
        return $this->objectPermissions;
    }

    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        if (!\is_array($attributes)) {
            $attributes = [$attributes];
        }

        try {
            return $this->isAnyGranted($this->superAdminRoles) ||
                $this->isAnyGranted($attributes, $object);
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
        $baseRole = $this->getBaseRole($admin);

        $results = [];
        foreach ($admin->getSecurityInformation() as $role => $permissions) {
            $results[sprintf($baseRole, $role)] = $permissions;
        }

        return $results;
    }

    public function createObjectSecurity(AdminInterface $admin, $object)
    {
        // retrieving the ACL for the object identity
        $objectIdentity = ObjectIdentity::fromDomainObject($object);
        $acl = $this->getObjectAcl($objectIdentity);
        if (null === $acl) {
            $acl = $this->createAcl($objectIdentity);
        }

        // retrieving the security identity of the currently logged-in user
        $user = $this->tokenStorage->getToken()->getUser();
        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        $this->addObjectOwner($acl, $securityIdentity);
        $this->addObjectClassAces($acl, $this->buildSecurityInformation($admin));
        $this->updateAcl($acl);
    }

    public function deleteObjectSecurity(AdminInterface $admin, $object)
    {
        $objectIdentity = ObjectIdentity::fromDomainObject($object);
        $this->deleteAcl($objectIdentity);
    }

    public function getObjectAcl(ObjectIdentityInterface $objectIdentity)
    {
        try {
            $acl = $this->aclProvider->findAcl($objectIdentity);
            // todo - remove `assert` statement after https://github.com/phpstan/phpstan-symfony/pull/92 is released
            \assert($acl instanceof MutableAclInterface);
        } catch (AclNotFoundException $e) {
            return null;
        }

        return $acl;
    }

    public function findObjectAcls(\Traversable $oids, array $sids = [])
    {
        try {
            $acls = $this->aclProvider->findAcls(iterator_to_array($oids), $sids);
        } catch (NotAllAclsFoundException $e) {
            $acls = $e->getPartialResult();
        } catch (AclNotFoundException $e) { // if only one oid, this error is thrown
            $acls = new \SplObjectStorage();
        }

        return $acls;
    }

    /**
     * NEXT_MAJOR: change signature to `addObjectOwner(MutableAclInterface $acl, ?UserSecurityIdentity $securityIdentity = null): void`.
     *
     * @param MutableAclInterface $acl
     *
     * @return void
     */
    public function addObjectOwner(AclInterface $acl, ?UserSecurityIdentity $securityIdentity = null)
    {
        // NEXT_MAJOR: remove `if` condition
        if (!$acl instanceof MutableAclInterface) {
            throw new \TypeError(sprintf(
                'Argument 1 passed to "%s()" must implement "%s".',
                __METHOD__,
                MutableAclInterface::class
            ));
        }
        if (false === $this->findClassAceIndexByUsername($acl, $securityIdentity->getUsername())) {
            // only add if not already exists
            $acl->insertObjectAce($securityIdentity, \constant("$this->maskBuilderClass::MASK_OWNER"));
        }
    }

    /**
     * Add the object class ACE's to the object ACL.
     *
     * NEXT_MAJOR: change signature to `addObjectClassAces(MutableAclInterface $acl, array $roleInformation = []): void`.
     *
     * @param MutableAclInterface $acl
     *
     * @return void
     */
    public function addObjectClassAces(AclInterface $acl, array $roleInformation = [])
    {
        // NEXT_MAJOR: remove `assert` statement
        \assert($acl instanceof MutableAclInterface);
        $builder = new $this->maskBuilderClass();

        foreach ($roleInformation as $role => $permissions) {
            $aceIndex = $this->findClassAceIndexByRole($acl, $role);
            $hasRole = false;

            foreach ($permissions as $permission) {
                // add only the object permissions
                if (\in_array($permission, $this->getObjectPermissions(), true)) {
                    $builder->add($permission);
                    $hasRole = true;
                }
            }

            if ($hasRole) {
                if (false === $aceIndex) {
                    $acl->insertClassAce(new RoleSecurityIdentity($role), $builder->get());
                } else {
                    $acl->updateClassAce($aceIndex, $builder->get());
                }

                $builder->reset();
            } elseif (false !== $aceIndex) {
                $acl->deleteClassAce($aceIndex);
            }
        }
    }

    /**
     * NEXT_MAJOR: change signature to `createAcl(ObjectIdentityInterface $objectIdentity): MutableAclInterface`.
     *
     * @return MutableAclInterface
     */
    public function createAcl(ObjectIdentityInterface $objectIdentity)
    {
        return $this->aclProvider->createAcl($objectIdentity);
    }

    /**
     * NEXT_MAJOR: change signature to `updateAcl(MutableAclInterface $acl): void`.
     *
     * @param MutableAclInterface $acl
     *
     * @@return void
     */
    public function updateAcl(AclInterface $acl)
    {
        // NEXT_MAJOR: remove `assert` statement
        \assert($acl instanceof MutableAclInterface);
        $this->aclProvider->updateAcl($acl);
    }

    public function deleteAcl(ObjectIdentityInterface $objectIdentity)
    {
        $this->aclProvider->deleteAcl($objectIdentity);
    }

    /**
     * NEXT_MAJOR: change signature to `findClassAceIndexByRole(MutableAclInterface $acl, string $role): int|string|false`.
     *
     * @param MutableAclInterface $acl
     *
     * @return array-key|false
     */
    public function findClassAceIndexByRole(AclInterface $acl, $role)
    {
        foreach ($acl->getClassAces() as $index => $entry) {
            if ($entry->getSecurityIdentity() instanceof RoleSecurityIdentity && $entry->getSecurityIdentity()->getRole() === $role) {
                return $index;
            }
        }

        return false;
    }

    /**
     * NEXT_MAJOR: change signature to `findClassAceIndexByUsername(MutableAclInterface $acl, string $username): int|string|false`.
     *
     * @param MutableAclInterface $acl
     *
     * @return array-key|false
     */
    public function findClassAceIndexByUsername(AclInterface $acl, $username)
    {
        foreach ($acl->getClassAces() as $index => $entry) {
            if ($entry->getSecurityIdentity() instanceof UserSecurityIdentity && $entry->getSecurityIdentity()->getUsername() === $username) {
                return $index;
            }
        }

        return false;
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
