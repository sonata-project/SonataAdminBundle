<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Filter\ORM;

use Sonata\AdminBundle\Filter\ORM\Filter;

class FilterTest_Filter extends Filter
{
    /**
     * Apply the filter to the QueryBuilder instance
     *
     * @param $queryBuilder
     * @param string $alias
     * @param string $field
     * @param string $value
     * @return void
     */
    function filter($queryBuilder, $alias, $field, $value)
    {
        // TODO: Implement filter() method.
    }

    function getDefaultOptions()
    {
        return array('option1' => 2);
    }
}


class FilterTest extends \PHPUnit_Framework_TestCase
{

    public function testFieldDescription()
    {
        $filter = new FilterTest_Filter();
        $this->assertEquals(array('option1' => 2), $filter->getDefaultOptions());
        $this->assertEquals(null, $filter->getOption('1'));

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue(array('field_options' => array('class' => 'FooBar'))));

        $fieldDescription->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('field_name'));

        $filter->setFieldDescription($fieldDescription);

        $this->assertEquals(2, $filter->getOption('option1'));
        $this->assertEquals(null, $filter->getOption('foo'));
        $this->assertEquals('bar', $filter->getOption('foo', 'bar'));

        $this->assertEquals('field_name', $filter->getName());
        $this->assertEquals('text', $filter->getFieldType());
        $this->assertEquals(array('class' => 'FooBar'), $filter->getFieldOptions());
    }

    public function testValues()
    {
        $filter = new FilterTest_Filter();
        $this->assertEmpty($filter->getValue());

        $filter->setValue(42);
        $this->assertEquals(42, $filter->getValue());
    }
}