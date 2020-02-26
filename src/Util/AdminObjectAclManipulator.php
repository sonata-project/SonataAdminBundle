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

use Sonata\AdminBundle\Form\Type\AclMatrixType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * A manipulator for updating ACL related to an object.
 *
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 * @author Baptiste Meyer <baptiste@les-tilleuls.coop>
 */
class AdminObjectAclManipulator
{
    public const ACL_USERS_FORM_NAME = 'acl_users_form';
    public const ACL_ROLES_FORM_NAME = 'acl_roles_form';

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;
    /**
     * @var string
     */
    protected $maskBuilderClass;

    /**
     * @param string $maskBuilderClass
     */
    public function __construct(FormFactoryInterface $formFactory, $maskBuilderClass)
    {
        $this->formFactory = $formFactory;
        $this->maskBuilderClass = $maskBuilderClass;
    }

    /**
     * Gets mask builder class name.
     *
     * @return string
     */
    public function getMaskBuilderClass()
    {
        return $this->maskBuilderClass;
    }

    /**
     * Gets the form.
     *
     * NEXT_MAJOR: remove this method.
     *
     * @return Form
     *
     * @deprecated since sonata-project/admin-bundle 3.0. Use createAclUsersForm() instead
     */
    public function createForm(AdminObjectAclData $data)
    {
        @trigger_error(
            'createForm() is deprecated since version 3.0 and will be removed in 4.0. '
            .'Use createAclUsersForm() instead.',
            E_USER_DEPRECATED
        );

        return $this->createAclUsersForm($data);
    }

    /**
     * Gets the ACL users form.
     *
     * @return Form
     */
    public function createAclUsersForm(AdminObjectAclData $data)
    {
        $aclValues = $data->getAclUsers();
        $formBuilder = $this->formFactory->createNamedBuilder(self::ACL_USERS_FORM_NAME, FormType::class);
        $form = $this->buildForm($data, $formBuilder, $aclValues);
        $data->setAclUsersForm($form);

        return $form;
    }

    /**
     * Gets the ACL roles form.
     *
     * @return Form
     */
    public function createAclRolesForm(AdminObjectAclData $data)
    {
        $aclValues = $data->getAclRoles();
        $formBuilder = $this->formFactory->createNamedBuilder(self::ACL_ROLES_FORM_NAME, FormType::class);
        $form = $this->buildForm($data, $formBuilder, $aclValues);
        $data->setAclRolesForm($form);

        return $form;
    }

    /**
     * Updates ACL users.
     */
    public function updateAclUsers(AdminObjectAclData $data)
    {
        $aclValues = $data->getAclUsers();
        $form = $data->getAclUsersForm();

        $this->buildAcl($data, $form, $aclValues);
    }

    /**
     * Updates ACL roles.
     */
    public function updateAclRoles(AdminObjectAclData $data)
    {
        $aclValues = $data->getAclRoles();
        $form = $data->getAclRolesForm();

        $this->buildAcl($data, $form, $aclValues);
    }

    /**
     * Updates ACl.
     *
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.0. Use updateAclUsers() instead
     */
    public function updateAcl(AdminObjectAclData $data)
    {
        @trigger_error(
            'updateAcl() is deprecated since version 3.0 and will be removed in 4.0.'
            .'Use updateAclUsers() instead.',
            E_USER_DEPRECATED
        );

        $this->updateAclUsers($data);
    }

    /**
     * Builds ACL.
     */
    protected function buildAcl(AdminObjectAclData $data, Form $form, \Traversable $aclValues)
    {
        $masks = $data->getMasks();
        $acl = $data->getAcl();
        $matrices = $form->getData();

        foreach ($aclValues as $aclValue) {
            foreach ($matrices as $key => $matrix) {
                if ($aclValue instanceof UserInterface) {
                    if (\array_key_exists('user', $matrix) && $aclValue->getUsername() === $matrix['user']) {
                        $matrices[$key]['acl_value'] = $aclValue;
                    }
                } elseif (\array_key_exists('role', $matrix) && $aclValue === $matrix['role']) {
                    $matrices[$key]['acl_value'] = $aclValue;
                }
            }
        }

        foreach ($matrices as $matrix) {
            if (!isset($matrix['acl_value'])) {
                continue;
            }

            $securityIdentity = $this->getSecurityIdentity($matrix['acl_value']);
            $maskBuilder = new $this->maskBuilderClass();

            foreach ($data->getUserPermissions() as $permission) {
                if (isset($matrix[$permission]) && true === $matrix[$permission]) {
                    $maskBuilder->add($permission);
                }
            }

            // Restore OWNER and MASTER permissions
            if (!$data->isOwner()) {
                foreach ($data->getOwnerPermissions() as $permission) {
                    if ($acl->isGranted([$masks[$permission]], [$securityIdentity])) {
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

    /**
     * Builds the form.
     *
     * @return Form
     */
    protected function buildForm(AdminObjectAclData $data, FormBuilderInterface $formBuilder, \Traversable $aclValues)
    {
        // Retrieve object identity
        $objectIdentity = ObjectIdentity::fromDomainObject($data->getObject());
        $acl = $data->getSecurityHandler()->getObjectAcl($objectIdentity);
        if (!$acl) {
            $acl = $data->getSecurityHandler()->createAcl($objectIdentity);
        }

        $data->setAcl($acl);

        $masks = $data->getMasks();
        $securityInformation = $data->getSecurityInformation();

        foreach ($aclValues as $key => $aclValue) {
            $securityIdentity = $this->getSecurityIdentity($aclValue);
            $permissions = [];

            foreach ($data->getUserPermissions() as $permission) {
                try {
                    $checked = $acl->isGranted([$masks[$permission]], [$securityIdentity]);
                } catch (NoAceFoundException $e) {
                    $checked = false;
                }

                $attr = [];
                if (
                    self::ACL_ROLES_FORM_NAME === $formBuilder->getName()
                    && isset($securityInformation[$aclValue])
                    && false !== array_search($permission, $securityInformation[$aclValue], true)
                ) {
                    $attr['disabled'] = 'disabled';
                }

                $permissions[$permission] = [
                    'required' => false,
                    'data' => $checked,
                    'disabled' => \array_key_exists('disabled', $attr),
                    'attr' => $attr,
                ];
            }

            $formBuilder->add(
                $key,
                AclMatrixType::class,
                ['permissions' => $permissions, 'acl_value' => $aclValue]
            );
        }

        return $formBuilder->getForm();
    }

    /**
     * Gets a user or a role security identity.
     *
     * @param string|UserInterface $aclValue
     *
     * @return RoleSecurityIdentity|UserSecurityIdentity
     */
    protected function getSecurityIdentity($aclValue)
    {
        return ($aclValue instanceof UserInterface)
            ? UserSecurityIdentity::fromAccount($aclValue)
            : new RoleSecurityIdentity($aclValue)
        ;
    }
}
