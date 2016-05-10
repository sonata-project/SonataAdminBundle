<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Event;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Mapper\BaseMapper;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is sent by hook:
 *   - configureFormFields
 *   - configureListFields
 *   - configureDatagridFilters
 *   - configureShowFields.
 *
 * You can register the listener to the event dispatcher by using:
 *   - sonata.admin.event.configure.[form|list|datagrid|show]
 *   - sonata.admin.event.configure.[admin_code].[form|list|datagrid|show] (not implemented yet)
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ConfigureEvent extends Event
{
    const TYPE_SHOW = 'show';
    const TYPE_DATAGRID = 'datagrid';
    const TYPE_FORM = 'form';
    const TYPE_LIST = 'list';

    /**
     * @var AdminInterface
     */
    protected $admin;

    /**
     * @var BaseMapper
     */
    protected $mapper;

    /**
     * @var string
     */
    protected $type;

    /**
     * @param AdminInterface $admin
     * @param BaseMapper     $mapper
     * @param string         $type
     */
    public function __construct(AdminInterface $admin, BaseMapper $mapper, $type)
    {
        $this->admin = $admin;
        $this->mapper = $mapper;
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return AdminInterface
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @return BaseMapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }
}
