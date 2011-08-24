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

use Sonata\AdminBundle\Filter\ORM\ModelFilter;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class ModelFilterTest extends \PHPUnit_Framework_TestCase
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
        $filter = new ModelFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('field_options' => array('class' => 'FooBar'))));

        $builder = new QueryBuilder;

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', array());

        $this->assertEquals(array(), $builder->query);
    }

    public function testFilterArray()
    {
        $filter = new ModelFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('field_options' => array('class' => 'FooBar'))));

        $builder = new QueryBuilder;

        $filter->filter($builder, 'alias', 'field', array('1', '2'));

        $this->assertEquals(array('in_alias.field', 'alias.field IN ("1,2")'), $builder->query);
    }

    public function testFilterScalar()
    {
        $filter = new ModelFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('field_options' => array('class' => 'FooBar'))));

        $builder = new QueryBuilder;

        $filter->filter($builder, 'alias', 'field', 2);

        $this->assertEquals(array('alias.field = :field_name'), $builder->query);
        $this->assertEquals(array('field_name' => 2), $builder->parameters);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testAssociationWithInvalidMapping()
    {
        $filter = new ModelFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('mapping_type' => 'foo')));

        $builder = new QueryBuilder;

        $filter->apply($builder, 'asd');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testAssociationWithValidMappingAndEmptyFieldName()
    {
        $filter = new ModelFilter;
        $filter->setFieldDescription($this->getFieldDescription(array('mapping_type' => ClassMetadataInfo::ONE_TO_ONE)));

        $builder = new QueryBuilder;

        $filter->apply($builder, 'asd');
    }

    public function testAssociationWithValidMapping()
    {
        $filter = new ModelFilter;
        $filter->setFieldDescription($this->getFieldDescription(array(
            'mapping_type' => ClassMetadataInfo::ONE_TO_ONE,
            'field_name' => 'field_name'
        )));

        $builder = new QueryBuilder;

        $filter->apply($builder, 'asd');

        $this->assertEquals(array('o.field_name', 'field_name.id = :field_name'), $builder->query);
    }
}