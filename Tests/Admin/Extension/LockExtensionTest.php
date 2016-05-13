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

    private $admin;

    private $modelManager;

    private $form;

    private $object;

    private $request;

    public function setUp()
    {
        $contractor = $this->getMock('Sonata\AdminBundle\Builder\FormContractorInterface');

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $this->eventDispatcher = new EventDispatcher();

        $formBuilder = new FormBuilder('form', null, $this->eventDispatcher, $formFactory);

        $this->modelManager = $this->getMock('Sonata\AdminBundle\Model\LockInterface');

        $this->admin = $this->getMockBuilder('Sonata\AdminBundle\Admin\AbstractAdmin')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($this->modelManager));

        $this->request = new Request();

        $this->admin->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request));

        $this->admin->expects($this->any())
            ->method('hasRequest')
            ->will($this->returnValue(true));

        $formMapper = new FormMapper(
            $contractor,
            $formBuilder,
            $this->admin
        );

        $this->object = new \StdClass();

        $this->form = $this->getMock('Symfony\Component\Form\FormInterface');

        $this->form->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($this->object));

        $this->lockExtension = new LockExtension();
        $this->lockExtension->configureFormFields($formMapper);
    }

    public function testConfigureFormFields()
    {
        $this->modelManager->expects($this->any())
            ->method('getLockVersion')
            ->will($this->returnValue(1));

        $this->form->expects($this->once())
            ->method('add')
            ->with(
                $this->equalTo('_lock_version'),
                $this->equalTo('hidden'),
                $this->equalTo(array(
                    'mapped' => false,
                    'data' => 1,
                ))
            );

        $event = new FormEvent($this->form, array());
        $this->eventDispatcher->dispatch(FormEvents::PRE_SET_DATA, $event);
    }

    public function testPreUpdateIfObjectIsNotVersioned()
    {
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

        $this->request->request->set($uniqid, array('_lock_version' => 1));

        $this->admin->expects($this->any())
            ->method('getUniqId')
            ->will($this->returnValue($uniqid));

        $this->lockExtension->preUpdate($this->admin, $this->object);
    }
}
