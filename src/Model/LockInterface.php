<?php

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
 */
interface LockInterface
{
    /**
     * @param object $object
     *
     * @return mixed|null
     */
    public function getLockVersion($object);

    /**
     * @param object $object
     * @param mixed  $expectedVersion
     *
     * @throws LockException
     */
    public function lock($object, $expectedVersion);
}
