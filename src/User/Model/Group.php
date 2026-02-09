<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\User\Model;

abstract class Group implements GroupInterface
{
    protected ?int $id = null;

    protected ?string $name = null;

    /**
     * @var list<string>
     */
    protected array $roles = [];

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = array_values($roles);
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRole(string $role): bool
    {
        return \in_array(strtoupper($role), $this->roles, true);
    }

    public function addRole(string $role): void
    {
        $role = strtoupper($role);

        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
        }
    }

    public function removeRole(string $role): void
    {
        $role = strtoupper($role);
        $this->roles = array_values(array_filter(
            $this->roles,
            static fn (string $r): bool => $r !== $role,
        ));
    }
}
