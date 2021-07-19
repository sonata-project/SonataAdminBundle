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

namespace Sonata\AdminBundle\Tests\Search;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Search\SearchableFilterInterface;
use Sonata\AdminBundle\Search\SearchHandler;

final class SearchHandlerTest extends TestCase
{
    public function testBuildPagerWithNonSearchableFilter(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects(self::never())->method('setOption');

        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects(self::once())->method('getFilters')->willReturn([$filter]);

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects(self::once())->method('getDatagrid')->willReturn($datagrid);

        $handler = new SearchHandler();
        self::assertNull($handler->search($admin, 'myservice'));
    }

    public function testBuildPagerWithSearchableFilter(): void
    {
        $filter = $this->createMock(SearchableFilterInterface::class);
        $filter->expects(self::once())->method('isSearchEnabled')->willReturn(true);

        $pager = $this->createMock(PagerInterface::class);
        $pager->expects(self::once())->method('setPage');
        $pager->expects(self::once())->method('setMaxPerPage');

        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects(self::once())->method('getFilters')->willReturn([$filter]);
        $datagrid->expects(self::once())->method('setValue');
        $datagrid->expects(self::once())->method('getPager')->willReturn($pager);

        $adminCode = 'my.admin';

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects(self::once())->method('getDatagrid')->willReturn($datagrid);
        $admin->expects(self::once())->method('getCode')->willReturn($adminCode);

        $handler = new SearchHandler();
        self::assertInstanceOf(PagerInterface::class, $handler->search($admin, 'myservice'));
    }

    public function testBuildPagerWithMultipleSearchableFilter(): void
    {
        $filter1 = $this->createMock(SearchableFilterInterface::class);
        $filter1->expects(self::once())->method('isSearchEnabled')->willReturn(true);

        $filter2 = $this->createMock(SearchableFilterInterface::class);
        $filter2->expects(self::once())->method('isSearchEnabled')->willReturn(false);

        $filter3 = $this->createMock(SearchableFilterInterface::class);
        $filter3->expects(self::once())->method('isSearchEnabled')->willReturn(true);
        $filter3->expects(self::once())->method('setPreviousFilter')->with($filter1);

        $pager = $this->createMock(PagerInterface::class);
        $pager->expects(self::once())->method('setPage');
        $pager->expects(self::once())->method('setMaxPerPage');

        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects(self::once())->method('getFilters')->willReturn([$filter1, $filter2, $filter3]);
        $datagrid->expects(self::exactly(2))->method('setValue');
        $datagrid->expects(self::once())->method('getPager')->willReturn($pager);

        $adminCode = 'my.admin';

        $admin = $this->createStub(AdminInterface::class);
        $admin->method('getDatagrid')->willReturn($datagrid);
        $admin->method('getCode')->willReturn($adminCode);

        $handler = new SearchHandler();
        self::assertInstanceOf(PagerInterface::class, $handler->search($admin, 'myservice'));
    }

    /**
     * @phpstan-param class-string|null $expected
     *
     * @dataProvider provideAdminSearchConfigurations
     */
    public function testAdminSearch(?string $expected, int $filterCallsCount, ?bool $enabled, string $adminCode): void
    {
        $filter = $this->createMock(SearchableFilterInterface::class);
        $filter->method('isSearchEnabled')->willReturn(true);

        $pager = $this->createMock(PagerInterface::class);
        $pager->expects(self::exactly($filterCallsCount))->method('setPage');
        $pager->expects(self::exactly($filterCallsCount))->method('setMaxPerPage');

        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects(self::exactly($filterCallsCount))->method('getFilters')->willReturn([$filter]);
        $datagrid->expects(self::exactly($filterCallsCount))->method('setValue');
        $datagrid->expects(self::exactly($filterCallsCount))->method('getPager')->willReturn($pager);

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects(self::exactly($filterCallsCount))->method('getDatagrid')->willReturn($datagrid);

        $admin->expects(self::once())->method('getCode')->willReturn($adminCode);

        $handler = new SearchHandler();

        if (null !== $enabled) {
            $handler->configureAdminSearch([$adminCode => $enabled]);
        }

        if (null === $expected) {
            self::assertNull($handler->search($admin, 'myservice'));
        } else {
            self::assertInstanceOf($expected, $handler->search($admin, 'myservice'));
        }
    }

    /**
     * @phpstan-return iterable<array-key, array{class-string|null, int, bool|null, string}>
     */
    public function provideAdminSearchConfigurations(): iterable
    {
        yield 'admin_search_enabled' => [PagerInterface::class, 1, true, 'admin.foo'];
        yield 'admin_search_disabled' => [null, 0, false, 'admin.bar'];
        yield 'admin_search_omitted' => [PagerInterface::class, 1, null, 'admin.baz'];
    }

    public function testBuildPagerWithDefaultFilters(): void
    {
        $defaultFilter = $this->createMock(SearchableFilterInterface::class);
        $defaultFilter->expects(self::once())->method('isSearchEnabled')->willReturn(false);
        $defaultFilter->expects(self::once())->method('getFormName')->willReturn('filter1');

        $filter = $this->createMock(SearchableFilterInterface::class);
        $filter->expects(self::once())->method('isSearchEnabled')->willReturn(true);
        $filter->expects(self::once())->method('getFormName')->willReturn('filter2');

        $pager = $this->createMock(PagerInterface::class);
        $pager->expects(self::once())->method('setPage');
        $pager->expects(self::once())->method('setMaxPerPage');

        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects(self::once())->method('getFilters')->willReturn([$defaultFilter, $filter]);
        $datagrid->expects(self::once())->method('setValue')->with('filter2', null, 'myservice');
        $datagrid->expects(self::once())->method('removeFilter')->with('filter1');
        $datagrid->expects(self::once())->method('getValues')->willReturn(['filter1' => ['type' => null, 'value' => null]]);
        $datagrid->expects(self::once())->method('getPager')->willReturn($pager);

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects(self::once())->method('getDatagrid')->willReturn($datagrid);

        $handler = new SearchHandler();
        $pager = $handler->search($admin, 'myservice');
        self::assertInstanceOf(PagerInterface::class, $pager);
    }
}
