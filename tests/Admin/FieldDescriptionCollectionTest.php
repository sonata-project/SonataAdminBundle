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
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;

class FieldDescriptionCollectionTest extends TestCase
{
    public function testMethods(): void
    {
        $collection = new FieldDescriptionCollection();

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('title'));
        $collection->add($fieldDescription);

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('position'));
        $collection->add($fieldDescription);

        $this->assertFalse($collection->has('foo'));
        $this->assertFalse(isset($collection['foo']));
        $this->assertTrue($collection->has('title'));
        $this->assertTrue(isset($collection['title']));

        $this->assertCount(2, $collection->getElements());
        $this->assertCount(2, $collection);

        $this->isInstanceOf(FieldDescriptionInterface::class, $collection['title']);
        $this->isInstanceOf(FieldDescriptionInterface::class, $collection->get('title'));

        $collection->remove('title');
        $this->assertFalse($collection->has('title'));

        unset($collection['position']);

        $this->assertCount(0, $collection->getElements());
        $this->assertCount(0, $collection);
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

        $collection['foo'] = null;
    }

    public function testReorderListWithoutBatchField(): void
    {
        $collection = new FieldDescriptionCollection();

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('title'));
        $collection->add($fieldDescription);

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('position'));
        $collection->add($fieldDescription);

        $newOrder = ['position', 'title'];
        $collection->reorder($newOrder);

        $actualElements = array_keys($collection->getElements());
        $this->assertSame($newOrder, $actualElements, 'the order is wrong');
    }

    public function testReorderListWithBatchField(): void
    {
        $collection = new FieldDescriptionCollection();

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('title'));
        $collection->add($fieldDescription);

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('position'));
        $collection->add($fieldDescription);

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('batch'));
        $collection->add($fieldDescription);

        $newOrder = ['position', 'title'];
        $collection->reorder($newOrder);
        array_unshift($newOrder, 'batch');

        $actualElements = array_keys($collection->getElements());
        $this->assertSame($newOrder, $actualElements, 'the order is wrong');
    }
}
