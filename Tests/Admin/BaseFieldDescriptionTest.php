<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Admin;

use Sonata\AdminBundle\Admin\BaseFieldDescription;

class BaseFieldDescriptionTest extends \PHPUnit_Framework_TestCase
{
    public function testSetName()
    {
        $description = new FieldDescription();
        $description->setName('foo');

        $this->assertEquals('foo', $description->getFieldName());
        $this->assertEquals('foo', $description->getName());
    }

    public function testOptions()
    {
        $description = new FieldDescription();
        $description->setOption('foo', 'bar');

        $this->assertNull($description->getOption('bar'));
        $this->assertEquals('bar', $description->getOption('foo'));

        $description->mergeOptions(array('settings' => array('value_1', 'value_2')));
        $description->mergeOptions(array('settings' => array('value_1', 'value_3')));

        $this->assertEquals(array('value_1', 'value_2', 'value_1', 'value_3'), $description->getOption('settings'));

        $description->mergeOption('settings', array('value_4'));
        $this->assertEquals(array('value_1', 'value_2', 'value_1', 'value_3', 'value_4'), $description->getOption('settings'));

        $description->mergeOption('bar', array('hello'));

        $this->assertCount(1, $description->getOption('bar'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testExceptionOnNonArrayOption()
    {
        $description = new FieldDescription();
        $description->setOption('bar', 'hello');
        $description->mergeOption('bar', array('exception'));
    }
}

class FieldDescription extends BaseFieldDescription
{
    function setAssociationMapping($associationMapping)
    {
        // TODO: Implement setAssociationMapping() method.
    }

    function getTargetEntity()
    {
        // TODO: Implement getTargetEntity() method.
    }

    function setFieldMapping($fieldMapping)
    {
        // TODO: Implement setFieldMapping() method.
    }

    function isIdentifier()
    {
        // TODO: Implement isIdentifier() method.
    }
}
