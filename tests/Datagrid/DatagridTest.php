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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Forms;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class DatagridTest extends TestCase
{
    /**
     * @var Datagrid
     */
    private $datagrid;

    /**
     * @var PagerInterface&MockObject
     */
    private $pager;

    /**
     * @var ProxyQueryInterface&MockObject
     */
    private $query;

    /**
     * @var FieldDescriptionCollection
     */
    private $columns;

    /**
     * @var FormBuilderInterface
     */
    private $formBuilder;

    protected function setUp(): void
    {
        $this->query = $this->createMock(ProxyQueryInterface::class);
        $this->columns = new FieldDescriptionCollection();
        $this->pager = $this->createMock(PagerInterface::class);

        $factory = Forms::createFormFactoryBuilder()
            ->getFormFactory();

        $this->formBuilder = $factory->createBuilder();

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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Filter named "foo" doesn\'t exist.');

        $this->datagrid->getFilter('foo');
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
        $foo = new \stdClass();
        $bar = new \stdClass();

        $this->pager->expects($this->once())
            ->method('getCurrentPageResults')
            ->willReturn([$foo, $bar]);

        $this->assertSame([$foo, $bar], $this->datagrid->getResults());
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
            ->willReturn([DefaultType::class, ['operator_options' => ['help' => 'baz1']]]);

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
            ->willReturn([DefaultType::class, ['operator_options' => ['help' => 'baz2']]]);

        $this->datagrid->addFilter($filter2);

        $this->datagrid->buildPager();

        $this->assertSame(['foo' => null, 'bar' => null], $this->datagrid->getValues());
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('fooFormName'));
        $this->assertSame(['help' => 'baz1'], $this->formBuilder->get('fooFormName')->getOptions()['operator_options']);
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('barFormName'));
        $this->assertSame(['help' => 'baz2'], $this->formBuilder->get('barFormName')->getOptions()['operator_options']);
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_BY));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_ORDER));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PAGE));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PER_PAGE));
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
        $filter->method('getRenderSettings')
            ->willReturn([DefaultType::class, ['operator_options' => ['help' => 'baz2']]]);
        $filter->expects($this->exactly($applyCallNumber))->method('apply');

        $this->datagrid->addFilter($filter);

        $this->datagrid->buildPager();
    }

    /**
     * @return iterable<array{?string, ?string, int}>
     */
    public function applyFilterDataProvider(): iterable
    {
        yield ['3', 'fakeValue', 1];
        yield [null, 'fakeValue', 1];
        yield [null, 'fakeValue', 1];
        yield ['3', '', 1];
        yield ['3', null, 1];
        yield [null, '', 0];
        yield [null, null, 0];
        yield [null, '', 0];
        yield [null, null, 0];
    }

    public function testBuildPagerWithException(): void
    {
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
            ->willReturn([DefaultType::class, ['operator_options' => ['help' => 'baz']]]);

        $this->datagrid->addFilter($filter);

        $this->datagrid->setValue(DatagridInterface::SORT_BY, 'foo', 'baz');

        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Sonata\\AdminBundle\\FieldDescription\\FieldDescriptionInterface", "array" given');

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
            ->with($this->equalTo('25'));

        $this->pager->expects($this->once())
            ->method('setPage')
            ->with($this->equalTo('1'));

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, [DatagridInterface::SORT_BY => $sortBy]);

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
            ->willReturn([DefaultType::class, ['operator_options' => ['help' => 'baz']]]);

        $this->datagrid->addFilter($filter);

        $this->datagrid->buildPager();

        $this->assertSame([DatagridInterface::SORT_BY => $sortBy, 'foo' => null, DatagridInterface::SORT_ORDER => 'ASC'], $this->datagrid->getValues());
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('fooFormName'));
        $this->assertSame(['help' => 'baz'], $this->formBuilder->get('fooFormName')->getOptions()['operator_options']);
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_BY));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_ORDER));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PAGE));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PER_PAGE));
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
            ->with($this->equalTo(50));

        $this->pager->expects($this->once())
            ->method('setPage')
            ->with($this->equalTo(3));

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, [DatagridInterface::SORT_BY => $sortBy, DatagridInterface::PAGE => $page, DatagridInterface::PER_PAGE => $perPage]);

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
            ->willReturn([DefaultType::class, ['operator_options' => ['help' => 'baz']]]);

        $this->datagrid->addFilter($filter);

        $this->datagrid->buildPager();

        $this->assertSame([
            DatagridInterface::SORT_BY => $sortBy,
            DatagridInterface::PAGE => $page,
            DatagridInterface::PER_PAGE => $perPage,
            'foo' => null,
            DatagridInterface::SORT_ORDER => 'ASC',
        ], $this->datagrid->getValues());
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get('fooFormName'));
        $this->assertSame(['help' => 'baz'], $this->formBuilder->get('fooFormName')->getOptions()['operator_options']);
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_BY));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_ORDER));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PAGE));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PER_PAGE));
    }

    public function getBuildPagerWithPageTests(): array
    {
        return [
            [3, 50],
            [3, ['type' => null, 'value' => 50]],
        ];
    }

    /**
     * @dataProvider getBuildPagerWithPage2Tests
     */
    public function testBuildPagerWithPage2($page, $perPage): void
    {
        $this->pager->expects($this->once())
            ->method('setMaxPerPage')
            ->with($this->equalTo(50));

        $this->pager->expects($this->once())
            ->method('setPage')
            ->with($this->equalTo(3));

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, []);
        $this->datagrid->setValue(DatagridInterface::PER_PAGE, null, $perPage);
        $this->datagrid->setValue(DatagridInterface::PAGE, null, $page);

        $this->datagrid->buildPager();

        $this->assertSame([
            DatagridInterface::PER_PAGE => ['type' => null, 'value' => $perPage],
            DatagridInterface::PAGE => ['type' => null, 'value' => $page],
        ], $this->datagrid->getValues());
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_BY));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_ORDER));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PAGE));
        $this->assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PER_PAGE));
    }

    public function getBuildPagerWithPage2Tests(): array
    {
        return [
            [3, 50],
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
            [DatagridInterface::SORT_BY => $field1, DatagridInterface::SORT_ORDER => 'ASC']
        );

        $parameters = $this->datagrid->getSortParameters($field1);

        $this->assertSame('DESC', $parameters['filter'][DatagridInterface::SORT_ORDER]);
        $this->assertSame('field1', $parameters['filter'][DatagridInterface::SORT_BY]);

        $parameters = $this->datagrid->getSortParameters($field2);

        $this->assertSame('ASC', $parameters['filter'][DatagridInterface::SORT_ORDER]);
        $this->assertSame('field2', $parameters['filter'][DatagridInterface::SORT_BY]);

        $parameters = $this->datagrid->getSortParameters($field3);

        $this->assertSame('ASC', $parameters['filter'][DatagridInterface::SORT_ORDER]);
        $this->assertSame('field3sortBy', $parameters['filter'][DatagridInterface::SORT_BY]);

        $this->datagrid = new Datagrid(
            $this->query,
            $this->columns,
            $this->pager,
            $this->formBuilder,
            [DatagridInterface::SORT_BY => $field3, DatagridInterface::SORT_ORDER => 'ASC']
        );

        $parameters = $this->datagrid->getSortParameters($field3);

        $this->assertSame('DESC', $parameters['filter'][DatagridInterface::SORT_ORDER]);
        $this->assertSame('field3sortBy', $parameters['filter'][DatagridInterface::SORT_BY]);
    }

    public function testGetPaginationParameters(): void
    {
        $field = $this->createMock(FieldDescriptionInterface::class);

        $this->datagrid = new Datagrid(
            $this->query,
            $this->columns,
            $this->pager,
            $this->formBuilder,
            [DatagridInterface::SORT_BY => $field, DatagridInterface::SORT_ORDER => 'ASC']
        );

        $field->expects($this->once())->method('getName')->willReturn($name = 'test');

        $result = $this->datagrid->getPaginationParameters($page = 5);

        $this->assertSame($page, $result['filter'][DatagridInterface::PAGE]);
        $this->assertSame($name, $result['filter'][DatagridInterface::SORT_BY]);
    }
}
