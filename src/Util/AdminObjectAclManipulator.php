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

use Sonata\AdminBundle\BCLayer\BCUserInterface;
use Sonata\AdminBundle\Form\Type\AclMatrixType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * A manipulator for updating ACL related to an object.
 *
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 * @author Baptiste Meyer <baptiste@les-tilleuls.coop>
 */
final class AdminObjectAclManipulator
{
    public const ACL_USERS_FORM_NAME = 'acl_users_form';
    public const ACL_ROLES_FORM_NAME = 'acl_roles_form';

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var string
     *
     * @phpstan-var class-string
     */
    private $maskBuilderClass;

    /**
     * @phpstan-param class-string $maskBuilderClass
     */
    public function __construct(FormFactoryInterface $formFactory, string $maskBuilderClass)
    {
        $this->formFactory = $formFactory;
        $this->maskBuilderClass = $maskBuilderClass;
    }

    /**
     * @phpstan-return class-string
     */
    public function getMaskBuilderClass(): string
    {
        return $this->maskBuilderClass;
    }

    public function createAclUsersForm(AdminObjectAclData $data): FormInterface
    {
        $aclValues = $data->getAclUsers();
        $formBuilder = $this->formFactory->createNamedBuilder(self::ACL_USERS_FORM_NAME, FormType::class);
        $form = $this->buildForm($data, $formBuilder, $aclValues);
        $data->setAclUsersForm($form);

        return $form;
    }

    public function createAclRolesForm(AdminObjectAclData $data): FormInterface
    {
        $aclValues = $data->getAclRoles();
        $formBuilder = $this->formFactory->createNamedBuilder(self::ACL_ROLES_FORM_NAME, FormType::class);
        $form = $this->buildForm($data, $formBuilder, $aclValues);
        $data->setAclRolesForm($form);

        return $form;
    }

    public function updateAclUsers(AdminObjectAclData $data): void
    {
        $form = $data->getAclUsersForm();
        if (null === $form) {
            throw new \InvalidArgumentException('Cannot update Acl roles if the acl users form is not set.');
        }

        $this->buildAcl($data, $form, $data->getAclUsers());
    }

    public function updateAclRoles(AdminObjectAclData $data): void
    {
        $form = $data->getAclRolesForm();
        if (null === $form) {
            throw new \InvalidArgumentException('Cannot update Acl roles if the acl roles form is not set.');
        }

        $this->buildAcl($data, $form, $data->getAclRoles());
    }

    /**
     * @param \Traversable<int|string, UserInterface|string> $aclValues
     *
     * @phpstan-param \Traversable<array-key, UserInterface|string> $aclValues
     */
    private function buildAcl(AdminObjectAclData $data, FormInterface $form, \Traversable $aclValues): void
    {
        $acl = $data->getAcl();
        if (null === $acl) {
            throw new \InvalidArgumentException('The acl cannot be null.');
        }

        $masks = $data->getMasks();
        $matrices = $form->getData();

        foreach ($aclValues as $aclValue) {
            foreach ($matrices as $key => $matrix) {
                if ($aclValue instanceof UserInterface) {
                    if (\array_key_exists('user', $matrix) && BCUserInterface::getUsername($aclValue) === $matrix['user']) {
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
            foreach ($acl->getObjectAces() as $currentIndex => $currentAce) {
                if ($currentAce->getSecurityIdentity()->equals($securityIdentity)) {
                    $index = $currentIndex;

                    break;
                }
            }

            if (null !== $index) {
                $acl->updateObjectAce($index, $mask);
            } else {
                $acl->insertObjectAce($securityIdentity, $mask);
            }
        }

        $data->getSecurityHandler()->updateAcl($acl);
    }

    /**
     * @param \Traversable<int|string, UserInterface|string> $aclValues
     *
     * @phpstan-param \Traversable<array-key, UserInterface|string> $aclValues
     */
    private function buildForm(AdminObjectAclData $data, FormBuilderInterface $formBuilder, \Traversable $aclValues): FormInterface
    {
        // Retrieve object identity
        $objectIdentity = ObjectIdentity::fromDomainObject($data->getObject());
        $acl = $data->getSecurityHandler()->getObjectAcl($objectIdentity);
        if (null === $acl) {
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
                    && \is_string($aclValue)
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
    private function getSecurityIdentity($aclValue): SecurityIdentityInterface
    {
        return ($aclValue instanceof UserInterface)
            ? UserSecurityIdentity::fromAccount($aclValue)
            : new RoleSecurityIdentity($aclValue);
    }
}
