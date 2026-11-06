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
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @var string[] Permissions managed only by a OWNER
     */
    protected static $ownerPermissions = ['MASTER', 'OWNER'];

    /**
     * @var AdminInterface<object>
     */
    protected $admin;

    /**
     * @var object
     */
    protected $object;

    /**
     * @var \Traversable<UserInterface|string> Users to set ACL for
     */
    protected $aclUsers;

    /**
     * @var \Traversable<string> Roles to set ACL for
     */
    protected $aclRoles;

    /**
     * @var array<string, mixed> Cache of masks
     */
    protected $masks = [];

    /**
     * @var FormInterface|null
     */
    protected $aclUsersForm;

    /**
     * @var FormInterface|null
     */
    protected $aclRolesForm;

    /**
     * @var MutableAclInterface|null
     */
    protected $acl;

    /**
     * @var string
     *
     * @phpstan-var class-string
     */
    protected $maskBuilderClass;

    /**
     * @param AdminInterface<object>             $admin
     * @param object                             $object
     * @param string                             $maskBuilderClass
     * @param \Traversable<UserInterface|string> $aclUsers
     * @param \Traversable<string>|null          $aclRoles
     *
     * @phpstan-param class-string $maskBuilderClass
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
     * @return AdminInterface<object>
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
     * @return \Traversable<UserInterface|string>
     */
    public function getAclUsers()
    {
        return $this->aclUsers;
    }

    /**
     * @return \Traversable<string>
     */
    public function getAclRoles()
    {
        return $this->aclRoles;
    }

    /**
     * @return AdminObjectAclData
     */
    public function setAcl(MutableAclInterface $acl)
    {
        $this->acl = $acl;

        return $this;
    }

    /**
     * @return MutableAclInterface|null
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMasks()
    {
        return $this->masks;
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @return AdminObjectAclData
     *
     * @deprecated since sonata-project/admin-bundle 3.0. Use setAclUsersForm() instead
     */
    public function setForm(Form $form)
    {
        @trigger_error(
            'setForm() is deprecated since version 3.0 and will be removed in 4.0. Use setAclUsersForm() instead.',
            \E_USER_DEPRECATED
        );

        return $this->setAclUsersForm($form);
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @return FormInterface
     *
     * @deprecated since sonata-project/admin-bundle version 3.0. Use getAclUsersForm() instead
     */
    public function getForm()
    {
        @trigger_error(
            'getForm() is deprecated since version 3.0 and will be removed in 4.0. Use getAclUsersForm() instead.',
            \E_USER_DEPRECATED
        );

        return $this->getAclUsersForm();
    }

    /**
     * @return AdminObjectAclData
     */
    public function setAclUsersForm(FormInterface $form)
    {
        $this->aclUsersForm = $form;

        return $this;
    }

    /**
     * @return FormInterface|null
     */
    public function getAclUsersForm()
    {
        return $this->aclUsersForm;
    }

    /**
     * @return AdminObjectAclData
     */
    public function setAclRolesForm(FormInterface $form)
    {
        $this->aclRolesForm = $form;

        return $this;
    }

    /**
     * @return FormInterface|null
     */
    public function getAclRolesForm()
    {
        return $this->aclRolesForm;
    }

    /**
     * @return string[]
     */
    public function getPermissions()
    {
        return $this->getSecurityHandler()->getObjectPermissions();
    }

    /**
     * @return string[]
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

    /**
     * @return string[]
     */
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
     * @return AclSecurityHandlerInterface
     */
    public function getSecurityHandler()
    {
        $securityHandler = $this->admin->getSecurityHandler();
        \assert($securityHandler instanceof AclSecurityHandlerInterface);

        return $securityHandler;
    }

    /**
     * @return array<string, string[]>
     */
    public function getSecurityInformation()
    {
        return $this->getSecurityHandler()->buildSecurityInformation($this->admin);
    }

    /**
     * Cache masks.
     *
     * @return void
     */
    protected function updateMasks()
    {
        $permissions = $this->getPermissions();

        $reflectionClass = new \ReflectionClass(new $this->maskBuilderClass());
        $this->masks = [];
        foreach ($permissions as $permission) {
            $this->masks[$permission] = $reflectionClass->getConstant(sprintf('MASK_%s', $permission));
        }
    }
}
