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
     * @var array
     */
    private $superAdminRoles = [];

    /**
     * @var array
     */
    private $adminPermissions = [];

    /**
     * @var array
     */
    private $objectPermissions = [];

    /**
     * @var string
     */
    private $maskBuilderClass;

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
        $user = $this->tokenStorage->getToken()->getUser();
        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        $this->addObjectOwner($acl, $securityIdentity);
        $this->addObjectClassAces($acl, $this->buildSecurityInformation($admin));
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
            // todo - remove `assert` statement after https://github.com/phpstan/phpstan-symfony/pull/92 is released
            \assert($acl instanceof MutableAclInterface);
        } catch (AclNotFoundException $e) {
            return null;
        }

        return $acl;
    }

    public function findObjectAcls(\Traversable $oids, array $sids = []): \SplObjectStorage
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

    public function addObjectOwner(MutableAclInterface $acl, ?UserSecurityIdentity $securityIdentity = null): void
    {
        if (false === $this->findClassAceIndexByUsername($acl, $securityIdentity->getUsername())) {
            // only add if not already exists
            $acl->insertObjectAce($securityIdentity, \constant("$this->maskBuilderClass::MASK_OWNER"));
        }
    }

    public function addObjectClassAces(MutableAclInterface $acl, array $roleInformation = []): void
    {
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

    public function findClassAceIndexByRole(MutableAclInterface $acl, $role)
    {
        foreach ($acl->getClassAces() as $index => $entry) {
            if ($entry->getSecurityIdentity() instanceof RoleSecurityIdentity && $entry->getSecurityIdentity()->getRole() === $role) {
                return $index;
            }
        }

        return false;
    }

    public function findClassAceIndexByUsername(MutableAclInterface $acl, string $username)
    {
        foreach ($acl->getClassAces() as $index => $entry) {
            if ($entry->getSecurityIdentity() instanceof UserSecurityIdentity && $entry->getSecurityIdentity()->getUsername() === $username) {
                return $index;
            }
        }

        return false;
    }

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
