<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Util;

use Symfony\Component\Form\Form;
use Symfony\Component\Security\Acl\Domain\Acl;

use Sonata\AdminBundle\Admin\AdminInterface;

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
    protected static $ownerPermissions = array('MASTER', 'OWNER');
    /**
     * @var \Sonata\AdminBundle\Admin\AdminInterface
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
     * @var \Symfony\Component\Form\Form
     */
    protected $aclUsersForm;
    /**
     * @var \Symfony\Component\Form\Form
     */
    protected $aclRolesForm;
    /**
     * @var \Symfony\Component\Security\Acl\Domain\Acl
     */
    protected $acl;
    /**
     * @var string
     */
    protected $maskBuilderClass;

    /**
     * Cache masks
     */
    protected function updateMasks()
    {
        $permissions = $this->getPermissions();

        $reflectionClass = new \ReflectionClass(new $this->maskBuilderClass());
        $this->masks = array();
        foreach ($permissions as $permission) {
            $this->masks[$permission] = $reflectionClass->getConstant('MASK_' . $permission);
        }
    }

    /**
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param mixed                                    $object
     * @param \Traversable                             $aclUsers
     * @param string                                   $maskBuilderClass
     * @param \Traversable|null                        $aclRoles
     */
    public function __construct(
        AdminInterface $admin, $object,
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
     * Gets admin
     *
     * @return \Sonata\AdminBundle\Admin\AdminInterface
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Gets object
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Gets ACL users
     *
     * @return \Traversable
     */
    public function getAclUsers()
    {
        return $this->aclUsers;
    }

    /**
     * Gets ACL roles
     *
     * @return \Traversable
     */
    public function getAclRoles()
    {
        return $this->aclRoles;
    }

    /**
     * Sets ACL
     *
     * @param  \Symfony\Component\Security\Acl\Domain\Acl  $acl
     * @return \Sonata\AdminBundle\Util\AdminObjectAclData
     */
    public function setAcl(Acl $acl)
    {
        $this->acl = $acl;

        return $this;
    }

    /**
     * Gets ACL
     *
     * @return \Symfony\Component\Security\Acl\Domain\Acl
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * Gets masks
     *
     * @return array
     */
    public function getMasks()
    {
        return $this->masks;
    }

    /**
     * Sets form
     *
     * @param  \Symfony\Component\Form\Form                $form
     * @return \Sonata\AdminBundle\Util\AdminObjectAclData
     *
     * @deprecated Deprecated since version 2.4. Use setAclUsersForm() instead.
     */
    public function setForm(Form $form)
    {
        trigger_error('setForm() is deprecated since version 2.4. Use setAclUsersForm() instead.', E_USER_DEPRECATED);

        return $this->setAclUsersForm($form);
    }

    /**
     * Gets form
     *
     * @return \Symfony\Component\Form\Form
     *
     * @deprecated Deprecated since version 2.4. Use getAclUsersForm() instead.
     */
    public function getForm()
    {
        trigger_error('getForm() is deprecated since version 2.4. Use getAclUsersForm() instead.', E_USER_DEPRECATED);

        return $this->getAclUsersForm();
    }

    /**
     * Sets ACL users form
     *
     * @param  \Symfony\Component\Form\Form                $form
     * @return \Sonata\AdminBundle\Util\AdminObjectAclData
     */
    public function setAclUsersForm(Form $form)
    {
        $this->aclUsersForm = $form;

        return $this;
    }

    /**
     * Gets ACL users form
     *
     * @return \Symfony\Component\Form\Form
     */
    public function getAclUsersForm()
    {
        return $this->aclUsersForm;
    }

    /**
     * Sets ACL roles form
     *
     * @param  \Symfony\Component\Form\Form                $form
     * @return \Sonata\AdminBundle\Util\AdminObjectAclData
     */
    public function setAclRolesForm(Form $form)
    {
        $this->aclRolesForm = $form;

        return $this;
    }

    /**
     * Gets ACL roles form
     *
     * @return \Symfony\Component\Form\Form
     */
    public function getAclRolesForm()
    {
        return $this->aclRolesForm;
    }

    /**
     * Gets permissions
     *
     * @return array
     */
    public function getPermissions()
    {
        return $this->admin->getSecurityHandler()->getObjectPermissions();
    }

    /**
     * Get permissions that the current user can set
     *
     * @return array
     */
    public function getUserPermissions()
    {
        $permissions = $this->getPermissions();

        if (!$this->isOwner()) {
            foreach (self::$ownerPermissions as $permission) {
                $key = array_search($permission, $permissions);
                if ($key !== false) {
                    unset($permissions[$key]);
                }
            }
        }

        return $permissions;
    }

    /**
     * Tests if the current user as the OWNER right
     *
     * @return boolean
     */
    public function isOwner()
    {
        // Only a owner can set MASTER and OWNER ACL
        return $this->admin->isGranted('OWNER', $this->object);
    }

    /**
     * Gets security handler
     *
     * @return \Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface
     */
    public function getSecurityHandler()
    {
        return $this->admin->getSecurityHandler();
    }

    /**
     * @return array
     */
    public function getSecurityInformation()
    {
        return $this->admin->getSecurityHandler()->buildSecurityInformation($this->admin);
    }
}
