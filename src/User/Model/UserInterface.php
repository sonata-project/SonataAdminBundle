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

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

interface UserInterface extends SymfonyUserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public const ROLE_DEFAULT = 'ROLE_USER';

    public function getId(): ?int;

    public function setEmail(string $email): void;

    public function getEmail(): ?string;

    public function setPassword(?string $password): void;

    public function setPlainPassword(?string $password): void;

    public function getPlainPassword(): ?string;

    public function setEnabled(bool $enabled): void;

    public function isEnabled(): bool;

    public function setFirstname(?string $firstname): void;

    public function getFirstname(): ?string;

    public function setLastname(?string $lastname): void;

    public function getLastname(): ?string;

    public function getFullname(): string;

    public function setPhone(?string $phone): void;

    public function getPhone(): ?string;

    public function setLocale(?string $locale): void;

    public function getLocale(): ?string;

    public function setTimezone(?string $timezone): void;

    public function getTimezone(): ?string;

    public function setCreatedAt(?\DateTimeImmutable $createdAt): void;

    public function getCreatedAt(): ?\DateTimeImmutable;

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void;

    public function getUpdatedAt(): ?\DateTimeImmutable;

    public function setConfirmationToken(?string $confirmationToken): void;

    public function getConfirmationToken(): ?string;

    public function setPasswordRequestedAt(?\DateTimeImmutable $date): void;

    public function getPasswordRequestedAt(): ?\DateTimeImmutable;

    public function isPasswordRequestNonExpired(int $ttl): bool;

    /**
     * @return list<string>
     */
    public function getRealRoles(): array;

    /**
     * @param list<string> $roles
     */
    public function setRealRoles(array $roles): void;

    public function isSuperAdmin(): bool;

    public function setSuperAdmin(bool $boolean): void;

    public function hasGroup(GroupInterface $group): bool;

    public function addGroup(GroupInterface $group): void;

    public function removeGroup(GroupInterface $group): void;

    /**
     * @return Collection<int, GroupInterface>
     */
    public function getGroups(): Collection;
}
