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
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Acl\Domain\Acl;

/**
 * AdminObjectAclData holds data manipulated by {@link AdminObjectAclManipulator}.
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
     * @var mixed
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
    protected $masks;

    /**
     * @var Form
     */
    protected $aclUsersForm;

    /**
     * @var Form
     */
    protected $aclRolesForm;

    /**
     * @var Acl
     */
    protected $acl;

    /**
     * @var string
     */
    protected $maskBuilderClass;

    /**
     * @param mixed  $object
     * @param string $maskBuilderClass
     */
    public function __construct(
        AdminInterface $admin,
        $object,
        \Traversable $aclUsers,
        $maskBuilderClass,
        \Traversable $aclRoles = null
    ) {
        $this->admin = $admin;
        $this->object = $object;
        $this->aclUsers = $aclUsers;
        $this->aclRoles = (null === $aclRoles) ? new \ArrayIterator() : $aclRoles;
        $this->maskBuilderClass = $maskBuilderClass;

        $this->updateMasks();
    }

    /**
     * Gets admin.
     */
    public function getAdmin(): AdminInterface
    {
        return $this->admin;
    }

    /**
     * Gets object.
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Gets ACL users.
     */
    public function getAclUsers(): \Traversable
    {
        return $this->aclUsers;
    }

    /**
     * Gets ACL roles.
     */
    public function getAclRoles(): \Traversable
    {
        return $this->aclRoles;
    }

    /**
     * Sets ACL.
     */
    public function setAcl(Acl $acl): self
    {
        $this->acl = $acl;

        return $this;
    }

    /**
     * Gets ACL.
     */
    public function getAcl(): Acl
    {
        return $this->acl;
    }

    /**
     * Gets masks.
     */
    public function getMasks(): array
    {
        return $this->masks;
    }

    /**
     * Sets form.
     *
     * NEXT_MAJOR: remove this method.
     *
     *
     * @deprecated Deprecated since version 3.0. Use setAclUsersForm() instead
     */
    public function setForm(Form $form): self
    {
        @trigger_error(
            'setForm() is deprecated since version 3.0 and will be removed in 4.0. '
            .'Use setAclUsersForm() instead.',
            E_USER_DEPRECATED
        );

        return $this->setAclUsersForm($form);
    }

    /**
     * Gets form.
     *
     * NEXT_MAJOR: remove this method.
     *
     *
     * @deprecated Deprecated since version 3.0. Use getAclUsersForm() instead
     */
    public function getForm(): Form
    {
        @trigger_error(
            'getForm() is deprecated since version 3.0 and will be removed in 4.0. '
            .'Use getAclUsersForm() instead.',
            E_USER_DEPRECATED
        );

        return $this->getAclUsersForm();
    }

    /**
     * Sets ACL users form.
     */
    public function setAclUsersForm(Form $form): self
    {
        $this->aclUsersForm = $form;

        return $this;
    }

    /**
     * Gets ACL users form.
     */
    public function getAclUsersForm(): Form
    {
        return $this->aclUsersForm;
    }

    /**
     * Sets ACL roles form.
     */
    public function setAclRolesForm(Form $form): self
    {
        $this->aclRolesForm = $form;

        return $this;
    }

    /**
     * Gets ACL roles form.
     */
    public function getAclRolesForm(): Form
    {
        return $this->aclRolesForm;
    }

    /**
     * Gets permissions.
     */
    public function getPermissions(): array
    {
        return $this->admin->getSecurityHandler()->getObjectPermissions();
    }

    /**
     * Get permissions that the current user can set.
     */
    public function getUserPermissions(): array
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
     */
    public function isOwner(): bool
    {
        // Only a owner can set MASTER and OWNER ACL
        return $this->admin->isGranted('OWNER', $this->object);
    }

    /**
     * Gets security handler.
     */
    public function getSecurityHandler(): SecurityHandlerInterface
    {
        return $this->admin->getSecurityHandler();
    }

    public function getSecurityInformation(): array
    {
        return $this->admin->getSecurityHandler()->buildSecurityInformation($this->admin);
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
            $this->masks[$permission] = $reflectionClass->getConstant('MASK_'.$permission);
        }
    }
}
