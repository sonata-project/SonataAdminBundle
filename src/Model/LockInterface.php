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

namespace SensioLabs\AdminBundle\Model;

use SensioLabs\AdminBundle\Exception\LockException;

/**
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 *
 * @phpstan-template T of object
 * @phpstan-extends ModelManagerInterface<T>
 */
interface LockInterface extends ModelManagerInterface
{
    /**
     * @return mixed
     *
     * @phpstan-param T $object
     */
    public function getLockVersion(object $object);

    /**
     * @throws LockException
     *
     * @phpstan-param T $object
     */
    public function lock(object $object, ?int $expectedVersion): void;
}
