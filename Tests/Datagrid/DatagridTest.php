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

use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Symfony\Component\Form\FormBuilder;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class DatagridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Datagrid
     */
    private $datagrid;

    /**
     * @var PagerInterface
     */
    private $pager;

    /**
     * @var ProxyQueryInterface
     */
    private $query;

    /**
     * @var FormBuilder
     */
    private $formBuilder;

    /**
     * @var array
     */
    private $formTypes;

    public function setUp()
    {
        $this->query = $this->getMock('Sonata\AdminBundle\Datagrid\ProxyQueryInterface');
        $this->columns = new FieldDescriptionCollection();
        $this->pager = $this->getMock('Sonata\AdminBundle\Datagrid\PagerInterface');

        $this->formTypes = array();

        // php 5.3 BC
        $formTypes = &$this->formTypes;

        $this->formBuilder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->formBuilder->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($name) use (& $formTypes) {
                if (isset($formTypes[$name])) {
                    return $formTypes[$name];
                }

                return null;
            }));

        // php 5.3 BC
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $this->formBuilder->expects($this->any())
            ->method('add')
            ->will($this->returnCallback(function ($name, $type, $options) use (& $formTypes, $eventDispatcher, $formFactory) {
                $formTypes[$name] = new FormBuilder($name, 'Sonata\AdminBundle\Tests\Fixtures\Entity\Form\TestEntity', $eventDispatcher, $formFactory, $options);

                return null;
            }));

        // php 5.3 BC
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->formBuilder->expects($this->any())
            ->method('getForm')
            ->will($this->returnCallback(function () use ($form) {
                return $form;
            }));

        $values = array();

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, $values);
    }

    public function testGetPager()
    {
        $this->assertEquals($this->pager, $this->datagrid->getPager());
    }

    public function testFilter()
    {
        $this->assertFalse($this->datagrid->hasFilter('foo'));
        $this->assertNull($this->datagrid->getFilter('foo'));

        $filter = $this->getMock('Sonata\AdminBundle\Filter\FilterInterface');
        $filter->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $this->datagrid->addFilter($filter);

        $this->assertTrue($this->datagrid->hasFilter('foo'));
        $this->assertFalse($this->datagrid->hasFilter('nonexistent'));
        $this->assertEquals($filter, $this->datagrid->getFilter('foo'));

        $this->datagrid->removeFilter('foo');

        $this->assertFalse($this->datagrid->hasFilter('foo'));
    }

    public function testGetFilters()
    {
        $this->assertEquals(array(), $this->datagrid->getFilters());

        $filter1 = $this->getMock('Sonata\AdminBundle\Filter\FilterInterface');
        $filter1->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $filter2 = $this->getMock('Sonata\AdminBundle\Filter\FilterInterface');
        $filter2->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('bar'));

        $filter3 = $this->getMock('Sonata\AdminBundle\Filter\FilterInterface');
        $filter3->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('baz'));

        $this->datagrid->addFilter($filter1);
        $this->datagrid->addFilter($filter2);
        $this->datagrid->addFilter($filter3);

        $this->assertEquals(array('foo'=>$filter1, 'bar'=>$filter2, 'baz'=>$filter3), $this->datagrid->getFilters());

        $this->datagrid->removeFilter('bar');

        $this->assertEquals(array('foo'=>$filter1, 'baz'=>$filter3), $this->datagrid->getFilters());
    }

    public function testReorderFilters()
    {
        $this->assertEquals(array(), $this->datagrid->getFilters());

        $filter1 = $this->getMock('Sonata\AdminBundle\Filter\FilterInterface');
        $filter1->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $filter2 = $this->getMock('Sonata\AdminBundle\Filter\FilterInterface');
        $filter2->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('bar'));

        $filter3 = $this->getMock('Sonata\AdminBundle\Filter\FilterInterface');
        $filter3->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('baz'));

        $this->datagrid->addFilter($filter1);
        $this->datagrid->addFilter($filter2);
        $this->datagrid->addFilter($filter3);

        $this->assertEquals(array('foo'=>$filter1, 'bar'=>$filter2, 'baz'=>$filter3), $this->datagrid->getFilters());
        $this->assertEquals(array('foo', 'bar', 'baz'), array_keys($this->datagrid->getFilters()));

        $this->datagrid->reorderFilters(array('bar', 'baz', 'foo'));

        $this->assertEquals(array('bar'=>$filter2, 'baz'=>$filter3, 'foo'=>$filter1), $this->datagrid->getFilters());
        $this->assertEquals(array('bar', 'baz', 'foo'), array_keys($this->datagrid->getFilters()));
    }

    public function testGetValues()
    {
        $this->assertEquals(array(), $this->datagrid->getValues());

        $this->datagrid->setValue('foo', 'bar', 'baz');

        $this->assertEquals(array('foo'=>array('type'=>'bar', 'value'=>'baz')), $this->datagrid->getValues());
    }

    public function testGetColumns()
    {
        $this->assertEquals($this->columns, $this->datagrid->getColumns());
    }

    public function testGetQuery()
    {
        $this->assertEquals($this->query, $this->datagrid->getQuery());
    }

    public function testHasActiveFilters()
    {
        $this->assertFalse($this->datagrid->hasActiveFilters());

        $filter1 = $this->getMock('Sonata\AdminBundle\Filter\FilterInterface');
        $filter1->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('foo'));
        $filter1->expects($this->any())
            ->method('isActive')
            ->will($this->returnValue(false));

        $this->datagrid->addFilter($filter1);

        $this->assertFalse($this->datagrid->hasActiveFilters());

        $filter2 = $this->getMock('Sonata\AdminBundle\Filter\FilterInterface');
        $filter2->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('bar'));
        $filter2->expects($this->any())
            ->method('isActive')
            ->will($this->returnValue(true));

        $this->datagrid->addFilter($filter2);

        $this->assertTrue($this->datagrid->hasActiveFilters());
    }

    public function testGetForm()
    {
        $this->assertInstanceOf('Symfony\Component\Form\Form', $this->datagrid->getForm());
    }

    public function testGetResults()
    {
        $this->assertEquals(null, $this->datagrid->getResults());

        $this->pager->expects($this->once())
            ->method('getResults')
            ->will($this->returnValue(array('foo', 'bar')));

        $this->assertEquals(array('foo', 'bar'), $this->datagrid->getResults());
    }

    public function testBuildPager()
    {
        $filter1 = $this->getMock('Sonata\AdminBundle\Filter\FilterInterface');
        $filter1->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('foo'));
        $filter1->expects($this->any())
            ->method('getFormName')
            ->will($this->returnValue('fooFormName'));
        $filter1->expects($this->any())
            ->method('isActive')
            ->will($this->returnValue(false));
        $filter1->expects($this->any())
            ->method('getRenderSettings')
            ->will($this->returnValue(array('foo1', array('bar1'=>'baz1'))));

        $this->datagrid->addFilter($filter1);

        $filter2 = $this->getMock('Sonata\AdminBundle\Filter\FilterInterface');
        $filter2->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('bar'));
        $filter2->expects($this->any())
            ->method('getFormName')
            ->will($this->returnValue('barFormName'));
        $filter2->expects($this->any())
            ->method('isActive')
            ->will($this->returnValue(true));
        $filter2->expects($this->any())
            ->method('getRenderSettings')
            ->will($this->returnValue(array('foo2', array('bar2'=>'baz2'))));

        $this->datagrid->addFilter($filter2);

        $this->datagrid->buildPager();

        $this->assertEquals(array('foo'=>null, 'bar'=> null), $this->datagrid->getValues());
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('fooFormName'));
        $this->assertEquals(array('bar1' => 'baz1'), $this->formBuilder->get('fooFormName')->getOptions());
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('barFormName'));
        $this->assertEquals(array('bar2' => 'baz2'), $this->formBuilder->get('barFormName')->getOptions());
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('_sort_by'));
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('_sort_order'));
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('_page'));
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('_per_page'));
    }

    /**
     * @expectedException        \Symfony\Component\Form\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "FieldDescriptionInterface", "array" given
     */
    public function testBuildPagerWithException()
    {
        $filter = $this->getMock('Sonata\AdminBundle\Filter\FilterInterface');
        $filter->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('foo'));
        $filter->expects($this->any())
            ->method('isActive')
            ->will($this->returnValue(false));
        $filter->expects($this->any())
            ->method('getRenderSettings')
            ->will($this->returnValue(array('foo', array('bar'=>'baz'))));

        $this->datagrid->addFilter($filter);

        $this->datagrid->setValue('_sort_by', 'foo', 'baz');

        $this->datagrid->buildPager();
    }

    public function testBuildPagerWithSortBy()
    {
        $sortBy = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $sortBy->expects($this->once())
            ->method('isSortable')
            ->will($this->returnValue(true));

        $this->pager->expects($this->once())
            ->method('setMaxPerPage')
            ->with($this->equalTo('25'))
            ->will($this->returnValue(null));

        $this->pager->expects($this->once())
            ->method('setPage')
            ->with($this->equalTo('1'))
            ->will($this->returnValue(null));

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, array('_sort_by'=>$sortBy));

        $filter = $this->getMock('Sonata\AdminBundle\Filter\FilterInterface');
        $filter->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('foo'));
        $filter->expects($this->any())
            ->method('getFormName')
            ->will($this->returnValue('fooFormName'));
        $filter->expects($this->any())
            ->method('isActive')
            ->will($this->returnValue(false));
        $filter->expects($this->any())
            ->method('getRenderSettings')
            ->will($this->returnValue(array('foo', array('bar'=>'baz'))));

        $this->datagrid->addFilter($filter);

        $this->datagrid->buildPager();

        $this->assertEquals(array('_sort_by'=>$sortBy, 'foo'=>null), $this->datagrid->getValues());
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('fooFormName'));
        $this->assertEquals(array('bar' => 'baz'), $this->formBuilder->get('fooFormName')->getOptions());
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('_sort_by'));
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('_sort_order'));
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('_page'));
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('_per_page'));
    }

    /**
     * @dataProvider getBuildPagerWithPageTests
     */
    public function testBuildPagerWithPage($page, $perPage)
    {
        $sortBy = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $sortBy->expects($this->once())
            ->method('isSortable')
            ->will($this->returnValue(true));

        $this->pager->expects($this->once())
            ->method('setMaxPerPage')
            ->with($this->equalTo('50'))
            ->will($this->returnValue(null));

        $this->pager->expects($this->once())
            ->method('setPage')
            ->with($this->equalTo('3'))
            ->will($this->returnValue(null));

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, array('_sort_by'=>$sortBy, '_page'=>$page, '_per_page'=>$perPage));

        $filter = $this->getMock('Sonata\AdminBundle\Filter\FilterInterface');
        $filter->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('foo'));
        $filter->expects($this->any())
            ->method('getFormName')
            ->will($this->returnValue('fooFormName'));
        $filter->expects($this->any())
            ->method('isActive')
            ->will($this->returnValue(false));
        $filter->expects($this->any())
            ->method('getRenderSettings')
            ->will($this->returnValue(array('foo', array('bar'=>'baz'))));

        $this->datagrid->addFilter($filter);

        $this->datagrid->buildPager();

        $this->assertEquals(array('_sort_by'=>$sortBy, 'foo'=>null, '_page' => 3, '_per_page' => 50), $this->datagrid->getValues());
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('fooFormName'));
        $this->assertEquals(array('bar' => 'baz'), $this->formBuilder->get('fooFormName')->getOptions());
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('_sort_by'));
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('_sort_order'));
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('_page'));
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('_per_page'));
    }

    public function getBuildPagerWithPageTests()
    {
        // tests for php 5.3, because isset functionality was changed since php 5.4
        return array(
            array(3, 50),
            array('3', '50'),
            array(3, '50'),
            array('3', 50),
        );
    }

    /**
     * @dataProvider getBuildPagerWithPage2Tests
     */
    public function testBuildPagerWithPage2($page, $perPage)
    {
        $this->pager->expects($this->once())
            ->method('setMaxPerPage')
            ->with($this->equalTo('50'))
            ->will($this->returnValue(null));

        $this->pager->expects($this->once())
            ->method('setPage')
            ->with($this->equalTo('3'))
            ->will($this->returnValue(null));

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, array());
        $this->datagrid->setValue('_per_page', null, $perPage);
        $this->datagrid->setValue('_page', null, $page);

        $this->datagrid->buildPager();

        $this->assertEquals(array('_page' => array('type'=>null, 'value'=>3), '_per_page' => array('type'=>null, 'value'=>50)), $this->datagrid->getValues());
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('_sort_by'));
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('_sort_order'));
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('_page'));
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->formBuilder->get('_per_page'));
    }

    public function getBuildPagerWithPage2Tests()
    {
        // tests for php 5.3, because isset functionality was changed since php 5.4
        return array(
            array(3, 50),
            array('3', '50'),
            array(3, '50'),
            array('3', 50),
        );
    }
}
