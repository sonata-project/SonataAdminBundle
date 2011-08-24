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

use Sonata\AdminBundle\Admin\ORM\FieldDescription;

class FieldDescriptionTest extends \PHPUnit_Framework_TestCase
{

    public function testOptions()
    {
        $field = new FieldDescription;
        $field->setOptions(array(
            'template' => 'foo',
            'type'     => 'bar',
            'misc'     => 'foobar',
        ));


        // test method shortcut
        $this->assertEquals(null, $field->getOption('template'));
        $this->assertEquals(null, $field->getOption('type'));

        $this->assertEquals('foo', $field->getTemplate());
        $this->assertEquals('bar', $field->getType());

        // test the default value option
        $this->assertEquals('default', $field->getOption('template', 'default'));

        // test the merge options
        $field->setOption('array', array('key1' => 'val1'));
        $field->mergeOption('array', array('key1' => 'key_1', 'key2' => 'key_2'));

        $this->assertEquals(array('key1' => 'key_1', 'key2' => 'key_2'), $field->getOption('array'));

        $field->mergeOption('non_existant', array('key1' => 'key_1', 'key2' => 'key_2'));
        $this->assertEquals(array('key1' => 'key_1', 'key2' => 'key_2'), $field->getOption('array'));


        $field->mergeOptions(array('array' => array('key3' => 'key_3')));

        $this->assertEquals(array('key1' => 'key_1', 'key2' => 'key_2', 'key3' => 'key_3'), $field->getOption('array'));


        $field->setOption('integer', 1);
        try {
            $field->mergeOption('integer', array());
            $this->fail('no exception raised !!');
        } catch (\RuntimeException $e) {

        }

        $field->mergeOptions(array('final' => 'test'));

        $expected = array (
          'misc' => 'foobar',
          'array' =>
          array (
            'key1' => 'key_1',
            'key2' => 'key_2',
            'key3' => 'key_3'
          ),
          'non_existant' =>
          array (
            'key1' => 'key_1',
            'key2' => 'key_2',
          ),
          'integer' => 1,
          'final' => 'test',
        );

        $this->assertEquals($expected, $field->getOptions());
    }

    public function testAssociationMapping()
    {
        $field = new FieldDescription;
        $field->setAssociationMapping(array(
            'type' => 'integer',
            'fieldName' => 'position'
        ));

        $this->assertEquals('integer', $field->getType());
        $this->assertEquals('integer', $field->getMappingType());
        $this->assertEquals('position', $field->getFieldName());

        // cannot overwrite defined definition
        $field->setAssociationMapping(array(
            'type' => 'overwrite?',
            'fieldName' => 'overwritten'
        ));

        $this->assertEquals('integer', $field->getType());
        $this->assertEquals('integer', $field->getMappingType());
        $this->assertEquals('overwritten', $field->getFieldName());

        $field->setMappingType('string');
        $this->assertEquals('string', $field->getMappingType());
        $this->assertEquals('integer', $field->getType());
    }

    public function testCamelize()
    {

        $this->assertEquals('FooBar', FieldDescription::camelize('foo_bar'));
        $this->assertEquals('FooBar', FieldDescription::camelize('foo bar'));
        $this->assertEquals('FOoBar', FieldDescription::camelize('fOo bar'));
    }
}
