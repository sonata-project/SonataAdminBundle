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

namespace Sonata\AdminBundle\Mapper;

use Sonata\AdminBundle\Admin\AdminInterface;

/**
 * This interface is used to simulate the Form API.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of object
 */
interface MapperInterface
{
    /**
     * @phpstan-return AdminInterface<T>
     */
    public function getAdmin(): AdminInterface;

    /**
     * @return mixed
     */
    public function get(string $key);

    public function has(string $key): bool;

    /**
     * @return static
     */
    public function remove(string $key);

    /**
     * Returns configured keys.
     *
     * @return string[]
     */
    public function keys(): array;

    /**
     * @param string[] $keys
     *
     * @return static
     */
    public function reorder(array $keys);
}
