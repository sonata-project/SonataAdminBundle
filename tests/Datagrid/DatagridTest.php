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
     * @var mixed[]
     */
    private $formData;

    /**
     * @var array
     */
    private $formTypes;

    protected function setUp(): void
    {
        $this->query = $this->createMock(ProxyQueryInterface::class);
        $this->columns = new FieldDescriptionCollection();
        // NEXT_MAJOR: Use createMock instead.
        $this->pager = $this->getMockBuilder(PagerInterface::class)
            ->addMethods(['getCurrentPageResults'])
            ->getMockForAbstractClass();

        $this->formData = [];
        $this->formTypes = [];

        $this->formBuilder = $this->getMockBuilder(FormBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formBuilder
            ->method('get')
            ->willReturnCallback(function (string $name): FormBuilder {
                if (isset($this->formTypes[$name])) {
                    return $this->formTypes[$name];
                }
            });

        $this->formBuilder
            ->method('add')
            ->willReturnCallback(function (?string $name, string $type, array $options): void {
                $this->formTypes[$name] = new FormBuilder(
                    $name,
                    TestEntity::class,
                    $this->createMock(EventDispatcherInterface::class),
                    $this->createMock(FormFactoryInterface::class),
                    $options
                );
            });

        $form = $this->createStub(Form::class);

        $form->method('submit')->willReturnCallback(function (array $values): void {
            $this->formData = $values;
        });
        $form->method('getData')->willReturnCallback(function (): array {
            return $this->formData;
        });

        $this->formBuilder->method('getForm')->willReturn($form);

        $values = [];

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, $values);
    }

    public function testGetPager(): void
    {
        $this->assertSame($this->pager, $this->datagrid->getPager());
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation Passing a nonexistent filter name as argument 1 to Sonata\AdminBundle\Datagrid\Datagrid::getFilter() is deprecated since sonata-project/admin-bundle 3.52 and will throw an exception in 4.0.
     */
    public function testFilter(): void
    {
        $this->assertFalse($this->datagrid->hasFilter('foo'));

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
        $this->assertNull($this->datagrid->getFilter('foo'));
        // NEXT_MAJOR: Remove previous assertion, the "@group" and "@expectedDeprecation" annotations and uncomment the following lines
        // $this->expectException(\InvalidArgumentException::class);
        // $this->expectExceptionMessage('Filter named "foo" doesn\'t exist.');
        //
        // $this->datagrid->getFilter('foo');
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
        $filter1
            ->method('isActive')
            ->willReturn(false);

        $this->datagrid->addFilter($filter1);

        $this->assertFalse($this->datagrid->hasActiveFilters());

        $filter2 = $this->createMock(FilterInterface::class);
        $filter2->expects($this->once())
            ->method('getName')
            ->willReturn('bar');
        $filter2
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
        $filter
            ->method('getOption')
            ->willReturn(false);
        $filter
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
        $filter
            ->method('getOption')
            ->willReturn(true);
        $filter
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
        $filter
            ->method('getOption')
            ->with($this->equalTo('show_filter'))
            ->willReturn(true);
        $filter
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
            ->method('getCurrentPageResults')
            ->willReturn(['foo', 'bar']);

        $this->assertSame(['foo', 'bar'], $this->datagrid->getResults());
    }

    public function testEmptyResults(): void
    {
        $this->pager->expects($this->once())
            ->method('getCurrentPageResults')
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
        $filter1
            ->method('getFormName')
            ->willReturn('fooFormName');
        $filter1
            ->method('isActive')
            ->willReturn(false);
        $filter1
            ->method('getRenderSettings')
            ->willReturn(['foo1', ['bar1' => 'baz1']]);

        $this->datagrid->addFilter($filter1);

        $filter2 = $this->createMock(FilterInterface::class);
        $filter2->expects($this->once())
            ->method('getName')
            ->willReturn('bar');
        $filter2
            ->method('getFormName')
            ->willReturn('barFormName');
        $filter2
            ->method('isActive')
            ->willReturn(true);
        $filter2
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

    /**
     * @dataProvider applyFilterDataProvider
     */
    public function testApplyFilter(?string $type, ?string $value, int $applyCallNumber): void
    {
        $this->datagrid->setValue('fooFormName', $type, $value);

        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())->method('getName')->willReturn('foo');
        $filter->method('getFormName')->willReturn('fooFormName');
        $filter->method('isActive')->willReturn(false);
        $filter->method('getRenderSettings')->willReturn(['foo1', ['bar1' => 'baz1']]);
        $filter->expects($this->exactly($applyCallNumber))->method('apply');

        $this->datagrid->addFilter($filter);

        $this->datagrid->buildPager();
    }

    /**
     * @return iterable<array{?string, ?string, int}>
     */
    public function applyFilterDataProvider(): iterable
    {
        yield ['fakeType', 'fakeValue', 1];
        yield ['', 'fakeValue', 1];
        yield [null, 'fakeValue', 1];
        yield ['fakeType', '', 1];
        yield ['fakeType', null, 1];
        yield ['', '', 0];
        yield ['', null, 0];
        yield [null, '', 0];
        yield [null, null, 0];
    }

    public function testBuildPagerWithException(): void
    {
        $this->expectException(\Symfony\Component\Form\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Sonata\\AdminBundle\\Admin\\FieldDescriptionInterface", "array" given');

        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())
            ->method('getName')
            ->willReturn('foo');
        $filter
            ->method('isActive')
            ->willReturn(false);
        $filter
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
        $filter
            ->method('getFormName')
            ->willReturn('fooFormName');
        $filter
            ->method('isActive')
            ->willReturn(false);
        $filter
            ->method('getRenderSettings')
            ->willReturn(['foo', ['bar' => 'baz']]);

        $this->datagrid->addFilter($filter);

        $this->datagrid->buildPager();

        $this->assertSame(['_sort_by' => $sortBy, 'foo' => null, '_sort_order' => 'ASC'], $this->datagrid->getValues());
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
        $filter
            ->method('getFormName')
            ->willReturn('fooFormName');
        $filter
            ->method('isActive')
            ->willReturn(false);
        $filter
            ->method('getRenderSettings')
            ->willReturn(['foo', ['bar' => 'baz']]);

        $this->datagrid->addFilter($filter);

        $this->datagrid->buildPager();

        $this->assertSame([
            '_sort_by' => $sortBy,
            '_page' => $page,
            '_per_page' => $perPage,
            'foo' => null,
            '_sort_order' => 'ASC',
        ], $this->datagrid->getValues());
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('fooFormName'));
        $this->assertSame(['bar' => 'baz'], $this->formBuilder->get('fooFormName')->getOptions());
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_sort_by'));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_sort_order'));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_page'));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('_per_page'));
    }

    public function getBuildPagerWithPageTests(): array
    {
        // tests for php 5.3, because isset functionality was changed since php 5.4
        return [
            [3, 50],
            ['3', '50'],
            [3, '50'],
            ['3', 50],
            [3, ['type' => null, 'value' => 50]],
            [3, ['type' => null, 'value' => '50']],
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

    public function getBuildPagerWithPage2Tests(): array
    {
        // tests for php 5.3, because isset functionality was changed since php 5.4
        return [
            [3, 50],
            ['3', '50'],
            [3, '50'],
            ['3', 50],
        ];
    }

    public function testSortParameters(): void
    {
        $field1 = $this->createMock(FieldDescriptionInterface::class);
        $field1->method('getName')->willReturn('field1');

        $field2 = $this->createMock(FieldDescriptionInterface::class);
        $field2->method('getName')->willReturn('field2');

        $field3 = $this->createMock(FieldDescriptionInterface::class);
        $field3->method('getName')->willReturn('field3');
        $field3->method('getOption')->with('sortable')->willReturn('field3sortBy');

        $this->datagrid = new Datagrid(
            $this->query,
            $this->columns,
            $this->pager,
            $this->formBuilder,
            ['_sort_by' => $field1, '_sort_order' => 'ASC']
        );

        $parameters = $this->datagrid->getSortParameters($field1);

        $this->assertSame('DESC', $parameters['filter']['_sort_order']);
        $this->assertSame('field1', $parameters['filter']['_sort_by']);

        $parameters = $this->datagrid->getSortParameters($field2);

        $this->assertSame('ASC', $parameters['filter']['_sort_order']);
        $this->assertSame('field2', $parameters['filter']['_sort_by']);

        $parameters = $this->datagrid->getSortParameters($field3);

        $this->assertSame('ASC', $parameters['filter']['_sort_order']);
        $this->assertSame('field3sortBy', $parameters['filter']['_sort_by']);

        $this->datagrid = new Datagrid(
            $this->query,
            $this->columns,
            $this->pager,
            $this->formBuilder,
            ['_sort_by' => $field3, '_sort_order' => 'ASC']
        );

        $parameters = $this->datagrid->getSortParameters($field3);

        $this->assertSame('DESC', $parameters['filter']['_sort_order']);
        $this->assertSame('field3sortBy', $parameters['filter']['_sort_by']);
    }

    public function testGetPaginationParameters(): void
    {
        $field = $this->createMock(FieldDescriptionInterface::class);

        $this->datagrid = new Datagrid(
            $this->query,
            $this->columns,
            $this->pager,
            $this->formBuilder,
            ['_sort_by' => $field, '_sort_order' => 'ASC']
        );

        $field->expects($this->once())->method('getName')->willReturn($name = 'test');

        $result = $this->datagrid->getPaginationParameters($page = 5);

        $this->assertSame($page, $result['filter']['_page']);
        $this->assertSame($name, $result['filter']['_sort_by']);
    }
}
