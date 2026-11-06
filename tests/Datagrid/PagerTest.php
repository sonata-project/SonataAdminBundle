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
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class PagerTest extends TestCase
{
    use ExpectDeprecationTrait;

    /**
     * @var Pager
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
        static::assertSame(10, $this->pager->getMaxPerPage());
        static::assertSame(1, $this->pager->getPage());

        if (null !== $page) {
            $this->pager->setPage($page);
        }

        $this->pager->setMaxPerPage($maxPerPage);

        static::assertSame($expectedPage, $this->pager->getPage());
        static::assertSame($expectedMaxPerPage, $this->pager->getMaxPerPage());
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

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetCurrentMaxLink(): void
    {
        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::getCurrentMaxLink()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertSame(1, $this->pager->getCurrentMaxLink());

        $this->pager->getLinks();
        static::assertSame(1, $this->pager->getCurrentMaxLink());

        $this->callMethod($this->pager, 'setLastPage', [20]);
        $this->pager->getLinks(10);
        static::assertSame(10, $this->pager->getCurrentMaxLink());

        $this->pager->setMaxPageLinks(5);
        $this->pager->setPage(2);
        static::assertSame(10, $this->pager->getCurrentMaxLink());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetMaxRecordLimit(): void
    {
        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::getMaxRecordLimit()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertFalse($this->pager->getMaxRecordLimit());

        $this->pager->setMaxRecordLimit(99);
        static::assertSame(99, $this->pager->getMaxRecordLimit());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetNbResults(): void
    {
        static::assertSame(0, $this->pager->getNbResults());

        $this->setProperty($this->pager, 'nbResults', 100);

        static::assertSame(100, $this->pager->getNbResults());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testCount(): void
    {
        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::count()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertSame(0, $this->pager->count());

        $this->setProperty($this->pager, 'nbResults', 100);

        static::assertSame(100, $this->pager->count());
    }

    public function testGetQuery(): void
    {
        $query = $this->createMock(ProxyQueryInterface::class);

        $this->pager->setQuery($query);
        static::assertSame($query, $this->pager->getQuery());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetCountColumn(): void
    {
        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::getCountColumn()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertSame(['id'], $this->pager->getCountColumn());

        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::setCountColumn()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        $this->pager->setCountColumn(['foo']);
        static::assertSame(['foo'], $this->pager->getCountColumn());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testParameters(): void
    {
        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::getParameter()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertNull($this->pager->getParameter('foo', null));
        static::assertSame('bar', $this->pager->getParameter('foo', 'bar'));
        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::hasParameter()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertFalse($this->pager->hasParameter('foo'));
        static::assertSame([], $this->pager->getParameters());

        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::setParameter()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        $this->pager->setParameter('foo', 'foo_value');

        static::assertTrue($this->pager->hasParameter('foo'));
        static::assertSame('foo_value', $this->pager->getParameter('foo', null));
        static::assertSame('foo_value', $this->pager->getParameter('foo', 'bar'));
        static::assertSame(['foo' => 'foo_value'], $this->pager->getParameters());

        $this->pager->setParameter('foo', 'baz');

        static::assertTrue($this->pager->hasParameter('foo'));
        static::assertSame('baz', $this->pager->getParameter('foo', null));
        static::assertSame('baz', $this->pager->getParameter('foo', 'bar'));
        static::assertSame(['foo' => 'baz'], $this->pager->getParameters());

        $this->pager->setParameter('foo2', 'foo2_value');

        static::assertTrue($this->pager->hasParameter('foo2'));
        static::assertSame('foo2_value', $this->pager->getParameter('foo2', null));
        static::assertSame('foo2_value', $this->pager->getParameter('foo2', 'bar'));
        static::assertSame(['foo' => 'baz', 'foo2' => 'foo2_value'], $this->pager->getParameters());
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
        static::assertSame(20, $this->pager->getPage());
        static::assertSame(20, $this->pager->getLastPage());
        static::assertTrue($this->pager->isLastPage());
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

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testIterator(): void
    {
        static::assertInstanceOf(\Iterator::class, $this->pager);

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

        static::assertSame(3, $counter);
        static::assertSame($object3, $value);
        static::assertSame($expectedObjects, $values);

        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::valid()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertFalse($this->pager->valid());

        $this->callMethod($this->pager, 'resetIterator');
        static::assertTrue($this->pager->valid());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testValid(): void
    {
        $this->pager
            ->method('getResults')
            ->willReturn([]);

        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::valid()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertFalse($this->pager->valid());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testNext(): void
    {
        $this->pager
            ->method('getResults')
            ->willReturn([]);

        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::next()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertFalse($this->pager->next());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testKey(): void
    {
        $this->pager
            ->method('getResults')
            ->willReturn([123 => new \stdClass()]);

        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::key()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertSame(123, $this->pager->key());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testCurrent(): void
    {
        $object = new \stdClass();

        $this->pager
            ->method('getResults')
            ->willReturn([$object]);

        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::current()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertSame($object, $this->pager->current());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetCursor(): void
    {
        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::getCursor()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertSame(1, $this->pager->getCursor());

        $this->pager->setCursor(0);
        static::assertSame(1, $this->pager->getCursor());

        $this->pager->setCursor(300);
        static::assertSame(0, $this->pager->getCursor());

        $this->setProperty($this->pager, 'nbResults', 100);

        $this->pager->setCursor(5);
        static::assertSame(5, $this->pager->getCursor());

        $this->pager->setCursor(300);
        static::assertSame(100, $this->pager->getCursor());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetObjectByCursor(): void
    {
        $object1 = new \stdClass();
        $object1->foo = 'bar1';

        $object2 = new \stdClass();
        $object2->foo = 'bar2';

        $object3 = new \stdClass();
        $object3->foo = 'bar3';

        $this->setProperty($this->pager, 'nbResults', 3);

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
            ->willReturnCallback(static function () use (&$id, $object1, $object2, $object3): ?array {
                switch ($id) {
                    case 0:
                        return [$object1];

                    case 1:
                        return [$object2];

                    case 2:
                        return [$object3];
                }

                return null;
            });

        $this->pager->setQuery($query);

        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::getObjectByCursor()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertSame($object1, $this->pager->getObjectByCursor(1));
        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::getCursor()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertSame(1, $this->pager->getCursor());

        $id = 1;
        static::assertSame($object2, $this->pager->getObjectByCursor(2));
        static::assertSame(2, $this->pager->getCursor());

        $id = 2;
        static::assertSame($object3, $this->pager->getObjectByCursor(3));
        static::assertSame(3, $this->pager->getCursor());

        $id = 3;
        static::assertNull($this->pager->getObjectByCursor(4));
        static::assertSame(3, $this->pager->getCursor());
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
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetFirstIndex(): void
    {
        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::getFirstIndex()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertSame(1, $this->pager->getFirstIndex());

        $this->pager->setMaxPerPage(0);
        $this->pager->setPage(0);
        static::assertSame(1, $this->pager->getFirstIndex());

        $this->pager->setPage(2);
        $this->pager->setMaxPerPage(10);
        static::assertSame(11, $this->pager->getFirstIndex());

        $this->pager->setPage(4);
        $this->pager->setMaxPerPage(7);
        static::assertSame(22, $this->pager->getFirstIndex());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetLastIndex(): void
    {
        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::getLastIndex()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertSame(0, $this->pager->getLastIndex());

        $this->pager->setMaxPerPage(0);
        $this->pager->setPage(0);
        static::assertSame(0, $this->pager->getLastIndex());

        $this->setProperty($this->pager, 'nbResults', 100);

        static::assertSame(100, $this->pager->getLastIndex());

        $this->pager->setPage(2);
        static::assertSame(0, $this->pager->getLastIndex());

        $this->pager->setMaxPerPage(10);
        static::assertSame(20, $this->pager->getLastIndex());

        $this->pager->setPage(11);
        static::assertSame(100, $this->pager->getLastIndex());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetNext(): void
    {
        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::getNext()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertNull($this->pager->getNext());

        $object1 = new \stdClass();
        $object1->foo = 'bar1';

        $object2 = new \stdClass();
        $object2->foo = 'bar2';

        $object3 = new \stdClass();
        $object3->foo = 'bar3';

        $this->setProperty($this->pager, 'nbResults', 3);

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
            ->willReturnCallback(static function () use (&$id, $object1, $object2, $object3): ?array {
                switch ($id) {
                    case 0:
                        return [$object1];

                    case 1:
                        return [$object2];

                    case 2:
                        return [$object3];
                }

                return null;
            });

        $this->pager->setQuery($query);

        $this->pager->setCursor(1);
        static::assertSame($object1, $this->pager->getCurrent());

        ++$id;
        static::assertSame($object2, $this->pager->getNext());

        ++$id;
        static::assertSame($object3, $this->pager->getNext());

        ++$id;
        static::assertNull($this->pager->getNext());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPrevious(): void
    {
        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::getPrevious()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        static::assertNull($this->pager->getPrevious());

        $object1 = new \stdClass();
        $object1->foo = 'bar1';

        $object2 = new \stdClass();
        $object2->foo = 'bar2';

        $object3 = new \stdClass();
        $object3->foo = 'bar3';

        $this->setProperty($this->pager, 'nbResults', 3);

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
            ->willReturnCallback(static function () use (&$id, $object1, $object2, $object3): ?array {
                switch ($id) {
                    case 0:
                        return [$object1];

                    case 1:
                        return [$object2];

                    case 2:
                        return [$object3];
                }

                return null;
            });

        $this->pager->setQuery($query);

        $this->pager->setCursor(2);
        static::assertSame($object3, $this->pager->getCurrent());

        --$id;
        static::assertSame($object2, $this->pager->getPrevious());

        --$id;
        static::assertSame($object1, $this->pager->getPrevious());

        --$id;
        static::assertNull($this->pager->getPrevious());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testSerialize(): void
    {
        $pagerClone = clone $this->pager;
        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::serialize()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        $data = $this->pager->serialize();
        static::assertNotEmpty($data);

        $this->pager->setPage(12);
        $this->pager->setMaxPerPage(4);
        $this->pager->setMaxPageLinks(6);

        $this->pager->unserialize($data);
        static::assertSame($pagerClone->getPage(), $this->pager->getPage());
        static::assertSame($pagerClone->getMaxPerPage(), $this->pager->getMaxPerPage());
        static::assertSame($pagerClone->getMaxPageLinks(), $this->pager->getMaxPageLinks());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
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

        $this->expectDeprecation('The method "Sonata\AdminBundle\Datagrid\Pager::unserialize()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.');
        $this->pager->unserialize(serialize($serialized));

        static::assertSame(7, $this->pager->getMaxPerPage());
        static::assertSame(6, $this->pager->getPage());
        static::assertSame(5, $this->pager->getMaxPageLinks());
        static::assertSame(4, $this->pager->getLastPage());
        static::assertSame(['idx'], $this->pager->getCountColumn());
        static::assertSame(30, $this->pager->getNbResults());
        static::assertSame(3, $this->pager->getCursor());
        static::assertSame(['foo' => 'bar'], $this->pager->getParameters());
        static::assertSame(2, $this->pager->getCurrentMaxLink());
        static::assertSame(22, $this->pager->getMaxRecordLimit());
        static::assertNull($this->pager->getQuery());
    }

    protected function callMethod($obj, string $name, array $args = [])
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }

    /**
     * NEXT_MAJOR: remove this method.
     */
    private function setProperty(object $obj, string $name, $value): void
    {
        $class = new \ReflectionClass($obj);
        $property = $class->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($obj, $value);
    }
}
