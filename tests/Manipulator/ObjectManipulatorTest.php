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
        $fieldDescription->expects(static::once())->method('getAssociationMapping')->willReturn(['fieldName' => 'fooBars']);
        $fieldDescription->expects(static::once())->method('getParentAssociationMappings')->willReturn([]);

        $instance = new \stdClass();

        $object = new class {
            /** @var object[] */
            private array $fooBars = [];

            public function addFooBar(object $fooBar): void
            {
                $this->fooBars[] = $fooBar;
            }

            public function removeFooBar(object $fooBar): void
            {
                $key = array_search($fooBar, $this->fooBars, true);
                unset($this->fooBars[$key]);
            }

            /** @return object[] */
            public function getFooBars(): array
            {
                return $this->fooBars;
            }
        };

        ObjectManipulator::addInstance($object, $instance, $fieldDescription);

        static::assertSame([$instance], $object->getFooBars());
    }

    public function testAddInstanceWithParentAssociation(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(static::once())->method('getAssociationMapping')->willReturn(['fieldName' => 'fooBars']);
        $fieldDescription->expects(static::once())->method('getParentAssociationMappings')->willReturn([['fieldName' => 'parent']]);

        $instance = new \stdClass();

        $object2 = new class {
            /** @var object[] */
            private array $fooBars = [];

            public function addFooBar(object $fooBar): void
            {
                $this->fooBars[] = $fooBar;
            }

            public function removeFooBar(object $fooBar): void
            {
                $key = array_search($fooBar, $this->fooBars, true);
                unset($this->fooBars[$key]);
            }

            /** @return object[] */
            public function getFooBars(): array
            {
                return $this->fooBars;
            }
        };

        $object1 = new class {
            private ?object $parent = null;

            public function setParent(object $parent): void
            {
                $this->parent = $parent;
            }

            public function getParent(): ?object
            {
                return $this->parent;
            }
        };
        $object1->setParent($object2);

        ObjectManipulator::addInstance($object1, $instance, $fieldDescription);

        static::assertSame([$instance], $object2->getFooBars());
    }

    public function testAddInstanceInflector(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(static::once())->method('getAssociationMapping')->willReturn(['fieldName' => 'entries']);
        $fieldDescription->expects(static::once())->method('getParentAssociationMappings')->willReturn([]);

        $instance = new \stdClass();

        $object = new class {
            /** @var object[] */
            private array $entries = [];

            public function addEntry(object $entry): void
            {
                $this->entries[] = $entry;
            }

            public function removeEntry(object $entry): void
            {
                $key = array_search($entry, $this->entries, true);
                unset($this->entries[$key]);
            }

            /** @return object[] */
            public function getEntries(): array
            {
                return $this->entries;
            }
        };

        ObjectManipulator::addInstance($object, $instance, $fieldDescription);

        static::assertSame([$instance], $object->getEntries());
    }

    public function testSetObject(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(static::once())->method('getAssociationMapping')->willReturn(['mappedBy' => 'parent']);
        $fieldDescription->expects(static::once())->method('getParentAssociationMappings')->willReturn([]);

        $object = new \stdClass();

        $instance = new class {
            private ?object $parent = null;

            public function setParent(object $parent): void
            {
                $this->parent = $parent;
            }

            public function getParent(): ?object
            {
                return $this->parent;
            }
        };

        ObjectManipulator::setObject($instance, $object, $fieldDescription);

        static::assertSame($object, $instance->getParent());
    }

    public function testSetObjectWithNullMapped(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(static::once())->method('getAssociationMapping')->willReturn(['mappedBy' => null]);

        ObjectManipulator::setObject(new \stdClass(), new \stdClass(), $fieldDescription);
    }

    public function testSetObjectWithoutMappedBy(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(static::once())->method('getAssociationMapping')->willReturn([]);

        ObjectManipulator::setObject(new \stdClass(), new \stdClass(), $fieldDescription);
    }

    public function testSetObjectWithParentAssociation(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(static::once())->method('getAssociationMapping')->willReturn(['mappedBy' => 'fooBar']);
        $fieldDescription->expects(static::once())->method('getParentAssociationMappings')->willReturn([['fieldName' => 'parent']]);

        $object2 = new \stdClass();

        $instance = new class {
            private ?object $fooBar = null;

            public function setFooBar(object $foobar): void
            {
                $this->fooBar = $foobar;
            }

            public function getFooBar(): ?object
            {
                return $this->fooBar;
            }
        };

        $object1 = new class {
            private ?object $parent = null;

            public function setParent(object $parent): void
            {
                $this->parent = $parent;
            }

            public function getParent(): ?object
            {
                return $this->parent;
            }
        };
        $object1->setParent($object2);

        ObjectManipulator::setObject($instance, $object1, $fieldDescription);

        static::assertSame($object2, $instance->getFooBar());
    }

    public function testSetObjectProperty(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(static::once())->method('getAssociationMapping')->willReturn(['mappedBy' => 'parent']);
        $fieldDescription->expects(static::once())->method('getParentAssociationMappings')->willReturn([]);

        $object = new \stdClass();
        $instance = new class {
            /** @var object|null */
            public $parent;
        };

        ObjectManipulator::setObject($instance, $object, $fieldDescription);

        static::assertSame($object, $instance->parent);
    }

    public function testSetObjectPropertyWithParentAssociation(): void
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(static::once())->method('getAssociationMapping')->willReturn(['mappedBy' => 'fooBar']);
        $fieldDescription->expects(static::once())->method('getParentAssociationMappings')->willReturn([['fieldName' => 'parent']]);

        $object2 = new \stdClass();
        $instance = new class {
            /** @var object|null */
            public $fooBar;
        };

        $object1 = new \stdClass();
        $object1->parent = $object2;

        ObjectManipulator::setObject($instance, $object1, $fieldDescription);

        static::assertSame($object2, $instance->fooBar);
    }
}
