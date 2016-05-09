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
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is sent by hook:
 *   - preUpdate | postUpdate
 *   - prePersist | postPersist
 *   - preRemove | postRemove.
 *
 * You can register the listener to the event dispatcher by using:
 *   - sonata.admin.event.persistence.[pre|post]_[persist|update|remove)
 *   - sonata.admin.event.persistence.[admin_code].[pre|post]_[persist|update|remove)  (not implemented yet)
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class PersistenceEvent extends Event
{
    const TYPE_PRE_UPDATE = 'pre_update';
    const TYPE_POST_UPDATE = 'post_update';
    const TYPE_PRE_PERSIST = 'pre_persist';
    const TYPE_POST_PERSIST = 'post_persist';
    const TYPE_PRE_REMOVE = 'pre_remove';
    const TYPE_POST_REMOVE = 'post_remove';

    /**
     * @var AdminInterface
     */
    protected $admin;

    /**
     * @var object
     */
    protected $object;

    /**
     * @var string
     */
    protected $type;

    /**
     * @param AdminInterface $admin
     * @param object         $object
     * @param string         $type
     */
    public function __construct(AdminInterface $admin, $object, $type)
    {
        $this->admin = $admin;
        $this->object = $object;
        $this->type = $type;
    }

    /**
     * @return AdminInterface
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
}
