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

interface GroupInterface
{
    public function getId(): ?int;

    public function setName(string $name): void;

    public function getName(): ?string;

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): void;

    /**
     * @return list<string>
     */
    public function getRoles(): array;

    public function hasRole(string $role): bool;

    public function addRole(string $role): void;

    public function removeRole(string $role): void;
}
