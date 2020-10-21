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

namespace Sonata\AdminBundle\Tests\Admin\Extension;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Extension\LockExtension;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Model\LockInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LockExtensionTest extends TestCase
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
     * @var \stdClass
     */
    private $object;

    /**
     * @var Request
     */
    private $request;

    protected function setUp(): void
    {
        $this->modelManager = $this->createMock(LockInterface::class);
        $this->admin = $this->createStub(AbstractAdmin::class);

        $this->eventDispatcher = new EventDispatcher();
        $this->request = new Request();
        $this->object = new \stdClass();
        $this->lockExtension = new LockExtension();
    }

    public function testModelManagerImplementsLockInterface(): void
    {
        $this->assertInstanceOf(LockInterface::class, $this->modelManager);
    }

    public function testConfigureFormFields(): void
    {
        $formMapper = $this->configureFormMapper();
        $form = $this->configureForm();

        $this->configureAdmin('', null, $this->modelManager);
        $event = new FormEvent($form, $this->object);

        $this->modelManager->method('getLockVersion')->with($this->object)->willReturn(1);

        $form->expects($this->once())->method('add')->with(
            '_lock_version',
            HiddenType::class,
            ['mapped' => false, 'data' => 1]
        );

        $this->lockExtension->configureFormFields($formMapper);
        $this->eventDispatcher->dispatch($event, FormEvents::PRE_SET_DATA);
    }

    public function testConfigureFormFieldsWhenModelManagerIsNotImplementingLockerInterface(): void
    {
        $modelManager = $this->createStub(ModelManagerInterface::class);
        $formMapper = $this->configureFormMapper();
        $form = $this->configureForm();
        $this->configureAdmin('', null, $modelManager);
        $event = new FormEvent($form, $this->object);

        $form->expects($this->never())->method('add');

        $this->lockExtension->configureFormFields($formMapper);
        $this->eventDispatcher->dispatch($event, FormEvents::PRE_SET_DATA);
    }

    public function testConfigureFormFieldsWhenFormEventHasNoData(): void
    {
        $formMapper = $this->configureFormMapper();
        $form = $this->configureForm();
        $event = new FormEvent($form, null);

        $form->expects($this->never())->method('add');

        $this->lockExtension->configureFormFields($formMapper);
        $this->eventDispatcher->dispatch($event, FormEvents::PRE_SET_DATA);
    }

    public function testConfigureFormFieldsWhenFormHasParent(): void
    {
        $formMapper = $this->configureFormMapper();
        $form = $this->configureForm();
        $event = new FormEvent($form, $this->object);

        $form->method('getParent')->willReturn('parent');
        $form->expects($this->never())->method('add');

        $this->lockExtension->configureFormFields($formMapper);
        $this->eventDispatcher->dispatch($event, FormEvents::PRE_SET_DATA);
    }

    public function testConfigureFormFieldsWhenModelManagerHasNoLockedVersion(): void
    {
        $data = new \stdClass();
        $formMapper = $this->configureFormMapper();
        $form = $this->configureForm();
        $this->configureAdmin('', null, $this->modelManager);
        $event = new FormEvent($form, $this->object);

        $this->modelManager->method('getLockVersion')->with($this->object)->willReturn(null);
        $form->expects($this->never())->method('add');

        $this->lockExtension->configureFormFields($formMapper);
        $this->eventDispatcher->dispatch($event, FormEvents::PRE_SET_DATA);
    }

    public function testPreUpdateIfAdminHasNoRequest(): void
    {
        $this->configureAdmin();
        $this->modelManager->expects($this->never())->method('lock');

        $this->lockExtension->preUpdate($this->admin, $this->object);
    }

    public function testPreUpdateIfObjectIsNotVersioned(): void
    {
        $this->configureAdmin();
        $this->modelManager->expects($this->never())->method('lock');

        $this->lockExtension->preUpdate($this->admin, $this->object);
    }

    public function testPreUpdateIfRequestDoesNotHaveLockVersion(): void
    {
        $uniqid = 'admin123';
        $this->configureAdmin($uniqid, $this->request);

        $this->modelManager->expects($this->never())->method('lock');

        $this->request->request->set($uniqid, ['something']);
        $this->lockExtension->preUpdate($this->admin, $this->object);
    }

    public function testPreUpdateIfModelManagerIsNotImplementingLockerInterface(): void
    {
        $uniqid = 'admin123';
        $this->configureAdmin(
            $uniqid,
            $this->request,
            $this->createStub(ModelManagerInterface::class)
        );
        $this->modelManager->expects($this->never())->method('lock');

        $this->request->request->set($uniqid, ['_lock_version' => 1]);
        $this->lockExtension->preUpdate($this->admin, $this->object);
    }

    public function testPreUpdateIfObjectIsVersioned(): void
    {
        $uniqid = 'admin123';
        $this->configureAdmin($uniqid, $this->request, $this->modelManager);

        $this->modelManager->expects($this->once())->method('lock')->with($this->object, 1);

        $this->request->request->set($uniqid, ['_lock_version' => 1]);
        $this->lockExtension->preUpdate($this->admin, $this->object);
    }

    private function configureForm(): MockObject
    {
        $form = $this->createMock(FormInterface::class);

        $form->method('getData')->willReturn($this->object);
        $form->method('getParent')->willReturn(null);

        return $form;
    }

    private function configureFormMapper(): FormMapper
    {
        $formBuilder = new FormBuilder(
            'form',
            null,
            $this->eventDispatcher,
            $this->createStub(FormFactoryInterface::class)
        );

        return new FormMapper(
            $this->createStub(FormContractorInterface::class),
            $formBuilder,
            $this->admin
        );
    }

    private function configureAdmin(
        string $uniqid = '',
        ?Request $request = null,
        ?ModelManagerInterface $modelManager = null
    ): void {
        $this->admin->method('getUniqid')->willReturn($uniqid);
        $this->admin->method('getModelManager')->willReturn($modelManager);

        $this->admin->method('hasRequest')->willReturn(null !== $request);
        if (null !== $request) {
            $this->admin->method('getRequest')->willReturn($request);
        }
    }
}
