<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Extension\Event;

use Sonata\AdminBundle\Datagrid\DatagridMapper;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ConfigureDatagridFiltersMessage implements MessageInterface
{
    /**
     * @var DatagridMapper
     */
    private $mapper;

    public function __construct(DatagridMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @return DatagridMapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }
}
