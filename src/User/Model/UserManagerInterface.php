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

/**
 * @phpstan-template T of UserInterface
 */
interface UserManagerInterface
{
    /**
     * @phpstan-return class-string<T>
     */
    public function getClass(): string;

    /**
     * @phpstan-return T
     */
    public function createUser(): UserInterface;

    /**
     * @phpstan-return T|null
     */
    public function findUserByEmail(string $email): ?UserInterface;

    /**
     * @phpstan-return T|null
     */
    public function findUserByConfirmationToken(string $token): ?UserInterface;

    public function updateUser(UserInterface $user): void;

    public function deleteUser(UserInterface $user): void;

    public function updatePassword(UserInterface $user): void;
}
