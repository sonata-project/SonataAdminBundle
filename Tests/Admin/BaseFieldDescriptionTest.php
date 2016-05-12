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

use Sonata\AdminBundle\Admin\BaseFieldDescription;
use Sonata\AdminBundle\Tests\Fixtures\Admin\FieldDescription;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooCall;

class BaseFieldDescriptionTest extends \PHPUnit_Framework_TestCase
{
    public function testSetName()
    {
        $description = new FieldDescription();
        $description->setName('foo');

        $this->assertSame('foo', $description->getFieldName());
        $this->assertSame('foo', $description->getName());
    }

    public function testOptions()
    {
        $description = new FieldDescription();
        $description->setOption('foo', 'bar');

        $this->assertNull($description->getOption('bar'));
        $this->assertSame('bar', $description->getOption('foo'));

        $description->mergeOptions(array('settings' => array('value_1', 'value_2')));
        $description->mergeOptions(array('settings' => array('value_1', 'value_3')));

        $this->assertSame(array('value_1', 'value_2', 'value_1', 'value_3'), $description->getOption('settings'));

        $description->mergeOption('settings', array('value_4'));
        $this->assertSame(array('value_1', 'value_2', 'value_1', 'value_3', 'value_4'), $description->getOption('settings'));

        $description->mergeOption('bar', array('hello'));

        $this->assertCount(1, $description->getOption('bar'));

        $description->setOption('label', 'trucmuche');
        $this->assertSame('trucmuche', $description->getLabel());
        $this->assertNull($description->getTemplate());
        $description->setOptions(array('type' => 'integer', 'template' => 'foo.twig.html', 'help' => 'fooHelp'));

        $this->assertSame('integer', $description->getType());
        $this->assertSame('foo.twig.html', $description->getTemplate());
        $this->assertSame('fooHelp', $description->getHelp());

        $this->assertCount(2, $description->getOptions());

        $description->setHelp('Please enter an integer');
        $this->assertSame('Please enter an integer', $description->getHelp());

        $description->setMappingType('int');
        $this->assertSame('int', $description->getMappingType());

        $this->assertSame('short_object_description_placeholder', $description->getOption('placeholder'));
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

        $this->assertSame(42, $description->getFieldValue($mock, 'fake'));

        /*
         * Test with One parameter int
         */
        $arg1 = 38;
        $oneParameter = array($arg1);
        $description1 = new FieldDescription();
        $description1->setOption('code', 'getWithOneParameter');
        $description1->setOption('parameters', $oneParameter);

        $mock1 = $this->getMock('stdClass', array('getWithOneParameter'));
        $returnValue1 = $arg1 + 2;
        $mock1->expects($this->once())->method('getWithOneParameter')->with($this->equalTo($arg1))->will($this->returnValue($returnValue1));

        $this->assertSame(40, $description1->getFieldValue($mock1, 'fake'));

        /*
         * Test with Two parameters int
         */
        $arg2 = 4;
        $twoParameters = array($arg1, $arg2);
        $description2 = new FieldDescription();
        $description2->setOption('code', 'getWithTwoParameters');
        $description2->setOption('parameters', $twoParameters);

        $mock2 = $this->getMock('stdClass', array('getWithTwoParameters'));
        $returnValue2 = $arg1 + $arg2;
        $mock2->expects($this->any())->method('getWithTwoParameters')->with($this->equalTo($arg1), $this->equalTo($arg2))->will($this->returnValue($returnValue2));
        $this->assertSame(42, $description2->getFieldValue($mock2, 'fake'));

        /*
         * Test with underscored attribute name
         */
        $description3 = new FieldDescription();
        $mock3 = $this->getMock('stdClass', array('getFake'));

        $mock3->expects($this->once())->method('getFake')->will($this->returnValue(42));
        $this->assertSame(42, $description3->getFieldValue($mock3, '_fake'));
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

    public function testGetVirtualValue()
    {
        $description = new FieldDescription();
        $mock = $this->getMock('stdClass', array('getFoo'));

        $description->setOption('virtual_field', true);
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

        $this->assertSame('AdminDomain', $description->getTranslationDomain());

        $admin->expects($this->never())
            ->method('getTranslationDomain');
        $description->setOption('translation_domain', 'ExtensionDomain');
        $this->assertSame('ExtensionDomain', $description->getTranslationDomain());
    }

    /**
     * @group legacy
     */
    public function testCamelize()
    {
        $this->assertSame('FooBar', BaseFieldDescription::camelize('foo_bar'));
        $this->assertSame('FooBar', BaseFieldDescription::camelize('foo bar'));
        $this->assertSame('FOoBar', BaseFieldDescription::camelize('fOo bar'));
    }

    public function testGetFieldValue()
    {
        $foo = new Foo();
        $foo->setBar('Bar');

        $description = new FieldDescription();
        $this->assertSame('Bar', $description->getFieldValue($foo, 'bar'));

        $this->setExpectedException('Sonata\AdminBundle\Exception\NoValueException');
        $description->getFieldValue($foo, 'inexistantMethod');
    }

    public function testGetFieldValueWithCodeOption()
    {
        $foo = new Foo();
        $foo->setBaz('Baz');

        $description = new FieldDescription();

        $description->setOption('code', 'getBaz');
        $this->assertSame('Baz', $description->getFieldValue($foo, 'inexistantMethod'));

        $description->setOption('code', 'inexistantMethod');
        $this->setExpectedException('Sonata\AdminBundle\Exception\NoValueException');
        $description->getFieldValue($foo, 'inexistantMethod');
    }

    public function testGetFieldValueMagicCall()
    {
        $parameters = array('foo', 'bar');
        $foo = new FooCall();

        $description = new FieldDescription();
        $description->setOption('parameters', $parameters);
        $this->assertSame(array('inexistantMethod', $parameters), $description->getFieldValue($foo, 'inexistantMethod'));
    }
}
