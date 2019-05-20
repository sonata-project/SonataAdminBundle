<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Datagrid;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Form\TestEntity;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class DatagridTest extends TestCase
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
     * @var FieldDescriptionCollection
     */
    private $columns;

    /**
     * @var FormBuilder
     */
    private $formBuilder;

    /**
     * @var array
     */
    private $formTypes;

    public function setUp(): void
    {
        $this->query = $this->createMock(ProxyQueryInterface::class);
        $this->columns = new FieldDescriptionCollection();
        $this->pager = $this->createMock(PagerInterface::class);

        $this->formTypes = [];

        $this->formBuilder = $this->getMockBuilder(FormBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formBuilder->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($name) {
                if (isset($this->formTypes[$name])) {
                    return $this->formTypes[$name];
                }
            });

        $this->formBuilder->expects($this->any())
            ->method('add')
            ->willReturnCallback(function ($name, $type, $options): void {
                $this->formTypes[$name] = new FormBuilder(
                    $name,
                    TestEntity::class,
                    $this->createMock(EventDispatcherInterface::class),
                    $this->createMock(FormFactoryInterface::class),
                    $options
                );
            });

        $this->formBuilder->expects($this->any())
            ->method('getForm')
            ->willReturnCallback(function () {
                return $this->getMockBuilder(Form::class)
                    ->disableOriginalConstructor()
                    ->getMock();
            });

        $values = [];

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, $values);
    }

    public function testGetPager(): void
    {
        $this->assertSame($this->pager, $this->datagrid->getPager());
    }

    public function testFilter(): void
    {
        $this->assertFalse($this->datagrid->hasFilter('foo'));
        $this->assertNull($this->datagrid->getFilter('foo'));

        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())
            ->method('getName')
            ->willReturn('foo');

        $this->datagrid->addFilter($filter);

        $this->assertTrue($this->datagrid->hasFilter('foo'));
        $this->assertFalse($this->datagrid->hasFilter('nonexistent'));
        $this->assertSame($filter, $this->datagrid->getFilter('foo'));

        $this->datagrid->removeFilter('foo');

        $this->assertFalse($this->datagrid->hasFilter('foo'));
    }

    public function testGetFilters(): void
    {
        $this->assertSame([], $this->datagrid->getFilters());

        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects($this->once())
            ->method('getName')
            ->willReturn('foo');

        $filter2 = $this->createMock(FilterInterface::class);
        $filter2->expects($this->once())
            ->method('getName')
            ->willReturn('bar');

        $filter3 = $this->createMock(FilterInterface::class);
        $filter3->expects($this->once())
            ->method('getName')
            ->willReturn('baz');

        $this->datagrid->addFilter($filter1);
        $this->datagrid->addFilter($filter2);
        $this->datagrid->addFilter($filter3);

        $this->assertSame(['foo' => $filter1, 'bar' => $filter2, 'baz' => $filter3], $this->datagrid->getFilters());

        $this->datagrid->removeFilter('bar');

        $this->assertSame(['foo' => $filter1, 'baz' => $filter3], $this->datagrid->getFilters());
    }

    public function testReorderFilters(): void
    {
        $this->assertSame([], $this->datagrid->getFilters());

        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects($this->once())
            ->method('getName')
            ->willReturn('foo');

        $filter2 = $this->createMock(FilterInterface::class);
        $filter2->expects($this->once())
            ->method('getName')
            ->willReturn('bar');

        $filter3 = $this->createMock(FilterInterface::class);
        $filter3->expects($this->once())
            ->method('getName')
            ->willReturn('baz');

        $this->datagrid->addFilter($filter1);
        $this->datagrid->addFilter($filter2);
        $this->datagrid->addFilter($filter3);

        $this->assertSame(['foo' => $filter1, 'bar' => $filter2, 'baz' => $filter3], $this->datagrid->getFilters());
        $this->assertSame(['foo', 'bar', 'baz'], array_keys($this->datagrid->getFilters()));

        $this->datagrid->reorderFilters(['bar', 'baz', 'foo']);

        $this->assertSame(['bar' => $filter2, 'baz' => $filter3, 'foo' => $filter1], $this->datagrid->getFilters());
        $this->assertSame(['bar', 'baz', 'foo'], array_keys($this->datagrid->getFilters()));
    }

    public function testGetValues(): void
    {
        $this->assertSame([], $this->datagrid->getValues());

        $this->datagrid->setValue('foo', 'bar', 'baz');

        $this->assertSame(['foo' => ['type' => 'bar', 'value' => 'baz']], $this->datagrid->getValues());
    }

    public function testGetColumns(): void
    {
        $this->assertSame($this->columns, $this->datagrid->getColumns());
    }

    public function testGetQuery(): void
    {
        $this->assertSame($this->query, $this->datagrid->getQuery());
    }

    public function testHasActiveFilters(): void
    {
        $this->assertFalse($this->datagrid->hasActiveFilters());

        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects($this->once())
            ->method('getName')
            ->willReturn('foo');
        $filter1->expects($this->any())
            ->method('isActive')
            ->willReturn(false);

        $this->datagrid->addFilter($filter1);

        $this->assertFalse($this->datagrid->hasActiveFilters());

        $filter2 = $this->createMock(FilterInterface::class);
        $filter2->expects($this->once())
            ->method('getName')
            ->willReturn('bar');
        $filter2->expects($this->any())
            ->method('isActive')
            ->willReturn(true);

        $this->datagrid->addFilter($filter2);

        $this->assertTrue($this->datagrid->hasActiveFilters());
    }

    public function testHasDisplayableFilters(): void
    {
        $this->assertFalse($this->datagrid->hasDisplayableFilters());
    }

    public function testHasDisplayableFiltersNotActive(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())
            ->method('getName')
            ->willReturn('foo');
        $filter->expects($this->any())
            ->method('getOption')
            ->willReturn(false);
        $filter->expects($this->any())
            ->method('isActive')
            ->willReturn(false);

        $this->datagrid->addFilter($filter);

        $this->assertFalse($this->datagrid->hasDisplayableFilters());
    }

    public function testHasDisplayableFiltersActive(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())
            ->method('getName')
            ->willReturn('bar');
        $filter->expects($this->any())
            ->method('getOption')
            ->willReturn(true);
        $filter->expects($this->any())
            ->method('isActive')
            ->willReturn(true);

        $this->datagrid->addFilter($filter);

        $this->assertTrue($this->datagrid->hasDisplayableFilters());
    }

    public function testHasDisplayableFiltersAlwaysShow(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())
            ->method('getName')
            ->willReturn('bar');
        $filter->expects($this->any())
            ->method('getOption')
            ->with($this->equalTo('show_filter'))
            ->willReturn(true);
        $filter->expects($this->any())
            ->method('isActive')
            ->willReturn(false);

        $this->datagrid->addFilter($filter);

        $this->assertTrue($this->datagrid->hasDisplayableFilters());
    }

    public function testGetForm(): void
    {
        $this->assertInstanceOf(Form::class, $this->datagrid->getForm());
    }

    public function testGetResults(): void
    {
        $this->assertNull($this->datagrid->getResults());

        $this->pager->expects($this->once())
            ->method('getResults')
            ->willReturn(['foo', 'bar']);

        $this->assertSame(['foo', 'bar'], $this->datagrid->getResults());
    }

    public function testEmptyResults(): void
    {
        $this->pager->expects($this->once())
            ->method('getResults')
            ->willReturn([]);

        $this->assertSame([], $this->datagrid->getResults());
        $this->assertSame([], $this->datagrid->getResults());
    }

    public function testBuildPager(): void
    {
        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects($this->once())
            ->method('getName')
            ->willReturn('foo');
        $filter1->expects($this->any())
            ->method('getFormName')
            ->willReturn('fooFormName');
        $filter1->expects($this->any())
            ->method('isActive')
            ->willReturn(false);
        $filter1->expects($this->any())
            ->method('getRenderSettings')
            ->willReturn(['foo1', ['bar1' => 'baz1']]);

        $this->datagrid->addFilter($filter1);

        $filter2 = $this->createMock(FilterInterface::class);
        $filter2->expects($this->once())
            ->method('getName')
            ->willReturn('bar');
        $filter2->expects($this->any())
            ->method('getFormName')
            ->willReturn('barFormName');
        $filter2->expects($this->any())
            ->method('isActive')
            ->willReturn(true);
        $filter2->expects($this->any())
            ->method('getRenderSettings')
            ->willReturn(['foo2', ['bar2' => 'baz2']]);

        $this->datagrid->addFilter($filter2);

        $this->datagrid->buildPager();

        $this->assertSame(['foo' => null, 'bar' => null], $this->datagrid->getValues());
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('fooFormName'));
        $this->assertSame(['bar1' => 'baz1'], $this->formBuilder->get('fooFormName')->getOptions());
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('barFormName'));
        $this->assertSame(['bar2' => 'baz2'], $this->formBuilder->get('barFormName')->getOptions());
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_sort_by'));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_sort_order'));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_page'));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_per_page'));
    }

    public function testBuildPagerWithException(): void
    {
        $this->expectException(\Symfony\Component\Form\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Sonata\\AdminBundle\\Admin\\FieldDescriptionInterface", "array" given');

        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())
            ->method('getName')
            ->willReturn('foo');
        $filter->expects($this->any())
            ->method('isActive')
            ->willReturn(false);
        $filter->expects($this->any())
            ->method('getRenderSettings')
            ->willReturn(['foo', ['bar' => 'baz']]);

        $this->datagrid->addFilter($filter);

        $this->datagrid->setValue('_sort_by', 'foo', 'baz');

        $this->datagrid->buildPager();
    }

    public function testBuildPagerWithSortBy(): void
    {
        $sortBy = $this->createMock(FieldDescriptionInterface::class);
        $sortBy->expects($this->once())
            ->method('isSortable')
            ->willReturn(true);

        $this->pager->expects($this->once())
            ->method('setMaxPerPage')
            ->with($this->equalTo('25'))
            ->willReturn(null);

        $this->pager->expects($this->once())
            ->method('setPage')
            ->with($this->equalTo('1'))
            ->willReturn(null);

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, ['_sort_by' => $sortBy]);

        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())
            ->method('getName')
            ->willReturn('foo');
        $filter->expects($this->any())
            ->method('getFormName')
            ->willReturn('fooFormName');
        $filter->expects($this->any())
            ->method('isActive')
            ->willReturn(false);
        $filter->expects($this->any())
            ->method('getRenderSettings')
            ->willReturn(['foo', ['bar' => 'baz']]);

        $this->datagrid->addFilter($filter);

        $this->datagrid->buildPager();

        $this->assertSame(['_sort_by' => $sortBy, 'foo' => null], $this->datagrid->getValues());
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('fooFormName'));
        $this->assertSame(['bar' => 'baz'], $this->formBuilder->get('fooFormName')->getOptions());
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_sort_by'));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_sort_order'));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_page'));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_per_page'));
    }

    /**
     * @dataProvider getBuildPagerWithPageTests
     */
    public function testBuildPagerWithPage($page, $perPage): void
    {
        $sortBy = $this->createMock(FieldDescriptionInterface::class);
        $sortBy->expects($this->once())
            ->method('isSortable')
            ->willReturn(true);

        $this->pager->expects($this->once())
            ->method('setMaxPerPage')
            ->with($this->equalTo('50'))
            ->willReturn(null);

        $this->pager->expects($this->once())
            ->method('setPage')
            ->with($this->equalTo('3'))
            ->willReturn(null);

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, ['_sort_by' => $sortBy, '_page' => $page, '_per_page' => $perPage]);

        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())
            ->method('getName')
            ->willReturn('foo');
        $filter->expects($this->any())
            ->method('getFormName')
            ->willReturn('fooFormName');
        $filter->expects($this->any())
            ->method('isActive')
            ->willReturn(false);
        $filter->expects($this->any())
            ->method('getRenderSettings')
            ->willReturn(['foo', ['bar' => 'baz']]);

        $this->datagrid->addFilter($filter);

        $this->datagrid->buildPager();

        $this->assertSame([
            '_sort_by' => $sortBy,
            '_page' => $page,
            '_per_page' => $perPage,
            'foo' => null,
        ], $this->datagrid->getValues());
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('fooFormName'));
        $this->assertSame(['bar' => 'baz'], $this->formBuilder->get('fooFormName')->getOptions());
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_sort_by'));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_sort_order'));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_page'));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_per_page'));
    }

    public function getBuildPagerWithPageTests()
    {
        // tests for php 5.3, because isset functionality was changed since php 5.4
        return [
            [3, 50],
            ['3', '50'],
            [3, '50'],
            ['3', 50],
        ];
    }

    /**
     * @dataProvider getBuildPagerWithPage2Tests
     */
    public function testBuildPagerWithPage2($page, $perPage): void
    {
        $this->pager->expects($this->once())
            ->method('setMaxPerPage')
            ->with($this->equalTo('50'))
            ->willReturn(null);

        $this->pager->expects($this->once())
            ->method('setPage')
            ->with($this->equalTo('3'))
            ->willReturn(null);

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, []);
        $this->datagrid->setValue('_per_page', null, $perPage);
        $this->datagrid->setValue('_page', null, $page);

        $this->datagrid->buildPager();

        $this->assertSame([
            '_per_page' => ['type' => null, 'value' => $perPage],
            '_page' => ['type' => null, 'value' => $page],
        ], $this->datagrid->getValues());
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_sort_by'));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_sort_order'));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_page'));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_per_page'));
    }

    public function getBuildPagerWithPage2Tests()
    {
        // tests for php 5.3, because isset functionality was changed since php 5.4
        return [
            [3, 50],
            ['3', '50'],
            [3, '50'],
            ['3', 50],
        ];
    }
}
