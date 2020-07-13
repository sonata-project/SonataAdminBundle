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

namespace Sonata\AdminBundle\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Bar;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorBuilder;

class AdminHelperTest extends TestCase
{
    /**
     * @var AdminHelper
     */
    protected $helper;

    protected function setUp(): void
    {
        $container = new Container();

        $pool = new Pool($container, 'title', 'logo.png');
        $this->helper = new AdminHelper($pool);
    }

    public function testGetChildFormBuilder(): void
    {
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $formBuilder = new FormBuilder('test', \stdClass::class, $eventDispatcher, $formFactory);

        $childFormBuilder = new FormBuilder('elementId', \stdClass::class, $eventDispatcher, $formFactory);
        $formBuilder->add($childFormBuilder);

        $this->assertNull($this->helper->getChildFormBuilder($formBuilder, 'foo'));
        $this->assertInstanceOf(FormBuilder::class, $this->helper->getChildFormBuilder($formBuilder, 'test_elementId'));
    }

    public function testGetChildFormView(): void
    {
        $formView = new FormView();
        $formView->vars['id'] = 'test';
        $child = new FormView($formView);
        $formView->children[] = $child;
        $child->vars['id'] = 'test_elementId';

        $this->assertNull($this->helper->getChildFormView($formView, 'foo'));
        $this->assertInstanceOf(FormView::class, $this->helper->getChildFormView($formView, 'test_elementId'));
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     *
     * @expectedDeprecation Method Sonata\AdminBundle\Admin\AdminHelper::addNewInstance() is deprecated since sonata-project/admin-bundle 3.x. It will be removed in version 4.0. Use Sonata\AdminBundle\Manipulator\ObjectManipulator::addInstance() instead.
     */
    public function testAddNewInstance(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('getNewInstance')->willReturn(new \stdClass());

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getAssociationAdmin')->willReturn($admin);
        $fieldDescription->expects($this->once())->method('getAssociationMapping')->willReturn(['fieldName' => 'fooBar']);
        $fieldDescription->expects($this->once())->method('getParentAssociationMappings')->willReturn([]);

        $object = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['addFooBar'])
            ->getMock();
        $object->expects($this->once())->method('addFooBar');

        $this->helper->addNewInstance($object, $fieldDescription);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     *
     * @expectedDeprecation Method Sonata\AdminBundle\Admin\AdminHelper::addNewInstance() is deprecated since sonata-project/admin-bundle 3.x. It will be removed in version 4.0. Use Sonata\AdminBundle\Manipulator\ObjectManipulator::addInstance() instead.
     */
    public function testAddNewInstancePlural(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('getNewInstance')->willReturn(new \stdClass());

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getAssociationAdmin')->willReturn($admin);
        $fieldDescription->expects($this->once())->method('getAssociationMapping')->willReturn(['fieldName' => 'fooBars']);
        $fieldDescription->expects($this->once())->method('getParentAssociationMappings')->willReturn([]);

        $object = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['addFooBar'])
            ->getMock();
        $object->expects($this->once())->method('addFooBar');

        $this->helper->addNewInstance($object, $fieldDescription);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     *
     * @expectedDeprecation Method Sonata\AdminBundle\Admin\AdminHelper::addNewInstance() is deprecated since sonata-project/admin-bundle 3.x. It will be removed in version 4.0. Use Sonata\AdminBundle\Manipulator\ObjectManipulator::addInstance() instead.
     */
    public function testAddNewInstanceInflector(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('getNewInstance')->willReturn(new \stdClass());

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getAssociationAdmin')->willReturn($admin);
        $fieldDescription->expects($this->once())->method('getAssociationMapping')->willReturn(['fieldName' => 'entries']);
        $fieldDescription->expects($this->once())->method('getParentAssociationMappings')->willReturn([]);

        $object = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['addEntry'])
            ->getMock();
        $object->expects($this->once())->method('addEntry');

        $this->helper->addNewInstance($object, $fieldDescription);
    }

    public function testGetElementAccessPath(): void
    {
        $object = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getPathToObject'])
            ->getMock();
        $subObject = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getAnother'])
            ->getMock();
        $sub2Object = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getMoreThings'])
            ->getMock();

        $object->expects($this->atLeastOnce())->method('getPathToObject')->willReturn([$subObject]);
        $subObject->expects($this->atLeastOnce())->method('getAnother')->willReturn($sub2Object);
        $sub2Object->expects($this->atLeastOnce())->method('getMoreThings')->willReturn('Value');

        $path = $this->helper->getElementAccessPath('uniquePartOfId_path_to_object_0_another_more_things', $object);

        $this->assertSame('path_to_object[0].another.more_things', $path);
    }

    public function testItThrowsExceptionWhenDoesNotFindTheFullPath(): void
    {
        $path = 'uniquePartOfId_path_to_object_0_more_calls';
        $object = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getPathToObject'])
            ->getMock();
        $subObject = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getMore'])
            ->getMock();

        $object->expects($this->atLeastOnce())->method('getPathToObject')->willReturn([$subObject]);
        $subObject->expects($this->atLeastOnce())->method('getMore')->willReturn('Value');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(sprintf('Could not get element id from %s Failing part: calls', $path));

        $this->helper->getElementAccessPath($path, $object);
    }

    public function testAppendFormFieldElement(): void
    {
        $container = new Container();

        $propertyAccessorBuilder = new PropertyAccessorBuilder();
        $propertyAccessor = $propertyAccessorBuilder->getPropertyAccessor();
        $pool = new Pool($container, 'title', 'logo.png', [], $propertyAccessor);
        $helper = new AdminHelper($pool);

        $admin = $this->createMock(AdminInterface::class);
        $admin
            ->method('getClass')
            ->willReturn(Foo::class);

        $associationAdmin = $this->createMock(AdminInterface::class);
        $associationAdmin
            ->method('getClass')
            ->willReturn(Bar::class);

        $associationMapping = [
            'fieldName' => 'bar',
            'targetEntity' => Foo::class,
            'sourceEntity' => Foo::class,
            'isOwningSide' => false,
        ];

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->method('getAssociationAdmin')->willReturn($associationAdmin);
        $fieldDescription->method('getAssociationMapping')->willReturn($associationMapping);
        $fieldDescription->method('getParentAssociationMappings')->willReturn([]);

        $admin
            ->method('getFormFieldDescription')
            ->willReturn($fieldDescription);

        $associationAdmin
            ->method('getFormFieldDescriptions')
            ->willReturn([
                'bar' => $fieldDescription,
            ]);

        $request = $this->createMock(Request::class);
        $request
            ->method('get')
            ->willReturn([
                'bar' => [
                    [
                        'baz' => [
                            'baz' => true,
                        ],
                    ],
                    ['_delete' => true],
                ],
            ]);

        $request->request = new ParameterBag();

        $admin
            ->method('getRequest')
            ->will($this->onConsecutiveCalls($request, $request, $request, null, $request, $request, $request, $request, null, $request));

        $foo = $this->createMock(Foo::class);
        $admin
            ->method('hasSubject')
            ->willReturn(true);
        $admin
            ->method('getSubject')
            ->willReturn($foo);

        $bar = new \stdClass();
        $associationAdmin
            ->expects($this->atLeastOnce())
            ->method('getNewInstance')
            ->willReturn($bar);

        $foo->expects($this->atLeastOnce())->method('addBar')->with($bar);

        $dataMapper = $this->createMock(DataMapperInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $formBuilder = new FormBuilder('test', \get_class($foo), $eventDispatcher, $formFactory);
        $childFormBuilder = new FormBuilder('bar', \stdClass::class, $eventDispatcher, $formFactory);
        $childFormBuilder->setCompound(true);
        $childFormBuilder->setDataMapper($dataMapper);
        $subChildFormBuilder = new FormBuilder('baz', \stdClass::class, $eventDispatcher, $formFactory);
        $subChildFormBuilder->setCompound(true);
        $subChildFormBuilder->setDataMapper($dataMapper);
        $childFormBuilder->add($subChildFormBuilder);

        $formBuilder->setCompound(true);
        $formBuilder->setDataMapper($dataMapper);
        $formBuilder->add($childFormBuilder);

        $associationAdmin->expects($this->atLeastOnce())->method('setSubject')->with($bar);
        $admin->method('getFormBuilder')->willReturn($formBuilder);

        $finalForm = $helper->appendFormFieldElement($admin, $foo, 'test_bar')[1];

        foreach ($finalForm->get($childFormBuilder->getName()) as $childField) {
            $this->assertFalse($childField->has('_delete'));
        }

        $deleteFormBuilder = new FormBuilder('_delete', null, $eventDispatcher, $formFactory);
        $subChildFormBuilder->add($deleteFormBuilder, CheckboxType::class, ['delete' => false]);

        $finalForm = $helper->appendFormFieldElement($admin, $foo, 'test_bar')[1];

        foreach ($finalForm->get($childFormBuilder->getName()) as $childField) {
            $this->assertTrue($childField->has('_delete'));
            $this->assertSame('', $childField->get('_delete')->getData());
        }
    }

    public function testAppendFormFieldElementNested(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $object = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getSubObject'])
            ->getMock();

        $subObject = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getAnd'])
            ->getMock();
        $sub2Object = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getMore'])
            ->getMock();
        $sub3Object = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getFinalData'])
            ->getMock();
        $dataMapper = $this->createMock(DataMapperInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $formBuilder = new FormBuilder('test', \get_class($object), $eventDispatcher, $formFactory);
        $childFormBuilder = new FormBuilder('subObject', \get_class($subObject), $eventDispatcher, $formFactory);

        $object->expects($this->atLeastOnce())->method('getSubObject')->willReturn([$subObject]);
        $subObject->expects($this->atLeastOnce())->method('getAnd')->willReturn($sub2Object);
        $sub2Object->expects($this->atLeastOnce())->method('getMore')->willReturn([$sub3Object]);
        $sub3Object->expects($this->atLeastOnce())->method('getFinalData')->willReturn('value');

        $formBuilder->setCompound(true);
        $formBuilder->setDataMapper($dataMapper);
        $formBuilder->add($childFormBuilder);

        $admin->method('hasSubject')->willReturn(true);
        $admin->method('getSubject')->willReturn($object);
        $admin->expects($this->once())->method('getFormBuilder')->willReturn($formBuilder);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('unknown collection class');

        $this->helper->appendFormFieldElement($admin, $object, 'uniquePartOfId_sub_object_0_and_more_0_final_data');
    }
}
