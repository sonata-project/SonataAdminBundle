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

namespace Sonata\AdminBundle\Tests\FieldDescription;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

final class FieldDescriptionCollectionTest extends TestCase
{
    public function testMethods(): void
    {
        $collection = new FieldDescriptionCollection();

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(static::once())->method('getName')->willReturn('title');
        $collection->add($fieldDescription);

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(static::once())->method('getName')->willReturn('position');
        $collection->add($fieldDescription);

        static::assertFalse($collection->has('foo'));
        static::assertArrayNotHasKey('foo', $collection);
        static::assertTrue($collection->has('title'));
        static::assertArrayHasKey('title', $collection);

        static::assertCount(2, $collection->getElements());
        static::assertCount(2, $collection);

        static::assertInstanceOf(FieldDescriptionInterface::class, $collection['title']);
        static::assertInstanceOf(FieldDescriptionInterface::class, $collection->get('title'));

        $collection->remove('title');
        static::assertFalse($collection->has('title'));

        unset($collection['position']);

        static::assertCount(0, $collection->getElements());
        static::assertCount(0, $collection);
    }

    public function testNonExistentField(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Element "foo" does not exist.');

        $collection = new FieldDescriptionCollection();
        $collection->get('foo');
    }

    public function testArrayAccessSetField(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot set value, use add');

        $collection = new FieldDescriptionCollection();

        $collection['foo'] = $this->createMock(FieldDescriptionInterface::class);
    }

    public function testReorderListWithoutBatchField(): void
    {
        $collection = new FieldDescriptionCollection();

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(static::once())->method('getName')->willReturn('title');
        $collection->add($fieldDescription);

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(static::once())->method('getName')->willReturn('position');
        $collection->add($fieldDescription);

        $newOrder = ['position', 'title'];
        $collection->reorder($newOrder);

        $actualElements = array_keys($collection->getElements());
        static::assertSame($newOrder, $actualElements, 'the order is wrong');
    }

    public function testReorderListWithBatchField(): void
    {
        $collection = new FieldDescriptionCollection();

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(static::once())->method('getName')->willReturn('title');
        $collection->add($fieldDescription);

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(static::once())->method('getName')->willReturn('position');
        $collection->add($fieldDescription);

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(static::once())->method('getName')->willReturn(ListMapper::NAME_BATCH);
        $collection->add($fieldDescription);

        $newOrder = ['position', 'title'];
        $collection->reorder($newOrder);
        array_unshift($newOrder, ListMapper::NAME_BATCH);

        $actualElements = array_keys($collection->getElements());
        static::assertSame($newOrder, $actualElements, 'the order is wrong');
    }

    public function testReorderWithInvalidName(): void
    {
        $collection = new FieldDescriptionCollection();

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->method('getName')->willReturn('title');
        $collection->add($fieldDescription);

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->method('getName')->willReturn('position');
        $collection->add($fieldDescription);

        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Element "foo" does not exist.');

        $newOrder = ['foo', 'title'];
        $collection->reorder($newOrder);
    }
}
