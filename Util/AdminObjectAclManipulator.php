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

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

use Sonata\AdminBundle\Admin\AdminInterface;

class AdminObjectAclManipulator
{
    /**
     * @var array Permissions managed only by a OWNER
     */
    protected static $ownerPermissions = array('MASTER', 'OWNER');
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;
    /**
     * @var string
     */
    protected $maskBuilderClass;
    /**
     * @var \Sonata\AdminBundle\Admin\AdminInterface
     */
    protected $admin;
    /**
     * @var mixed
     */
    protected $object;
    /**
     * @var array Users to set ACL for
     */
    protected $aclUsers;
    /**
     * @var array Cache of masks
     */
    protected $masks;
    /**
     * @var \Symfony\Component\Form\Form
     */
    protected $form;
    /**
     * @var \Symfony\Component\Security\Acl\Domain\Acl
     */
    protected $acl;

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param string                                       $maskBuilderClass
     */
    public function __construct(FormFactoryInterface $formFactory, $maskBuilderClass)
    {
        $this->formFactory = $formFactory;
        $this->maskBuilderClass = $maskBuilderClass;
    }

    /**
     * Sets admin class
     *
     * @param  \Sonata\AdminBundle\Admin\AdminInterface           $admin
     * @return \Sonata\AdminBundle\Util\AdminObjectAclManipulator
     */
    public function setAdmin(AdminInterface $admin)
    {
        $this->admin = $admin;
        $this->updateMasks();

        return $this;
    }

    /**
     * Sets object
     *
     * @param  mixed                                              $object
     * @return \Sonata\AdminBundle\Util\AdminObjectAclManipulator
     */
    public function setObject($object = null)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Sets users
     *
     * @param  array                                              $aclUsers
     * @return \Sonata\AdminBundle\Util\AdminObjectAclManipulator
     */
    public function setAclUsers(array $aclUsers)
    {
        $this->aclUsers = $aclUsers;

        return $this;
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
            foreach ($this->ownerPermissions as $permission) {
                $key = array_search($permission, $permissions);
                if ($key !== false) {
                    unset($permissions[$key]);
                }
            }
        }

        return $permissions;
    }

    /**
     * Gets the form
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createForm()
    {
        $permissions = $this->getUserPermissions();

        // Retrieve object identity
        $objectIdentity = ObjectIdentity::fromDomainObject($this->object);
        $this->acl = $this->getSecurityHandler()->getObjectAcl($objectIdentity);
        if (!$this->acl) {
            $this->acl = $this->getSecurityHandler()->createAcl($objectIdentity);
        }

        // Create a form to set ACL
        $formBuilder = $this->formFactory->createBuilder('form');
        foreach ($this->aclUsers as $aclUser) {
            $securityIdentity = UserSecurityIdentity::fromAccount($aclUser);

            foreach ($permissions as $permission) {
                try {
                    $checked = $this->acl->isGranted(array($this->masks[$permission]), array($securityIdentity));
                } catch (NoAceFoundException $e) {
                    $checked = false;
                }

                $formBuilder->add($aclUser->getId() . $permission, 'checkbox', array('required' => false, 'data' => $checked));
            }
        }

        $this->form = $formBuilder->getForm();

        return $this->form;
    }

    /**
     * Updates ACL
     */
    public function updateAcl()
    {
        foreach ($this->aclUsers as $aclUser) {
            $securityIdentity = UserSecurityIdentity::fromAccount($aclUser);

            $maskBuilder = new $this->maskBuilderClass();
            foreach ($this->getUserPermissions() as $permission) {
                if ($this->form->get($aclUser->getId() . $permission)->getData()) {
                    $maskBuilder->add($permission);
                }
            }

            // Restore OWNER and MASTER permissions
            if (!$this->isOwner()) {
                foreach ($this->ownerPermissions as $permission) {
                    if ($this->acl->isGranted(array($this->masks[$permission]), array($securityIdentity))) {
                        $maskBuilder->add($permission);
                    }
                }
            }

            $mask = $maskBuilder->get();

            $index = null;
            $ace = null;
            foreach ($this->acl->getObjectAces() as $currentIndex => $currentAce) {
                if ($currentAce->getSecurityIdentity()->equals($securityIdentity)) {
                    $index = $currentIndex;
                    $ace = $currentAce;
                    break;
                }
            }

            if ($ace) {
                $this->acl->updateObjectAce($index, $mask);
            } else {
                $this->acl->insertObjectAce($securityIdentity, $mask);
            }
        }

        $this->getSecurityHandler()->updateAcl($this->acl);
    }

    /**
     * Gets permissions
     *
     * @return array
     */
    protected function getPermissions()
    {
        return $this->admin->getSecurityHandler()->getObjectPermissions();
    }

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
     * Tests if the current user as the OWNER right
     *
     * @return boolean
     */
    protected function isOwner()
    {
        // Only a owner can set MASTER and OWNER ACL
        return $this->admin->isGranted('OWNER', $this->object);
    }

    /**
     * Gets security handler
     * @return \Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface
     */
    protected function getSecurityHandler()
    {
        return $this->admin->getSecurityHandler();
    }
}
