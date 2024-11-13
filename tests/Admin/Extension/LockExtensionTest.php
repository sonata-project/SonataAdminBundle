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
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
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

final class LockExtensionTest extends TestCase
{
    private LockExtension $lockExtension;

    private EventDispatcher $eventDispatcher;

    /**
     * @var AdminInterface<object>&Stub
     */
    private AdminInterface $admin;

    /**
     * @var LockInterface<object>&MockObject
     */
    private LockInterface $modelManager;

    private \stdClass $object;

    private Request $request;

    protected function setUp(): void
    {
        $this->modelManager = $this->createMock(LockInterface::class);
        $this->admin = $this->createStub(AdminInterface::class);

        $this->eventDispatcher = new EventDispatcher();
        $this->request = new Request();
        $this->object = new \stdClass();
        $this->lockExtension = new LockExtension();
    }

    public function testModelManagerImplementsLockInterface(): void
    {
        static::assertInstanceOf(LockInterface::class, $this->modelManager);
    }

    public function testConfigureFormFields(): void
    {
        $formMapper = $this->configureFormMapper();
        $form = $this->configureForm();

        $this->configureAdmin($this->modelManager);
        $event = new FormEvent($form, $this->object);

        $this->modelManager->method('getLockVersion')->with($this->object)->willReturn(1);

        $form->expects(static::once())->method('add')->with(
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
        $this->configureAdmin($modelManager);
        $event = new FormEvent($form, $this->object);

        $form->expects(static::never())->method('add');

        $this->lockExtension->configureFormFields($formMapper);
        $this->eventDispatcher->dispatch($event, FormEvents::PRE_SET_DATA);
    }

    public function testConfigureFormFieldsWhenFormEventHasNoData(): void
    {
        $formMapper = $this->configureFormMapper();
        $form = $this->configureForm();
        $event = new FormEvent($form, null);

        $form->expects(static::never())->method('add');

        $this->lockExtension->configureFormFields($formMapper);
        $this->eventDispatcher->dispatch($event, FormEvents::PRE_SET_DATA);
    }

    public function testConfigureFormFieldsWhenFormHasParent(): void
    {
        $formMapper = $this->configureFormMapper();
        $form = $this->configureForm();
        $this->configureAdmin($this->modelManager);
        $event = new FormEvent($form, $this->object);

        $form->method('getParent')->willReturn($this->createStub(FormInterface::class));
        $form->expects(static::never())->method('add');

        $this->lockExtension->configureFormFields($formMapper);
        $this->eventDispatcher->dispatch($event, FormEvents::PRE_SET_DATA);
    }

    public function testConfigureFormFieldsWhenModelManagerHasNoLockedVersion(): void
    {
        $formMapper = $this->configureFormMapper();
        $form = $this->configureForm();
        $this->configureAdmin($this->modelManager);
        $event = new FormEvent($form, $this->object);

        $this->modelManager->method('getLockVersion')->with($this->object)->willReturn(null);
        $form->expects(static::never())->method('add');

        $this->lockExtension->configureFormFields($formMapper);
        $this->eventDispatcher->dispatch($event, FormEvents::PRE_SET_DATA);
    }

    public function testPreUpdateIfAdminHasNoRequest(): void
    {
        $this->configureAdmin($this->modelManager);
        $this->modelManager->expects(static::never())->method('lock');

        $this->lockExtension->preUpdate($this->admin, $this->object);
    }

    public function testPreUpdateIfObjectIsNotVersioned(): void
    {
        $this->configureAdmin($this->modelManager);
        $this->modelManager->expects(static::never())->method('lock');

        $this->lockExtension->preUpdate($this->admin, $this->object);
    }

    public function testPreUpdateIfRequestDoesNotHaveLockVersion(): void
    {
        $uniqId = 'admin123';
        $this->configureAdmin($this->modelManager, $uniqId, $this->request);

        $this->modelManager->expects(static::never())->method('lock');

        $this->request->request->set($uniqId, ['something']);
        $this->lockExtension->preUpdate($this->admin, $this->object);
    }

    public function testPreUpdateIfModelManagerIsNotImplementingLockerInterface(): void
    {
        $uniqId = 'admin123';
        $this->configureAdmin(
            $this->createStub(ModelManagerInterface::class),
            $uniqId,
            $this->request
        );
        $this->modelManager->expects(static::never())->method('lock');

        $this->request->request->set($uniqId, ['_lock_version' => 1]);
        $this->lockExtension->preUpdate($this->admin, $this->object);
    }

    public function testPreUpdateIfObjectIsVersioned(): void
    {
        $uniqId = 'admin123';
        $this->configureAdmin($this->modelManager, $uniqId, $this->request);

        $this->modelManager->expects(static::once())->method('lock')->with($this->object, 1);

        $this->request->request->set($uniqId, ['_lock_version' => 1]);
        $this->lockExtension->preUpdate($this->admin, $this->object);
    }

    /**
     * @return MockObject&FormInterface
     */
    private function configureForm(): MockObject
    {
        $form = $this->createMock(FormInterface::class);

        $form->method('getData')->willReturn($this->object);
        $form->method('getParent')->willReturn(null);

        return $form;
    }

    /**
     * @return FormMapper<object>
     */
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

    /**
     * @param ModelManagerInterface<object> $modelManager
     */
    private function configureAdmin(
        ModelManagerInterface $modelManager,
        string $uniqId = '',
        ?Request $request = null,
    ): void {
        $this->admin->method('getUniqId')->willReturn($uniqId);
        $this->admin->method('getModelManager')->willReturn($modelManager);

        $this->admin->method('hasRequest')->willReturn(null !== $request);
        if (null !== $request) {
            $this->admin->method('getRequest')->willReturn($request);
        }
    }
}
