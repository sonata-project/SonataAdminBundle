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

final class AdminHelperTest extends TestCase
{
    protected AdminHelper $helper;

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

        static::assertNull($this->helper->getChildFormBuilder($formBuilder, 'foo'));
        static::assertInstanceOf(FormBuilder::class, $this->helper->getChildFormBuilder($formBuilder, 'test_elementId'));
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

        static::assertInstanceOf(FormBuilder::class, $this->helper->getChildFormBuilder($formBuilder, 'parent_child_grandchild'));
    }

    public function testGetChildFormView(): void
    {
        $formView = new FormView();
        $formView->vars['id'] = 'test';
        $child = new FormView($formView);
        $formView->children['child'] = $child;
        $child->vars['id'] = 'test_elementId';

        static::assertNull($this->helper->getChildFormView($formView, 'foo'));
        static::assertInstanceOf(FormView::class, $this->helper->getChildFormView($formView, 'test_elementId'));
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

        $object->expects(static::atLeastOnce())->method('getPathToObject')->willReturn([$subObject]);
        $subObject->expects(static::atLeastOnce())->method('getAnother')->willReturn($sub2Object);
        $sub2Object->expects(static::atLeastOnce())->method('getMoreThings')->willReturn('Value');

        $path = $this->getMethodAsPublic('getElementAccessPath')->invoke(
            $this->helper,
            'uniquePartOfId_path_to_object_0_another_more_things',
            $object
        );

        static::assertSame('path_to_object[0].another.more_things', $path);
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

        $object->expects(static::atLeastOnce())->method('getPathToObject')->willReturn([$subObject]);
        $subObject->expects(static::atLeastOnce())->method('getMore')->willReturn('Value');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(\sprintf('Could not get element id from %s Failing part: calls', $path));

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
            'targetEntity' => Bar::class,
            'sourceEntity' => Foo::class,
            'isOwningSide' => false,
        ];

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->method('getName')->willReturn('bar');
        $fieldDescription->method('getAssociationAdmin')->willReturn($associationAdmin);
        $fieldDescription->method('getAssociationMapping')->willReturn($associationMapping);
        $fieldDescription->method('getParentAssociationMappings')->willReturn([]);
        $fieldDescription->method('hasAdmin')->willReturn(true);
        $fieldDescription->method('getAdmin')->willReturn($admin);

        $admin->method('hasFormFieldDescription')->with('bar')->willReturn(true);
        $admin->method('getFormFieldDescription')->with('bar')->willReturn($fieldDescription);

        $associationAdmin
            ->method('getFormFieldDescriptions')
            ->willReturn([
                'bar' => $fieldDescription,
            ]);

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
            ->expects(static::atLeastOnce())
            ->method('getRequest')
            ->willReturn($request);

        $foo = new class {
            /** @var object[] */
            public array $bar = [];
        };

        $admin
            ->method('hasSubject')
            ->willReturn(true);
        $admin
            ->method('getSubject')
            ->willReturn($foo);

        $bar = new \stdClass();
        $associationAdmin
            ->expects(static::atLeastOnce())
            ->method('getNewInstance')
            ->willReturn($bar);

        $dataMapper = $this->createStub(DataMapperInterface::class);
        $formFactory = $this->createStub(FormFactoryInterface::class);
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);
        $formBuilder = new FormBuilder('test', $foo::class, $eventDispatcher, $formFactory);
        $childFormBuilder = new FormBuilder('bar', \stdClass::class, $eventDispatcher, $formFactory, [
            'sonata_field_description' => $fieldDescription,
        ]);
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

        $associationAdmin->expects(static::atLeastOnce())->method('setSubject')->with($bar);
        $admin->method('getFormBuilder')->willReturn($formBuilder);

        $finalForm = $this->helper->appendFormFieldElement($admin, $foo, 'test_bar')[1];

        foreach ($finalForm->get($childFormBuilder->getName()) as $childField) {
            static::assertFalse($childField->has('_delete'));
        }

        $deleteFormBuilder = new FormBuilder('_delete', null, $eventDispatcher, $formFactory);
        $subChildFormBuilder->add($deleteFormBuilder, CheckboxType::class, ['delete' => false]);

        $finalForm = $this->helper->appendFormFieldElement($admin, $foo, 'test_bar')[1];

        foreach ($finalForm->get($childFormBuilder->getName()) as $childField) {
            static::assertTrue($childField->has('_delete'));
            static::assertSame('', $childField->get('_delete')->getData());
        }

        static::assertGreaterThan(0, \count($foo->bar));
    }

    public function testAppendFormFieldElementWithoutFormFieldDescriptionInAdminAndNoArrayAccess(): void
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
        $formBuilder = new FormBuilder('test', $foo::class, $eventDispatcher, $formFactory);
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
        $this->expectExceptionMessage(\sprintf('Collection must be an instance of %s or array, "%s" given.', \ArrayAccess::class, \gettype(null)));
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

        $foo = new class {
            /** @var Collection<int, Bar> */
            private Collection $bar;

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
        $formBuilder = new FormBuilder('test', $foo::class, $eventDispatcher, $formFactory);
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
            static::assertFalse($childField->has('_delete'));
        }
    }

    public function testAppendFormFieldElementWithArray(): void
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

        $foo = new class {
            /** @var Collection<int, Bar> */
            private Collection $bar;

            public function __construct()
            {
                $this->bar = new ArrayCollection();
            }

            /** @return array<int, Bar> */
            public function getBar(): array
            {
                return $this->bar->toArray();
            }

            /** @param array<int, Bar> $bar */
            public function setBar(array $bar): void
            {
                $this->bar = new ArrayCollection($bar);
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
        $formBuilder = new FormBuilder('test', $foo::class, $eventDispatcher, $formFactory);
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
            static::assertFalse($childField->has('_delete'));
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
            ->expects(static::atLeastOnce())
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
        $formBuilder = new FormBuilder('test', $object::class, $eventDispatcher, $formFactory);
        $childFormBuilder = new FormBuilder('subObject', $subObject::class, $eventDispatcher, $formFactory);

        $object->expects(static::atLeastOnce())->method('getSubObject')->willReturn([$subObject]);
        $subObject->expects(static::atLeastOnce())->method('getAnd')->willReturn($sub2Object);
        $sub2Object->expects(static::atLeastOnce())->method('getMore')->willReturn([$sub3Object]);
        $sub3Object->expects(static::atLeastOnce())->method('getFinalData')->willReturn('value');

        $formBuilder->setRequestHandler(new HttpFoundationRequestHandler());
        $formBuilder->setCompound(true);
        $formBuilder->setDataMapper($dataMapper);
        $formBuilder->add($childFormBuilder);

        $admin->method('hasSubject')->willReturn(true);
        $admin->method('getSubject')->willReturn($object);
        $admin->expects(static::once())->method('getFormBuilder')->willReturn($formBuilder);

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage(\sprintf('Collection must be an instance of %s or array, "string" given.', \ArrayAccess::class));

        $this->helper->appendFormFieldElement($admin, $object, 'uniquePartOfId_sub_object_0_and_more_0_final_data');
    }

    public function testAppendFormFieldElementOnNestedWithSameNamedCollection(): void
    {
        $subObject = new class {
            /** @var Collection<int, \stdClass> */
            private Collection $collection;

            public function __construct()
            {
                $this->collection = new ArrayCollection();
            }

            /** @return Collection<int, \stdClass> */
            public function getCollection(): Collection
            {
                return $this->collection;
            }

            /** @param Collection<int, \stdClass> $collection */
            public function setCollection(Collection $collection): void
            {
                $this->collection = $collection;
            }
        };

        $object = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getSubObject', 'getCollection'])
            ->getMock();

        $collectionObject = $this->createMock(\stdClass::class);

        $object->expects(static::never())->method('getCollection');
        $object->expects(static::atLeastOnce())->method('getSubObject')->willReturn($subObject);

        $admin = $this->createMock(AdminInterface::class);
        $admin->method('hasFormFieldDescription')->with('collection')->willReturn(true);
        $admin->method('getClass')->willReturn($object::class);

        $subObjectAdmin = $this->createMock(AdminInterface::class);
        $subObjectAdmin->method('getClass')->willReturn($subObject::class);

        $subObjectMapping = [
            'fieldName' => 'sub_object',
            'targetEntity' => $subObject::class,
            'sourceEntity' => $object::class,
            'isOwningSide' => false,
        ];

        $subObjectFieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $subObjectFieldDescription->method('getName')->willReturn('sub_object');
        $subObjectFieldDescription->method('getAssociationAdmin')->willReturn($subObjectAdmin);
        $subObjectFieldDescription->method('getAssociationMapping')->willReturn($subObjectMapping);
        $subObjectFieldDescription->method('getParentAssociationMappings')->willReturn([]);
        $subObjectFieldDescription->expects(static::never())->method('getValue');

        $subObjectCollectionAdmin = $this->createMock(AdminInterface::class);
        $subObjectCollectionAdmin->method('getClass')->willReturn($collectionObject::class);

        $subObjectCollectionMapping = [
            'fieldName' => 'collection',
            'targetEntity' => $collectionObject::class,
            'sourceEntity' => $subObject::class,
            'isOwningSide' => false,
        ];

        $subObjectCollectionFieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $subObjectCollectionFieldDescription->method('getName')->willReturn('collection');
        $subObjectCollectionFieldDescription->method('getAssociationAdmin')->willReturn($subObjectCollectionAdmin);
        $subObjectCollectionFieldDescription->method('getAssociationMapping')->willReturn($subObjectCollectionMapping);
        $subObjectCollectionFieldDescription->method('getParentAssociationMappings')->willReturn([]);
        $subObjectCollectionFieldDescription->expects(static::never())->method('getValue');

        $subObjectAdmin->method('getFormFieldDescription')->with('collection')->willReturn(
            $subObjectCollectionFieldDescription
        );

        $collectionAdmin = $this->createMock(AdminInterface::class);
        $collectionAdmin->method('getClass')->willReturn($collectionObject::class);

        $collectionMapping = [
            'fieldName' => 'collection',
            'targetEntity' => $collectionObject::class,
            'sourceEntity' => $object::class,
            'isOwningSide' => false,
        ];

        $collectionFieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $collectionFieldDescription->method('getName')->willReturn('collection');
        $collectionFieldDescription->method('getAssociationAdmin')->willReturn($collectionAdmin);
        $collectionFieldDescription->method('getAssociationMapping')->willReturn($collectionMapping);
        $collectionFieldDescription->method('getParentAssociationMappings')->willReturn([]);
        $collectionFieldDescription->expects(static::never())->method('getValue');

        $admin->method('getFormFieldDescription')->willReturnMap([
            ['sub_object', $subObjectFieldDescription],
            ['collection', $collectionFieldDescription],
        ]);

        $request = new Request([], [
            'main' => [],
        ]);

        $admin->expects(static::atLeastOnce())->method('getRequest')->willReturn($request);

        $dataMapper = $this->createStub(DataMapperInterface::class);
        $formFactory = $this->createStub(FormFactoryInterface::class);
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);

        $collectionFormBuilder = new FormBuilder('collection', null, $eventDispatcher, $formFactory, [
            'sonata_field_description' => $collectionFieldDescription,
        ]);
        $collectionFormBuilder->setCompound(true);
        $collectionFormBuilder->setDataMapper($dataMapper);

        $childCollectionFormBuilder = new FormBuilder('collection', null, $eventDispatcher, $formFactory, [
            'sonata_field_description' => $subObjectCollectionFieldDescription,
        ]);
        $childCollectionFormBuilder->setCompound(true);
        $childCollectionFormBuilder->setDataMapper($dataMapper);

        $childFormBuilder = new FormBuilder('sub_object', $subObject::class, $eventDispatcher, $formFactory, [
            'sonata_field_description' => $subObjectFieldDescription,
        ]);
        $childFormBuilder->setCompound(true);
        $childFormBuilder->setDataMapper($dataMapper);
        $childFormBuilder->add($childCollectionFormBuilder);

        $formBuilder = new FormBuilder('main', $object::class, $eventDispatcher, $formFactory);
        $formBuilder->setRequestHandler(new HttpFoundationRequestHandler());
        $formBuilder->setCompound(true);
        $formBuilder->setDataMapper($dataMapper);
        $formBuilder->add($childFormBuilder);
        $formBuilder->add($collectionFormBuilder);

        $admin->expects(static::atLeastOnce())->method('getFormBuilder')->willReturn($formBuilder);

        $fieldDescription = $this->helper->appendFormFieldElement($admin, $object, 'main_sub_object_collection')[0];

        static::assertNull($fieldDescription);
    }

    private function getMethodAsPublic(string $privateMethod): \ReflectionMethod
    {
        $reflectionMethod = new \ReflectionMethod(AdminHelper::class, $privateMethod);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod;
    }
}
