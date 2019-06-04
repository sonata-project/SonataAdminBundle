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

namespace Sonata\AdminBundle\Event;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AdminEventExtension extends AbstractAdminExtension
{
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function configureFormFields(FormMapper $form)
    {
        if ($this->eventDispatcher instanceof ContractsEventDispatcherInterface) {
            $event = new ConfigureEvent($form->getAdmin(), $form, ConfigureEvent::TYPE_FORM);
            $eventName = 'sonata.admin.event.configure.form';
        } else {
            // BC for Symfony < 4.3 where `dispatch()` has a different signature
            // NEXT_MAJOR: Remove this condition
            $event = 'sonata.admin.event.configure.form';
            $eventName = new ConfigureEvent($form->getAdmin(), $form, ConfigureEvent::TYPE_FORM);
        }
        $this->eventDispatcher->dispatch($event, $eventName);
    }

    public function configureListFields(ListMapper $list)
    {
        if ($this->eventDispatcher instanceof ContractsEventDispatcherInterface) {
            $event = new ConfigureEvent($list->getAdmin(), $list, ConfigureEvent::TYPE_LIST);
            $eventName = 'sonata.admin.event.configure.list';
        } else {
            // BC for Symfony < 4.3 where `dispatch()` has a different signature
            // NEXT_MAJOR: Remove this condition
            $event = 'sonata.admin.event.configure.list';
            $eventName = new ConfigureEvent($list->getAdmin(), $list, ConfigureEvent::TYPE_LIST);
        }
        $this->eventDispatcher->dispatch($event, $eventName);
    }

    public function configureDatagridFilters(DatagridMapper $filter)
    {
        if ($this->eventDispatcher instanceof ContractsEventDispatcherInterface) {
            $event = new ConfigureEvent($filter->getAdmin(), $filter, ConfigureEvent::TYPE_DATAGRID);
            $eventName = 'sonata.admin.event.configure.datagrid';
        } else {
            // BC for Symfony < 4.3 where `dispatch()` has a different signature
            // NEXT_MAJOR: Remove this condition
            $event = 'sonata.admin.event.configure.datagrid';
            $eventName = new ConfigureEvent($filter->getAdmin(), $filter, ConfigureEvent::TYPE_DATAGRID);
        }
        $this->eventDispatcher->dispatch($event, $eventName);
    }

    public function configureShowFields(ShowMapper $show)
    {
        if ($this->eventDispatcher instanceof ContractsEventDispatcherInterface) {
            $event = new ConfigureEvent($show->getAdmin(), $show, ConfigureEvent::TYPE_SHOW);
            $eventName = 'sonata.admin.event.configure.show';
        } else {
            // BC for Symfony < 4.3 where `dispatch()` has a different signature
            // NEXT_MAJOR: Remove this condition
            $event = 'sonata.admin.event.configure.show';
            $eventName = new ConfigureEvent($show->getAdmin(), $show, ConfigureEvent::TYPE_SHOW);
        }
        $this->eventDispatcher->dispatch($event, $eventName);
    }

    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context = 'list')
    {
        if ($this->eventDispatcher instanceof ContractsEventDispatcherInterface) {
            $event = new ConfigureQueryEvent($admin, $query, $context);
            $eventName = 'sonata.admin.event.configure.query';
        } else {
            // BC for Symfony < 4.3 where `dispatch()` has a different signature
            // NEXT_MAJOR: Remove this condition
            $event = 'sonata.admin.event.configure.query';
            $eventName = new ConfigureQueryEvent($admin, $query, $context);
        }
        $this->eventDispatcher->dispatch($event, $eventName);
    }

    public function preUpdate(AdminInterface $admin, $object)
    {
        if ($this->eventDispatcher instanceof ContractsEventDispatcherInterface) {
            $event = new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_PRE_UPDATE);
            $eventName = 'sonata.admin.event.persistence.pre_update';
        } else {
            // BC for Symfony < 4.3 where `dispatch()` has a different signature
            // NEXT_MAJOR: Remove this condition
            $event = 'sonata.admin.event.persistence.pre_update';
            $eventName = new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_PRE_UPDATE);
        }
        $this->eventDispatcher->dispatch($event, $eventName);
    }

    public function postUpdate(AdminInterface $admin, $object)
    {
        if ($this->eventDispatcher instanceof ContractsEventDispatcherInterface) {
            $event = new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_POST_UPDATE);
            $eventName = 'sonata.admin.event.persistence.post_update';
        } else {
            // BC for Symfony < 4.3 where `dispatch()` has a different signature
            // NEXT_MAJOR: Remove this condition
            $event = 'sonata.admin.event.persistence.post_update';
            $eventName = new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_POST_UPDATE);
        }
        $this->eventDispatcher->dispatch($event, $eventName);
    }

    public function prePersist(AdminInterface $admin, $object)
    {
        if ($this->eventDispatcher instanceof ContractsEventDispatcherInterface) {
            $event = new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_PRE_PERSIST);
            $eventName = 'sonata.admin.event.persistence.pre_persist';
        } else {
            // BC for Symfony < 4.3 where `dispatch()` has a different signature
            // NEXT_MAJOR: Remove this condition
            $event = 'sonata.admin.event.persistence.pre_persist';
            $eventName = new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_PRE_PERSIST);
        }
        $this->eventDispatcher->dispatch($event, $eventName);
    }

    public function postPersist(AdminInterface $admin, $object)
    {
        if ($this->eventDispatcher instanceof ContractsEventDispatcherInterface) {
            $event = new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_POST_PERSIST);
            $eventName = 'sonata.admin.event.persistence.post_persist';
        } else {
            // BC for Symfony < 4.3 where `dispatch()` has a different signature
            // NEXT_MAJOR: Remove this condition
            $event = 'sonata.admin.event.persistence.post_persist';
            $eventName = new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_POST_PERSIST);
        }
        $this->eventDispatcher->dispatch($event, $eventName);
    }

    public function preRemove(AdminInterface $admin, $object)
    {
        if ($this->eventDispatcher instanceof ContractsEventDispatcherInterface) {
            $event = new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_PRE_REMOVE);
            $eventName = 'sonata.admin.event.persistence.pre_remove';
        } else {
            // BC for Symfony < 4.3 where `dispatch()` has a different signature
            // NEXT_MAJOR: Remove this condition
            $event = 'sonata.admin.event.persistence.pre_remove';
            $eventName = new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_PRE_REMOVE);
        }
        $this->eventDispatcher->dispatch($event, $eventName);
    }

    public function postRemove(AdminInterface $admin, $object)
    {
        if ($this->eventDispatcher instanceof ContractsEventDispatcherInterface) {
            $event = new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_POST_REMOVE);
            $eventName = 'sonata.admin.event.persistence.post_remove';
        } else {
            // BC for Symfony < 4.3 where `dispatch()` has a different signature
            // NEXT_MAJOR: Remove this condition
            $event = 'sonata.admin.event.persistence.post_remove';
            $eventName = new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_POST_REMOVE);
        }
        $this->eventDispatcher->dispatch($event, $eventName);
    }
}
