<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Filter;

use Sonata\AdminBundle\Tests\Fixtures\Filter\FooFilter;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    public function testFilter()
    {
        $filter = new FooFilter();

        $this->assertSame(method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Symfony\Component\Form\Extension\Core\Type\TextType'
            : 'text', $filter->getFieldType());
        $this->assertSame(array('required' => false), $filter->getFieldOptions());
        $this->assertNull($filter->getLabel());

        $options = array(
            'label' => 'foo',
            'field_type' => 'integer',
            'field_options' => array('required' => true),
            'field_name' => 'name',
        );

        $filter->setOptions($options);

        $this->assertSame('foo', $filter->getOption('label'));
        $this->assertSame('foo', $filter->getLabel());

        $expected = array_merge(array(
            'show_filter' => null,
            'advanced_filter' => true,
            'foo' => 'bar',
        ), $options);

        $this->assertSame($expected, $filter->getOptions());
        $this->assertSame('name', $filter->getFieldName());

        $this->assertSame('default', $filter->getOption('fake', 'default'));

        $filter->setValue(42);
        $this->assertSame(42, $filter->getValue());

        $filter->setCondition('>');
        $this->assertSame('>', $filter->getCondition());
    }

    public function testGetFieldOption()
    {
        $filter = new FooFilter();
        $filter->initialize('name', array(
            'field_options' => array('foo' => 'bar', 'baz' => 12345),
        ));

        $this->assertSame(array('foo' => 'bar', 'baz' => 12345), $filter->getFieldOptions());
        $this->assertSame('bar', $filter->getFieldOption('foo'));
        $this->assertSame(12345, $filter->getFieldOption('baz'));
    }

    public function testSetFieldOption()
    {
        $filter = new FooFilter();
        $this->assertSame(array('required' => false), $filter->getFieldOptions());

        $filter->setFieldOption('foo', 'bar');
        $filter->setFieldOption('baz', 12345);

        $this->assertSame(array('foo' => 'bar', 'baz' => 12345), $filter->getFieldOptions());
        $this->assertSame('bar', $filter->getFieldOption('foo'));
        $this->assertSame(12345, $filter->getFieldOption('baz'));
    }

    public function testInitialize()
    {
        $filter = new FooFilter();
        $filter->initialize('name', array(
            'field_name' => 'bar',
        ));

        $this->assertSame('name', $filter->getName());
        $this->assertSame('bar', $filter->getOption('field_name'));
        $this->assertSame('bar', $filter->getFieldName());
    }

    public function testLabel()
    {
        $filter = new FooFilter();
        $filter->setLabel('foo');

        $this->assertSame('foo', $filter->getLabel());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testExceptionOnNonDefinedFieldName()
    {
        $filter = new FooFilter();

        $filter->getFieldName();
    }

    /**
     * @dataProvider isActiveData
     *
     * @param $expected
     * @param $value
     */
    public function testIsActive($expected, $value)
    {
        $filter = new FooFilter();
        $filter->setValue($value);

        $this->assertSame($expected, $filter->isActive());
    }

    public function isActiveData()
    {
        return array(
            array(false, array()),
            array(false, array('value' => null)),
            array(false, array('value' => '')),
            array(false, array('value' => false)),
            array(true, array('value' => 'active')),
        );
    }

    public function testGetTranslationDomain()
    {
        $filter = new FooFilter();
        $this->assertSame(null, $filter->getTranslationDomain());
        $filter->setOption('translation_domain', 'baz');
        $this->assertSame('baz', $filter->getTranslationDomain());
    }

    public function testGetFieldMappingException()
    {
        $filter = new FooFilter();
        $filter->initialize('foo');

        try {
            $filter->getFieldMapping();
        } catch (\RuntimeException $e) {
            $this->assertContains('The option `field_mapping` must be set for field: `foo`', $e->getMessage());

            return;
        }

        $this->fail('Failed asserting that exception of type "\RuntimeException" is thrown.');
    }

    public function testGetFieldMapping()
    {
        $fieldMapping = array(
            'fieldName' => 'username',
            'type' => 'string',
            'columnName' => 'username',
            'length' => 200,
            'unique' => true,
            'nullable' => false,
            'declared' => 'Foo\Bar\User',
        );

        $filter = new FooFilter();
        $filter->setOption('field_mapping', $fieldMapping);
        $this->assertSame($fieldMapping, $filter->getFieldMapping());
    }

    public function testGetParentAssociationMappings()
    {
        $parentAssociationMapping = array(
            0 => array('fieldName' => 'user',
                'targetEntity' => 'Foo\Bar\User',
                'joinColumns' => array(
                    0 => array(
                        'name' => 'user_id',
                        'referencedColumnName' => 'user_id',
                    ),
                ),
                'type' => 2,
                'mappedBy' => null,
            ),
        );

        $filter = new FooFilter();
        $this->assertSame(array(), $filter->getParentAssociationMappings());
        $filter->setOption('parent_association_mappings', $parentAssociationMapping);
        $this->assertSame($parentAssociationMapping, $filter->getParentAssociationMappings());
    }

    public function testGetAssociationMappingException()
    {
        $filter = new FooFilter();
        $filter->initialize('foo');
        try {
            $filter->getAssociationMapping();
        } catch (\RuntimeException $e) {
            $this->assertContains('The option `association_mapping` must be set for field: `foo`', $e->getMessage());

            return;
        }

        $this->fail('Failed asserting that exception of type "\RuntimeException" is thrown.');
    }

    public function testGetAssociationMapping()
    {
        $associationMapping = array(
            'fieldName' => 'user',
            'targetEntity' => 'Foo\Bar\User',
            'joinColumns' => array(
                0 => array(
                    'name' => 'user_id',
                    'referencedColumnName' => 'user_id',
                ),
            ),
            'type' => 2,
            'mappedBy' => null,
        );

        $filter = new FooFilter();
        $filter->setOption('association_mapping', $associationMapping);
        $this->assertSame($associationMapping, $filter->getAssociationMapping());
    }
}
