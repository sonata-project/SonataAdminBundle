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
use Sonata\AdminBundle\Search\SearchHandler;

class SearchHandlerTest extends TestCase
{
    public function testBuildPagerWithNoGlobalSearchField(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())->method('getOption')->with('global_search')->willReturn(false);
        $filter->expects($this->never())->method('setOption');

        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects($this->once())->method('getFilters')->willReturn([$filter]);

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('getDatagrid')->willReturn($datagrid);

        $handler = new SearchHandler(true);
        $this->assertFalse($handler->search($admin, 'myservice'));
    }

    /**
     * @dataProvider buildPagerWithGlobalSearchFieldProvider
     */
    public function testBuildPagerWithGlobalSearchField(bool $caseSensitive): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())->method('getOption')->with('global_search')->willReturn(true);
        $filter->expects($this->once())->method('setOption')->with('case_sensitive', $caseSensitive);

        $pager = $this->createMock(PagerInterface::class);
        $pager->expects($this->once())->method('setPage');
        $pager->expects($this->once())->method('setMaxPerPage');

        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects($this->once())->method('getFilters')->willReturn([$filter]);
        $datagrid->expects($this->once())->method('setValue');
        $datagrid->expects($this->once())->method('getPager')->willReturn($pager);

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('getDatagrid')->willReturn($datagrid);

        $handler = new SearchHandler($caseSensitive);
        $this->assertInstanceOf(PagerInterface::class, $handler->search($admin, 'myservice'));
    }

    public function buildPagerWithGlobalSearchFieldProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider provideAdminSearchConfigurations
     */
    public function testAdminSearch($expected, $filterCallsCount, ?bool $enabled, string $adminCode): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->exactly($filterCallsCount))->method('getOption')->with('global_search')->willReturn(true);
        $filter->expects($this->exactly($filterCallsCount))->method('setOption')->with('case_sensitive', true);

        $pager = $this->createMock(PagerInterface::class);
        $pager->expects($this->exactly($filterCallsCount))->method('setPage');
        $pager->expects($this->exactly($filterCallsCount))->method('setMaxPerPage');

        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects($this->exactly($filterCallsCount))->method('getFilters')->willReturn([$filter]);
        $datagrid->expects($this->exactly($filterCallsCount))->method('setValue');
        $datagrid->expects($this->exactly($filterCallsCount))->method('getPager')->willReturn($pager);

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->exactly($filterCallsCount))->method('getDatagrid')->willReturn($datagrid);
        $admin->expects($this->once())->method('getCode')->willReturn($adminCode);

        $handler = new SearchHandler(true);

        if (null !== $enabled) {
            $handler->configureAdminSearch([$adminCode => $enabled]);
        }

        if (false === $expected) {
            $this->assertFalse($handler->search($admin, 'myservice'));
        } else {
            $this->assertInstanceOf($expected, $handler->search($admin, 'myservice'));
        }
    }

    public function provideAdminSearchConfigurations(): iterable
    {
        yield 'admin_search_enabled' => [PagerInterface::class, 1, true, 'admin.foo'];
        yield 'admin_search_disabled' => [false, 0, false, 'admin.bar'];
        yield 'admin_search_omitted' => [PagerInterface::class, 1, null, 'admin.baz'];
    }

    public function testBuildPagerWithDefaultFilters(): void
    {
        $defaultFilter = $this->createMock(FilterInterface::class);
        $defaultFilter->expects($this->once())->method('getOption')->with('global_search')->willReturn(false);
        $defaultFilter->expects($this->once())->method('getFormName')->willReturn('filter1');

        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())->method('getOption')->with('global_search')->willReturn(true);
        $filter->expects($this->once())->method('getFormName')->willReturn('filter2');

        $pager = $this->createMock(PagerInterface::class);
        $pager->expects($this->once())->method('setPage');
        $pager->expects($this->once())->method('setMaxPerPage');

        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects($this->once())->method('getFilters')->willReturn([$defaultFilter, $filter]);
        $datagrid->expects($this->once())->method('setValue')->with('filter2', null, 'myservice');
        $datagrid->expects($this->once())->method('removeFilter')->with('filter1');
        $datagrid->expects($this->once())->method('getValues')->willReturn(['filter1' => ['type' => null, 'value' => null]]);
        $datagrid->expects($this->once())->method('getPager')->willReturn($pager);

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('getDatagrid')->willReturn($datagrid);

        $handler = new SearchHandler(true);
        $pager = $handler->search($admin, 'myservice');
        $this->assertInstanceOf(PagerInterface::class, $pager);
    }
}
