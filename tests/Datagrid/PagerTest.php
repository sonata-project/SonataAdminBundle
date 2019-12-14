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

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class PagerTest extends TestCase
{
    /**
     * @var Pager
     */
    private $pager;

    protected function setUp(): void
    {
        $this->pager = $this->getMockForAbstractClass(Pager::class);
    }

    /**
     * @dataProvider getGetMaxPerPage1Tests
     */
    public function testGetMaxPerPage1($expectedMaxPerPage, $expectedPage, $maxPerPage, $page): void
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

    public function getGetMaxPerPage1Tests()
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

    public function testGetCurrentMaxLink(): void
    {
        $this->assertSame(1, $this->pager->getCurrentMaxLink());

        $this->pager->getLinks();
        $this->assertSame(1, $this->pager->getCurrentMaxLink());

        $this->callMethod($this->pager, 'setLastPage', [20]);
        $this->pager->getLinks(10);
        $this->assertSame(10, $this->pager->getCurrentMaxLink());

        $this->pager->setMaxPageLinks(5);
        $this->pager->setPage(2);
        $this->assertSame(10, $this->pager->getCurrentMaxLink());
    }

    public function testGetMaxRecordLimit(): void
    {
        $this->assertFalse($this->pager->getMaxRecordLimit());

        $this->pager->setMaxRecordLimit(99);
        $this->assertSame(99, $this->pager->getMaxRecordLimit());
    }

    public function testGetNbResults(): void
    {
        $this->assertSame(0, $this->pager->getNbResults());

        $this->callMethod($this->pager, 'setNbResults', [100]);

        $this->assertSame(100, $this->pager->getNbResults());
    }

    public function testCount(): void
    {
        $this->assertSame(0, $this->pager->count());

        $this->callMethod($this->pager, 'setNbResults', [100]);

        $this->assertSame(100, $this->pager->count());
    }

    public function testGetQuery(): void
    {
        $query = $this->createMock(ProxyQueryInterface::class);

        $this->pager->setQuery($query);
        $this->assertSame($query, $this->pager->getQuery());
    }

    public function testGetCountColumn(): void
    {
        $this->assertSame(['id'], $this->pager->getCountColumn());

        $this->pager->setCountColumn(['foo']);
        $this->assertSame(['foo'], $this->pager->getCountColumn());
    }

    public function testParameters(): void
    {
        $this->assertNull($this->pager->getParameter('foo', null));
        $this->assertSame('bar', $this->pager->getParameter('foo', 'bar'));
        $this->assertFalse($this->pager->hasParameter('foo'));
        $this->assertSame([], $this->pager->getParameters());

        $this->pager->setParameter('foo', 'foo_value');

        $this->assertTrue($this->pager->hasParameter('foo'));
        $this->assertSame('foo_value', $this->pager->getParameter('foo', null));
        $this->assertSame('foo_value', $this->pager->getParameter('foo', 'bar'));
        $this->assertSame(['foo' => 'foo_value'], $this->pager->getParameters());

        $this->pager->setParameter('foo', 'baz');

        $this->assertTrue($this->pager->hasParameter('foo'));
        $this->assertSame('baz', $this->pager->getParameter('foo', null));
        $this->assertSame('baz', $this->pager->getParameter('foo', 'bar'));
        $this->assertSame(['foo' => 'baz'], $this->pager->getParameters());

        $this->pager->setParameter('foo2', 'foo2_value');

        $this->assertTrue($this->pager->hasParameter('foo2'));
        $this->assertSame('foo2_value', $this->pager->getParameter('foo2', null));
        $this->assertSame('foo2_value', $this->pager->getParameter('foo2', 'bar'));
        $this->assertSame(['foo' => 'baz', 'foo2' => 'foo2_value'], $this->pager->getParameters());
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
        $this->assertSame(20, $this->pager->getPage());
        $this->assertSame(20, $this->pager->getLastPage());
        $this->assertTrue($this->pager->isLastPage());
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

        $this->callMethod($this->pager, 'setNbResults', [100]);
        $this->assertTrue($this->pager->haveToPaginate());
    }

    public function testIterator(): void
    {
        $this->assertInstanceOf(\Iterator::class, $this->pager);

        $object1 = new \stdClass();
        $object1->foo = 'bar1';

        $object2 = new \stdClass();
        $object2->foo = 'bar2';

        $object3 = new \stdClass();
        $object3->foo = 'bar3';

        $expectedObjects = [$object1, $object2, $object3];

        $this->pager
            ->method('getResults')
            ->willReturn($expectedObjects);

        $counter = 0;
        $values = [];
        foreach ($this->pager as $key => $value) {
            $values[$key] = $value;
            ++$counter;
        }

        $this->assertSame(3, $counter);
        $this->assertSame($object3, $value);
        $this->assertSame($expectedObjects, $values);

        $this->assertFalse($this->pager->valid());

        $this->callMethod($this->pager, 'resetIterator');
        $this->assertTrue($this->pager->valid());
    }

    public function testValid(): void
    {
        $this->pager
            ->method('getResults')
            ->willReturn([]);

        $this->assertFalse($this->pager->valid());
    }

    public function testNext(): void
    {
        $this->pager
            ->method('getResults')
            ->willReturn([]);

        $this->assertFalse($this->pager->next());
    }

    public function testKey(): void
    {
        $this->pager
            ->method('getResults')
            ->willReturn([123 => new \stdClass()]);

        $this->assertSame(123, $this->pager->key());
    }

    public function testCurrent(): void
    {
        $object = new \stdClass();

        $this->pager
            ->method('getResults')
            ->willReturn([$object]);

        $this->assertSame($object, $this->pager->current());
    }

    public function testGetCursor(): void
    {
        $this->assertSame(1, $this->pager->getCursor());

        $this->pager->setCursor(0);
        $this->assertSame(1, $this->pager->getCursor());

        $this->pager->setCursor(300);
        $this->assertSame(0, $this->pager->getCursor());

        $this->callMethod($this->pager, 'setNbResults', [100]);

        $this->pager->setCursor(5);
        $this->assertSame(5, $this->pager->getCursor());

        $this->pager->setCursor(300);
        $this->assertSame(100, $this->pager->getCursor());
    }

    public function testGetObjectByCursor(): void
    {
        $object1 = new \stdClass();
        $object1->foo = 'bar1';

        $object2 = new \stdClass();
        $object2->foo = 'bar2';

        $object3 = new \stdClass();
        $object3->foo = 'bar3';

        $this->callMethod($this->pager, 'setNbResults', [3]);

        $query = $this->createMock(ProxyQueryInterface::class);

        $query
            ->method('setFirstResult')
            ->willReturn($query);

        $query
            ->method('setMaxResults')
            ->willReturn($query);

        $id = 0;
        $query
            ->method('execute')
            ->willReturnCallback(static function () use (&$id, $object1, $object2, $object3) {
                switch ($id) {
                    case 0:
                        return [$object1];

                    case 1:
                        return [$object2];

                    case 2:
                        return [$object3];
                }
            });

        $this->pager->setQuery($query);

        $this->assertSame($object1, $this->pager->getObjectByCursor(1));
        $this->assertSame(1, $this->pager->getCursor());

        $id = 1;
        $this->assertSame($object2, $this->pager->getObjectByCursor(2));
        $this->assertSame(2, $this->pager->getCursor());

        $id = 2;
        $this->assertSame($object3, $this->pager->getObjectByCursor(3));
        $this->assertSame(3, $this->pager->getCursor());

        $id = 3;
        $this->assertNull($this->pager->getObjectByCursor(4));
        $this->assertSame(3, $this->pager->getCursor());
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

    public function testGetFirstIndex(): void
    {
        $this->assertSame(1, $this->pager->getFirstIndex());

        $this->pager->setMaxPerPage(0);
        $this->pager->setPage(0);
        $this->assertSame(1, $this->pager->getFirstIndex());

        $this->pager->setPage(2);
        $this->pager->setMaxPerPage(10);
        $this->assertSame(11, $this->pager->getFirstIndex());

        $this->pager->setPage(4);
        $this->pager->setMaxPerPage(7);
        $this->assertSame(22, $this->pager->getFirstIndex());
    }

    public function testGetLastIndex(): void
    {
        $this->assertSame(0, $this->pager->getLastIndex());

        $this->pager->setMaxPerPage(0);
        $this->pager->setPage(0);
        $this->assertSame(0, $this->pager->getLastIndex());

        $this->callMethod($this->pager, 'setNbResults', [100]);

        $this->assertSame(100, $this->pager->getLastIndex());

        $this->pager->setPage(2);
        $this->assertSame(0, $this->pager->getLastIndex());

        $this->pager->setMaxPerPage(10);
        $this->assertSame(20, $this->pager->getLastIndex());

        $this->pager->setPage(11);
        $this->assertSame(100, $this->pager->getLastIndex());
    }

    public function testGetNext(): void
    {
        $this->assertNull($this->pager->getNext());

        $object1 = new \stdClass();
        $object1->foo = 'bar1';

        $object2 = new \stdClass();
        $object2->foo = 'bar2';

        $object3 = new \stdClass();
        $object3->foo = 'bar3';

        $this->callMethod($this->pager, 'setNbResults', [3]);

        $query = $this->createMock(ProxyQueryInterface::class);

        $query
            ->method('setFirstResult')
            ->willReturn($query);

        $query
            ->method('setMaxResults')
            ->willReturn($query);

        $id = 0;
        $query
            ->method('execute')
            ->willReturnCallback(static function () use (&$id, $object1, $object2, $object3) {
                switch ($id) {
                    case 0:
                        return [$object1];

                    case 1:
                        return [$object2];

                    case 2:
                        return [$object3];
                }
            });

        $this->pager->setQuery($query);

        $this->pager->setCursor(1);
        $this->assertSame($object1, $this->pager->getCurrent());

        ++$id;
        $this->assertSame($object2, $this->pager->getNext());

        ++$id;
        $this->assertSame($object3, $this->pager->getNext());

        ++$id;
        $this->assertNull($this->pager->getNext());
    }

    public function testGetPrevious(): void
    {
        $this->assertNull($this->pager->getPrevious());

        $object1 = new \stdClass();
        $object1->foo = 'bar1';

        $object2 = new \stdClass();
        $object2->foo = 'bar2';

        $object3 = new \stdClass();
        $object3->foo = 'bar3';

        $this->callMethod($this->pager, 'setNbResults', [3]);

        $query = $this->createMock(ProxyQueryInterface::class);

        $query
            ->method('setFirstResult')
            ->willReturn($query);

        $query
            ->method('setMaxResults')
            ->willReturn($query);

        $id = 2;
        $query
            ->method('execute')
            ->willReturnCallback(static function () use (&$id, $object1, $object2, $object3) {
                switch ($id) {
                    case 0:
                        return [$object1];

                    case 1:
                        return [$object2];

                    case 2:
                        return [$object3];
                }
            });

        $this->pager->setQuery($query);

        $this->pager->setCursor(2);
        $this->assertSame($object3, $this->pager->getCurrent());

        --$id;
        $this->assertSame($object2, $this->pager->getPrevious());

        --$id;
        $this->assertSame($object1, $this->pager->getPrevious());

        --$id;
        $this->assertNull($this->pager->getPrevious());
    }

    public function testSerialize(): void
    {
        $pagerClone = clone $this->pager;
        $data = $this->pager->serialize();
        $this->assertNotEmpty($data);

        $this->pager->setPage(12);
        $this->pager->setMaxPerPage(4);
        $this->pager->setMaxPageLinks(6);

        $this->pager->unserialize($data);
        $this->assertSame($pagerClone->getPage(), $this->pager->getPage());
        $this->assertSame($pagerClone->getMaxPerPage(), $this->pager->getMaxPerPage());
        $this->assertSame($pagerClone->getMaxPageLinks(), $this->pager->getMaxPageLinks());
    }

    public function testUnserialize(): void
    {
        $serialized = [
            'page' => 6,
            'maxPerPage' => 7,
            'maxPageLinks' => 5,
            'lastPage' => 4,
            'nbResults' => 30,
            'cursor' => 3,
            'parameters' => ['foo' => 'bar'],
            'currentMaxLink' => 2,
            'maxRecordLimit' => 22,
            'countColumn' => ['idx'],
        ];

        $this->pager
            ->method('getResults')
            ->willReturn([]);
        $this->pager->current();

        $this->pager->unserialize(serialize($serialized));

        $this->assertSame(7, $this->pager->getMaxPerPage());
        $this->assertSame(6, $this->pager->getPage());
        $this->assertSame(5, $this->pager->getMaxPageLinks());
        $this->assertSame(4, $this->pager->getLastPage());
        $this->assertSame(['idx'], $this->pager->getCountColumn());
        $this->assertSame(30, $this->pager->getNbResults());
        $this->assertSame(3, $this->pager->getCursor());
        $this->assertSame(['foo' => 'bar'], $this->pager->getParameters());
        $this->assertSame(2, $this->pager->getCurrentMaxLink());
        $this->assertSame(22, $this->pager->getMaxRecordLimit());
        $this->assertNull($this->pager->getQuery());
    }

    protected function callMethod($obj, $name, array $args = [])
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
