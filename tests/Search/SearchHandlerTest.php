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
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Search\SearchHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class SearchHandlerTest extends TestCase
{
    /**
     * @param AdminInterface $admin
     *
     * @return Pool
     */
    public function getPool(AdminInterface $admin = null)
    {
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->any())->method('get')->will($this->returnCallback(function ($id) use ($admin) {
            if ('fake' == $id) {
                throw new ServiceNotFoundException('Fake service does not exist');
            }

            return $admin;
        }));

        return new Pool($container, 'title', 'logo', ['asd']);
    }

    public function testBuildPagerWithNoGlobalSearchField()
    {
        $filter = $this->getMockForAbstractClass(FilterInterface::class);
        $filter->expects($this->once())->method('getOption')->will($this->returnValue(false));

        $datagrid = $this->getMockForAbstractClass(DatagridInterface::class);
        $datagrid->expects($this->once())->method('getFilters')->will($this->returnValue([$filter]));

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->expects($this->once())->method('getDatagrid')->will($this->returnValue($datagrid));

        $handler = new SearchHandler($this->getPool($admin));
        $this->assertFalse($handler->search($admin, 'myservice'));
    }

    public function testBuildPagerWithGlobalSearchField()
    {
        $filter = $this->getMockForAbstractClass(FilterInterface::class);
        $filter->expects($this->once())->method('getOption')->will($this->returnValue(true));

        $pager = $this->getMockForAbstractClass(PagerInterface::class);
        $pager->expects($this->once())->method('setPage');
        $pager->expects($this->once())->method('setMaxPerPage');

        $datagrid = $this->getMockForAbstractClass(DatagridInterface::class);
        $datagrid->expects($this->once())->method('getFilters')->will($this->returnValue([$filter]));
        $datagrid->expects($this->once())->method('setValue');
        $datagrid->expects($this->once())->method('getPager')->will($this->returnValue($pager));

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->expects($this->once())->method('getDatagrid')->will($this->returnValue($datagrid));

        $handler = new SearchHandler($this->getPool($admin));
        $this->assertInstanceOf(PagerInterface::class, $handler->search($admin, 'myservice'));
    }
}
