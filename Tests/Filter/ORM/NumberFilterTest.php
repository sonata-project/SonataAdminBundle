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

use Sonata\AdminBundle\Filter\ORM\NumberFilter;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;

class NumberFilterTest extends \PHPUnit_Framework_TestCase
{
    public function getFieldDescription(array $options)
    {
        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $fieldDescription->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('field_name'));

        return $fieldDescription;
    }

    public function testFilterEmpty()
    {
        $filter = new NumberFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('field_options' => array('class' => 'FooBar'))));

        $builder = new QueryBuilder;

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', 'asds');

        $this->assertEquals(array(), $builder->query);
    }

    public function testFilterInvalidOperator()
    {
        $filter = new NumberFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('field_options' => array('class' => 'FooBar'))));

        $builder = new QueryBuilder;

        $filter->filter($builder, 'alias', 'field', array('type' => 'foo'));

        $this->assertEquals(array(), $builder->query);
    }

    public function testFilter()
    {
        $filter = new NumberFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('field_options' => array('class' => 'FooBar'))));

        $builder = new QueryBuilder;

        $filter->filter($builder, 'alias', 'field', array('type' => NumberType::TYPE_EQUAL, 'value' => 42));
        $filter->filter($builder, 'alias', 'field', array('type' => NumberType::TYPE_GREATER_EQUAL, 'value' => 42));
        $filter->filter($builder, 'alias', 'field', array('type' => NumberType::TYPE_GREATER_THAN, 'value' => 42));
        $filter->filter($builder, 'alias', 'field', array('type' => NumberType::TYPE_LESS_EQUAL, 'value' => 42));
        $filter->filter($builder, 'alias', 'field', array('type' => NumberType::TYPE_LESS_THAN, 'value' => 42));

        $expected = array(
            'alias.field = :field_name',
            'alias.field >= :field_name',
            'alias.field > :field_name',
            'alias.field <= :field_name',
            'alias.field < :field_name'
        );

        $this->assertEquals($expected, $builder->query);
    }
}