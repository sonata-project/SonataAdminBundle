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
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * AdminObjectAclData holds data manipulated by {@link AdminObjectAclManipulator}.
 *
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 */
final class AdminObjectAclData
{
    /**
     * @var string[] Permissions managed only by a OWNER
     */
    private static array $ownerPermissions = ['MASTER', 'OWNER'];

    /**
     * @var array<string, mixed> Cache of masks
     */
    private array $masks = [];

    private ?FormInterface $aclUsersForm = null;

    private ?FormInterface $aclRolesForm = null;

    private ?MutableAclInterface $acl = null;

    /**
     * @param AdminInterface<object>             $admin
     * @param \Traversable<UserInterface|string> $aclUsers
     * @param \Traversable<string>               $aclRoles
     *
     * @phpstan-param class-string $maskBuilderClass
     */
    public function __construct(
        private AdminInterface $admin,
        private object $object,
        private \Traversable $aclUsers,
        private string $maskBuilderClass,
        private \Traversable $aclRoles = new \ArrayIterator(),
    ) {
        if (!$admin->isAclEnabled()) {
            throw new \InvalidArgumentException('The admin must have ACL enabled.');
        }

        $this->updateMasks();
    }

    /**
     * @return AdminInterface<object>
     */
    public function getAdmin(): AdminInterface
    {
        return $this->admin;
    }

    public function getObject(): object
    {
        return $this->object;
    }

    /**
     * @return \Traversable<UserInterface|string>
     */
    public function getAclUsers(): \Traversable
    {
        return $this->aclUsers;
    }

    /**
     * @return \Traversable<string>
     */
    public function getAclRoles(): \Traversable
    {
        return $this->aclRoles;
    }

    /**
     * @return $this
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
     * @return $this
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
     * @return $this
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

    /**
     * @return string[]
     */
    public function getPermissions(): array
    {
        return $this->getSecurityHandler()->getObjectPermissions();
    }

    /**
     * @return string[]
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

    /**
     * @return string[]
     */
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
    private function updateMasks(): void
    {
        $permissions = $this->getPermissions();

        $reflectionClass = new \ReflectionClass(new $this->maskBuilderClass());
        $this->masks = [];
        foreach ($permissions as $permission) {
            $this->masks[$permission] = $reflectionClass->getConstant(\sprintf('MASK_%s', $permission));
        }
    }
}
