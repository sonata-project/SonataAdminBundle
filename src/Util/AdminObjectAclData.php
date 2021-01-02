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
use Symfony\Component\Form\FormInterface;
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
     * @var string[] Permissions managed only by a OWNER
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
     * @phpstan-param class-string $maskBuilderClass
     */
    public function __construct(
        AdminInterface $admin,
        object $object,
        \Traversable $aclUsers,
        string $maskBuilderClass,
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

    public function getAdmin(): AdminInterface
    {
        return $this->admin;
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function getAclUsers(): \Traversable
    {
        return $this->aclUsers;
    }

    public function getAclRoles(): \Traversable
    {
        return $this->aclRoles;
    }

    /**
     * @return static
     */
    public function setAcl(MutableAclInterface $acl): self
    {
        $this->acl = $acl;

        return $this;
    }

    public function getAcl(): ?MutableAclInterface
    {
        return $this->acl;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMasks(): array
    {
        return $this->masks;
    }

    /**
     * @return static
     */
    public function setAclUsersForm(FormInterface $form): self
    {
        $this->aclUsersForm = $form;

        return $this;
    }

    public function getAclUsersForm(): ?FormInterface
    {
        return $this->aclUsersForm;
    }

    /**
     * @return static
     */
    public function setAclRolesForm(FormInterface $form): self
    {
        $this->aclRolesForm = $form;

        return $this;
    }

    public function getAclRolesForm(): ?FormInterface
    {
        return $this->aclRolesForm;
    }

    public function getPermissions(): array
    {
        return $this->getSecurityHandler()->getObjectPermissions();
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

    public function getOwnerPermissions(): array
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

    public function getSecurityHandler(): AclSecurityHandlerInterface
    {
        $securityHandler = $this->admin->getSecurityHandler();
        \assert($securityHandler instanceof AclSecurityHandlerInterface);

        return $securityHandler;
    }

    /**
     * @return array<string, string[]>
     */
    public function getSecurityInformation(): array
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
