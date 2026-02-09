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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class User implements UserInterface
{
    protected ?int $id = null;

    protected ?string $email = null;

    protected ?string $password = null;

    protected ?string $plainPassword = null;

    protected bool $enabled = false;

    /**
     * @var list<string>
     */
    protected array $roles = [];

    protected ?string $firstname = null;

    protected ?string $lastname = null;

    protected ?string $phone = null;

    protected ?string $locale = null;

    protected ?string $timezone = null;

    protected ?\DateTimeImmutable $createdAt = null;

    protected ?\DateTimeImmutable $updatedAt = null;

    protected ?string $confirmationToken = null;

    protected ?\DateTimeImmutable $passwordRequestedAt = null;

    /**
     * @var Collection<int, GroupInterface>
     */
    protected Collection $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getFullname() ?: $this->email ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return $this->email ?? '';
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPlainPassword(?string $password): void
    {
        $this->plainPassword = $password;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        $roles[] = self::ROLE_DEFAULT;

        return array_values(array_unique($roles));
    }

    public function getRealRoles(): array
    {
        return $this->roles;
    }

    public function setRealRoles(array $roles): void
    {
        $this->roles = array_values($roles);
    }

    public function isSuperAdmin(): bool
    {
        return \in_array(self::ROLE_SUPER_ADMIN, $this->roles, true);
    }

    public function setSuperAdmin(bool $boolean): void
    {
        if ($boolean) {
            if (!$this->isSuperAdmin()) {
                $this->roles[] = self::ROLE_SUPER_ADMIN;
            }
        } else {
            $this->roles = array_values(array_filter(
                $this->roles,
                static fn (string $role): bool => self::ROLE_SUPER_ADMIN !== $role,
            ));
        }
    }

    public function setFirstname(?string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setLastname(?string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function getFullname(): string
    {
        return trim(($this->firstname ?? '').' '.($this->lastname ?? ''));
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setTimezone(?string $timezone): void
    {
        $this->timezone = $timezone;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setConfirmationToken(?string $confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setPasswordRequestedAt(?\DateTimeImmutable $date): void
    {
        $this->passwordRequestedAt = $date;
    }

    public function getPasswordRequestedAt(): ?\DateTimeImmutable
    {
        return $this->passwordRequestedAt;
    }

    public function isPasswordRequestNonExpired(int $ttl): bool
    {
        if (null === $this->passwordRequestedAt) {
            return false;
        }

        return $this->passwordRequestedAt->getTimestamp() + $ttl > time();
    }

    public function hasGroup(GroupInterface $group): bool
    {
        return $this->groups->contains($group);
    }

    public function addGroup(GroupInterface $group): void
    {
        if (!$this->hasGroup($group)) {
            $this->groups->add($group);
        }
    }

    public function removeGroup(GroupInterface $group): void
    {
        $this->groups->removeElement($group);
    }

    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }
}
