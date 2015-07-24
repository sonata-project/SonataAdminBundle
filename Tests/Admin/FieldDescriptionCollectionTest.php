<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Admin;

use Sonata\AdminBundle\Admin\FieldDescriptionCollection;

class FieldDescriptionCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testMethods()
    {
        $collection = new FieldDescriptionCollection();

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('title'));
        $collection->add($fieldDescription);

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('position'));
        $collection->add($fieldDescription);

        $this->assertFalse($collection->has('foo'));
        $this->assertFalse(isset($collection['foo']));
        $this->assertTrue($collection->has('title'));
        $this->assertTrue(isset($collection['title']));

        $this->assertCount(2, $collection->getElements());
        $this->assertCount(2, $collection);

        $this->isInstanceOf('Sonata\AdminBundle\Admin\FieldDescriptionInterface', $collection['title']);
        $this->isInstanceOf('Sonata\AdminBundle\Admin\FieldDescriptionInterface', $collection->get('title'));

        $collection->remove('title');
        $this->assertFalse($collection->has('title'));

        unset($collection['position']);

        $this->assertCount(0, $collection->getElements());
        $this->assertCount(0, $collection);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Element "foo" does not exist.
     */
    public function testNonExistentField()
    {
        $collection = new FieldDescriptionCollection();
        $collection->get('foo');
    }

    /**
     * @expectedException        RunTimeException
     * @expectedExceptionMessage Cannot set value, use add
     */
    public function testArrayAccessSetField()
    {
        $collection = new FieldDescriptionCollection();

        $collection['foo'] = null;
    }

    public function testReorderListWithoutBatchField()
    {
        $collection = new FieldDescriptionCollection();

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('title'));
        $collection->add($fieldDescription);

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('position'));
        $collection->add($fieldDescription);

        $newOrder = array('position', 'title');
        $collection->reorder($newOrder);

        $actualElements = array_keys($collection->getElements());
        $this->assertSame($newOrder, $actualElements, 'the order is wrong');
    }

    public function testReorderListWithBatchField()
    {
        $collection = new FieldDescriptionCollection();

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('title'));
        $collection->add($fieldDescription);

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('position'));
        $collection->add($fieldDescription);

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('batch'));
        $collection->add($fieldDescription);

        $newOrder = array('position', 'title');
        $collection->reorder($newOrder);
        array_unshift($newOrder, 'batch');

        $actualElements = array_keys($collection->getElements());
        $this->assertSame($newOrder, $actualElements, 'the order is wrong');
    }
}
