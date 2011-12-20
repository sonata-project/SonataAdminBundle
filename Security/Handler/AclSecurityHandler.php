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
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Sonata\AdminBundle\Admin\AdminInterface;

class AclSecurityHandler implements SecurityHandlerInterface
{
    protected $securityContext;
    protected $aclProvider;
    protected $superAdminRoles;
    protected $adminPermissions;
    protected $objectPermissions;
    protected $maskBuilderClass;

    /**
     * @param SecurityContextInterface $securityContext
     * @param AclProviderInterface $aclProvider
     * @param string $maskBuilderClass
     * @param array $superAdminRoles
     */
    public function __construct(SecurityContextInterface $securityContext, AclProviderInterface $aclProvider, $maskBuilderClass, array $superAdminRoles)
    {
        $this->securityContext = $securityContext;
        $this->aclProvider = $aclProvider;
        $this->maskBuilderClass = $maskBuilderClass;
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
     * Set the permissions related to an object instance
     *
     * @param array $permissions
     */
    public function setObjectPermissions(array $permissions)
    {
        $this->objectPermissions = $permissions;
    }

    /**
     * Return the permissions related to an object instance
     *
     * @return array
     */
    public function getObjectPermissions()
    {
        return $this->objectPermissions;
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
    public function createObjectSecurity(AdminInterface $admin, $object)
    {
        // retrieving the ACL for the object identity
        $objectIdentity = ObjectIdentity::fromDomainObject($object);
        $acl = $this->getObjectAcl($objectIdentity);
        if (is_null($acl)) {
            $acl = $this->createAcl($objectIdentity);
        }

        // retrieving the security identity of the currently logged-in user
        $user = $this->securityContext->getToken()->getUser();
        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        $this->addObjectOwner($acl, $securityIdentity);
        $this->addObjectClassAces($acl, $this->buildSecurityInformation($admin));
        $this->updateAcl($acl);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteObjectSecurity(AdminInterface $admin, $object)
    {
        $objectIdentity = ObjectIdentity::fromDomainObject($object);
        if (!is_null($acl = $this->getObjectAcl($objectIdentity)))
        {
            $this->deleteAcl($acl);
        }
    }

    /**
     * Get the ACL for the object
     *
     * @param ObjectIdentityInterface $objectIdentity
     * @return \Symfony\Component\Security\Acl\Model\AclInterface
     */
    public function getObjectAcl(ObjectIdentityInterface $objectIdentity)
    {
        try {
            $acl = $this->aclProvider->findAcl($objectIdentity);
        } catch(AclNotFoundException $e) {
            return null;
        }

        return $acl;
    }

    /**
     * Add an object owner ACE to the object ACL
     *
     * @param AclInterface $acl
     * @param UserSecurityIdentity $securityIdentity
     */
    public function addObjectOwner(AclInterface $acl, UserSecurityIdentity $securityIdentity = null)
    {
        if (false === $this->findClassAceIndexByUsername($acl, $securityIdentity->getUsername())) {
            // only add if not already exists
            $acl->insertObjectAce($securityIdentity, constant("$this->maskBuilderClass::MASK_OWNER"));
        }
    }

    /**
     * Add the object class ACE's to the object ACL
     *
     * @param AclInterface $acl
     * @param array $roleInformation
     * @return void
     */
    public function addObjectClassAces(AclInterface $acl, array $roleInformation = array())
    {
        $builder = new $this->maskBuilderClass();

        foreach ($roleInformation as $role => $permissions) {
            $aceIndex = $this->findClassAceIndexByRole($acl, $role);
            $hasRole = false;

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
     * Add the class ACE's to the admin ACL
     *
     * @param AclInterface $acl
     * @param array $roleInformation
     * @param Symfony\Component\Console\Output\OutputInterface $output
     * @return boolean TRUE if admin class ACEs are added, FALSE if not
     */
    public function addAdminClassAces(AclInterface $acl, array $roleInformation = array(), \Symfony\Component\Console\Output\OutputInterface $output = null)
    {
        if (count($this->getAdminPermissions()) > 0 ) {
            $builder = new $this->maskBuilderClass();

            foreach ($roleInformation as $role => $permissions) {
                $aceIndex = $this->findClassAceIndexByRole($acl, $role);
                $roleAdminPermissions = array();

                foreach ($permissions as $permission) {
                    // add only the admin permissions
                    if (in_array($permission, $this->getAdminPermissions())) {
                        $builder->add($permission);
                        $roleAdminPermissions[] = $permission;
                    }
                }

                if (count($roleAdminPermissions) > 0) {
                    if ($aceIndex === false) {
                        $acl->insertClassAce(new RoleSecurityIdentity($role), $builder->get());
                        $action = 'add';
                    } else {
                        $acl->updateClassAce($aceIndex, $builder->get());
                        $action = 'update';
                    }

                    if (!is_null($output)) {
                        $output->writeln(sprintf('   - %s role: %s, permissions: %s', $action, $role, json_encode($roleAdminPermissions)));
                    }

                    $builder->reset();
                } elseif ($aceIndex !== false) {
                    $acl->deleteClassAce($aceIndex);

                    if (!is_null($output)) {
                        $output->writeln(sprintf('   - remove role: %s', $action, $role));
                    }
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Create an object ACL
     *
     * @param ObjectIdentityInterface $objectIdentity
     * @return AclInterface
     */
    public function createAcl(ObjectIdentityInterface $objectIdentity)
    {
        return $this->aclProvider->createAcl($objectIdentity);
    }

    /**
     * Update the ACL
     *
     * @param AclInterface $acl
     * @return void
     */
    public function updateAcl(AclInterface $acl)
    {
        $this->aclProvider->updateAcl($acl);
    }

    /**
     * Delete the ACL
     *
     * @param AclInterface $acl
     * @return void
     */
    public function deleteAcl(AclInterface $acl)
    {
        $this->aclProvider->deleteAcl($acl);
    }

    /**
     * Helper method to find the index of a class ACE for a role
     *
     * @param AclInterface $acl
     * @param string $role
     */
    protected function findClassAceIndexByRole(AclInterface $acl, $role)
    {
        foreach ($acl->getClassAces() as $index => $entry) {
            if ($entry->getSecurityIdentity() instanceof RoleSecurityIdentity && $entry->getSecurityIdentity()->getRole() === $role) {
                return $index;
            }
        }

        return false;
    }

    /**
     * Helper method to find the index of a class ACE for a username
     *
     * @param AclInterface $acl
     * @param string $username
     */
    protected function findClassAceIndexByUsername(AclInterface $acl, $username)
    {
        foreach ($acl->getClassAces() as $index => $entry) {
            if ($entry->getSecurityIdentity() instanceof UserSecurityIdentity && $entry->getSecurityIdentity()->getUsername() === $username) {
                return $index;
            }
        }

        return false;
    }
}