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

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Datagrid\SimplePager;

/**
 * Simple pager.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Sjoerd Peters <sjoerd.peters@gmail.com>
 */
class SimplePagerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->pager = new SimplePager(10, 2);
        $this->proxyQuery = $this->getMockBuilder(ProxyQueryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testInitNumPages(): void
    {
        $pager = new SimplePager(10, 2);
        $this->proxyQuery->expects($this->once())
                ->method('execute')
                ->willReturn(new ArrayCollection(range(0, 12)));

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

    public function testInitOffset(): void
    {
        $this->proxyQuery->expects($this->once())
            ->method('execute')
            ->willReturn(new ArrayCollection(range(0, 12)));

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

    public function testNoPagesPerConfig(): void
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

    public function testNoPagesForNoResults(): void
    {
        $this->proxyQuery->expects($this->once())
            ->method('execute')
            ->willReturn([]);

        $this->proxyQuery->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(21));
        $this->proxyQuery->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(0));

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->init();
        $this->assertSame(1, $this->pager->getLastPage());
        $this->assertSame(0, $this->pager->countResults());
    }

    public function testInitNoQuery(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->pager->init();
    }

    /**
     * @dataProvider getCurrentPageResultsReturnType
     */
    public function testGetCurrentPageResultsReturnTypeArrayCollection(array $queryReturnValues, ?int $maxPerPage): void
    {
        $this->proxyQuery->expects($this->once())
            ->method('execute')
            ->willReturn($queryReturnValues);

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->setMaxPerPage($maxPerPage);

        $this->assertInstanceOf(ArrayCollection::class, $this->pager->getCurrentPageResults());
    }

    public function getCurrentPageResultsReturnType(): array
    {
        return [
            [['foo', 'bar'], 2],
            [['foo', 'bar'], 1],
            [[], null],
        ];
    }
}
