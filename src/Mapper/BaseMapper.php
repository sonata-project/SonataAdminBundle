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
use Sonata\AdminBundle\Builder\BuilderInterface;

/**
 * This class is used to simulate the Form API.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseMapper
{
    /**
     * @var AdminInterface
     */
    protected $admin;

    /**
     * @var BuilderInterface
     */
    protected $builder;

    public function __construct(BuilderInterface $builder, AdminInterface $admin)
    {
        $this->builder = $builder;
        $this->admin = $admin;
    }

    public function getAdmin(): AdminInterface
    {
        return $this->admin;
    }

    /**
     * @return mixed
     */
    abstract public function get(string $key);

    abstract public function has(string $key): bool;

    /**
     * @return $this
     */
    abstract public function remove(string $key);

    /**
     * Returns configured keys.
     *
     * @return string[]
     */
    abstract public function keys(): array;

    /**
     * @param string[] $keys field names
     *
     * @return $this
     */
    abstract public function reorder(array $keys);
}
