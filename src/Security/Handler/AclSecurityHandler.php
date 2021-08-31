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
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class AclSecurityHandler implements AclSecurityHandlerInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var MutableAclProviderInterface
     */
    private $aclProvider;

    /**
     * @var string[]
     */
    private $superAdminRoles = [];

    /**
     * @var string[]
     */
    private $adminPermissions = [];

    /**
     * @var string[]
     */
    private $objectPermissions = [];

    /**
     * @var string
     * @phpstan-var class-string
     */
    private $maskBuilderClass;

    /**
     * @param string[] $superAdminRoles
     * @phpstan-param class-string $maskBuilderClass
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        MutableAclProviderInterface $aclProvider,
        string $maskBuilderClass,
        array $superAdminRoles
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->aclProvider = $aclProvider;
        $this->maskBuilderClass = $maskBuilderClass;
        $this->superAdminRoles = $superAdminRoles;
    }

    public function setAdminPermissions(array $permissions): void
    {
        $this->adminPermissions = $permissions;
    }

    public function getAdminPermissions(): array
    {
        return $this->adminPermissions;
    }

    public function setObjectPermissions(array $permissions): void
    {
        $this->objectPermissions = $permissions;
    }

    public function getObjectPermissions(): array
    {
        return $this->objectPermissions;
    }

    public function isGranted(AdminInterface $admin, $attributes, ?object $object = null): bool
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

    public function getBaseRole(AdminInterface $admin): string
    {
        return sprintf('ROLE_%s_%%s', str_replace('.', '_', strtoupper($admin->getCode())));
    }

    public function buildSecurityInformation(AdminInterface $admin): array
    {
        $baseRole = $this->getBaseRole($admin);

        $results = [];
        foreach ($admin->getSecurityInformation() as $role => $permissions) {
            $results[sprintf($baseRole, $role)] = $permissions;
        }

        return $results;
    }

    public function createObjectSecurity(AdminInterface $admin, object $object): void
    {
        // retrieving the ACL for the object identity
        $objectIdentity = ObjectIdentity::fromDomainObject($object);
        $acl = $this->getObjectAcl($objectIdentity);
        if (null === $acl) {
            $acl = $this->createAcl($objectIdentity);
        }

        // retrieving the security identity of the currently logged-in user
        $token = $this->tokenStorage->getToken();
        \assert(null !== $token);
        $user = $token->getUser();
        \assert($user instanceof UserInterface);
        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        $this->addObjectOwner($acl, $securityIdentity);
        $this->addObjectClassAces($acl, $admin);
        $this->updateAcl($acl);
    }

    public function deleteObjectSecurity(AdminInterface $admin, object $object): void
    {
        $objectIdentity = ObjectIdentity::fromDomainObject($object);
        $this->deleteAcl($objectIdentity);
    }

    public function getObjectAcl(ObjectIdentityInterface $objectIdentity): ?MutableAclInterface
    {
        try {
            $acl = $this->aclProvider->findAcl($objectIdentity);
        } catch (AclNotFoundException $e) {
            return null;
        }

        return $acl;
    }

    public function findObjectAcls(\Traversable $oids, array $sids = []): \SplObjectStorage
    {
        try {
            /** @var \SplObjectStorage<ObjectIdentityInterface, MutableAclInterface> $acls */
            $acls = $this->aclProvider->findAcls(iterator_to_array($oids), $sids);
        } catch (NotAllAclsFoundException $e) {
            /** @var \SplObjectStorage<ObjectIdentityInterface, MutableAclInterface> $acls */
            $acls = $e->getPartialResult();
        } catch (AclNotFoundException $e) { // if only one oid, this error is thrown
            /** @var \SplObjectStorage<ObjectIdentityInterface, MutableAclInterface> $acls */
            $acls = new \SplObjectStorage();
        }

        return $acls;
    }

    public function addObjectOwner(MutableAclInterface $acl, UserSecurityIdentity $securityIdentity): void
    {
        if (false === $this->findClassAceIndexByUsername($acl, $securityIdentity->getUsername())) {
            // only add if not already exists
            $acl->insertObjectAce($securityIdentity, \constant("$this->maskBuilderClass::MASK_OWNER"));
        }
    }

    public function addObjectClassAces(MutableAclInterface $acl, AdminInterface $admin): void
    {
        $builder = new $this->maskBuilderClass();

        foreach ($this->buildSecurityInformation($admin) as $role => $permissions) {
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

    public function createAcl(ObjectIdentityInterface $objectIdentity): MutableAclInterface
    {
        return $this->aclProvider->createAcl($objectIdentity);
    }

    public function updateAcl(MutableAclInterface $acl): void
    {
        $this->aclProvider->updateAcl($acl);
    }

    public function deleteAcl(ObjectIdentityInterface $objectIdentity): void
    {
        $this->aclProvider->deleteAcl($objectIdentity);
    }

    public function findClassAceIndexByRole(MutableAclInterface $acl, string $role)
    {
        foreach ($acl->getClassAces() as $index => $entry) {
            $securityIdentity = $entry->getSecurityIdentity();
            if ($securityIdentity instanceof RoleSecurityIdentity && $securityIdentity->getRole() === $role) {
                return $index;
            }
        }

        return false;
    }

    public function findClassAceIndexByUsername(MutableAclInterface $acl, string $username)
    {
        foreach ($acl->getClassAces() as $index => $entry) {
            $securityIdentity = $entry->getSecurityIdentity();
            if ($securityIdentity instanceof UserSecurityIdentity && $securityIdentity->getUsername() === $username) {
                return $index;
            }
        }

        return false;
    }

    /**
     * @param string[] $attributes
     */
    private function isAnyGranted(array $attributes, ?object $subject = null): bool
    {
        foreach ($attributes as $attribute) {
            if ($this->authorizationChecker->isGranted($attribute, $subject)) {
                return true;
            }
        }

        return false;
    }
}
