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
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Event\AdminEventExtension;
use Sonata\AdminBundle\Event\ConfigureEvent;
use Sonata\AdminBundle\Event\PersistenceEvent;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AdminEventExtensionTest extends TestCase
{
    /**
     * @return AdminEventExtension
     */
    public function getExtension($args)
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $stub = $eventDispatcher->expects($this->once())->method('dispatch');
        \call_user_func_array([$stub, 'with'], $args);

        return new AdminEventExtension($eventDispatcher);
    }

    public function getMapper($class)
    {
        $mapper = $this->getMockBuilder($class)->disableOriginalConstructor()->getMock();
        $mapper->expects($this->once())->method('getAdmin')->willReturn($this->createMock(AdminInterface::class));

        return $mapper;
    }

    /**
     * @param $type
     *
     * @return callable
     */
    public function getConfigureEventClosure($type)
    {
        return static function ($event) use ($type) {
            if (!$event instanceof ConfigureEvent) {
                return false;
            }

            if ($event->getType() !== $type) {
                return false;
            }

            return true;
        };
    }

    /**
     * @param $type
     *
     * @return callable
     */
    public function getConfigurePersistenceClosure($type)
    {
        return static function ($event) use ($type) {
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
                $this->equalTo('sonata.admin.event.configure.form'),
                $this->callback($this->getConfigureEventClosure(ConfigureEvent::TYPE_FORM)),
            ])
            ->configureFormFields($this->getMapper(FormMapper::class));
    }

    public function testConfigureListFields(): void
    {
        $this
            ->getExtension([
                $this->equalTo('sonata.admin.event.configure.list'),
                $this->callback($this->getConfigureEventClosure(ConfigureEvent::TYPE_LIST)),
            ])
            ->configureListFields($this->getMapper(ListMapper::class));
    }

    public function testConfigureDatagridFields(): void
    {
        $this
            ->getExtension([
                $this->equalTo('sonata.admin.event.configure.datagrid'),
                $this->callback($this->getConfigureEventClosure(ConfigureEvent::TYPE_DATAGRID)),
            ])
            ->configureDatagridFilters($this->getMapper(DatagridMapper::class));
    }

    public function testConfigureShowFields(): void
    {
        $this
            ->getExtension([
                $this->equalTo('sonata.admin.event.configure.show'),
                $this->callback($this->getConfigureEventClosure(ConfigureEvent::TYPE_SHOW)),
            ])
            ->configureShowFields($this->getMapper(ShowMapper::class));
    }

    public function testPreUpdate(): void
    {
        $this->getExtension([
            $this->equalTo('sonata.admin.event.persistence.pre_update'),
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_PRE_UPDATE)),
        ])->preUpdate($this->createMock(AdminInterface::class), new \stdClass());
    }

    public function testConfigureQuery(): void
    {
        $this->getExtension([
            $this->equalTo('sonata.admin.event.configure.query'),
        ])->configureQuery($this->createMock(AdminInterface::class), $this->createMock(ProxyQueryInterface::class));
    }

    public function testPostUpdate(): void
    {
        $this->getExtension([
            $this->equalTo('sonata.admin.event.persistence.post_update'),
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_POST_UPDATE)),
        ])->postUpdate($this->createMock(AdminInterface::class), new \stdClass());
    }

    public function testPrePersist(): void
    {
        $this->getExtension([
            $this->equalTo('sonata.admin.event.persistence.pre_persist'),
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_PRE_PERSIST)),
        ])->prePersist($this->createMock(AdminInterface::class), new \stdClass());
    }

    public function testPostPersist(): void
    {
        $this->getExtension([
            $this->equalTo('sonata.admin.event.persistence.post_persist'),
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_POST_PERSIST)),
        ])->postPersist($this->createMock(AdminInterface::class), new \stdClass());
    }

    public function testPreRemove(): void
    {
        $this->getExtension([
            $this->equalTo('sonata.admin.event.persistence.pre_remove'),
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_PRE_REMOVE)),
        ])->preRemove($this->createMock(AdminInterface::class), new \stdClass());
    }

    public function testPostRemove(): void
    {
        $this->getExtension([
            $this->equalTo('sonata.admin.event.persistence.post_remove'),
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_POST_REMOVE)),
        ])->postRemove($this->createMock(AdminInterface::class), new \stdClass());
    }
}
