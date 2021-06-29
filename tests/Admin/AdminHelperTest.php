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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Bar;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationRequestHandler;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AdminHelperTest extends TestCase
{
    /**
     * @var AdminHelper
     */
    protected $helper;

    protected function setUp(): void
    {
        $this->helper = new AdminHelper(PropertyAccess::createPropertyAccessor());
    }

    public function testGetChildFormBuilder(): void
    {
        $formFactory = $this->createStub(FormFactoryInterface::class);
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);

        $formBuilder = new FormBuilder('test', \stdClass::class, $eventDispatcher, $formFactory);

        $childFormBuilder = new FormBuilder('elementId', \stdClass::class, $eventDispatcher, $formFactory);
        $formBuilder->add($childFormBuilder);

        self::assertNull($this->helper->getChildFormBuilder($formBuilder, 'foo'));
        self::assertInstanceOf(FormBuilder::class, $this->helper->getChildFormBuilder($formBuilder, 'test_elementId'));
    }

    public function testGetGrandChildFormBuilder(): void
    {
        $formFactory = $this->createStub(FormFactoryInterface::class);
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);

        $formBuilder = new FormBuilder('parent', \stdClass::class, $eventDispatcher, $formFactory);
        $childFormBuilder = new FormBuilder('child', \stdClass::class, $eventDispatcher, $formFactory);
        $grandchildFormBuilder = new FormBuilder('grandchild', \stdClass::class, $eventDispatcher, $formFactory);

        $formBuilder->add($childFormBuilder);
        $childFormBuilder->add($grandchildFormBuilder);

        self::assertInstanceOf(FormBuilder::class, $this->helper->getChildFormBuilder($formBuilder, 'parent_child_grandchild'));
    }

    public function testGetChildFormView(): void
    {
        $formView = new FormView();
        $formView->vars['id'] = 'test';
        $child = new FormView($formView);
        $formView->children['child'] = $child;
        $child->vars['id'] = 'test_elementId';

        self::assertNull($this->helper->getChildFormView($formView, 'foo'));
        self::assertInstanceOf(FormView::class, $this->helper->getChildFormView($formView, 'test_elementId'));
    }

    public function testGetElementAccessPath(): void
    {
        $object = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getPathToObject'])
            ->getMock();
        $subObject = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getAnother'])
            ->getMock();
        $sub2Object = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getMoreThings'])
            ->getMock();

        $object->expects(self::atLeastOnce())->method('getPathToObject')->willReturn([$subObject]);
        $subObject->expects(self::atLeastOnce())->method('getAnother')->willReturn($sub2Object);
        $sub2Object->expects(self::atLeastOnce())->method('getMoreThings')->willReturn('Value');

        $path = $this->getMethodAsPublic('getElementAccessPath')->invoke(
            $this->helper,
            'uniquePartOfId_path_to_object_0_another_more_things',
            $object
        );

        self::assertSame('path_to_object[0].another.more_things', $path);
    }

    public function testItThrowsExceptionWhenDoesNotFindTheFullPath(): void
    {
        $path = 'uniquePartOfId_path_to_object_0_more_calls';
        $object = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getPathToObject'])
            ->getMock();
        $subObject = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getMore'])
            ->getMock();

        $object->expects(self::atLeastOnce())->method('getPathToObject')->willReturn([$subObject]);
        $subObject->expects(self::atLeastOnce())->method('getMore')->willReturn('Value');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(sprintf('Could not get element id from %s Failing part: calls', $path));

        $this->getMethodAsPublic('getElementAccessPath')->invoke(
            $this->helper,
            $path,
            $object
        );
    }

    public function testAppendFormFieldElement(): void
    {
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

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);
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

        $admin
            ->method('hasFormFieldDescription')
            ->with($associationMapping['fieldName'])
            ->willReturn(true);

        $request = new Request([], [
            'test' => [
                'bar' => [
                    [
                        'baz' => [
                            'baz' => true,
                        ],
                    ],
                    ['_delete' => true],
                ],
            ],
        ]);

        $admin
            ->expects(self::atLeastOnce())
            ->method('getRequest')
            ->willReturn($request);

        $foo = $this->getMockBuilder(\stdClass::class)->addMethods(['addBar'])->getMock();
        $admin
            ->method('hasSubject')
            ->willReturn(true);
        $admin
            ->method('getSubject')
            ->willReturn($foo);

        $bar = new \stdClass();
        $associationAdmin
            ->expects(self::atLeastOnce())
            ->method('getNewInstance')
            ->willReturn($bar);

        $foo->expects(self::atLeastOnce())->method('addBar')->with($bar);

        $dataMapper = $this->createStub(DataMapperInterface::class);
        $formFactory = $this->createStub(FormFactoryInterface::class);
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);
        $formBuilder = new FormBuilder('test', \get_class($foo), $eventDispatcher, $formFactory);
        $childFormBuilder = new FormBuilder('bar', \stdClass::class, $eventDispatcher, $formFactory);
        $childFormBuilder->setCompound(true);
        $childFormBuilder->setDataMapper($dataMapper);
        $subChildFormBuilder = new FormBuilder('baz', \stdClass::class, $eventDispatcher, $formFactory);
        $subChildFormBuilder->setCompound(true);
        $subChildFormBuilder->setDataMapper($dataMapper);
        $childFormBuilder->add($subChildFormBuilder);

        $formBuilder->setRequestHandler(new HttpFoundationRequestHandler());
        $formBuilder->setCompound(true);
        $formBuilder->setDataMapper($dataMapper);
        $formBuilder->add($childFormBuilder);

        $associationAdmin->expects(self::atLeastOnce())->method('setSubject')->with($bar);
        $admin->method('getFormBuilder')->willReturn($formBuilder);

        $finalForm = $this->helper->appendFormFieldElement($admin, $foo, 'test_bar')[1];

        foreach ($finalForm->get($childFormBuilder->getName()) as $childField) {
            self::assertFalse($childField->has('_delete'));
        }

        $deleteFormBuilder = new FormBuilder('_delete', null, $eventDispatcher, $formFactory);
        $subChildFormBuilder->add($deleteFormBuilder, CheckboxType::class, ['delete' => false]);

        $finalForm = $this->helper->appendFormFieldElement($admin, $foo, 'test_bar')[1];

        foreach ($finalForm->get($childFormBuilder->getName()) as $childField) {
            self::assertTrue($childField->has('_delete'));
            self::assertSame('', $childField->get('_delete')->getData());
        }
    }

    public function testAppendFormFieldElementWithoutFormFieldDescriptionInAdminAndNoCollectionClass(): void
    {
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

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);
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

        $admin
            ->method('hasFormFieldDescription')
            ->with($associationMapping['fieldName'])
            ->willReturn(false);

        $request = new Request([], [
            'test' => [
                'bar' => [
                    [
                        'baz' => [
                            'baz' => true,
                        ],
                    ],
                    ['_delete' => true],
                ],
            ],
        ]);

        $admin
            ->method('getRequest')
            ->willReturn($request);

        $foo = new Foo();
        $admin
            ->method('hasSubject')
            ->willReturn(true);
        $admin
            ->method('getSubject')
            ->willReturn($foo);

        $dataMapper = $this->createStub(DataMapperInterface::class);
        $formFactory = $this->createStub(FormFactoryInterface::class);
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);
        $formBuilder = new FormBuilder('test', \get_class($foo), $eventDispatcher, $formFactory);
        $formBuilder->setRequestHandler(new HttpFoundationRequestHandler());
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

        $admin->method('getFormBuilder')->willReturn($formBuilder);

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage(sprintf('Collection must be an instance of %s, "%s" given.', Collection::class, \gettype(null)));
        $this->helper->appendFormFieldElement($admin, $foo, 'test_bar');
    }

    public function testAppendFormFieldElementWithCollection(): void
    {
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

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);
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

        $admin
            ->method('hasFormFieldDescription')
            ->with($associationMapping['fieldName'])
            ->willReturn(false);

        $request = new Request([], [
            'test' => [
                'bar' => [
                    [
                        'baz' => [
                            'baz' => true,
                        ],
                    ],
                    ['_delete' => true],
                ],
            ],
        ]);

        $admin
            ->method('getRequest')
            ->willReturn($request);

        $foo = new class() {
            /** @var Collection<int, Bar> */
            private $bar;

            public function __construct()
            {
                $this->bar = new ArrayCollection();
            }

            /** @return Collection<int, Bar> */
            public function getBar(): Collection
            {
                return $this->bar;
            }

            /** @param Collection<int, Bar> $bar */
            public function setBar(Collection $bar): void
            {
                $this->bar = $bar;
            }
        };

        $admin
            ->method('hasSubject')
            ->willReturn(true);
        $admin
            ->method('getSubject')
            ->willReturn($foo);

        $dataMapper = $this->createStub(DataMapperInterface::class);
        $formFactory = $this->createStub(FormFactoryInterface::class);
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);
        $formBuilder = new FormBuilder('test', \get_class($foo), $eventDispatcher, $formFactory);
        $formBuilder->setRequestHandler(new HttpFoundationRequestHandler());
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

        $admin->method('getFormBuilder')->willReturn($formBuilder);

        $finalForm = $this->helper->appendFormFieldElement($admin, $foo, 'test_bar')[1];

        foreach ($finalForm->get($childFormBuilder->getName()) as $childField) {
            self::assertFalse($childField->has('_delete'));
        }
    }

    public function testAppendFormFieldElementNested(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $request = new Request([], [
            'test' => [
                'bar' => [
                    [
                        'baz' => [
                            'baz' => true,
                        ],
                    ],
                    ['_delete' => true],
                ],
            ],
        ]);

        $admin
            ->expects(self::atLeastOnce())
            ->method('getRequest')
            ->willReturn($request);
        $object = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getSubObject'])
            ->getMock();

        $subObject = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getAnd'])
            ->getMock();
        $sub2Object = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getMore'])
            ->getMock();
        $sub3Object = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getFinalData'])
            ->getMock();
        $dataMapper = $this->createStub(DataMapperInterface::class);
        $formFactory = $this->createStub(FormFactoryInterface::class);
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);
        $formBuilder = new FormBuilder('test', \get_class($object), $eventDispatcher, $formFactory);
        $childFormBuilder = new FormBuilder('subObject', \get_class($subObject), $eventDispatcher, $formFactory);

        $object->expects(self::atLeastOnce())->method('getSubObject')->willReturn([$subObject]);
        $subObject->expects(self::atLeastOnce())->method('getAnd')->willReturn($sub2Object);
        $sub2Object->expects(self::atLeastOnce())->method('getMore')->willReturn([$sub3Object]);
        $sub3Object->expects(self::atLeastOnce())->method('getFinalData')->willReturn('value');

        $formBuilder->setRequestHandler(new HttpFoundationRequestHandler());
        $formBuilder->setCompound(true);
        $formBuilder->setDataMapper($dataMapper);
        $formBuilder->add($childFormBuilder);

        $admin->method('hasSubject')->willReturn(true);
        $admin->method('getSubject')->willReturn($object);
        $admin->expects(self::once())->method('getFormBuilder')->willReturn($formBuilder);

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage(sprintf('Collection must be an instance of %s, "string" given.', Collection::class));

        $this->helper->appendFormFieldElement($admin, $object, 'uniquePartOfId_sub_object_0_and_more_0_final_data');
    }

    private function getMethodAsPublic(string $privateMethod): \ReflectionMethod
    {
        $reflectionMethod = new \ReflectionMethod(AdminHelper::class, $privateMethod);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod;
    }
}
