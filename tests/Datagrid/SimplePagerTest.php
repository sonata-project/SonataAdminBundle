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
class SimplePagerTest extends TestCase
{
    /**
     * @var SimplePager<ProxyQueryInterface>
     */
    private $pager;

    /**
     * @var MockObject&ProxyQueryInterface
     */
    private $proxyQuery;

    protected function setUp(): void
    {
        $this->pager = new SimplePager(10, 2);
        $this->proxyQuery = $this->createMock(ProxyQueryInterface::class);
    }

    public function testInitNumPages(): void
    {
        $this->proxyQuery->expects(self::once())
                ->method('execute')
                ->willReturn(new ArrayCollection(array_fill(0, 13, new \stdClass())));

        $this->proxyQuery->expects(self::once())
            ->method('setMaxResults')
            ->with(self::equalTo(21));

        $this->proxyQuery->expects(self::once())
            ->method('setFirstResult')
            ->with(self::equalTo(0));

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->init();

        self::assertSame(2, $this->pager->getLastPage());

        // We're not knowing exactly the result number, at least 13 (the result found)
        self::assertSame(13, $this->pager->countResults());
    }

    public function testInitOffset(): void
    {
        $this->proxyQuery->expects(self::once())
            ->method('execute')
            ->willReturn(new ArrayCollection(array_fill(0, 13, new \stdClass())));

        $this->proxyQuery->expects(self::once())
            ->method('setMaxResults')
            ->with(self::equalTo(21));

        // Asserting that the offset will be set correctly
        $this->proxyQuery->expects(self::once())
            ->method('setFirstResult')
            ->with(self::equalTo(10));

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->setPage(2);
        $this->pager->init();

        self::assertSame(3, $this->pager->getLastPage());

        // We're not knowing exactly the result number, at least 10 (first page) + 13 (the result found)
        self::assertSame(23, $this->pager->countResults());
    }

    public function testLasPage(): void
    {
        $this->proxyQuery->expects(self::once())
            ->method('execute')
            ->willReturn(new ArrayCollection(array_fill(0, 9, new \stdClass())));

        $this->proxyQuery->expects(self::once())
            ->method('setMaxResults')
            ->with(self::equalTo(21));

        // Asserting that the offset will be set correctly
        $this->proxyQuery->expects(self::once())
            ->method('setFirstResult')
            ->with(self::equalTo(10));

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->setPage(2);
        $this->pager->init();

        self::assertSame(2, $this->pager->getLastPage());

        // We're knowing exactly the result number: 10 (first page) + 9 (this page)
        self::assertSame(19, $this->pager->countResults());
    }

    public function testNoPagesPerConfig(): void
    {
        $this->proxyQuery->expects(self::once())
            ->method('setMaxResults')
            ->with(self::equalTo(0));

        $this->proxyQuery->expects(self::once())
            ->method('setFirstResult')
            ->with(self::equalTo(0));

        $this->pager->setQuery($this->proxyQuery);

        // Max per page 0 means no pagination
        $this->pager->setMaxPerPage(0);
        $this->pager->init();

        self::assertSame(0, $this->pager->getLastPage());
        self::assertSame(0, $this->pager->countResults());
    }

    public function testNoPagesForNoResults(): void
    {
        $this->proxyQuery->expects(self::once())
            ->method('execute')
            ->willReturn([]);

        $this->proxyQuery->expects(self::once())
            ->method('setMaxResults')
            ->with(self::equalTo(21));
        $this->proxyQuery->expects(self::once())
            ->method('setFirstResult')
            ->with(self::equalTo(0));

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->init();
        self::assertSame(1, $this->pager->getLastPage());
        self::assertSame(0, $this->pager->countResults());
    }

    public function testInitNoQuery(): void
    {
        $this->expectException(\LogicException::class);
        $this->pager->init();
    }

    /**
     * @param string[] $queryReturnValues
     *
     * @dataProvider getCurrentPageResultsReturnType
     */
    public function testGetCurrentPageResultsReturnTypeArrayCollection(array $queryReturnValues, int $maxPerPage): void
    {
        $this->proxyQuery->expects(self::once())
            ->method('execute')
            ->willReturn($queryReturnValues);

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->setMaxPerPage($maxPerPage);

        self::assertInstanceOf(ArrayCollection::class, $this->pager->getCurrentPageResults());
    }

    /**
     * @phpstan-return iterable<array-key, array{string[], int}>
     */
    public function getCurrentPageResultsReturnType(): iterable
    {
        return [
            [['foo', 'bar'], 2],
            [['foo', 'bar'], 1],
            [[], 1],
        ];
    }
}
