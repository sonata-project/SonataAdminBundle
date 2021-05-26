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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-extends AbstractAdminExtension<object>
 */
final class AdminEventExtension extends AbstractAdminExtension
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function configureFormFields(FormMapper $form): void
    {
        $this->eventDispatcher->dispatch(
            new ConfigureEvent($form->getAdmin(), $form, ConfigureEvent::TYPE_FORM),
            'sonata.admin.event.configure.form'
        );
    }

    public function configureListFields(ListMapper $list): void
    {
        $this->eventDispatcher->dispatch(
            new ConfigureEvent($list->getAdmin(), $list, ConfigureEvent::TYPE_LIST),
            'sonata.admin.event.configure.list'
        );
    }

    public function configureDatagridFilters(DatagridMapper $filter): void
    {
        $this->eventDispatcher->dispatch(
            new ConfigureEvent($filter->getAdmin(), $filter, ConfigureEvent::TYPE_DATAGRID),
            'sonata.admin.event.configure.datagrid'
        );
    }

    public function configureShowFields(ShowMapper $show): void
    {
        $this->eventDispatcher->dispatch(
            new ConfigureEvent($show->getAdmin(), $show, ConfigureEvent::TYPE_SHOW),
            'sonata.admin.event.configure.show'
        );
    }

    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, string $context = 'list'): void
    {
        $this->eventDispatcher->dispatch(
            new ConfigureQueryEvent($admin, $query, $context),
            'sonata.admin.event.configure.query'
        );
    }

    public function preUpdate(AdminInterface $admin, object $object): void
    {
        $this->eventDispatcher->dispatch(
            new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_PRE_UPDATE),
            'sonata.admin.event.persistence.pre_update'
        );
    }

    public function postUpdate(AdminInterface $admin, object $object): void
    {
        $this->eventDispatcher->dispatch(
            new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_POST_UPDATE),
            'sonata.admin.event.persistence.post_update'
        );
    }

    public function prePersist(AdminInterface $admin, object $object): void
    {
        $this->eventDispatcher->dispatch(
            new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_PRE_PERSIST),
            'sonata.admin.event.persistence.pre_persist'
        );
    }

    public function postPersist(AdminInterface $admin, object $object): void
    {
        $this->eventDispatcher->dispatch(
            new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_POST_PERSIST),
            'sonata.admin.event.persistence.post_persist'
        );
    }

    public function preRemove(AdminInterface $admin, object $object): void
    {
        $this->eventDispatcher->dispatch(
            new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_PRE_REMOVE),
            'sonata.admin.event.persistence.pre_remove'
        );
    }

    public function postRemove(AdminInterface $admin, object $object): void
    {
        $this->eventDispatcher->dispatch(
            new PersistenceEvent($admin, $object, PersistenceEvent::TYPE_POST_REMOVE),
            'sonata.admin.event.persistence.post_remove'
        );
    }
}
