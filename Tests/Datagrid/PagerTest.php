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

use Sonata\AdminBundle\Datagrid\Pager;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class PagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Pager
     */
    private $pager;

    protected function setUp()
    {
        $this->pager = $this->getMockForAbstractClass('Sonata\AdminBundle\Datagrid\Pager');
    }

    /**
     * @dataProvider getGetMaxPerPage1Tests
     */
    public function testGetMaxPerPage1($expectedMaxPerPage, $expectedPage, $maxPerPage, $page)
    {
        $this->assertSame(10, $this->pager->getMaxPerPage());
        $this->assertSame(1, $this->pager->getPage());

        if ($page !== null) {
            $this->pager->setPage($page);
        }

        $this->pager->setMaxPerPage($maxPerPage);

        $this->assertSame($expectedPage, $this->pager->getPage());
        $this->assertSame($expectedMaxPerPage, $this->pager->getMaxPerPage());
    }

    public function getGetMaxPerPage1Tests()
    {
        return array(
            array(123, 1, 123, 1),
            array(123, 321, 123, 321),
            array(1, 1, 1, 0),
            array(0, 0, 0, 0),
            array(1, 1, -1, 1),
            array(1, 1, -1, 0),
            array(1, 1, -1, -1),
            array(0, 0, 0, null),
        );
    }

    public function testGetMaxPerPage2()
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

    public function testGetMaxPerPage3()
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

    public function testGetCurrentMaxLink()
    {
        $this->assertSame(1, $this->pager->getCurrentMaxLink());

        $this->pager->getLinks();
        $this->assertSame(1, $this->pager->getCurrentMaxLink());

        $this->callMethod($this->pager, 'setLastPage', array(20));
        $this->pager->getLinks(10);
        $this->assertSame(10, $this->pager->getCurrentMaxLink());

        $this->pager->setMaxPageLinks(5);
        $this->pager->setPage(2);
        $this->assertSame(10, $this->pager->getCurrentMaxLink());
    }

    public function testGetMaxRecordLimit()
    {
        $this->assertSame(false, $this->pager->getMaxRecordLimit());

        $this->pager->setMaxRecordLimit(99);
        $this->assertSame(99, $this->pager->getMaxRecordLimit());
    }

    public function testGetNbResults()
    {
        $this->assertSame(0, $this->pager->getNbResults());

        $this->callMethod($this->pager, 'setNbResults', array(100));

        $this->assertSame(100, $this->pager->getNbResults());
    }

    public function testCount()
    {
        $this->assertSame(0, $this->pager->count());

        $this->callMethod($this->pager, 'setNbResults', array(100));

        $this->assertSame(100, $this->pager->count());
    }

    public function testGetQuery()
    {
        $query = $this->getMock('Sonata\AdminBundle\Datagrid\ProxyQueryInterface');

        $this->pager->setQuery($query);
        $this->assertSame($query, $this->pager->getQuery());
    }

    public function testGetCountColumn()
    {
        $this->assertSame(array('id'), $this->pager->getCountColumn());

        $this->pager->setCountColumn(array('foo'));
        $this->assertSame(array('foo'), $this->pager->getCountColumn());
    }

    public function testParameters()
    {
        $this->assertSame(null, $this->pager->getParameter('foo', null));
        $this->assertSame('bar', $this->pager->getParameter('foo', 'bar'));
        $this->assertFalse($this->pager->hasParameter('foo'));
        $this->assertSame(array(), $this->pager->getParameters());

        $this->pager->setParameter('foo', 'foo_value');

        $this->assertTrue($this->pager->hasParameter('foo'));
        $this->assertSame('foo_value', $this->pager->getParameter('foo', null));
        $this->assertSame('foo_value', $this->pager->getParameter('foo', 'bar'));
        $this->assertSame(array('foo' => 'foo_value'), $this->pager->getParameters());

        $this->pager->setParameter('foo', 'baz');

        $this->assertTrue($this->pager->hasParameter('foo'));
        $this->assertSame('baz', $this->pager->getParameter('foo', null));
        $this->assertSame('baz', $this->pager->getParameter('foo', 'bar'));
        $this->assertSame(array('foo' => 'baz'), $this->pager->getParameters());

        $this->pager->setParameter('foo2', 'foo2_value');

        $this->assertTrue($this->pager->hasParameter('foo2'));
        $this->assertSame('foo2_value', $this->pager->getParameter('foo2', null));
        $this->assertSame('foo2_value', $this->pager->getParameter('foo2', 'bar'));
        $this->assertSame(array('foo'  => 'baz', 'foo2' => 'foo2_value'), $this->pager->getParameters());
    }

    public function testGetMaxPageLinks()
    {
        $this->assertSame(0, $this->pager->getMaxPageLinks());

        $this->pager->setMaxPageLinks(123);
        $this->assertSame(123, $this->pager->getMaxPageLinks());
    }

    public function testIsFirstPage()
    {
        $this->assertTrue($this->pager->isFirstPage());

        $this->pager->setPage(123);
        $this->assertFalse($this->pager->isFirstPage());
    }

    public function testIsLastPage()
    {
        $this->assertTrue($this->pager->isLastPage());
        $this->assertSame(1, $this->pager->getLastPage());

        $this->pager->setPage(10);
        $this->callMethod($this->pager, 'setLastPage', array(50));
        $this->assertSame(50, $this->pager->getLastPage());
        $this->assertFalse($this->pager->isLastPage());

        $this->pager->setPage(50);
        $this->assertTrue($this->pager->isLastPage());

        $this->callMethod($this->pager, 'setLastPage', array(20));
        $this->assertSame(20, $this->pager->getPage());
        $this->assertSame(20, $this->pager->getLastPage());
        $this->assertTrue($this->pager->isLastPage());
    }

    public function testGetLinks()
    {
        $this->assertSame(array(), $this->pager->getLinks());

        $this->pager->setPage(1);
        $this->pager->setMaxPageLinks(1);
        $this->assertSame(array(1), $this->pager->getLinks());
        $this->assertSame(array(1), $this->pager->getLinks(10));

        $this->pager->setPage(1);
        $this->pager->setMaxPageLinks(7);
        $this->callMethod($this->pager, 'setLastPage', array(50));
        $this->assertSame(7, count($this->pager->getLinks()));
        $this->assertSame(array(1, 2, 3, 4, 5, 6, 7), $this->pager->getLinks());

        $this->pager->setPage(10);
        $this->pager->setMaxPageLinks(12);
        $this->assertSame(5, count($this->pager->getLinks(5)));
        $this->assertSame(array(8, 9, 10, 11, 12), $this->pager->getLinks(5));

        $this->pager->setPage(10);
        $this->pager->setMaxPageLinks(6);
        $this->assertSame(6, count($this->pager->getLinks()));
        $this->assertSame(array(7, 8, 9, 10, 11, 12), $this->pager->getLinks());

        $this->pager->setPage(50);
        $this->pager->setMaxPageLinks(6);
        $this->assertSame(6, count($this->pager->getLinks()));
        $this->assertSame(array(45, 46, 47, 48, 49, 50), $this->pager->getLinks());
    }

    public function testHaveToPaginate()
    {
        $this->assertFalse($this->pager->haveToPaginate());

        $this->pager->setMaxPerPage(10);
        $this->assertFalse($this->pager->haveToPaginate());

        $this->callMethod($this->pager, 'setNbResults', array(100));
        $this->assertTrue($this->pager->haveToPaginate());
    }

    public function testIterator()
    {
        $this->assertTrue($this->pager instanceof \Iterator);

        $object1 = new \stdClass();
        $object1->foo = 'bar1';

        $object2 = new \stdClass();
        $object2->foo = 'bar2';

        $object3 = new \stdClass();
        $object3->foo = 'bar3';

        $expectedObjects = array($object1, $object2, $object3);

        $this->pager->expects($this->any())
            ->method('getResults')
            ->will($this->returnValue($expectedObjects));

        $counter = 0;
        $values = array();
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

    public function testValid()
    {
        $this->assertFalse($this->pager->valid());
    }

    public function testNext()
    {
        $this->pager->expects($this->any())
            ->method('getResults')
            ->will($this->returnValue(array()));

        $this->assertFalse($this->pager->next());
    }

    public function testKey()
    {
        $this->pager->expects($this->any())
            ->method('getResults')
            ->will($this->returnValue(array(123 => new \stdClass())));

        $this->assertSame(123, $this->pager->key());
    }

    public function testCurrent()
    {
        $object = new \stdClass();

        $this->pager->expects($this->any())
            ->method('getResults')
            ->will($this->returnValue(array($object)));

        $this->assertSame($object, $this->pager->current());
    }

    public function testGetCursor()
    {
        $this->assertSame(1, $this->pager->getCursor());

        $this->pager->setCursor(0);
        $this->assertSame(1, $this->pager->getCursor());

        $this->pager->setCursor(300);
        $this->assertSame(0, $this->pager->getCursor());

        $this->callMethod($this->pager, 'setNbResults', array(100));

        $this->pager->setCursor(5);
        $this->assertSame(5, $this->pager->getCursor());

        $this->pager->setCursor(300);
        $this->assertSame(100, $this->pager->getCursor());
    }

    public function testGetObjectByCursor()
    {
        $object1 = new \stdClass();
        $object1->foo = 'bar1';

        $object2 = new \stdClass();
        $object2->foo = 'bar2';

        $object3 = new \stdClass();
        $object3->foo = 'bar3';

        $this->callMethod($this->pager, 'setNbResults', array(3));

        $query = $this->getMock('Sonata\AdminBundle\Datagrid\ProxyQueryInterface');

        $query->expects($this->any())
            ->method('setFirstResult')
            ->will($this->returnValue($query));

        $query->expects($this->any())
            ->method('setMaxResults')
            ->will($this->returnValue($query));

        $id = 0;
        $query->expects($this->any())
            ->method('execute')
            ->will($this->returnCallback(function () use (&$id, $object1, $object2, $object3) {
                switch ($id) {
                    case 0:
                        return array($object1);
                        break;

                    case 1:
                        return array($object2);
                        break;

                    case 2:
                        return array($object3);
                        break;
                }

                return;
            }));

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
        $this->assertSame(null, $this->pager->getObjectByCursor(4));
        $this->assertSame(3, $this->pager->getCursor());
    }

    public function testGetFirstPage()
    {
        $this->assertSame(1, $this->pager->getFirstPage());
    }

    public function testGetNextPage()
    {
        $this->assertSame(1, $this->pager->getNextPage());

        $this->pager->setPage(3);
        $this->callMethod($this->pager, 'setLastPage', array(20));
        $this->assertSame(4, $this->pager->getNextPage());

        $this->pager->setPage(21);
        $this->assertSame(20, $this->pager->getNextPage());
    }

    public function testGetPreviousPage()
    {
        $this->assertSame(1, $this->pager->getPreviousPage());

        $this->pager->setPage(3);
        $this->assertSame(2, $this->pager->getPreviousPage());

        $this->pager->setPage(21);
        $this->assertSame(20, $this->pager->getPreviousPage());
    }

    public function testGetFirstIndice()
    {
        $this->assertSame(1, $this->pager->getFirstIndice());

        $this->pager->setMaxPerPage(0);
        $this->pager->setPage(0);
        $this->assertSame(1, $this->pager->getFirstIndice());

        $this->pager->setPage(2);
        $this->pager->setMaxPerPage(10);
        $this->assertSame(11, $this->pager->getFirstIndice());

        $this->pager->setPage(4);
        $this->pager->setMaxPerPage(7);
        $this->assertSame(22, $this->pager->getFirstIndice());
    }

    public function testGetLastIndice()
    {
        $this->assertSame(0, $this->pager->getLastIndice());

        $this->pager->setMaxPerPage(0);
        $this->pager->setPage(0);
        $this->assertSame(0, $this->pager->getLastIndice());

        $this->callMethod($this->pager, 'setNbResults', array(100));

        $this->assertSame(100, $this->pager->getLastIndice());

        $this->pager->setPage(2);
        $this->assertSame(0, $this->pager->getLastIndice());

        $this->pager->setMaxPerPage(10);
        $this->assertSame(20, $this->pager->getLastIndice());

        $this->pager->setPage(11);
        $this->assertSame(100, $this->pager->getLastIndice());
    }

    public function testGetNext()
    {
        $this->assertSame(null, $this->pager->getNext());

        $object1 = new \stdClass();
        $object1->foo = 'bar1';

        $object2 = new \stdClass();
        $object2->foo = 'bar2';

        $object3 = new \stdClass();
        $object3->foo = 'bar3';

        $this->callMethod($this->pager, 'setNbResults', array(3));

        $query = $this->getMock('Sonata\AdminBundle\Datagrid\ProxyQueryInterface');

        $query->expects($this->any())
            ->method('setFirstResult')
            ->will($this->returnValue($query));

        $query->expects($this->any())
            ->method('setMaxResults')
            ->will($this->returnValue($query));

        $id = 0;
        $query->expects($this->any())
            ->method('execute')
            ->will($this->returnCallback(function () use (&$id, $object1, $object2, $object3) {
                switch ($id) {
                    case 0:
                        return array($object1);
                        break;

                    case 1:
                        return array($object2);
                        break;

                    case 2:
                        return array($object3);
                        break;
                }

                return;
            }));

        $this->pager->setQuery($query);

        $this->pager->setCursor(1);
        $this->assertSame($object1, $this->pager->getCurrent());

        ++$id;
        $this->assertSame($object2, $this->pager->getNext());

        ++$id;
        $this->assertSame($object3, $this->pager->getNext());

        ++$id;
        $this->assertSame(null, $this->pager->getNext());
    }

    public function testGetPrevious()
    {
        $this->assertSame(null, $this->pager->getPrevious());

        $object1 = new \stdClass();
        $object1->foo = 'bar1';

        $object2 = new \stdClass();
        $object2->foo = 'bar2';

        $object3 = new \stdClass();
        $object3->foo = 'bar3';

        $this->callMethod($this->pager, 'setNbResults', array(3));

        $query = $this->getMock('Sonata\AdminBundle\Datagrid\ProxyQueryInterface');

        $query->expects($this->any())
            ->method('setFirstResult')
            ->will($this->returnValue($query));

        $query->expects($this->any())
            ->method('setMaxResults')
            ->will($this->returnValue($query));

        $id = 2;
        $query->expects($this->any())
            ->method('execute')
            ->will($this->returnCallback(function () use (&$id, $object1, $object2, $object3) {
                switch ($id) {
                    case 0:
                        return array($object1);
                        break;

                    case 1:
                        return array($object2);
                        break;

                    case 2:
                        return array($object3);
                        break;
                }

                return;
            }));

        $this->pager->setQuery($query);

        $this->pager->setCursor(2);
        $this->assertSame($object3, $this->pager->getCurrent());

        --$id;
        $this->assertSame($object2, $this->pager->getPrevious());

        --$id;
        $this->assertSame($object1, $this->pager->getPrevious());

        --$id;
        $this->assertSame(null, $this->pager->getPrevious());
    }

    public function testSerialize()
    {
        $pagerClone = clone $this->pager;
        $data  = $this->pager->serialize();
        $this->assertNotEmpty($data);

        $this->pager->setPage(12);
        $this->pager->setMaxPerPage(4);
        $this->pager->setMaxPageLinks(6);

        $this->pager->unserialize($data);
        $this->assertSame($pagerClone->getPage(), $this->pager->getPage());
        $this->assertSame($pagerClone->getMaxPerPage(), $this->pager->getMaxPerPage());
        $this->assertSame($pagerClone->getMaxPageLinks(), $this->pager->getMaxPageLinks());
    }

    public function testUnserialize()
    {
        $serialized = array(
            'page'           => 6,
            'maxPerPage'     => 7,
            'maxPageLinks'   => 5,
            'lastPage'       => 4,
            'nbResults'      => 30,
            'cursor'         => 3,
            'parameters'     => array('foo' => 'bar'),
            'currentMaxLink' => 2,
            'maxRecordLimit' => 22,
            'countColumn'    => array('idx'),
        );

        $this->pager->expects($this->any())
            ->method('getResults')
            ->will($this->returnValue(array()));
        $this->pager->current();

        $this->pager->unserialize(serialize($serialized));

        $this->assertSame(7, $this->pager->getMaxPerPage());
        $this->assertSame(6, $this->pager->getPage());
        $this->assertSame(5, $this->pager->getMaxPageLinks());
        $this->assertSame(4, $this->pager->getLastPage());
        $this->assertSame(array('idx'), $this->pager->getCountColumn());
        $this->assertSame(30, $this->pager->getNbResults());
        $this->assertSame(3, $this->pager->getCursor());
        $this->assertSame(array('foo' => 'bar'), $this->pager->getParameters());
        $this->assertSame(2, $this->pager->getCurrentMaxLink());
        $this->assertSame(22, $this->pager->getMaxRecordLimit());
        $this->assertSame(null, $this->pager->getQuery());
    }

    protected function callMethod($obj, $name, array $args = array())
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
