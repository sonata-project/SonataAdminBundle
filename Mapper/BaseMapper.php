<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Sonata\AdminBundle\Mapper;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\BuilderInterface;

/**
 * This class is used to simulate the Form API
 *
 */
abstract class BaseMapper
{

    protected $admin;
    
    protected $builder;
    
    /**
     * @param \Sonata\AdminBundle\Builder\BuilderInterface $builder
     * @param \Sonata\AdminBundle\Admin\AdminInterface     $admin
     */
    public function __construct(BuilderInterface $builder, AdminInterface $admin)
    {
        $this->builder = $builder;
        $this->admin   = $admin;
    }
    
    /**
     * @return \Sonata\AdminBundle\Admin\AdminInterface
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
    public abstract function get($key);

    /**
     * @param string $key
     *
     * @return boolean
     */
    public abstract function has($key);

    /**
     * @param string $key
     *
     * @return \Sonata\AdminBundle\Mapper\BaseMapper
     */
    public abstract function remove($key);
    
    /**
     * @param array $keys field names
     *
     * @return \Sonata\AdminBundle\Mapper\BaseMapper
     */
    public abstract function reorder(array $keys);
    
}
