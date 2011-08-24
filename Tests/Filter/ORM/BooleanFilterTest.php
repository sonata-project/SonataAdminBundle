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

use Sonata\AdminBundle\Filter\ORM\BooleanFilter;
use Sonata\AdminBundle\Form\Type\Filter\BooleanType;

class BooleanFilterTest extends \PHPUnit_Framework_TestCase
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
        $filter = new BooleanFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('field_options' => array('class' => 'FooBar'))));

        $builder = new QueryBuilder;

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', '');
        $filter->filter($builder, 'alias', 'field', 'test');
        $filter->filter($builder, 'alias', 'field', false);

        $filter->filter($builder, 'alias', 'field', array());
        $filter->filter($builder, 'alias', 'field', array(null, 'test'));

        $this->assertEquals(array(), $builder->query);
    }

    public function testFilterNo()
    {
        $filter = new BooleanFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('field_options' => array('class' => 'FooBar'))));

        $builder = new QueryBuilder;

        $filter->filter($builder, 'alias', 'field', BooleanType::TYPE_NO);

        $this->assertEquals(array('alias.field = :field_name'), $builder->query);
        $this->assertEquals(array('field_name' => 0), $builder->parameters);
    }

    public function testFilterYes()
    {
        $filter = new BooleanFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('field_options' => array('class' => 'FooBar'))));

        $builder = new QueryBuilder;

        $filter->filter($builder, 'alias', 'field', BooleanType::TYPE_YES);

        $this->assertEquals(array('alias.field = :field_name'), $builder->query);
        $this->assertEquals(array('field_name' => 1), $builder->parameters);
    }

    public function testFilterArray()
    {
        $filter = new BooleanFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('field_options' => array('class' => 'FooBar'))));

        $builder = new QueryBuilder;

        $filter->filter($builder, 'alias', 'field', array(BooleanType::TYPE_NO));

        $this->assertEquals(array('in_alias.field', 'alias.field IN ("0")'), $builder->query);
    }
}