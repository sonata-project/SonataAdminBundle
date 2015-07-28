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
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class AclSecurityHandler implements AclSecurityHandlerInterface
{
    protected $securityContext;
    protected $aclProvider;
    protected $superAdminRoles;
    protected $adminPermissions;
    protected $objectPermissions;
    protected $maskBuilderClass;

    /**
     * @param \Symfony\Component\Security\Core\SecurityContextInterface         $securityContext
     * @param \Symfony\Component\Security\Acl\Model\MutableAclProviderInterface $aclProvider
     * @param string                                                            $maskBuilderClass
     * @param array                                                             $superAdminRoles
     */
    public function __construct(SecurityContextInterface $securityContext, MutableAclProviderInterface $aclProvider, $maskBuilderClass, array $superAdminRoles)
    {
        $this->securityContext  = $securityContext;
        $this->aclProvider      = $aclProvider;
        $this->maskBuilderClass = $maskBuilderClass;
        $this->superAdminRoles  = $superAdminRoles;
    }

    /**
     * {@inheritdoc}
     */
    public function setAdminPermissions(array $permissions)
    {
        $this->adminPermissions = $permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminPermissions()
    {
        return $this->adminPermissions;
    }

    /**
     * {@inheritdoc}
     */
    public function setObjectPermissions(array $permissions)
    {
        $this->objectPermissions = $permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectPermissions()
    {
        return $this->objectPermissions;
    }

    /**
     * {@inheritdoc}
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
        $baseRole = $this->getBaseRole($admin);

        $results = array();
        foreach ($admin->getSecurityInformation() as $role => $permissions) {
            $results[sprintf($baseRole, $role)] = $permissions;
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function createObjectSecurity(AdminInterface $admin, $object)
    {
        // retrieving the ACL for the object identity
        $objectIdentity = ObjectIdentity::fromDomainObject($object);
        $acl            = $this->getObjectAcl($objectIdentity);
        if (is_null($acl)) {
            $acl = $this->createAcl($objectIdentity);
        }

        // retrieving the security identity of the currently logged-in user
        $user             = $this->securityContext->getToken()->getUser();
        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        $this->addObjectOwner($acl, $securityIdentity);
        $this->addObjectClassAces($acl, $this->buildSecurityInformation($admin));
        $this->updateAcl($acl);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteObjectSecurity(AdminInterface $admin, $object)
    {
        $objectIdentity = ObjectIdentity::fromDomainObject($object);
        $this->deleteAcl($objectIdentity);
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectAcl(ObjectIdentityInterface $objectIdentity)
    {
        try {
            $acl = $this->aclProvider->findAcl($objectIdentity);
        } catch (AclNotFoundException $e) {
            return;
        }

        return $acl;
    }

    /**
     * {@inheritdoc}
     */
    public function findObjectAcls(\Traversable $oids, array $sids = array())
    {
        try {
            $acls = $this->aclProvider->findAcls(iterator_to_array($oids), $sids);
        } catch (\Exception $e) {
            if ($e instanceof NotAllAclsFoundException) {
                $acls = $e->getPartialResult();
            } elseif ($e instanceof AclNotFoundException) {
                // if only one oid, this error is thrown
                $acls = new \SplObjectStorage();
            } else {
                throw $e;
            }
        }

        return $acls;
    }

    /**
     * {@inheritdoc}
     */
    public function addObjectOwner(AclInterface $acl, UserSecurityIdentity $securityIdentity = null)
    {
        if (false === $this->findClassAceIndexByUsername($acl, $securityIdentity->getUsername())) {
            // only add if not already exists
            $acl->insertObjectAce($securityIdentity, constant("$this->maskBuilderClass::MASK_OWNER"));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addObjectClassAces(AclInterface $acl, array $roleInformation = array())
    {
        $builder = new $this->maskBuilderClass();

        foreach ($roleInformation as $role => $permissions) {
            $aceIndex = $this->findClassAceIndexByRole($acl, $role);
            $hasRole  = false;

            foreach ($permissions as $permission) {
                // add only the object permissions
                if (in_array($permission, $this->getObjectPermissions())) {
                    $builder->add($permission);
                    $hasRole = true;
                }
            }

            if ($hasRole) {
                if ($aceIndex === false) {
                    $acl->insertClassAce(new RoleSecurityIdentity($role), $builder->get());
                } else {
                    $acl->updateClassAce($aceIndex, $builder->get());
                }

                $builder->reset();
            } elseif ($aceIndex !== false) {
                $acl->deleteClassAce($aceIndex);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createAcl(ObjectIdentityInterface $objectIdentity)
    {
        return $this->aclProvider->createAcl($objectIdentity);
    }

    /**
     * {@inheritdoc}
     */
    public function updateAcl(AclInterface $acl)
    {
        $this->aclProvider->updateAcl($acl);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAcl(ObjectIdentityInterface $objectIdentity)
    {
        $this->aclProvider->deleteAcl($objectIdentity);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
}
