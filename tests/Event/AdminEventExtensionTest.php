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

namespace Sonata\AdminBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminExtensionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Event\AdminEventExtension;
use Sonata\AdminBundle\Event\ConfigureEvent;
use Sonata\AdminBundle\Event\ConfigureQueryEvent;
use Sonata\AdminBundle\Event\PersistenceEvent;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdminEventExtensionTest extends TestCase
{
    public function getExtension(array $args): AdminExtensionInterface
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $stub = $eventDispatcher->expects($this->once())->method('dispatch');
        $stub->with(...$args);

        return new AdminEventExtension($eventDispatcher);
    }

    public function getConfigureEventClosure(string $type): callable
    {
        return static function ($event) use ($type): bool {
            if (!$event instanceof ConfigureEvent) {
                return false;
            }

            if ($event->getType() !== $type) {
                return false;
            }

            return true;
        };
    }

    public function getConfigurePersistenceClosure(string $type): callable
    {
        return static function ($event) use ($type): bool {
            if (!$event instanceof PersistenceEvent) {
                return false;
            }

            if ($event->getType() !== $type) {
                return false;
            }

            return true;
        };
    }

    public function testConfigureFormFields(): void
    {
        $this
            ->getExtension([
                $this->callback($this->getConfigureEventClosure(ConfigureEvent::TYPE_FORM)),
                $this->equalTo('sonata.admin.event.configure.form'),
            ])
            ->configureFormFields(new FormMapper(
                $this->createStub(FormContractorInterface::class),
                $this->createStub(FormBuilderInterface::class),
                $this->createStub(AdminInterface::class)
            ));
    }

    public function testConfigureListFields(): void
    {
        $this
            ->getExtension([
                $this->callback($this->getConfigureEventClosure(ConfigureEvent::TYPE_LIST)),
                $this->equalTo('sonata.admin.event.configure.list'),
            ])
            ->configureListFields(new ListMapper(
                $this->createStub(ListBuilderInterface::class),
                new FieldDescriptionCollection(),
                $this->createStub(AdminInterface::class)
            ));
    }

    public function testConfigureDatagridFields(): void
    {
        $this
            ->getExtension([
                $this->callback($this->getConfigureEventClosure(ConfigureEvent::TYPE_DATAGRID)),
                $this->equalTo('sonata.admin.event.configure.datagrid'),
            ])
            ->configureDatagridFilters(new DatagridMapper(
                $this->createStub(DatagridBuilderInterface::class),
                $this->createStub(DatagridInterface::class),
                $this->createStub(AdminInterface::class)
            ));
    }

    public function testConfigureShowFields(): void
    {
        $this
            ->getExtension([
                $this->callback($this->getConfigureEventClosure(ConfigureEvent::TYPE_SHOW)),
                $this->equalTo('sonata.admin.event.configure.show'),
            ])
            ->configureShowFields(new ShowMapper(
                $this->createStub(ShowBuilderInterface::class),
                new FieldDescriptionCollection(),
                $this->createStub(AdminInterface::class)
            ));
    }

    public function testPreUpdate(): void
    {
        $this->getExtension([
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_PRE_UPDATE)),
            $this->equalTo('sonata.admin.event.persistence.pre_update'),
        ])->preUpdate($this->createMock(AdminInterface::class), new \stdClass());
    }

    public function testConfigureQuery(): void
    {
        $this->getExtension([
            $this->isInstanceOf(ConfigureQueryEvent::class),
            $this->equalTo('sonata.admin.event.configure.query'),
        ])->configureQuery($this->createMock(AdminInterface::class), $this->createMock(ProxyQueryInterface::class));
    }

    public function testPostUpdate(): void
    {
        $this->getExtension([
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_POST_UPDATE)),
            $this->equalTo('sonata.admin.event.persistence.post_update'),
        ])->postUpdate($this->createMock(AdminInterface::class), new \stdClass());
    }

    public function testPrePersist(): void
    {
        $this->getExtension([
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_PRE_PERSIST)),
            $this->equalTo('sonata.admin.event.persistence.pre_persist'),
        ])->prePersist($this->createMock(AdminInterface::class), new \stdClass());
    }

    public function testPostPersist(): void
    {
        $this->getExtension([
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_POST_PERSIST)),
            $this->equalTo('sonata.admin.event.persistence.post_persist'),
        ])->postPersist($this->createMock(AdminInterface::class), new \stdClass());
    }

    public function testPreRemove(): void
    {
        $this->getExtension([
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_PRE_REMOVE)),
            $this->equalTo('sonata.admin.event.persistence.pre_remove'),
        ])->preRemove($this->createMock(AdminInterface::class), new \stdClass());
    }

    public function testPostRemove(): void
    {
        $this->getExtension([
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_POST_REMOVE)),
            $this->equalTo('sonata.admin.event.persistence.post_remove'),
        ])->postRemove($this->createMock(AdminInterface::class), new \stdClass());
    }
}
