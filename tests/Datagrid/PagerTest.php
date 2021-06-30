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
class PagerTest extends TestCase
{
    /**
     * @var Pager<ProxyQueryInterface>&MockObject
     */
    private $pager;

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
     * @dataProvider getGetMaxPerPage1Tests
     */
    public function testGetMaxPerPage1(int $expectedMaxPerPage, int $expectedPage, int $maxPerPage, ?int $page): void
    {
        self::assertSame(10, $this->pager->getMaxPerPage());
        self::assertSame(1, $this->pager->getPage());

        if (null !== $page) {
            $this->pager->setPage($page);
        }

        $this->pager->setMaxPerPage($maxPerPage);

        self::assertSame($expectedPage, $this->pager->getPage());
        self::assertSame($expectedMaxPerPage, $this->pager->getMaxPerPage());
    }

    /**
     * @phpstan-return iterable<array-key, array{int, int, int, int|null}>
     */
    public function getGetMaxPerPage1Tests(): iterable
    {
        return [
            [123, 1, 123, 1],
            [123, 321, 123, 321],
            [1, 1, 1, 0],
            [0, 0, 0, 0],
            [1, 1, -1, 1],
            [1, 1, -1, 0],
            [1, 1, -1, -1],
            [0, 0, 0, null],
        ];
    }

    public function testGetMaxPerPage2(): void
    {
        self::assertSame(10, $this->pager->getMaxPerPage());
        self::assertSame(1, $this->pager->getPage());

        $this->pager->setMaxPerPage(0);
        $this->pager->setPage(0);

        self::assertSame(0, $this->pager->getMaxPerPage());
        self::assertSame(0, $this->pager->getPage());

        $this->pager->setMaxPerPage(12);

        self::assertSame(12, $this->pager->getMaxPerPage());
        self::assertSame(1, $this->pager->getPage());
    }

    public function testGetMaxPerPage3(): void
    {
        self::assertSame(10, $this->pager->getMaxPerPage());
        self::assertSame(1, $this->pager->getPage());

        $this->pager->setMaxPerPage(0);

        self::assertSame(0, $this->pager->getMaxPerPage());
        self::assertSame(0, $this->pager->getPage());

        $this->pager->setMaxPerPage(-1);

        self::assertSame(1, $this->pager->getMaxPerPage());
        self::assertSame(1, $this->pager->getPage());
    }

    public function testGetQuery(): void
    {
        $query = $this->createMock(ProxyQueryInterface::class);

        $this->pager->setQuery($query);
        self::assertSame($query, $this->pager->getQuery());
    }

    public function testGetMaxPageLinks(): void
    {
        self::assertSame(0, $this->pager->getMaxPageLinks());

        $this->pager->setMaxPageLinks(123);
        self::assertSame(123, $this->pager->getMaxPageLinks());
    }

    public function testIsFirstPage(): void
    {
        self::assertTrue($this->pager->isFirstPage());

        $this->pager->setPage(123);
        self::assertFalse($this->pager->isFirstPage());
    }

    public function testIsLastPage(): void
    {
        self::assertTrue($this->pager->isLastPage());
        self::assertSame(1, $this->pager->getLastPage());

        $this->pager->setPage(10);
        $this->callMethod($this->pager, 'setLastPage', [50]);
        self::assertSame(50, $this->pager->getLastPage());
        self::assertFalse($this->pager->isLastPage());

        $this->pager->setPage(50);
        self::assertTrue($this->pager->isLastPage());

        $this->callMethod($this->pager, 'setLastPage', [20]);
        self::assertSame(20, $this->pager->getLastPage());
        self::assertFalse($this->pager->isLastPage());
    }

    public function testGetLinks(): void
    {
        self::assertSame([], $this->pager->getLinks());

        $this->pager->setPage(1);
        $this->pager->setMaxPageLinks(1);
        self::assertSame([1], $this->pager->getLinks());
        self::assertSame([1], $this->pager->getLinks(10));

        $this->pager->setPage(1);
        $this->pager->setMaxPageLinks(7);
        $this->callMethod($this->pager, 'setLastPage', [50]);
        self::assertCount(7, $this->pager->getLinks());
        self::assertSame([1, 2, 3, 4, 5, 6, 7], $this->pager->getLinks());

        $this->pager->setPage(10);
        $this->pager->setMaxPageLinks(12);
        self::assertCount(5, $this->pager->getLinks(5));
        self::assertSame([8, 9, 10, 11, 12], $this->pager->getLinks(5));

        $this->pager->setPage(10);
        $this->pager->setMaxPageLinks(6);
        self::assertCount(6, $this->pager->getLinks());
        self::assertSame([7, 8, 9, 10, 11, 12], $this->pager->getLinks());

        $this->pager->setPage(50);
        $this->pager->setMaxPageLinks(6);
        self::assertCount(6, $this->pager->getLinks());
        self::assertSame([45, 46, 47, 48, 49, 50], $this->pager->getLinks());
    }

    public function testHaveToPaginate(): void
    {
        self::assertFalse($this->pager->haveToPaginate());

        $this->pager->setMaxPerPage(10);
        self::assertFalse($this->pager->haveToPaginate());

        $this->pager->expects(self::once())
            ->method('countResults')
            ->willReturn(100);

        self::assertTrue($this->pager->haveToPaginate());
    }

    public function testGetFirstPage(): void
    {
        self::assertSame(1, $this->pager->getFirstPage());
    }

    public function testGetNextPage(): void
    {
        self::assertSame(1, $this->pager->getNextPage());

        $this->pager->setPage(3);
        $this->callMethod($this->pager, 'setLastPage', [20]);
        self::assertSame(4, $this->pager->getNextPage());

        $this->pager->setPage(21);
        self::assertSame(20, $this->pager->getNextPage());
    }

    public function testGetPreviousPage(): void
    {
        self::assertSame(1, $this->pager->getPreviousPage());

        $this->pager->setPage(3);
        self::assertSame(2, $this->pager->getPreviousPage());

        $this->pager->setPage(21);
        self::assertSame(20, $this->pager->getPreviousPage());
    }

    /**
     * @param mixed[] $args
     *
     * @return mixed
     */
    protected function callMethod(object $obj, string $name, array $args = [])
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
