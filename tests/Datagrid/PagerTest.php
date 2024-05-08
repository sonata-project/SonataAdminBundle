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
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class PagerTest extends TestCase
{
    /**
     * @var Pager<ProxyQueryInterface<object>>&MockObject
     */
    private Pager $pager;

    protected function setUp(): void
    {
        $this->pager = $this->getMockForAbstractClass(
            Pager::class,
            [],
            '',
            true,
            true,
            true,
            ['countResults']
        );
    }

    /**
     * @dataProvider provideGetMaxPerPage1Cases
     */
    public function testGetMaxPerPage1(int $expectedMaxPerPage, int $expectedPage, int $maxPerPage, ?int $page): void
    {
        static::assertSame(10, $this->pager->getMaxPerPage());
        static::assertSame(1, $this->pager->getPage());

        if (null !== $page) {
            $this->pager->setPage($page);
        }

        $this->pager->setMaxPerPage($maxPerPage);

        static::assertSame($expectedPage, $this->pager->getPage());
        static::assertSame($expectedMaxPerPage, $this->pager->getMaxPerPage());
    }

    /**
     * @phpstan-return iterable<array-key, array{int, int, int, int|null}>
     */
    public function provideGetMaxPerPage1Cases(): iterable
    {
        yield [123, 1, 123, 1];
        yield [123, 321, 123, 321];
        yield [1, 1, 1, 0];
        yield [0, 0, 0, 0];
        yield [1, 1, -1, 1];
        yield [1, 1, -1, 0];
        yield [1, 1, -1, -1];
        yield [0, 0, 0, null];
    }

    public function testGetMaxPerPage2(): void
    {
        static::assertSame(10, $this->pager->getMaxPerPage());
        static::assertSame(1, $this->pager->getPage());

        $this->pager->setMaxPerPage(0);
        $this->pager->setPage(0);

        static::assertSame(0, $this->pager->getMaxPerPage());
        static::assertSame(0, $this->pager->getPage());

        $this->pager->setMaxPerPage(12);

        static::assertSame(12, $this->pager->getMaxPerPage());
        static::assertSame(1, $this->pager->getPage());
    }

    public function testGetMaxPerPage3(): void
    {
        static::assertSame(10, $this->pager->getMaxPerPage());
        static::assertSame(1, $this->pager->getPage());

        $this->pager->setMaxPerPage(0);

        static::assertSame(0, $this->pager->getMaxPerPage());
        static::assertSame(0, $this->pager->getPage());

        $this->pager->setMaxPerPage(-1);

        static::assertSame(1, $this->pager->getMaxPerPage());
        static::assertSame(1, $this->pager->getPage());
    }

    public function testGetQuery(): void
    {
        $query = $this->createMock(ProxyQueryInterface::class);

        $this->pager->setQuery($query);
        static::assertSame($query, $this->pager->getQuery());
    }

    public function testGetMaxPageLinks(): void
    {
        static::assertSame(0, $this->pager->getMaxPageLinks());

        $this->pager->setMaxPageLinks(123);
        static::assertSame(123, $this->pager->getMaxPageLinks());
    }

    public function testIsFirstPage(): void
    {
        static::assertTrue($this->pager->isFirstPage());

        $this->pager->setPage(123);
        static::assertFalse($this->pager->isFirstPage());
    }

    public function testIsLastPage(): void
    {
        static::assertTrue($this->pager->isLastPage());
        static::assertSame(1, $this->pager->getLastPage());

        $this->pager->setPage(10);
        $this->callMethod($this->pager, 'setLastPage', [50]);
        static::assertSame(50, $this->pager->getLastPage());
        static::assertFalse($this->pager->isLastPage());

        $this->pager->setPage(50);
        static::assertTrue($this->pager->isLastPage());

        $this->callMethod($this->pager, 'setLastPage', [20]);
        static::assertSame(20, $this->pager->getLastPage());
        static::assertFalse($this->pager->isLastPage());
    }

    public function testGetLinks(): void
    {
        static::assertSame([], $this->pager->getLinks());

        $this->pager->setPage(1);
        $this->pager->setMaxPageLinks(1);
        static::assertSame([1], $this->pager->getLinks());
        static::assertSame([1], $this->pager->getLinks(10));

        $this->pager->setPage(1);
        $this->pager->setMaxPageLinks(7);
        $this->callMethod($this->pager, 'setLastPage', [50]);
        static::assertCount(7, $this->pager->getLinks());
        static::assertSame([1, 2, 3, 4, 5, 6, 7], $this->pager->getLinks());

        $this->pager->setPage(10);
        $this->pager->setMaxPageLinks(12);
        static::assertCount(5, $this->pager->getLinks(5));
        static::assertSame([8, 9, 10, 11, 12], $this->pager->getLinks(5));

        $this->pager->setPage(10);
        $this->pager->setMaxPageLinks(6);
        static::assertCount(6, $this->pager->getLinks());
        static::assertSame([7, 8, 9, 10, 11, 12], $this->pager->getLinks());

        $this->pager->setPage(50);
        $this->pager->setMaxPageLinks(6);
        static::assertCount(6, $this->pager->getLinks());
        static::assertSame([45, 46, 47, 48, 49, 50], $this->pager->getLinks());
    }

    public function testHaveToPaginate(): void
    {
        static::assertFalse($this->pager->haveToPaginate());

        $this->pager->setMaxPerPage(10);
        static::assertFalse($this->pager->haveToPaginate());

        $this->pager->expects(static::once())
            ->method('countResults')
            ->willReturn(100);

        static::assertTrue($this->pager->haveToPaginate());
    }

    public function testGetFirstPage(): void
    {
        static::assertSame(1, $this->pager->getFirstPage());
    }

    public function testGetNextPage(): void
    {
        static::assertSame(1, $this->pager->getNextPage());

        $this->pager->setPage(3);
        $this->callMethod($this->pager, 'setLastPage', [20]);
        static::assertSame(4, $this->pager->getNextPage());

        $this->pager->setPage(21);
        static::assertSame(20, $this->pager->getNextPage());
    }

    public function testGetPreviousPage(): void
    {
        static::assertSame(1, $this->pager->getPreviousPage());

        $this->pager->setPage(3);
        static::assertSame(2, $this->pager->getPreviousPage());

        $this->pager->setPage(21);
        static::assertSame(20, $this->pager->getPreviousPage());
    }

    /**
     * @param mixed[] $args
     */
    protected function callMethod(object $obj, string $name, array $args = []): mixed
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
