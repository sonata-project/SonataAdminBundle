<?php

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
 * Class BaseMapper
 * This class is used to simulate the Form API.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
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

    /**
     * @param BuilderInterface $builder
     * @param AdminInterface   $admin
     */
    public function __construct(BuilderInterface $builder, AdminInterface $admin)
    {
        $this->builder = $builder;
        $this->admin   = $admin;
    }

    /**
     * @return AdminInterface
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    abstract public function get($key);

    /**
     * @param string $key
     *
     * @return bool
     */
    abstract public function has($key);

    /**
     * @param string $key
     *
     * @return $this
     */
    abstract public function remove($key);

    /**
     * @param array $keys field names
     *
     * @return $this
     */
    abstract public function reorder(array $keys);
}
