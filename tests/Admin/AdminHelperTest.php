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

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Symfony\Component\DependencyInjection\ContainerInterface;
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

    public function setUp(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $pool = new Pool($container, 'title', 'logo.png');
        $this->helper = new AdminHelper($pool);
    }

    public function testGetChildFormBuilder(): void
    {
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $formBuilder = new FormBuilder('test', 'stdClass', $eventDispatcher, $formFactory);

        $childFormBuilder = new FormBuilder('elementId', 'stdClass', $eventDispatcher, $formFactory);
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

    public function testAddNewInstance(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('getNewInstance')->willReturn(new \stdClass());

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getAssociationAdmin')->willReturn($admin);
        $fieldDescription->expects($this->once())->method('getAssociationMapping')->willReturn(['fieldName' => 'fooBar']);

        $object = $this->getMockBuilder('stdClass')
            ->setMethods(['addFooBar'])
            ->getMock();
        $object->expects($this->once())->method('addFooBar');

        $this->helper->addNewInstance($object, $fieldDescription);
    }

    public function testAddNewInstancePlural(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('getNewInstance')->willReturn(new \stdClass());

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getAssociationAdmin')->willReturn($admin);
        $fieldDescription->expects($this->once())->method('getAssociationMapping')->willReturn(['fieldName' => 'fooBars']);

        $object = $this->getMockBuilder('stdClass')
            ->setMethods(['addFooBar'])
            ->getMock();
        $object->expects($this->once())->method('addFooBar');

        $this->helper->addNewInstance($object, $fieldDescription);
    }

    public function testAddNewInstanceInflector(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('getNewInstance')->willReturn(new \stdClass());

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getAssociationAdmin')->willReturn($admin);
        $fieldDescription->expects($this->once())->method('getAssociationMapping')->willReturn(['fieldName' => 'entries']);

        $object = $this->getMockBuilder('stdClass')
            ->setMethods(['addEntry'])
            ->getMock();
        $object->expects($this->once())->method('addEntry');

        $this->helper->addNewInstance($object, $fieldDescription);
    }

    public function testGetElementAccessPath(): void
    {
        $object = $this->getMockBuilder('stdClass')
            ->setMethods(['getPathToObject'])
            ->getMock();
        $subObject = $this->getMockBuilder('stdClass')
            ->setMethods(['getAnother'])
            ->getMock();
        $sub2Object = $this->getMockBuilder('stdClass')
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
        $object = $this->getMockBuilder('stdClass')
            ->setMethods(['getPathToObject'])
            ->getMock();
        $subObject = $this->getMockBuilder('stdClass')
            ->setMethods(['getMore'])
            ->getMock();

        $object->expects($this->atLeastOnce())->method('getPathToObject')->willReturn([$subObject]);
        $subObject->expects($this->atLeastOnce())->method('getMore')->willReturn('Value');

        $this->expectException(\Exception::class, 'Could not get element id from '.$path.' Failing part: calls');

        $this->helper->getElementAccessPath($path, $object);
    }

    public function testAppendFormFieldElement(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $propertyAccessorBuilder = new PropertyAccessorBuilder();
        $propertyAccesor = $propertyAccessorBuilder->getPropertyAccessor();
        $pool = new Pool($container, 'title', 'logo.png', [], $propertyAccesor);
        $helper = new AdminHelper($pool);

        $admin = $this->createMock(AdminInterface::class);
        $admin
            ->expects($this->any())
            ->method('getClass')
            ->willReturn(Foo::class);

        $associationMapping = [
            'fieldName' => 'bar',
            'targetEntity' => Foo::class,
            'sourceEntity' => Foo::class,
            'isOwningSide' => false,
        ];

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->any())->method('getAssociationAdmin')->willReturn($admin);
        $fieldDescription->expects($this->any())->method('getAssociationMapping')->willReturn($associationMapping);

        $admin
            ->expects($this->any())
            ->method('getFormFieldDescription')
            ->willReturn($fieldDescription);

        $admin
            ->expects($this->any())
            ->method('getFormFieldDescriptions')
            ->willReturn([
                'bar' => $fieldDescription,
            ]);

        $request = $this->createMock(Request::class);
        $request
            ->expects($this->any())
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
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->onConsecutiveCalls($request, $request, $request, null, $request, $request, $request, $request, null, $request));

        $foo = $this->createMock(Foo::class);

        $collection = $this->createMock(Collection::class);
        $foo->setBar($collection);

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

        $admin->expects($this->any())->method('getFormBuilder')->willReturn($formBuilder);
        $admin->expects($this->any())->method('getSubject')->willReturn($foo);

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
        $object = $this->getMockBuilder('stdClass')
            ->setMethods(['getSubObject'])
            ->getMock();
        $simpleObject = $this->getMockBuilder('stdClass')
            ->setMethods(['getSubObject'])
            ->getMock();
        $subObject = $this->getMockBuilder('stdClass')
            ->setMethods(['getAnd'])
            ->getMock();
        $sub2Object = $this->getMockBuilder('stdClass')
            ->setMethods(['getMore'])
            ->getMock();
        $sub3Object = $this->getMockBuilder('stdClass')
            ->setMethods(['getFinalData'])
            ->getMock();
        $dataMapper = $this->createMock(DataMapperInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $formBuilder = new FormBuilder('test', \get_class($simpleObject), $eventDispatcher, $formFactory);
        $childFormBuilder = new FormBuilder('subObject', \get_class($subObject), $eventDispatcher, $formFactory);

        $object->expects($this->atLeastOnce())->method('getSubObject')->willReturn([$subObject]);
        $subObject->expects($this->atLeastOnce())->method('getAnd')->willReturn($sub2Object);
        $sub2Object->expects($this->atLeastOnce())->method('getMore')->willReturn([$sub3Object]);
        $sub3Object->expects($this->atLeastOnce())->method('getFinalData')->willReturn('value');

        $formBuilder->setCompound(true);
        $formBuilder->setDataMapper($dataMapper);
        $formBuilder->add($childFormBuilder);

        $admin->expects($this->once())->method('getFormBuilder')->willReturn($formBuilder);
        $admin->expects($this->once())->method('getSubject')->willReturn($object);

        $this->expectException(\Exception::class, 'unknown collection class');

        $this->helper->appendFormFieldElement($admin, $simpleObject, 'uniquePartOfId_sub_object_0_and_more_0_final_data');
    }
}
