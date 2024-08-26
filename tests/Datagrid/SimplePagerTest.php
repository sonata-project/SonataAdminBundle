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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Datagrid\SimplePager;

/**
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Sjoerd Peters <sjoerd.peters@gmail.com>
 */
final class SimplePagerTest extends TestCase
{
    /**
     * @var SimplePager<ProxyQueryInterface<object>>
     */
    private SimplePager $pager;

    /**
     * @var MockObject&ProxyQueryInterface<object>
     */
    private ProxyQueryInterface $proxyQuery;

    protected function setUp(): void
    {
        $this->pager = new SimplePager(10, 2);
        $this->proxyQuery = $this->createMock(ProxyQueryInterface::class);
    }

    public function testInitNumPages(): void
    {
        $this->proxyQuery->expects(static::once())
                ->method('execute')
                ->willReturn(new ArrayCollection(array_fill(0, 13, new \stdClass())));

        $this->proxyQuery->expects(static::once())
            ->method('setMaxResults')
            ->with(static::equalTo(21));

        $this->proxyQuery->expects(static::once())
            ->method('setFirstResult')
            ->with(static::equalTo(0));

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->init();

        static::assertSame(2, $this->pager->getLastPage());

        // We're not knowing exactly the result number, at least 13 (the result found)
        static::assertSame(13, $this->pager->countResults());
    }

    public function testInitOffset(): void
    {
        $this->proxyQuery->expects(static::once())
            ->method('execute')
            ->willReturn(new ArrayCollection(array_fill(0, 13, new \stdClass())));

        $this->proxyQuery->expects(static::once())
            ->method('setMaxResults')
            ->with(static::equalTo(21));

        // Asserting that the offset will be set correctly
        $this->proxyQuery->expects(static::once())
            ->method('setFirstResult')
            ->with(static::equalTo(10));

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->setPage(2);
        $this->pager->init();

        static::assertSame(3, $this->pager->getLastPage());

        // We're not knowing exactly the result number, at least 10 (first page) + 13 (the result found)
        static::assertSame(23, $this->pager->countResults());
    }

    public function testLastPage(): void
    {
        $this->proxyQuery->expects(static::once())
            ->method('execute')
            ->willReturn(new ArrayCollection(array_fill(0, 9, new \stdClass())));

        $this->proxyQuery->expects(static::once())
            ->method('setMaxResults')
            ->with(static::equalTo(21));

        // Asserting that the offset will be set correctly
        $this->proxyQuery->expects(static::once())
            ->method('setFirstResult')
            ->with(static::equalTo(10));

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->setPage(2);
        $this->pager->init();

        static::assertSame(2, $this->pager->getLastPage());

        // We're knowing exactly the result number: 10 (first page) + 9 (this page)
        static::assertSame(19, $this->pager->countResults());
    }

    public function testNoPagesPerConfig(): void
    {
        $this->proxyQuery->expects(static::once())
            ->method('setMaxResults')
            ->with(static::equalTo(0));

        $this->proxyQuery->expects(static::once())
            ->method('setFirstResult')
            ->with(static::equalTo(0));

        $this->pager->setQuery($this->proxyQuery);

        // Max per page 0 means no pagination
        $this->pager->setMaxPerPage(0);
        $this->pager->init();

        static::assertSame(0, $this->pager->getLastPage());
        static::assertSame(0, $this->pager->countResults());
    }

    public function testNoPagesForNoResults(): void
    {
        $this->proxyQuery->expects(static::once())
            ->method('execute')
            ->willReturn([]);

        $this->proxyQuery->expects(static::once())
            ->method('setMaxResults')
            ->with(static::equalTo(21));
        $this->proxyQuery->expects(static::once())
            ->method('setFirstResult')
            ->with(static::equalTo(0));

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->init();
        static::assertSame(1, $this->pager->getLastPage());
        static::assertSame(0, $this->pager->countResults());
    }

    public function testInitNoQuery(): void
    {
        $this->expectException(\LogicException::class);
        $this->pager->init();
    }

    /**
     * @param string[] $queryReturnValues
     *
     * @dataProvider provideGetCurrentPageResultsReturnTypeArrayCollectionCases
     */
    public function testGetCurrentPageResultsReturnTypeArrayCollection(array $queryReturnValues, int $maxPerPage): void
    {
        $this->proxyQuery->expects(static::once())
            ->method('execute')
            ->willReturn($queryReturnValues);

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->setMaxPerPage($maxPerPage);

        static::assertInstanceOf(ArrayCollection::class, $this->pager->getCurrentPageResults());
    }

    /**
     * @phpstan-return iterable<array-key, array{string[], int}>
     */
    public function provideGetCurrentPageResultsReturnTypeArrayCollectionCases(): iterable
    {
        yield [['foo', 'bar'], 2];
        yield [['foo', 'bar'], 1];
        yield [[], 1];
    }
}
