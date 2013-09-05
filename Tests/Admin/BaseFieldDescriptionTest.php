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
use Sonata\AdminBundle\Admin\AdminInterface;

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

        $description->setOption('label', 'trucmuche');
        $this->assertEquals('trucmuche', $description->getLabel());
        $this->assertNull($description->getTemplate());
        $description->setOptions(array('type' => 'integer', 'template' => 'foo.twig.html', 'help' => 'fooHelp'));

        $this->assertEquals('integer', $description->getType());
        $this->assertEquals('foo.twig.html', $description->getTemplate());
        $this->assertEquals('fooHelp', $description->getHelp());

        $this->assertCount(1, $description->getOptions());

        $description->setHelp('Please enter an integer');
        $this->assertEquals('Please enter an integer', $description->getHelp());

        $description->setMappingType('int');
        $this->assertEquals('int', $description->getMappingType());

        $this->assertEquals('short_object_description_placeholder', $description->getOption('placeholder'));
        $description->setOptions(array('placeholder' => false));
        $this->assertFalse($description->getOption('placeholder'));

        $description->setOption('sortable', false);
        $this->assertFalse($description->isSortable());

        $description->setOption('sortable', 'field_name');
        $this->assertTrue($description->isSortable());
    }

    public function testAdmin()
    {
        $description = new FieldDescription();

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $description->setAdmin($admin);
        $this->isInstanceOf('Sonata\AdminBundle\Admin\AdminInterface', $description->getAdmin());

        $associationAdmin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $associationAdmin->expects($this->once())->method('setParentFieldDescription');

        $this->assertFalse($description->hasAssociationAdmin());
        $description->setAssociationAdmin($associationAdmin);
        $this->assertTrue($description->hasAssociationAdmin());
        $this->isInstanceOf('Sonata\AdminBundle\Admin\AdminInterface', $description->getAssociationAdmin());

        $parent = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $description->setParent($parent);
        $this->isInstanceOf('Sonata\AdminBundle\Admin\AdminInterface', $description->getParent());
    }

    public function testGetValue()
    {
        $description = new FieldDescription();
        $description->setOption('code', 'getFoo');

        $mock = $this->getMock('stdClass', array('getFoo'));
        $mock->expects($this->once())->method('getFoo')->will($this->returnValue(42));

        $this->assertEquals(42, $description->getFieldValue($mock, 'fake'));
    }

    /**
     * @expectedException Sonata\AdminBundle\Exception\NoValueException
     */
    public function testGetValueNoValueException()
    {
        $description = new FieldDescription();
        $mock = $this->getMock('stdClass', array('getFoo'));

        $description->getFieldValue($mock, 'fake');
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

    public function testGetTranslationDomain()
    {
        $description = new FieldDescription();

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $description->setAdmin($admin);

        $admin->expects($this->once())
            ->method('getTranslationDomain')
            ->will($this->returnValue('AdminDomain'));

        $this->assertEquals('AdminDomain', $description->getTranslationDomain());

        $admin->expects($this->never())
            ->method('getTranslationDomain');
        $description->setOption('translation_domain', 'ExtensionDomain');
        $this->assertEquals('ExtensionDomain', $description->getTranslationDomain());
    }

    public function testCamelize()
    {
        $this->assertEquals('FooBar', BaseFieldDescription::camelize('foo_bar'));
        $this->assertEquals('FooBar', BaseFieldDescription::camelize('foo bar'));
        $this->assertEquals('FOoBar', BaseFieldDescription::camelize('fOo bar'));
    }
}

class FieldDescription extends BaseFieldDescription
{
    public function setAssociationMapping($associationMapping)
    {
        // TODO: Implement setAssociationMapping() method.
    }

    public function getTargetEntity()
    {
        // TODO: Implement getTargetEntity() method.
    }

    public function setFieldMapping($fieldMapping)
    {
        // TODO: Implement setFieldMapping() method.
    }

    public function isIdentifier()
    {
        // TODO: Implement isIdentifier() method.
    }

    /**
     * set the parent association mappings information
     *
     * @param  array $parentAssociationMappings
     * @return void
     */
    public function setParentAssociationMappings(array $parentAssociationMappings)
    {
        // TODO: Implement setParentAssociationMappings() method.
    }

    /**
     * return the value linked to the description
     *
     * @param  $object
     * @return bool|mixed
     */
    public function getValue($object)
    {
        // TODO: Implement getValue() method.
    }
}
