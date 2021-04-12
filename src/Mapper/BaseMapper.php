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
abstract class BaseMapper implements MapperInterface
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

    public function getAdmin()
    {
        return $this->admin;
    }
}
