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
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Form\Type\Filter\FilterDataType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
     * @var Datagrid<ProxyQueryInterface<object>&Stub>
     */
    private Datagrid $datagrid;

    /**
     * @var PagerInterface<ProxyQueryInterface<object>&Stub>&MockObject
     */
    private PagerInterface $pager;

    /**
     * @var ProxyQueryInterface<object>&Stub
     */
    private ProxyQueryInterface $query;

    /**
     * @var FieldDescriptionCollection<FieldDescriptionInterface>
     */
    private FieldDescriptionCollection $columns;

    private FormBuilderInterface $formBuilder;

    protected function setUp(): void
    {
        /** @var ProxyQueryInterface<object>&Stub $query */
        $query = $this->createStub(ProxyQueryInterface::class);
        $this->query = $query;
        $this->columns = new FieldDescriptionCollection();
        $this->formBuilder = Forms::createFormFactoryBuilder()->getFormFactory()->createBuilder();

        /** @var PagerInterface<ProxyQueryInterface<object>&Stub>&MockObject $pager */
        $pager = $this->createMock(PagerInterface::class);
        $this->pager = $pager;
        $this->datagrid = new Datagrid($this->query, $this->columns, $pager, $this->formBuilder, []);
    }

    public function testGetPager(): void
    {
        static::assertSame($this->pager, $this->datagrid->getPager());
    }

    public function testFilter(): void
    {
        static::assertFalse($this->datagrid->hasFilter('foo'));

        $filter = $this->createMock(FilterInterface::class);
        $filter->expects(static::once())
            ->method('getName')
            ->willReturn('foo');

        $this->datagrid->addFilter($filter);

        static::assertTrue($this->datagrid->hasFilter('foo'));
        static::assertFalse($this->datagrid->hasFilter('nonexistent'));
        static::assertSame($filter, $this->datagrid->getFilter('foo'));

        $this->datagrid->removeFilter('foo');

        static::assertFalse($this->datagrid->hasFilter('foo'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Filter named "foo" doesn\'t exist.');

        $this->datagrid->getFilter('foo');
    }

    public function testGetFilters(): void
    {
        static::assertSame([], $this->datagrid->getFilters());

        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects(static::once())
            ->method('getName')
            ->willReturn('foo');

        $filter2 = $this->createMock(FilterInterface::class);
        $filter2->expects(static::once())
            ->method('getName')
            ->willReturn('bar');

        $filter3 = $this->createMock(FilterInterface::class);
        $filter3->expects(static::once())
            ->method('getName')
            ->willReturn('baz');

        $this->datagrid->addFilter($filter1);
        $this->datagrid->addFilter($filter2);
        $this->datagrid->addFilter($filter3);

        static::assertSame(['foo' => $filter1, 'bar' => $filter2, 'baz' => $filter3], $this->datagrid->getFilters());

        $this->datagrid->removeFilter('bar');

        static::assertSame(['foo' => $filter1, 'baz' => $filter3], $this->datagrid->getFilters());
    }

    public function testReorderFilters(): void
    {
        static::assertSame([], $this->datagrid->getFilters());

        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects(static::once())
            ->method('getName')
            ->willReturn('foo');

        $filter2 = $this->createMock(FilterInterface::class);
        $filter2->expects(static::once())
            ->method('getName')
            ->willReturn('bar');

        $filter3 = $this->createMock(FilterInterface::class);
        $filter3->expects(static::once())
            ->method('getName')
            ->willReturn('baz');

        $this->datagrid->addFilter($filter1);
        $this->datagrid->addFilter($filter2);
        $this->datagrid->addFilter($filter3);

        static::assertSame(['foo' => $filter1, 'bar' => $filter2, 'baz' => $filter3], $this->datagrid->getFilters());
        static::assertSame(['foo', 'bar', 'baz'], array_keys($this->datagrid->getFilters()));

        $this->datagrid->reorderFilters(['bar', 'baz', 'foo']);

        static::assertSame(['bar' => $filter2, 'baz' => $filter3, 'foo' => $filter1], $this->datagrid->getFilters());
        static::assertSame(['bar', 'baz', 'foo'], array_keys($this->datagrid->getFilters()));
    }

    public function testReorderWithInvalidFilter(): void
    {
        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->method('getName')->willReturn('foo');

        $filter2 = $this->createMock(FilterInterface::class);
        $filter2->method('getName')->willReturn('bar');

        $this->datagrid->addFilter($filter1);
        $this->datagrid->addFilter($filter2);

        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Filter "baz" does not exist.');

        $this->datagrid->reorderFilters(['bar', 'baz', 'foo']);
    }

    public function testGetValues(): void
    {
        static::assertSame([], $this->datagrid->getValues());

        $this->datagrid->setValue('foo', 'bar', 'baz');

        static::assertSame(['foo' => ['type' => 'bar', 'value' => 'baz']], $this->datagrid->getValues());
    }

    public function testGetColumns(): void
    {
        static::assertSame($this->columns, $this->datagrid->getColumns());
    }

    public function testGetQuery(): void
    {
        static::assertSame($this->query, $this->datagrid->getQuery());
    }

    public function testHasActiveFilters(): void
    {
        static::assertFalse($this->datagrid->hasActiveFilters());

        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects(static::once())
            ->method('getName')
            ->willReturn('foo');
        $filter1
            ->method('isActive')
            ->willReturn(false);

        $this->datagrid->addFilter($filter1);

        static::assertFalse($this->datagrid->hasActiveFilters());

        $filter2 = $this->createMock(FilterInterface::class);
        $filter2->expects(static::once())
            ->method('getName')
            ->willReturn('bar');
        $filter2
            ->method('isActive')
            ->willReturn(true);

        $this->datagrid->addFilter($filter2);

        static::assertTrue($this->datagrid->hasActiveFilters());
    }

    public function testHasDisplayableFilters(): void
    {
        static::assertFalse($this->datagrid->hasDisplayableFilters());
    }

    public function testHasDisplayableFiltersNotActive(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects(static::once())
            ->method('getName')
            ->willReturn('foo');
        $filter
            ->method('getOption')
            ->willReturn(false);
        $filter
            ->method('isActive')
            ->willReturn(false);

        $this->datagrid->addFilter($filter);

        static::assertFalse($this->datagrid->hasDisplayableFilters());
    }

    public function testHasDisplayableFiltersActive(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects(static::once())
            ->method('getName')
            ->willReturn('bar');
        $filter
            ->method('getOption')
            ->willReturn(true);
        $filter
            ->method('isActive')
            ->willReturn(true);

        $this->datagrid->addFilter($filter);

        static::assertTrue($this->datagrid->hasDisplayableFilters());
    }

    public function testHasDisplayableFiltersAlwaysShow(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects(static::once())
            ->method('getName')
            ->willReturn('bar');
        $filter
            ->method('getOption')
            ->with(static::equalTo('show_filter'))
            ->willReturn(true);
        $filter
            ->method('isActive')
            ->willReturn(false);

        $this->datagrid->addFilter($filter);

        static::assertTrue($this->datagrid->hasDisplayableFilters());
    }

    public function testGetForm(): void
    {
        static::assertInstanceOf(Form::class, $this->datagrid->getForm());
    }

    public function testGetResults(): void
    {
        $foo = new \stdClass();
        $bar = new \stdClass();

        $this->pager->expects(static::once())
            ->method('getCurrentPageResults')
            ->willReturn([$foo, $bar]);

        static::assertSame([$foo, $bar], $this->datagrid->getResults());
    }

    public function testEmptyResults(): void
    {
        $this->pager->expects(static::once())
            ->method('getCurrentPageResults')
            ->willReturn([]);

        static::assertSame([], $this->datagrid->getResults());
        static::assertSame([], $this->datagrid->getResults());
    }

    public function testBuildPager(): void
    {
        $filter1 = $this->getMockBuilder(FilterInterface::class)
            ->addMethods(['getFormOptions', 'getLabelTranslationParameters'])
            ->getMockForAbstractClass();
        $filter1->expects(static::once())
            ->method('getName')
            ->willReturn('foo');
        $filter1
            ->method('getFormName')
            ->willReturn('fooFormName');
        $filter1
            ->method('isActive')
            ->willReturn(false);
        $filter1
            ->method('getFieldType')
            ->willReturn(TextType::class);
        $filter1
            ->method('getFormOptions')
            ->willReturn(['operator_options' => ['help' => 'baz1']]);

        $this->datagrid->addFilter($filter1);

        $filter2 = $this->getMockBuilder(FilterInterface::class)
            ->addMethods(['getFormOptions', 'getLabelTranslationParameters'])
            ->getMockForAbstractClass();
        $filter2->expects(static::once())
            ->method('getName')
            ->willReturn('bar');
        $filter2
            ->method('getFormName')
            ->willReturn('barFormName');
        $filter2
            ->method('isActive')
            ->willReturn(true);
        $filter2
            ->method('getFieldType')
            ->willReturn(TextType::class);
        $filter2
            ->method('getFormOptions')
            ->willReturn(['operator_options' => ['help' => 'baz2']]);

        $this->datagrid->addFilter($filter2);

        $this->datagrid->buildPager();

        static::assertSame(['foo' => null, 'bar' => null], $this->datagrid->getValues());
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get('fooFormName'));
        static::assertSame(['help' => 'baz1'], $this->formBuilder->get('fooFormName')->getOptions()['operator_options']);
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get('barFormName'));
        static::assertSame(['help' => 'baz2'], $this->formBuilder->get('barFormName')->getOptions()['operator_options']);
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_BY));
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_ORDER));
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PAGE));
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PER_PAGE));
    }

    /**
     * @dataProvider applyFilterDataProvider
     */
    public function testApplyFilter(?string $type, ?string $value, int $applyCallNumber): void
    {
        $this->datagrid->setValue('fooFormName', $type, $value);

        $filter = $this->getMockBuilder(FilterInterface::class)
            ->addMethods(['getFormOptions', 'getLabelTranslationParameters'])
            ->getMockForAbstractClass();
        $filter->expects(static::once())->method('getName')->willReturn('foo');
        $filter->method('getFormName')->willReturn('fooFormName');
        $filter->method('isActive')->willReturn(false);
        $filter->method('getFormOptions')->willReturn(['operator_options' => ['help' => 'baz2']]);
        $filter->method('getFieldType')->willReturn(TextType::class);
        $filter->expects(static::exactly($applyCallNumber))->method('apply');

        $this->datagrid->addFilter($filter);

        $this->datagrid->buildPager();
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     *
     * @dataProvider applyFilterDataProvider
     */
    public function testLegacyApplyFilter(?string $type, ?string $value, int $applyCallNumber): void
    {
        $this->datagrid->setValue('fooFormName', $type, $value);

        $filter = $this->createMock(FilterInterface::class);
        $filter->expects(static::once())->method('getName')->willReturn('foo');
        $filter->method('getFormName')->willReturn('fooFormName');
        $filter->method('isActive')->willReturn(false);
        $filter->method('getRenderSettings')
            ->willReturn([FilterDataType::class, ['operator_options' => ['help' => 'baz2']]]);
        $filter->expects(static::exactly($applyCallNumber))->method('apply');

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
        $filter = $this->getMockBuilder(FilterInterface::class)
            ->addMethods(['getFormOptions', 'getLabelTranslationParameters'])
            ->getMockForAbstractClass();
        $filter->expects(static::once())
            ->method('getName')
            ->willReturn('foo');

        $filter
            ->method('getFormName')
            ->willReturn('fooFormName');

        $filter
            ->method('isActive')
            ->willReturn(false);
        $filter
            ->method('getFieldType')
            ->willReturn(TextType::class);
        $filter
            ->method('getFormOptions')
            ->willReturn(['operator_options' => ['help' => 'baz']]);

        $this->datagrid->addFilter($filter);

        $this->datagrid->setValue(DatagridInterface::SORT_BY, 'foo', 'baz');

        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Sonata\\AdminBundle\\FieldDescription\\FieldDescriptionInterface", "array" given');

        $this->datagrid->buildPager();
    }

    public function testBuildPagerWithSortBy(): void
    {
        $sortBy = $this->createMock(FieldDescriptionInterface::class);
        $sortBy->expects(static::once())
            ->method('isSortable')
            ->willReturn(true);

        $this->pager->expects(static::once())
            ->method('setMaxPerPage')
            ->with(static::equalTo('25'));

        $this->pager->expects(static::once())
            ->method('setPage')
            ->with(static::equalTo('1'));

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, [DatagridInterface::SORT_BY => $sortBy]);

        $filter = $this->getMockBuilder(FilterInterface::class)
            ->addMethods(['getFormOptions', 'getLabelTranslationParameters'])
            ->getMockForAbstractClass();
        $filter->expects(static::once())
            ->method('getName')
            ->willReturn('foo');
        $filter
            ->method('getFormName')
            ->willReturn('fooFormName');
        $filter
            ->method('isActive')
            ->willReturn(false);
        $filter
            ->method('getFieldType')
            ->willReturn(TextType::class);
        $filter
            ->method('getFormOptions')
            ->willReturn(['operator_options' => ['help' => 'baz']]);

        $this->datagrid->addFilter($filter);

        $this->datagrid->buildPager();

        static::assertSame([DatagridInterface::SORT_BY => $sortBy, 'foo' => null, DatagridInterface::SORT_ORDER => 'ASC'], $this->datagrid->getValues());
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get('fooFormName'));
        static::assertSame(['help' => 'baz'], $this->formBuilder->get('fooFormName')->getOptions()['operator_options']);
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_BY));
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_ORDER));
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PAGE));
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PER_PAGE));
    }

    /**
     * @phpstan-param int|array{value: int} $perPage
     *
     * @dataProvider provideBuildPagerWithPageCases
     */
    public function testBuildPagerWithPage(int $page, int|array $perPage): void
    {
        $sortBy = $this->createMock(FieldDescriptionInterface::class);
        $sortBy->expects(static::once())
            ->method('isSortable')
            ->willReturn(true);

        $this->pager->expects(static::once())
            ->method('setMaxPerPage')
            ->with(static::equalTo(50));

        $this->pager->expects(static::once())
            ->method('setPage')
            ->with(static::equalTo(3));

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, [DatagridInterface::SORT_BY => $sortBy, DatagridInterface::PAGE => $page, DatagridInterface::PER_PAGE => $perPage]);

        $filter = $this->getMockBuilder(FilterInterface::class)
            ->addMethods(['getFormOptions', 'getLabelTranslationParameters'])
            ->getMockForAbstractClass();
        $filter->expects(static::once())
            ->method('getName')
            ->willReturn('foo');
        $filter
            ->method('getFormName')
            ->willReturn('fooFormName');
        $filter
            ->method('isActive')
            ->willReturn(false);
        $filter
            ->method('getFieldType')
            ->willReturn(TextType::class);
        $filter
            ->method('getFormOptions')
            ->willReturn(['operator_options' => ['help' => 'baz']]);

        $this->datagrid->addFilter($filter);

        $this->datagrid->buildPager();

        static::assertSame([
            DatagridInterface::SORT_BY => $sortBy,
            DatagridInterface::PAGE => $page,
            DatagridInterface::PER_PAGE => $perPage,
            'foo' => null,
            DatagridInterface::SORT_ORDER => 'ASC',
        ], $this->datagrid->getValues());
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get('fooFormName'));
        static::assertSame(['help' => 'baz'], $this->formBuilder->get('fooFormName')->getOptions()['operator_options']);
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_BY));
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_ORDER));
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PAGE));
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PER_PAGE));
    }

    /**
     * @phpstan-return iterable<array-key, array{int, int|array{value: int}}>
     */
    public function provideBuildPagerWithPageCases(): iterable
    {
        yield [3, 50];
        yield [3, ['value' => 50]];
    }

    /**
     * @dataProvider provideBuildPagerWithPage2Cases
     */
    public function testBuildPagerWithPage2(int $page, int $perPage): void
    {
        $this->pager->expects(static::once())
            ->method('setMaxPerPage')
            ->with(static::equalTo(50));

        $this->pager->expects(static::once())
            ->method('setPage')
            ->with(static::equalTo(3));

        $this->datagrid = new Datagrid($this->query, $this->columns, $this->pager, $this->formBuilder, []);
        $this->datagrid->setValue(DatagridInterface::PER_PAGE, null, $perPage);
        $this->datagrid->setValue(DatagridInterface::PAGE, null, $page);

        $this->datagrid->buildPager();

        static::assertSame([
            DatagridInterface::PER_PAGE => ['type' => null, 'value' => $perPage],
            DatagridInterface::PAGE => ['type' => null, 'value' => $page],
        ], $this->datagrid->getValues());
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_BY));
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::SORT_ORDER));
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PAGE));
        static::assertInstanceOf(FormBuilder::class, $this->formBuilder->get(DatagridInterface::PER_PAGE));
    }

    /**
     * @phpstan-return iterable<array-key, array{int, int}>
     */
    public function provideBuildPagerWithPage2Cases(): iterable
    {
        yield [3, 50];
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

        static::assertSame('DESC', $parameters['filter'][DatagridInterface::SORT_ORDER]);
        static::assertSame('field1', $parameters['filter'][DatagridInterface::SORT_BY]);

        $parameters = $this->datagrid->getSortParameters($field2);

        static::assertSame('ASC', $parameters['filter'][DatagridInterface::SORT_ORDER]);
        static::assertSame('field2', $parameters['filter'][DatagridInterface::SORT_BY]);

        $parameters = $this->datagrid->getSortParameters($field3);

        static::assertSame('ASC', $parameters['filter'][DatagridInterface::SORT_ORDER]);
        static::assertSame('field3sortBy', $parameters['filter'][DatagridInterface::SORT_BY]);

        $this->datagrid = new Datagrid(
            $this->query,
            $this->columns,
            $this->pager,
            $this->formBuilder,
            [DatagridInterface::SORT_BY => $field3, DatagridInterface::SORT_ORDER => 'ASC']
        );

        $parameters = $this->datagrid->getSortParameters($field3);

        static::assertSame('DESC', $parameters['filter'][DatagridInterface::SORT_ORDER]);
        static::assertSame('field3sortBy', $parameters['filter'][DatagridInterface::SORT_BY]);
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

        $field->expects(static::once())->method('getName')->willReturn($name = 'test');

        $result = $this->datagrid->getPaginationParameters($page = 5);

        static::assertSame($page, $result['filter'][DatagridInterface::PAGE]);
        static::assertSame($name, $result['filter'][DatagridInterface::SORT_BY]);
    }
}
