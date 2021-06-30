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
     * @var Datagrid<ProxyQueryInterface>
     */
    private $datagrid;

    /**
     * @var PagerInterface<ProxyQueryInterface>&MockObject
     */
    private $pager;

    /**
     * @var ProxyQueryInterface
     */
    private $query;

    /**
     * @var FieldDescriptionCollection<FieldDescriptionInterface>
     */
    private $columns;

    /**
     * @var FormBuilderInterface
     */
    private $formBuilder;

    protected function setUp(): void
    {
        $this->query = $this->createStub(ProxyQueryInterface::class);
        \assert($this->query instanceof ProxyQueryInterface); // https://github.com/vimeo/psalm/issues/5818

        $this->columns = new FieldDescriptionCollection();
        $this->pager = $this->createMock(PagerInterface::class);
        $this->formBuilder = Forms::createFormFactoryBuilder()->getFormFactory()->createBuilder();

        $values = [];

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, $values);
    }

    public function testGetPager(): void
    {
        self::assertSame($this->pager, $this->datagrid->getPager());
    }

    public function testFilter(): void
    {
        self::assertFalse($this->datagrid->hasFilter('foo'));

        $filter = $this->createMock(FilterInterface::class);
        $filter->expects(self::once())
            ->method('getName')
            ->willReturn('foo');

        $this->datagrid->addFilter($filter);

        self::assertTrue($this->datagrid->hasFilter('foo'));
        self::assertFalse($this->datagrid->hasFilter('nonexistent'));
        self::assertSame($filter, $this->datagrid->getFilter('foo'));

        $this->datagrid->removeFilter('foo');

        self::assertFalse($this->datagrid->hasFilter('foo'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Filter named "foo" doesn\'t exist.');

        $this->datagrid->getFilter('foo');
    }

    public function testGetFilters(): void
    {
        self::assertSame([], $this->datagrid->getFilters());

        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects(self::once())
            ->method('getName')
            ->willReturn('foo');

        $filter2 = $this->createMock(FilterInterface::class);
        $filter2->expects(self::once())
            ->method('getName')
            ->willReturn('bar');

        $filter3 = $this->createMock(FilterInterface::class);
        $filter3->expects(self::once())
            ->method('getName')
            ->willReturn('baz');

        $this->datagrid->addFilter($filter1);
        $this->datagrid->addFilter($filter2);
        $this->datagrid->addFilter($filter3);

        self::assertSame(['foo' => $filter1, 'bar' => $filter2, 'baz' => $filter3], $this->datagrid->getFilters());

        $this->datagrid->removeFilter('bar');

        self::assertSame(['foo' => $filter1, 'baz' => $filter3], $this->datagrid->getFilters());
    }

    public function testReorderFilters(): void
    {
        self::assertSame([], $this->datagrid->getFilters());

        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects(self::once())
            ->method('getName')
            ->willReturn('foo');

        $filter2 = $this->createMock(FilterInterface::class);
        $filter2->expects(self::once())
            ->method('getName')
            ->willReturn('bar');

        $filter3 = $this->createMock(FilterInterface::class);
        $filter3->expects(self::once())
            ->method('getName')
            ->willReturn('baz');

        $this->datagrid->addFilter($filter1);
        $this->datagrid->addFilter($filter2);
        $this->datagrid->addFilter($filter3);

        self::assertSame(['foo' => $filter1, 'bar' => $filter2, 'baz' => $filter3], $this->datagrid->getFilters());
        self::assertSame(['foo', 'bar', 'baz'], array_keys($this->datagrid->getFilters()));

        $this->datagrid->reorderFilters(['bar', 'baz', 'foo']);

        self::assertSame(['bar' => $filter2, 'baz' => $filter3, 'foo' => $filter1], $this->datagrid->getFilters());
        self::assertSame(['bar', 'baz', 'foo'], array_keys($this->datagrid->getFilters()));
    }

    public function testGetValues(): void
    {
        self::assertSame([], $this->datagrid->getValues());

        $this->datagrid->setValue('foo', 'bar', 'baz');

        self::assertSame(['foo' => ['type' => 'bar', 'value' => 'baz']], $this->datagrid->getValues());
    }

    public function testGetColumns(): void
    {
        self::assertSame($this->columns, $this->datagrid->getColumns());
    }

    public function testGetQuery(): void
    {
        self::assertSame($this->query, $this->datagrid->getQuery());
    }

    public function testHasActiveFilters(): void
    {
        self::assertFalse($this->datagrid->hasActiveFilters());

        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects(self::once())
            ->method('getName')
            ->willReturn('foo');
        $filter1
            ->method('isActive')
            ->willReturn(false);

        $this->datagrid->addFilter($filter1);

        self::assertFalse($this->datagrid->hasActiveFilters());

        $filter2 = $this->createMock(FilterInterface::class);
        $filter2->expects(self::once())
            ->method('getName')
            ->willReturn('bar');
        $filter2
            ->method('isActive')
            ->willReturn(true);

        $this->datagrid->addFilter($filter2);

        self::assertTrue($this->datagrid->hasActiveFilters());
    }

    public function testHasDisplayableFilters(): void
    {
        self::assertFalse($this->datagrid->hasDisplayableFilters());
    }

    public function testHasDisplayableFiltersNotActive(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects(self::once())
            ->method('getName')
            ->willReturn('foo');
        $filter
            ->method('getOption')
            ->willReturn(false);
        $filter
            ->method('isActive')
            ->willReturn(false);

        $this->datagrid->addFilter($filter);

        self::assertFalse($this->datagrid->hasDisplayableFilters());
    }

    public function testHasDisplayableFiltersActive(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects(self::once())
            ->method('getName')
            ->willReturn('bar');
        $filter
            ->method('getOption')
            ->willReturn(true);
        $filter
            ->method('isActive')
            ->willReturn(true);

        $this->datagrid->addFilter($filter);

        self::assertTrue($this->datagrid->hasDisplayableFilters());
    }

    public function testHasDisplayableFiltersAlwaysShow(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects(self::once())
            ->method('getName')
            ->willReturn('bar');
        $filter
            ->method('getOption')
            ->with(self::equalTo('show_filter'))
            ->willReturn(true);
        $filter
            ->method('isActive')
            ->willReturn(false);

        $this->datagrid->addFilter($filter);

        self::assertTrue($this->datagrid->hasDisplayableFilters());
    }

    public function testGetForm(): void
    {
        self::assertInstanceOf(Form::class, $this->datagrid->getForm());
    }

    public function testGetResults(): void
    {
        $foo = new \stdClass();
        $bar = new \stdClass();

        $this->pager->expects(self::once())
            ->method('getCurrentPageResults')
            ->willReturn([$foo, $bar]);

        self::assertSame([$foo, $bar], $this->datagrid->getResults());
    }

    public function testEmptyResults(): void
    {
        $this->pager->expects(self::once())
            ->method('getCurrentPageResults')
            ->willReturn([]);

        self::assertSame([], $this->datagrid->getResults());
        self::assertSame([], $this->datagrid->getResults());
    }

    public function testBuildPager(): void
    {
        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects(self::once())
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
        $filter2->expects(self::once())
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

        self::assertSame(['foo' => null, 'bar' => null], $this->datagrid->getValues());
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get('fooFormName'));
        self::assertSame(['help' => 'baz1'], $this->formBuilder->get('fooFormName')->getOptions()['operator_options']);
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get('barFormName'));
        self::assertSame(['help' => 'baz2'], $this->formBuilder->get('barFormName')->getOptions()['operator_options']);
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_BY));
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_ORDER));
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PAGE));
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PER_PAGE));
    }

    /**
     * @dataProvider applyFilterDataProvider
     */
    public function testApplyFilter(?string $type, ?string $value, int $applyCallNumber): void
    {
        $this->datagrid->setValue('fooFormName', $type, $value);

        $filter = $this->createMock(FilterInterface::class);
        $filter->expects(self::once())->method('getName')->willReturn('foo');
        $filter->method('getFormName')->willReturn('fooFormName');
        $filter->method('isActive')->willReturn(false);
        $filter->method('getRenderSettings')
            ->willReturn([DefaultType::class, ['operator_options' => ['help' => 'baz2']]]);
        $filter->expects(self::exactly($applyCallNumber))->method('apply');

        $this->datagrid->addFilter($filter);

        $this->datagrid->buildPager();
    }

    /**
     * @phpstan-return iterable<array-key, array{string|null, string|null, int}>
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
        $filter->expects(self::once())
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
        $sortBy->expects(self::once())
            ->method('isSortable')
            ->willReturn(true);

        $this->pager->expects(self::once())
            ->method('setMaxPerPage')
            ->with(self::equalTo('25'));

        $this->pager->expects(self::once())
            ->method('setPage')
            ->with(self::equalTo('1'));

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, [DatagridInterface::SORT_BY => $sortBy]);

        $filter = $this->createMock(FilterInterface::class);
        $filter->expects(self::once())
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

        self::assertSame([DatagridInterface::SORT_BY => $sortBy, 'foo' => null, DatagridInterface::SORT_ORDER => 'ASC'], $this->datagrid->getValues());
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get('fooFormName'));
        self::assertSame(['help' => 'baz'], $this->formBuilder->get('fooFormName')->getOptions()['operator_options']);
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_BY));
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_ORDER));
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PAGE));
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PER_PAGE));
    }

    /**
     * @param int|array $perPage
     * @phpstan-param int|array{value: int} $perPage
     *
     * @dataProvider getBuildPagerWithPageTests
     */
    public function testBuildPagerWithPage(int $page, $perPage): void
    {
        $sortBy = $this->createMock(FieldDescriptionInterface::class);
        $sortBy->expects(self::once())
            ->method('isSortable')
            ->willReturn(true);

        $this->pager->expects(self::once())
            ->method('setMaxPerPage')
            ->with(self::equalTo(50));

        $this->pager->expects(self::once())
            ->method('setPage')
            ->with(self::equalTo(3));

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, [DatagridInterface::SORT_BY => $sortBy, DatagridInterface::PAGE => $page, DatagridInterface::PER_PAGE => $perPage]);

        $filter = $this->createMock(FilterInterface::class);
        $filter->expects(self::once())
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

        self::assertSame([
            DatagridInterface::SORT_BY => $sortBy,
            DatagridInterface::PAGE => $page,
            DatagridInterface::PER_PAGE => $perPage,
            'foo' => null,
            DatagridInterface::SORT_ORDER => 'ASC',
        ], $this->datagrid->getValues());
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get('fooFormName'));
        self::assertSame(['help' => 'baz'], $this->formBuilder->get('fooFormName')->getOptions()['operator_options']);
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_BY));
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_ORDER));
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PAGE));
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PER_PAGE));
    }

    /**
     * @phpstan-return iterable<array-key, array{int, int|array{value: int}}>
     */
    public function getBuildPagerWithPageTests(): iterable
    {
        return [
            [3, 50],
            [3, ['type' => null, 'value' => 50]],
        ];
    }

    /**
     * @dataProvider getBuildPagerWithPage2Tests
     */
    public function testBuildPagerWithPage2(int $page, int $perPage): void
    {
        $this->pager->expects(self::once())
            ->method('setMaxPerPage')
            ->with(self::equalTo(50));

        $this->pager->expects(self::once())
            ->method('setPage')
            ->with(self::equalTo(3));

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, []);
        $this->datagrid->setValue(DatagridInterface::PER_PAGE, null, $perPage);
        $this->datagrid->setValue(DatagridInterface::PAGE, null, $page);

        $this->datagrid->buildPager();

        self::assertSame([
            DatagridInterface::PER_PAGE => ['type' => null, 'value' => $perPage],
            DatagridInterface::PAGE => ['type' => null, 'value' => $page],
        ], $this->datagrid->getValues());
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_BY));
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_ORDER));
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PAGE));
        self::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PER_PAGE));
    }

    /**
     * @phpstan-return iterable<array-key, array{int, int}>
     */
    public function getBuildPagerWithPage2Tests(): iterable
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

        self::assertSame('DESC', $parameters['filter'][DatagridInterface::SORT_ORDER]);
        self::assertSame('field1', $parameters['filter'][DatagridInterface::SORT_BY]);

        $parameters = $this->datagrid->getSortParameters($field2);

        self::assertSame('ASC', $parameters['filter'][DatagridInterface::SORT_ORDER]);
        self::assertSame('field2', $parameters['filter'][DatagridInterface::SORT_BY]);

        $parameters = $this->datagrid->getSortParameters($field3);

        self::assertSame('ASC', $parameters['filter'][DatagridInterface::SORT_ORDER]);
        self::assertSame('field3sortBy', $parameters['filter'][DatagridInterface::SORT_BY]);

        $this->datagrid = new Datagrid(
            $this->query,
            $this->columns,
            $this->pager,
            $this->formBuilder,
            [DatagridInterface::SORT_BY => $field3, DatagridInterface::SORT_ORDER => 'ASC']
        );

        $parameters = $this->datagrid->getSortParameters($field3);

        self::assertSame('DESC', $parameters['filter'][DatagridInterface::SORT_ORDER]);
        self::assertSame('field3sortBy', $parameters['filter'][DatagridInterface::SORT_BY]);
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

        $field->expects(self::once())->method('getName')->willReturn($name = 'test');

        $result = $this->datagrid->getPaginationParameters($page = 5);

        self::assertSame($page, $result['filter'][DatagridInterface::PAGE]);
        self::assertSame($name, $result['filter'][DatagridInterface::SORT_BY]);
    }
}
