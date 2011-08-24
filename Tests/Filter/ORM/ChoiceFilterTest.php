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

use Sonata\AdminBundle\Filter\ORM\ChoiceFilter;

class ChoiceFilterTest extends \PHPUnit_Framework_TestCase
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
        $filter = new ChoiceFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('field_options' => array('class' => 'FooBar'))));

        $builder = new QueryBuilder;

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', 'all');
        $filter->filter($builder, 'alias', 'field', array());

        $this->assertEquals(array(), $builder->query);
    }

    public function testFilterArray()
    {
        $filter = new ChoiceFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('field_options' => array('class' => 'FooBar'))));

        $builder = new QueryBuilder;

        $filter->filter($builder, 'alias', 'field', array('1', '2'));

        $this->assertEquals(array('in_alias.field', 'alias.field IN ("1,2")'), $builder->query);
    }

    public function testFilterScalar()
    {
        $filter = new ChoiceFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('field_options' => array('class' => 'FooBar'))));

        $builder = new QueryBuilder;

        $filter->filter($builder, 'alias', 'field', '1');

        $this->assertEquals(array('alias.field = :field_name'), $builder->query);
        $this->assertEquals(array('field_name' => '1'), $builder->parameters);

    }
}