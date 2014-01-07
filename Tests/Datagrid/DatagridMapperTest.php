<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sonata\AdminBundle\Tests\Datagrid;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Filter\Filter;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class DatagridMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DatagridMapper
     */
    private $datagridMapper;

    /**
     * @var Datagrid
     */
    private $datagrid;

    public function setUp()
    {
        $datagridBuilder = $this->getMock('Sonata\AdminBundle\Builder\DatagridBuilderInterface');

        $proxyQuery = $this->getMock('Sonata\AdminBundle\Datagrid\ProxyQueryInterface');
        $pager = $this->getMock('Sonata\AdminBundle\Datagrid\PagerInterface');
        $fieldDescriptionCollection = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionCollection');
        $formBuilder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
                     ->disableOriginalConstructor()
                     ->getMock();

        $this->datagrid = new Datagrid($proxyQuery, $fieldDescriptionCollection, $pager, $formBuilder, array());

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        // php 5.3 BC
        $filter = $this->getMockForAbstractClass('Sonata\AdminBundle\Filter\Filter');

        $filter->expects($this->any())
            ->method('getDefaultOptions')
            ->will($this->returnValue(array('foo_default_option'=>'bar_default')));

        $datagridBuilder->expects($this->any())
            ->method('addFilter')
            ->will($this->returnCallback(function($datagrid, $type, $fieldDescription, $admin) use ($filter) {
                $fieldDescription->setType($type);

                $filterClone = clone $filter;
                $filterClone->initialize($fieldDescription->getName(), $fieldDescription->getOptions());
                $datagrid->addFilter($filterClone);
            }));

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        // php 5.3 BC
        $fieldDescription = $this->getFieldDescriptionMock();

        $modelManager->expects($this->any())
            ->method('getNewFieldDescriptionInstance')
            ->will($this->returnCallback(function($class, $name, array $options = array()) use ($fieldDescription) {
                $fieldDescriptionClone = clone $fieldDescription;
                $fieldDescriptionClone->setName($name);
                $fieldDescriptionClone->setOptions($options);

                return $fieldDescriptionClone;
            }));

        $admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $this->datagridMapper = new DatagridMapper($datagridBuilder, $this->datagrid, $admin);
    }

    public function testFluidInterface()
    {
        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->assertEquals($this->datagridMapper, $this->datagridMapper->add($fieldDescription, null, array('field_name'=>'fooFilterName')));
        $this->assertEquals($this->datagridMapper, $this->datagridMapper->remove('fooName'));
        $this->assertEquals($this->datagridMapper, $this->datagridMapper->reorder(array()));
    }

    public function testGet()
    {
        $this->assertFalse($this->datagridMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('foo.name', 'fooLabel');

        $this->datagridMapper->add($fieldDescription, null, array('field_name'=>'fooFilterName'));

        $filter = $this->datagridMapper->get('foo.name');
        $this->assertInstanceOf('Sonata\AdminBundle\Filter\FilterInterface', $filter);
        $this->assertEquals('foo.name', $filter->getName());
        $this->assertEquals('foo__name', $filter->getFormName());
        $this->assertEquals('text', $filter->getFieldType());
        $this->assertEquals('fooLabel', $filter->getLabel());
        $this->assertEquals(array('required'=>false), $filter->getFieldOptions());
        $this->assertEquals(array(
            'foo_default_option' => 'bar_default',
            'label' => 'fooLabel',
            'field_name' => 'fooFilterName',
            'placeholder' => 'short_object_description_placeholder',
            'link_parameters' => array()
        ), $filter->getOptions());
    }

    public function testGet2()
    {
        $this->assertFalse($this->datagridMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->datagridMapper->add($fieldDescription, 'foo_type', array('field_name'=>'fooFilterName', 'foo_filter_option'=>'foo_filter_option_value', 'foo_default_option'=>'bar_custom'), 'foo_field_type', array('foo_field_option'=>'baz'));

        $filter = $this->datagridMapper->get('fooName');
        $this->assertInstanceOf('Sonata\AdminBundle\Filter\FilterInterface', $filter);
        $this->assertEquals('fooName', $filter->getName());
        $this->assertEquals('fooName', $filter->getFormName());
        $this->assertEquals('foo_field_type', $filter->getFieldType());
        $this->assertEquals('fooLabel', $filter->getLabel());
        $this->assertEquals(array('foo_field_option'=>'baz'), $filter->getFieldOptions());
        $this->assertEquals(array(
            'foo_default_option' => 'bar_custom',
            'label' => 'fooLabel',
            'field_name' => 'fooFilterName',
            'field_options' => array ('foo_field_option' => 'baz'),
            'field_type' => 'foo_field_type',
            'placeholder' => 'short_object_description_placeholder',
            'foo_filter_option' => 'foo_filter_option_value',
            'link_parameters' => array()
        ), $filter->getOptions());
    }

    public function testAdd()
    {
        $this->datagridMapper->add('fooName');

        $this->assertTrue($this->datagridMapper->has('fooName'));

        $fieldDescription = $this->datagridMapper->get('fooName');

        $this->assertInstanceOf('Sonata\AdminBundle\Filter\FilterInterface', $fieldDescription);
        $this->assertEquals('fooName', $fieldDescription->getName());
    }

    public function testAddRemove()
    {
        $this->assertFalse($this->datagridMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->datagridMapper->add($fieldDescription, null, array('field_name'=>'fooFilterName'));
        $this->assertTrue($this->datagridMapper->has('fooName'));

        $this->datagridMapper->remove('fooName');
        $this->assertFalse($this->datagridMapper->has('fooName'));
    }

    public function testAddException()
    {
        try {
            $this->datagridMapper->add(12345);
        } catch (\RuntimeException $e) {
            $this->assertContains('invalid state', $e->getMessage());

            return;
        }

        $this->fail('Failed asserting that exception of type "\RuntimeException" is thrown.');
    }

    public function testAddException2()
    {
        try {
            $this->datagridMapper->getAdmin()
                ->expects($this->any())
                ->method('hasFilterFieldDescription')
                ->will($this->returnValue(true))
            ;
            $this->datagridMapper->add('field');
        } catch (\RuntimeException $e) {
            $this->assertContains('The field "field" is already defined', $e->getMessage());

            return;
        }

        $this->fail('Failed asserting that exception of type "\RuntimeException" is thrown.');
    }

    public function testReorder()
    {
        $fieldDescription1 = $this->getFieldDescriptionMock('fooName1', 'fooLabel1');
        $fieldDescription2 = $this->getFieldDescriptionMock('fooName2', 'fooLabel2');
        $fieldDescription3 = $this->getFieldDescriptionMock('fooName3', 'fooLabel3');
        $fieldDescription4 = $this->getFieldDescriptionMock('fooName4', 'fooLabel4');

        $this->datagridMapper->add($fieldDescription1, null, array('field_name'=>'fooFilterName1'));
        $this->datagridMapper->add($fieldDescription2, null, array('field_name'=>'fooFilterName2'));
        $this->datagridMapper->add($fieldDescription3, null, array('field_name'=>'fooFilterName3'));
        $this->datagridMapper->add($fieldDescription4, null, array('field_name'=>'fooFilterName4'));

        $this->assertEquals(array(
            'fooName1',
            'fooName2',
            'fooName3',
            'fooName4',
       ), array_keys($this->datagrid->getFilters()));

        $this->datagridMapper->reorder(array('fooName3', 'fooName2', 'fooName1', 'fooName4'));

        $this->assertEquals(array(
            'fooName3',
            'fooName2',
            'fooName1',
            'fooName4',
       ), array_keys($this->datagrid->getFilters()));
    }

    private function getFieldDescriptionMock($name=null, $label=null)
    {
        $fieldDescription = $this->getMockForAbstractClass('Sonata\AdminBundle\Admin\BaseFieldDescription');

        if ($name !== null) {
            $fieldDescription->setName($name);
        }

        if ($label !== null) {
            $fieldDescription->setOption('label', $label);
        }

        return $fieldDescription;
    }
}
