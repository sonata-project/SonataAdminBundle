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

use Sonata\AdminBundle\Filter\ORM\StringFilter;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;

class StringFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $filter = new StringFilter;
        $filter->initialize('field_name', array('field_options' => array('class' => 'FooBar')));

        $builder = new QueryBuilder;

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', '');

        $this->assertEquals(array(), $builder->query);
    }

    public function testContains()
    {
        $filter = new StringFilter;
        $filter->initialize('field_name', array('format' => '%s'));

        $builder = new QueryBuilder;
        $this->assertEquals(array(), $builder->query);

        $filter->filter($builder, 'alias', 'field', array('value' => 'asd', 'type' => ChoiceType::TYPE_CONTAINS));
        $this->assertEquals(array('alias.field LIKE :field_name'), $builder->query);
        $this->assertEquals(array('field_name' => 'asd'), $builder->parameters);


        $builder = new QueryBuilder;
        $this->assertEquals(array(), $builder->query);

        $filter->filter($builder, 'alias', 'field', array('value' => 'asd', 'type' => null));
        $this->assertEquals(array('alias.field LIKE :field_name'), $builder->query);
        $this->assertEquals(array('field_name' => 'asd'), $builder->parameters);
    }

    public function testNotContains()
    {
        $filter = new StringFilter;
        $filter->initialize('field_name', array('format' => '%s'));

        $builder = new QueryBuilder;
        $this->assertEquals(array(), $builder->query);

        $filter->filter($builder, 'alias', 'field', array('value' => 'asd', 'type' => ChoiceType::TYPE_NOT_CONTAINS));
        $this->assertEquals(array('alias.field NOT LIKE :field_name'), $builder->query);
        $this->assertEquals(array('field_name' => 'asd'), $builder->parameters);
    }

    public function testEquals()
    {
        $filter = new StringFilter;
        $filter->initialize('field_name', array('format' => '%s'));

        $builder = new QueryBuilder;
        $this->assertEquals(array(), $builder->query);

        $filter->filter($builder, 'alias', 'field', array('value' => 'asd', 'type' => ChoiceType::TYPE_EQUAL));
        $this->assertEquals(array('alias.field = :field_name'), $builder->query);
        $this->assertEquals(array('field_name' => 'asd'), $builder->parameters);
    }
}