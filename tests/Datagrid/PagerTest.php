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
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class PagerTest extends TestCase
{
    use ExpectDeprecationTrait;

    /**
     * @var Pager&MockObject
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
        $this->assertSame(10, $this->pager->getMaxPerPage());
        $this->assertSame(1, $this->pager->getPage());

        if (null !== $page) {
            $this->pager->setPage($page);
        }

        $this->pager->setMaxPerPage($maxPerPage);

        $this->assertSame($expectedPage, $this->pager->getPage());
        $this->assertSame($expectedMaxPerPage, $this->pager->getMaxPerPage());
    }

    public function getGetMaxPerPage1Tests(): array
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
        $this->assertSame(10, $this->pager->getMaxPerPage());
        $this->assertSame(1, $this->pager->getPage());

        $this->pager->setMaxPerPage(0);
        $this->pager->setPage(0);

        $this->assertSame(0, $this->pager->getMaxPerPage());
        $this->assertSame(0, $this->pager->getPage());

        $this->pager->setMaxPerPage(12);

        $this->assertSame(12, $this->pager->getMaxPerPage());
        $this->assertSame(1, $this->pager->getPage());
    }

    public function testGetMaxPerPage3(): void
    {
        $this->assertSame(10, $this->pager->getMaxPerPage());
        $this->assertSame(1, $this->pager->getPage());

        $this->pager->setMaxPerPage(0);

        $this->assertSame(0, $this->pager->getMaxPerPage());
        $this->assertSame(0, $this->pager->getPage());

        $this->pager->setMaxPerPage(-1);

        $this->assertSame(1, $this->pager->getMaxPerPage());
        $this->assertSame(1, $this->pager->getPage());
    }

    public function testGetQuery(): void
    {
        $query = $this->createMock(ProxyQueryInterface::class);

        $this->pager->setQuery($query);
        $this->assertSame($query, $this->pager->getQuery());
    }

    public function testGetMaxPageLinks(): void
    {
        $this->assertSame(0, $this->pager->getMaxPageLinks());

        $this->pager->setMaxPageLinks(123);
        $this->assertSame(123, $this->pager->getMaxPageLinks());
    }

    public function testIsFirstPage(): void
    {
        $this->assertTrue($this->pager->isFirstPage());

        $this->pager->setPage(123);
        $this->assertFalse($this->pager->isFirstPage());
    }

    public function testIsLastPage(): void
    {
        $this->assertTrue($this->pager->isLastPage());
        $this->assertSame(1, $this->pager->getLastPage());

        $this->pager->setPage(10);
        $this->callMethod($this->pager, 'setLastPage', [50]);
        $this->assertSame(50, $this->pager->getLastPage());
        $this->assertFalse($this->pager->isLastPage());

        $this->pager->setPage(50);
        $this->assertTrue($this->pager->isLastPage());

        $this->callMethod($this->pager, 'setLastPage', [20]);
        $this->assertSame(20, $this->pager->getLastPage());
        $this->assertFalse($this->pager->isLastPage());
    }

    public function testGetLinks(): void
    {
        $this->assertSame([], $this->pager->getLinks());

        $this->pager->setPage(1);
        $this->pager->setMaxPageLinks(1);
        $this->assertSame([1], $this->pager->getLinks());
        $this->assertSame([1], $this->pager->getLinks(10));

        $this->pager->setPage(1);
        $this->pager->setMaxPageLinks(7);
        $this->callMethod($this->pager, 'setLastPage', [50]);
        $this->assertCount(7, $this->pager->getLinks());
        $this->assertSame([1, 2, 3, 4, 5, 6, 7], $this->pager->getLinks());

        $this->pager->setPage(10);
        $this->pager->setMaxPageLinks(12);
        $this->assertCount(5, $this->pager->getLinks(5));
        $this->assertSame([8, 9, 10, 11, 12], $this->pager->getLinks(5));

        $this->pager->setPage(10);
        $this->pager->setMaxPageLinks(6);
        $this->assertCount(6, $this->pager->getLinks());
        $this->assertSame([7, 8, 9, 10, 11, 12], $this->pager->getLinks());

        $this->pager->setPage(50);
        $this->pager->setMaxPageLinks(6);
        $this->assertCount(6, $this->pager->getLinks());
        $this->assertSame([45, 46, 47, 48, 49, 50], $this->pager->getLinks());
    }

    public function testHaveToPaginate(): void
    {
        $this->assertFalse($this->pager->haveToPaginate());

        $this->pager->setMaxPerPage(10);
        $this->assertFalse($this->pager->haveToPaginate());

        $this->pager->expects($this->once())
            ->method('countResults')
            ->willReturn(100);

        $this->assertTrue($this->pager->haveToPaginate());
    }

    public function testGetFirstPage(): void
    {
        $this->assertSame(1, $this->pager->getFirstPage());
    }

    public function testGetNextPage(): void
    {
        $this->assertSame(1, $this->pager->getNextPage());

        $this->pager->setPage(3);
        $this->callMethod($this->pager, 'setLastPage', [20]);
        $this->assertSame(4, $this->pager->getNextPage());

        $this->pager->setPage(21);
        $this->assertSame(20, $this->pager->getNextPage());
    }

    public function testGetPreviousPage(): void
    {
        $this->assertSame(1, $this->pager->getPreviousPage());

        $this->pager->setPage(3);
        $this->assertSame(2, $this->pager->getPreviousPage());

        $this->pager->setPage(21);
        $this->assertSame(20, $this->pager->getPreviousPage());
    }

    protected function callMethod($obj, string $name, array $args = [])
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
