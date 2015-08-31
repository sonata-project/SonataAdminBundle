<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;
use Sonata\AdminBundle\Datagrid\SimplePager;

/**
 * Simple pager.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Sjoerd Peters <sjoerd.peters@gmail.com>
 */
class SimplePagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->pager = new SimplePager(10, 2);
        $this->proxyQuery = $this->getMockBuilder('Sonata\AdminBundle\Datagrid\ProxyQueryInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testInitNumPages()
    {
        $pager = new SimplePager(10, 2);
        $this->proxyQuery->expects($this->once())
                ->method('execute')
                ->with(array(), null)
                ->will($this->returnValue(new ArrayCollection(range(0, 12))));

        $this->proxyQuery->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(21));

        $this->proxyQuery->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(0));

        $pager->setQuery($this->proxyQuery);
        $pager->init();

        $this->assertSame(2, $pager->getLastPage());
    }

    public function testInitOffset()
    {
        $this->proxyQuery->expects($this->once())
            ->method('execute')
            ->with(array(), null)
            ->will($this->returnValue(new ArrayCollection(range(0, 12))));

        $this->proxyQuery->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(21));

        // Asserting that the offset will be set correctly
        $this->proxyQuery->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(10));

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->setPage(2);
        $this->pager->init();

        $this->assertSame(3, $this->pager->getLastPage());
    }

    public function testNoPagesPerConfig()
    {
        $this->proxyQuery->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(0));

        $this->proxyQuery->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(0));

        $this->pager->setQuery($this->proxyQuery);

        // Max per page 0 means no pagination
        $this->pager->setMaxPerPage(0);
        $this->pager->init();

        $this->assertSame(0, $this->pager->getLastPage());
    }

    public function testNoPagesForNoResults()
    {
        $this->proxyQuery->expects($this->once())
            ->method('execute')
            ->with(array(), null)
            ->will($this->returnValue(array()));

        $this->proxyQuery->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(21));
        $this->proxyQuery->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(0));

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->init();
        $this->AssertEquals(0, $this->pager->getLastPage());
    }

    public function testInitNoQuery()
    {
        $this->setExpectedException('RuntimeException');
        $this->pager->init();
    }
}
