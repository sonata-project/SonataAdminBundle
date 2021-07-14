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

namespace Sonata\AdminBundle\Tests\Manipulator;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Manipulator\ObjectManipulator;

final class ObjectManipulatorTest extends TestCase
{
    public function testAddInstance(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(self::once())->method('getAssociationMapping')->willReturn(['fieldName' => 'fooBar']);
        $fieldDescription->expects(self::once())->method('getParentAssociationMappings')->willReturn([]);

        $instance = new \stdClass();
        $object = $this->getMockBuilder(\stdClass::class)->addMethods(['addFooBar'])->getMock();
        $object->expects(self::once())->method('addFooBar')->with($instance);

        ObjectManipulator::addInstance($object, $instance, $fieldDescription);
    }

    public function testAddInstanceWithParentAssociation(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(self::once())->method('getAssociationMapping')->willReturn(['fieldName' => 'fooBar']);
        $fieldDescription->expects(self::once())->method('getParentAssociationMappings')->willReturn([['fieldName' => 'parent']]);

        $instance = new \stdClass();

        $object2 = $this->getMockBuilder(\stdClass::class)->addMethods(['addFooBar'])->getMock();
        $object2->expects(self::once())->method('addFooBar')->with($instance);

        $object1 = $this->getMockBuilder(\stdClass::class)->addMethods(['getParent'])->getMock();
        $object1->expects(self::once())->method('getParent')->willReturn($object2);

        ObjectManipulator::addInstance($object1, $instance, $fieldDescription);
    }

    public function testAddInstancePlural(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(self::once())->method('getAssociationMapping')->willReturn(['fieldName' => 'fooBars']);
        $fieldDescription->expects(self::once())->method('getParentAssociationMappings')->willReturn([]);

        $instance = new \stdClass();
        $object = $this->getMockBuilder(\stdClass::class)->addMethods(['addFooBar'])->getMock();
        $object->expects(self::once())->method('addFooBar')->with($instance);

        ObjectManipulator::addInstance($object, $instance, $fieldDescription);
    }

    public function testAddInstanceInflector(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(self::once())->method('getAssociationMapping')->willReturn(['fieldName' => 'entries']);
        $fieldDescription->expects(self::once())->method('getParentAssociationMappings')->willReturn([]);

        $instance = new \stdClass();
        $object = $this->getMockBuilder(\stdClass::class)->addMethods(['addEntry'])->getMock();
        $object->expects(self::once())->method('addEntry')->with($instance);

        ObjectManipulator::addInstance($object, $instance, $fieldDescription);
    }

    public function testSetObject(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(self::once())->method('getAssociationMapping')->willReturn(['mappedBy' => 'parent']);
        $fieldDescription->expects(self::once())->method('getParentAssociationMappings')->willReturn([]);

        $object = new \stdClass();
        $instance = $this->getMockBuilder(\stdClass::class)->addMethods(['setParent'])->getMock();
        $instance->expects(self::once())->method('setParent')->with($object);

        ObjectManipulator::setObject($instance, $object, $fieldDescription);
    }

    public function testSetObjectWithNullMapped(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(self::once())->method('getAssociationMapping')->willReturn(['mappedBy' => null]);

        ObjectManipulator::setObject(new \stdClass(), new \stdClass(), $fieldDescription);
    }

    public function testSetObjectWithoutMappedBy(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(self::once())->method('getAssociationMapping')->willReturn([]);

        ObjectManipulator::setObject(new \stdClass(), new \stdClass(), $fieldDescription);
    }

    public function testSetObjectWithParentAssociation(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(self::once())->method('getAssociationMapping')->willReturn(['mappedBy' => 'fooBar']);
        $fieldDescription->expects(self::once())->method('getParentAssociationMappings')->willReturn([['fieldName' => 'parent']]);

        $object2 = new \stdClass();

        $instance = $this->getMockBuilder(\stdClass::class)->addMethods(['setFooBar'])->getMock();
        $instance->expects(self::once())->method('setFooBar')->with($object2);

        $object1 = $this->getMockBuilder(\stdClass::class)->addMethods(['getParent'])->getMock();
        $object1->expects(self::once())->method('getParent')->willReturn($object2);

        ObjectManipulator::setObject($instance, $object1, $fieldDescription);
    }

    public function testSetObjectProperty(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(self::once())->method('getAssociationMapping')->willReturn(['mappedBy' => 'parent']);
        $fieldDescription->expects(self::once())->method('getParentAssociationMappings')->willReturn([]);

        $object = new \stdClass();
        $instance = new class() {
            /** @var object|null */
            public $parent;
        };

        ObjectManipulator::setObject($instance, $object, $fieldDescription);

        self::assertSame($object, $instance->parent);
    }

    public function testSetObjectPropertyWithParentAssociation(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(self::once())->method('getAssociationMapping')->willReturn(['mappedBy' => 'fooBar']);
        $fieldDescription->expects(self::once())->method('getParentAssociationMappings')->willReturn([['fieldName' => 'parent']]);

        $object2 = new \stdClass();
        $instance = new class() {
            /** @var object|null */
            public $fooBar;
        };

        $object1 = new \stdClass();
        $object1->parent = $object2;

        ObjectManipulator::setObject($instance, $object1, $fieldDescription);

        self::assertSame($object2, $instance->fooBar);
    }
}
