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

namespace Sonata\AdminBundle\Model;

use Sonata\AdminBundle\Exception\LockException;

/**
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 *
 * @phpstan-template T of object
 */
interface LockInterface
{
    /**
     * @param object $object
     *
     * @return mixed|null
     *
     * @phpstan-param T $object
     */
    public function getLockVersion($object);

    /**
     * @param object $object
     * @param mixed  $expectedVersion
     *
     * @throws LockException
     *
     * @phpstan-param T $object
     */
    public function lock($object, $expectedVersion);
}
