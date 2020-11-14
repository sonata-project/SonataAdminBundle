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
 */
interface LockInterface extends ModelManagerInterface
{
    /**
     * @return mixed
     */
    public function getLockVersion(object $object);

    /**
     * @throws LockException
     */
    public function lock(object $object, ?int $expectedVersion): void;
}
