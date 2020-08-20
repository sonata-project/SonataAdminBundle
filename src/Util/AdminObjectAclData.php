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

namespace Sonata\AdminBundle\Util;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;

/**
 * AdminObjectAclData holds data manipulated by {@link AdminObjectAclManipulator}.
 *
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 */
class AdminObjectAclData
{
    /**
     * @var array Permissions managed only by a OWNER
     */
    protected static $ownerPermissions = ['MASTER', 'OWNER'];

    /**
     * @var AdminInterface
     */
    protected $admin;

    /**
     * @var object
     */
    protected $object;

    /**
     * @var \Traversable Users to set ACL for
     */
    protected $aclUsers;

    /**
     * @var \Traversable Roles to set ACL for
     */
    protected $aclRoles;

    /**
     * @var array Cache of masks
     */
    protected $masks = [];

    /**
     * @var FormInterface
     */
    protected $aclUsersForm;

    /**
     * @var FormInterface
     */
    protected $aclRolesForm;

    /**
     * @var MutableAclInterface
     */
    protected $acl;

    /**
     * @var string
     */
    protected $maskBuilderClass;

    /**
     * @param object $object
     * @param string $maskBuilderClass
     */
    public function __construct(
        AdminInterface $admin,
        $object,
        \Traversable $aclUsers,
        $maskBuilderClass,
        ?\Traversable $aclRoles = null
    ) {
        $this->admin = $admin;
        $this->object = $object;
        $this->aclUsers = $aclUsers;
        $this->aclRoles = (null === $aclRoles) ? new \ArrayIterator() : $aclRoles;
        $this->maskBuilderClass = $maskBuilderClass;
        if (!$admin->isAclEnabled()) {
            throw new \InvalidArgumentException('The admin must have ACL enabled.');
        }

        $this->updateMasks();
    }

    /**
     * Gets admin.
     *
     * @return AdminInterface
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Gets object.
     *
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Gets ACL users.
     *
     * @return \Traversable
     */
    public function getAclUsers()
    {
        return $this->aclUsers;
    }

    /**
     * Gets ACL roles.
     *
     * @return \Traversable
     */
    public function getAclRoles()
    {
        return $this->aclRoles;
    }

    /**
     * Sets ACL.
     *
     * @return AdminObjectAclData
     */
    public function setAcl(MutableAclInterface $acl)
    {
        $this->acl = $acl;

        return $this;
    }

    /**
     * Gets ACL.
     *
     * @return MutableAclInterface
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * Gets masks.
     *
     * @return array
     */
    public function getMasks()
    {
        return $this->masks;
    }

    /**
     * Sets ACL users form.
     *
     * @return AdminObjectAclData
     */
    public function setAclUsersForm(FormInterface $form)
    {
        $this->aclUsersForm = $form;

        return $this;
    }

    /**
     * Gets ACL users form.
     *
     * @return FormInterface
     */
    public function getAclUsersForm()
    {
        return $this->aclUsersForm;
    }

    /**
     * Sets ACL roles form.
     *
     * @return AdminObjectAclData
     */
    public function setAclRolesForm(FormInterface $form)
    {
        $this->aclRolesForm = $form;

        return $this;
    }

    /**
     * Gets ACL roles form.
     *
     * @return FormInterface
     */
    public function getAclRolesForm()
    {
        return $this->aclRolesForm;
    }

    /**
     * Gets permissions.
     *
     * @return array
     */
    public function getPermissions()
    {
        return $this->getSecurityHandler()->getObjectPermissions();
    }

    /**
     * Get permissions that the current user can set.
     *
     * @return array
     */
    public function getUserPermissions()
    {
        $permissions = $this->getPermissions();

        if (!$this->isOwner()) {
            foreach (self::$ownerPermissions as $permission) {
                $key = array_search($permission, $permissions, true);
                if (false !== $key) {
                    unset($permissions[$key]);
                }
            }
        }

        return $permissions;
    }

    public function getOwnerPermissions()
    {
        return self::$ownerPermissions;
    }

    /**
     * Tests if the current user has the OWNER right.
     *
     * @return bool
     */
    public function isOwner()
    {
        // Only a owner can set MASTER and OWNER ACL
        return $this->admin->isGranted('OWNER', $this->object);
    }

    /**
     * Gets security handler.
     *
     * @return AclSecurityHandlerInterface
     */
    public function getSecurityHandler()
    {
        $securityHandler = $this->admin->getSecurityHandler();
        \assert($securityHandler instanceof AclSecurityHandlerInterface);

        return $securityHandler;
    }

    /**
     * @return array
     */
    public function getSecurityInformation()
    {
        return $this->getSecurityHandler()->buildSecurityInformation($this->admin);
    }

    /**
     * Cache masks.
     */
    protected function updateMasks(): void
    {
        $permissions = $this->getPermissions();

        $reflectionClass = new \ReflectionClass(new $this->maskBuilderClass());
        $this->masks = [];
        foreach ($permissions as $permission) {
            $this->masks[$permission] = $reflectionClass->getConstant(sprintf('MASK_%s', $permission));
        }
    }
}
