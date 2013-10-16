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

/**
 * A manipulator for updating ACL related to an object.
 *
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 */
class AdminObjectAclManipulator
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;
    /**
     * @var string
     */
    protected $maskBuilderClass;

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
     * Gets mask builder class name
     *
     * @return string
     */
    public function getMaskBuilderClass()
    {
        return $this->maskBuilderClass;
    }

    /**
     * Gets the form
     *
     * @param  \Sonata\AdminBundle\Util\AdminObjectAclData $data
     * @return \Symfony\Component\Form\Form
     */
    public function createForm(AdminObjectAclData $data)
    {
        // Retrieve object identity
        $objectIdentity = ObjectIdentity::fromDomainObject($data->getObject());
        $acl = $data->getSecurityHandler()->getObjectAcl($objectIdentity);
        if (!$acl) {
            $acl = $data->getSecurityHandler()->createAcl($objectIdentity);
        }

        $data->setAcl($acl);

        $masks = $data->getMasks();

        // Create a form to set ACL
        $formBuilder = $this->formFactory->createBuilder('form');
        foreach ($data->getAclUsers() as $aclUser) {
            $securityIdentity = UserSecurityIdentity::fromAccount($aclUser);

            foreach ($data->getUserPermissions() as $permission) {
                try {
                    $checked = $acl->isGranted(array($masks[$permission]), array($securityIdentity));
                } catch (NoAceFoundException $e) {
                    $checked = false;
                }

                $formBuilder->add($aclUser->getId() . $permission, 'checkbox', array('required' => false, 'data' => $checked));
            }
        }

        $form = $formBuilder->getForm();
        $data->setForm($form);

        return $form;
    }

    /**
     * Updates ACL
     *
     * @param \Sonata\AdminBundle\Util\AdminObjectAclData $data
     */
    public function updateAcl(AdminObjectAclData $data)
    {
        foreach ($data->getAclUsers() as $aclUser) {
            $securityIdentity = UserSecurityIdentity::fromAccount($aclUser);

            $maskBuilder = new $this->maskBuilderClass();
            foreach ($data->getUserPermissions() as $permission) {
                if ($data->getForm()->get($aclUser->getId() . $permission)->getData()) {
                    $maskBuilder->add($permission);
                }
            }

            $masks = $data->getMasks();
            $acl = $data->getAcl();

            // Restore OWNER and MASTER permissions
            if (!$data->isOwner()) {
                foreach ($data->getOwnerPermissions() as $permission) {
                    if ($acl->isGranted(array($masks[$permission]), array($securityIdentity))) {
                        $maskBuilder->add($permission);
                    }
                }
            }

            $mask = $maskBuilder->get();

            $index = null;
            $ace = null;
            foreach ($acl->getObjectAces() as $currentIndex => $currentAce) {
                if ($currentAce->getSecurityIdentity()->equals($securityIdentity)) {
                    $index = $currentIndex;
                    $ace = $currentAce;
                    break;
                }
            }

            if ($ace) {
                $acl->updateObjectAce($index, $mask);
            } else {
                $acl->insertObjectAce($securityIdentity, $mask);
            }
        }

        $data->getSecurityHandler()->updateAcl($acl);
    }
}
