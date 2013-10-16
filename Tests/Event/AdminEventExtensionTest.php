<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Event;

use Sonata\AdminBundle\Event\AdminEventExtension;
use Sonata\AdminBundle\Event\ConfigureEvent;
use Sonata\AdminBundle\Event\PersistenceEvent;

class AdminEventExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return AdminEventExtension
     */
    public function getExtension($args)
    {
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $stub = $eventDispatcher->expects($this->once())->method('dispatch');
        call_user_func_array(array($stub, 'with'), $args);

        return new AdminEventExtension($eventDispatcher);
    }

    public function getMapper($class)
    {
        $mapper = $this->getMockBuilder($class)->disableOriginalConstructor()->getMock();
        $mapper->expects($this->once())->method('getAdmin')->will($this->returnValue($this->getMock('Sonata\AdminBundle\Admin\AdminInterface')));

        return $mapper;
    }

    /**
     * @param $type
     * @return callable
     */
    public function getConfigureEventClosure($type)
    {
        return function ($event) use ($type) {
            if (!$event instanceof ConfigureEvent) {
                return false;
            }

            if ($event->getType() != $type) {
                return false;
            }

            return true;
        };
    }

    /**
     * @param $type
     * @return callable
     */
    public function getConfigurePersistenceClosure($type)
    {
        return function ($event) use ($type) {
            if (!$event instanceof PersistenceEvent) {
                return false;
            }

            if ($event->getType() != $type) {
                return false;
            }

            return true;
        };
    }

    public function testConfigureFormFields()
    {
        $this
            ->getExtension(array(
                $this->equalTo('sonata.admin.event.configure.form'),
                $this->callback($this->getConfigureEventClosure(ConfigureEvent::TYPE_FORM))
            ))
            ->configureFormFields($this->getMapper('Sonata\AdminBundle\Form\FormMapper'));
    }

    public function testConfigureListFields()
    {
        $this
            ->getExtension(array(
                $this->equalTo('sonata.admin.event.configure.list'),
                $this->callback($this->getConfigureEventClosure(ConfigureEvent::TYPE_LIST))
            ))
            ->configureListFields($this->getMapper('Sonata\AdminBundle\Datagrid\ListMapper'));
    }

    public function testConfigureDatagridFields()
    {
        $this
            ->getExtension(array(
                $this->equalTo('sonata.admin.event.configure.datagrid'),
                $this->callback($this->getConfigureEventClosure(ConfigureEvent::TYPE_DATAGRID))
            ))
            ->configureDatagridFilters($this->getMapper('Sonata\AdminBundle\Datagrid\DatagridMapper'));
    }

    public function testConfigureShowFields()
    {
        $this
            ->getExtension(array(
                $this->equalTo('sonata.admin.event.configure.show'),
                $this->callback($this->getConfigureEventClosure(ConfigureEvent::TYPE_SHOW))
            ))
            ->configureShowFields($this->getMapper('Sonata\AdminBundle\Show\ShowMapper'));
    }

    public function testPreUpdate()
    {
        $this->getExtension(array(
            $this->equalTo('sonata.admin.event.persistence.pre_update'),
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_PRE_UPDATE))
        ))->preUpdate($this->getMock('Sonata\AdminBundle\Admin\AdminInterface'), new \stdClass);
    }

    public function testPostUpdate()
    {
        $this->getExtension(array(
            $this->equalTo('sonata.admin.event.persistence.post_update'),
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_POST_UPDATE))
        ))->postUpdate($this->getMock('Sonata\AdminBundle\Admin\AdminInterface'), new \stdClass);
    }

    public function testPrePersist()
    {
        $this->getExtension(array(
            $this->equalTo('sonata.admin.event.persistence.pre_persist'),
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_PRE_PERSIST))
        ))->prePersist($this->getMock('Sonata\AdminBundle\Admin\AdminInterface'), new \stdClass);
    }

    public function testPostPersist()
    {
        $this->getExtension(array(
            $this->equalTo('sonata.admin.event.persistence.post_persist'),
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_POST_PERSIST))
        ))->postPersist($this->getMock('Sonata\AdminBundle\Admin\AdminInterface'), new \stdClass);
    }

    public function testPreRemove()
    {
        $this->getExtension(array(
            $this->equalTo('sonata.admin.event.persistence.pre_remove'),
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_PRE_REMOVE))
        ))->preRemove($this->getMock('Sonata\AdminBundle\Admin\AdminInterface'), new \stdClass);
    }

    public function testPostRemove()
    {
        $this->getExtension(array(
            $this->equalTo('sonata.admin.event.persistence.post_remove'),
            $this->callback($this->getConfigurePersistenceClosure(PersistenceEvent::TYPE_POST_REMOVE))
        ))->postRemove($this->getMock('Sonata\AdminBundle\Admin\AdminInterface'), new \stdClass);
    }

}