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

class StringFilterTest extends \PHPUnit_Framework_TestCase
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

    public function testFilter()
    {
        $filter = new StringFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('field_options' => array('class' => 'FooBar'))));

        $builder = new QueryBuilder;

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', '');

        $this->assertEquals(array(), $builder->query);

        $filter->filter($builder, 'alias', 'field', 'asd');
        $this->assertEquals(array('alias.field LIKE :field_name'), $builder->query);
        $this->assertEquals(array('field_name' => '%asd%'), $builder->parameters);
    }

    public function testFormat()
    {
        $filter = new StringFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('format' => '%s')));

        $builder = new QueryBuilder;

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', '');

        $this->assertEquals(array(), $builder->query);

        $filter->filter($builder, 'alias', 'field', 'asd');
        $this->assertEquals(array('alias.field LIKE :field_name'), $builder->query);
        $this->assertEquals(array('field_name' => 'asd'), $builder->parameters);
    }
}