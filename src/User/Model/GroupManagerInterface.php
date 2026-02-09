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
 * @phpstan-template T of GroupInterface
 */
interface GroupManagerInterface
{
    /**
     * @phpstan-return class-string<T>
     */
    public function getClass(): string;

    /**
     * @phpstan-return T
     */
    public function createGroup(): GroupInterface;

    /**
     * @param array<string, mixed> $criteria
     *
     * @phpstan-return T|null
     */
    public function findGroupBy(array $criteria): ?GroupInterface;

    public function updateGroup(GroupInterface $group): void;

    public function deleteGroup(GroupInterface $group): void;
}
