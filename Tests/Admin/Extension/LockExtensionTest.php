<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Admin\Extension;

use Sonata\AdminBundle\Admin\Extension\LockExtension;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;

class LockExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LockExtension
     */
    private $lockExtension;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var LockInterface
     */
    private $modelManager;

    /**
     * @var stdClass
     */
    private $object;

    /**
     * @var Request
     */
    private $request;

    public function setUp()
    {
        $this->eventDispatcher = new EventDispatcher();
        $this->request = new Request();
        $this->object = new \stdClass();
        $this->lockExtension = new LockExtension();

        $this->modelManager = $this->getMock('Sonata\AdminBundle\Model\LockInterface');

        $this->admin = $this->getMockBuilder('Sonata\AdminBundle\Admin\AbstractAdmin')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testConfigureFormFields()
    {
        $formMapper = $this->configureFormMapper();
        $form = $this->configureForm();
        $event = new FormEvent($form, array());

        $this->modelManager->expects($this->once())
            ->method('getLockVersion')
            ->will($this->returnValue(1));

        $this->admin->expects($this->once())
            ->method('getModelManager')
            ->will($this->returnValue($this->modelManager));

        $form->expects($this->once())
            ->method('add')
            ->with(
                $this->equalTo('_lock_version'),
                $this->equalTo('hidden'),
                $this->equalTo(array(
                    'mapped' => false,
                    'data' => 1,
                ))
            );

        $this->lockExtension->configureFormFields($formMapper);

        $this->eventDispatcher->dispatch(FormEvents::PRE_SET_DATA, $event);
    }

    public function testConfigureFormFieldsWhenModelManagerIsNotImplementingLockerInterface()
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $formMapper = $this->configureFormMapper();
        $form = $this->configureForm();
        $event = new FormEvent($form, array());

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $form->expects($this->never())
            ->method('add');

        $this->lockExtension->configureFormFields($formMapper);

        $this->eventDispatcher->dispatch(FormEvents::PRE_SET_DATA, $event);
    }

    public function testConfigureFormFieldsWhenFormEventHasNoData()
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $formMapper = $this->configureFormMapper();
        $form = $this->configureForm();
        $event = new FormEvent($form, null);

        $form->expects($this->never())
            ->method('add');

        $this->lockExtension->configureFormFields($formMapper);

        $this->eventDispatcher->dispatch(FormEvents::PRE_SET_DATA, $event);
    }

    public function testConfigureFormFieldsWhenFormHasParent()
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $formMapper = $this->configureFormMapper();
        $form = $this->configureForm();

        $event = new FormEvent($form, array());

        $form->expects($this->once())
            ->method('getParent')
            ->will($this->returnValue('parent'));

        $form->expects($this->never())
            ->method('add');

        $this->lockExtension->configureFormFields($formMapper);

        $this->eventDispatcher->dispatch(FormEvents::PRE_SET_DATA, $event);
    }

    public function testConfigureFormFieldsWhenModelManagerHasNoLockedVersion()
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $formMapper = $this->configureFormMapper();
        $form = $this->configureForm();

        $event = new FormEvent($form, array());

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($this->modelManager));

        $this->modelManager->expects($this->once())
            ->method('getLockVersion')
            ->will($this->returnValue(null));

        $form->expects($this->never())
            ->method('add');

        $this->lockExtension->configureFormFields($formMapper);

        $this->eventDispatcher->dispatch(FormEvents::PRE_SET_DATA, $event);
    }

    public function testPreUpdateIfAdminHasNoRequest()
    {
        $this->modelManager->expects($this->never())
            ->method('lock');

        $this->lockExtension->preUpdate($this->admin, $this->object);
    }

    public function testPreUpdateIfObjectIsNotVersioned()
    {
        $this->configureAdminRequest();

        $this->modelManager->expects($this->never())
            ->method('lock');

        $this->lockExtension->preUpdate($this->admin, $this->object);
    }

    public function testPreUpdateIfRequestDoesNotHaveLockVersion()
    {
        $uniqid = 'admin123';

        $this->admin->expects($this->any())
            ->method('getUniqId')
            ->will($this->returnValue($uniqid));

        $this->configureAdminRequest();

        $this->request->request->set($uniqid, array('something'));

        $this->modelManager->expects($this->never())
            ->method('lock');

        $this->lockExtension->preUpdate($this->admin, $this->object);
    }

    public function testPreUpdateIfModelManagerIsNotImplementingLockerInterface()
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $uniqid = 'admin123';

        $this->admin->expects($this->any())
            ->method('getUniqId')
            ->will($this->returnValue($uniqid));

        $this->configureAdminRequest();

        $this->request->request->set($uniqid, array('_lock_version' => 1));

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $this->modelManager->expects($this->never())
            ->method('lock');

        $this->lockExtension->preUpdate($this->admin, $this->object);
    }

    public function testPreUpdateIfObjectIsVersioned()
    {
        $uniqid = 'admin123';

        $this->modelManager->expects($this->once())
            ->method('lock')
            ->with($this->object, 1);

        $this->configureAdminRequest();

        $this->request->request->set($uniqid, array('_lock_version' => 1));

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($this->modelManager));

        $this->admin->expects($this->any())
            ->method('getUniqId')
            ->will($this->returnValue($uniqid));

        $this->lockExtension->preUpdate($this->admin, $this->object);
    }

    private function configureForm()
    {
        $form = $this->getMock('Symfony\Component\Form\FormInterface');

        $form->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($this->object));

        return $form;
    }

    private function configureFormMapper()
    {
        $contractor = $this->getMock('Sonata\AdminBundle\Builder\FormContractorInterface');
        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $formBuilder = new FormBuilder('form', null, $this->eventDispatcher, $formFactory);
        $formMapper = new FormMapper($contractor, $formBuilder, $this->admin);

        return $formMapper;
    }

    private function configureAdminRequest()
    {
        $this->admin->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->request));

        $this->admin->expects($this->once())
            ->method('hasRequest')
            ->will($this->returnValue(true));
    }
}
