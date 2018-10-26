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
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
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
    protected $superAdminRoles;

    /**
     * @var array
     */
    protected $adminPermissions;

    /**
     * @var array
     */
    protected $objectPermissions;

    /**
     * @var string
     */
    protected $maskBuilderClass;

    /**
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param string                        $maskBuilderClass
     */
    public function __construct(
        $tokenStorage,
        $authorizationChecker,
        MutableAclProviderInterface $aclProvider,
        $maskBuilderClass,
        array $superAdminRoles
    ) {
        // NEXT_MAJOR: Move TokenStorageInterface check to method signature
        if (!$tokenStorage instanceof TokenStorageInterface) {
            throw new \InvalidArgumentException('Argument 1 should be an instance of Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        }
        // NEXT_MAJOR: Move AuthorizationCheckerInterface check to method signature
        if (!$authorizationChecker instanceof AuthorizationCheckerInterface) {
            throw new \InvalidArgumentException('Argument 2 should be an instance of Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        }

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
            return $this->authorizationChecker->isGranted($this->superAdminRoles) || $this->authorizationChecker->isGranted($attributes, $object);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        }
    }

    public function getBaseRole(AdminInterface $admin)
    {
        return 'ROLE_'.str_replace('.', '_', strtoupper($admin->getCode())).'_%s';
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
        } catch (AclNotFoundException $e) {
            return;
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

    public function addObjectOwner(AclInterface $acl, UserSecurityIdentity $securityIdentity = null)
    {
        if (false === $this->findClassAceIndexByUsername($acl, $securityIdentity->getUsername())) {
            // only add if not already exists
            $acl->insertObjectAce($securityIdentity, \constant("$this->maskBuilderClass::MASK_OWNER"));
        }
    }

    public function addObjectClassAces(AclInterface $acl, array $roleInformation = [])
    {
        $builder = new $this->maskBuilderClass();

        foreach ($roleInformation as $role => $permissions) {
            $aceIndex = $this->findClassAceIndexByRole($acl, $role);
            $hasRole = false;

            foreach ($permissions as $permission) {
                // add only the object permissions
                if (\in_array($permission, $this->getObjectPermissions())) {
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

    public function createAcl(ObjectIdentityInterface $objectIdentity)
    {
        return $this->aclProvider->createAcl($objectIdentity);
    }

    public function updateAcl(AclInterface $acl)
    {
        $this->aclProvider->updateAcl($acl);
    }

    public function deleteAcl(ObjectIdentityInterface $objectIdentity)
    {
        $this->aclProvider->deleteAcl($objectIdentity);
    }

    public function findClassAceIndexByRole(AclInterface $acl, $role)
    {
        foreach ($acl->getClassAces() as $index => $entry) {
            if ($entry->getSecurityIdentity() instanceof RoleSecurityIdentity && $entry->getSecurityIdentity()->getRole() === $role) {
                return $index;
            }
        }

        return false;
    }

    public function findClassAceIndexByUsername(AclInterface $acl, $username)
    {
        foreach ($acl->getClassAces() as $index => $entry) {
            if ($entry->getSecurityIdentity() instanceof UserSecurityIdentity && $entry->getSecurityIdentity()->getUsername() === $username) {
                return $index;
            }
        }

        return false;
    }
}
