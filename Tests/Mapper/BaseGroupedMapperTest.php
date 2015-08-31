<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Mapper;

use Sonata\AdminBundle\Mapper\BaseGroupedMapper;

/**
 * Test for BaseGroupedMapper.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class BaseGroupedMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BaseGroupedMapper
     */
    protected $baseGroupedMapper;

    private $tabs;
    private $groups;

    public function setUp()
    {
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $builder = $this->getMock('Sonata\AdminBundle\Builder\BuilderInterface');

        $this->baseGroupedMapper = $this->getMockForAbstractClass('Sonata\AdminBundle\Mapper\BaseGroupedMapper', array($builder, $admin));

        // php 5.3 BC
        $object = $this;
        $this->tabs = array();
        $this->groups = array();

        $this->baseGroupedMapper->expects($this->any())
            ->method('getTabs')
            ->will($this->returnCallback(function () use ($object) {
                return $object->getTabs();
            }));

        $this->baseGroupedMapper->expects($this->any())
            ->method('setTabs')
            ->will($this->returnCallback(function (array $tabs) use ($object) {
                $object->setTabs($tabs);
            }));

        $this->baseGroupedMapper->expects($this->any())
            ->method('getGroups')
            ->will($this->returnCallback(function () use ($object) {
                return $object->getGroups();
            }));

        $this->baseGroupedMapper->expects($this->any())
            ->method('setGroups')
            ->will($this->returnCallback(function (array $groups) use ($object) {
                $object->setGroups($groups);
            }));
    }

    public function testWith()
    {
        $this->assertCount(0, $this->tabs);
        $this->assertCount(0, $this->groups);
        $this->assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->with('fooGroup'));
        $this->assertCount(1, $this->tabs);
        $this->assertCount(1, $this->groups);
    }

    public function testEnd()
    {
        $this->assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->with('fooGroup'));
    }

    public function testTab()
    {
        $this->assertCount(0, $this->tabs);
        $this->assertCount(0, $this->groups);
        $this->assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->tab('fooTab'));
        $this->assertCount(1, $this->tabs);
        $this->assertCount(0, $this->groups);
    }

    public function testTab2()
    {
        $this->assertCount(0, $this->tabs);
        $this->assertCount(0, $this->groups);
        $this->assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->with('fooTab', array('tab' => true)));
        $this->assertCount(1, $this->tabs);
        $this->assertCount(0, $this->groups);
    }

    public function testFluidInterface()
    {
        $this->assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->tab('fooTab')->with('fooGroup1')->end()->with('fooGroup2')->end()->with('fooGroup3')->end()->end()->tab('barTab')->with('barGroup1')->end()->with('barGroup2')->end()->with('barGroup3')->end()->end());
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage You should close previous group "fooGroup1" with end() before adding new tab "fooGroup2".
     */
    public function testGroupNotClosedException()
    {
        $this->baseGroupedMapper->with('fooGroup1');
        $this->baseGroupedMapper->with('fooGroup2');
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage New tab was added automatically when you have added field or group. You should close current tab before adding new one OR add tabs before adding groups and fields.
     */
    public function testGroupInTabException()
    {
        $this->baseGroupedMapper->with('fooGroup');
        $this->baseGroupedMapper->tab('fooTab');
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage You should close previous tab "fooTab" with end() before adding new tab "barTab".
     */
    public function testTabInTabException()
    {
        $this->baseGroupedMapper->tab('fooTab');
        $this->baseGroupedMapper->tab('barTab');
    }

    public function testHasOpenTab()
    {
        $this->assertFalse($this->baseGroupedMapper->hasOpenTab(), '->hasOpenTab() returns false when there are no tabs');

        $this->baseGroupedMapper->tab('fooTab');
        $this->assertTrue($this->baseGroupedMapper->hasOpenTab(), '->hasOpenTab() returns true when there is an open tab');

        $this->baseGroupedMapper->end();
        $this->assertFalse($this->baseGroupedMapper->hasOpenTab(), '->hasOpenTab() returns false when all tabs are closed');
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage No open tabs or groups, you cannot use end()
     */
    public function testEndException()
    {
        $this->baseGroupedMapper->end();
    }

    public function getTabs()
    {
        return $this->tabs;
    }

    public function setTabs($tabs)
    {
        $this->tabs = $tabs;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function setGroups($groups)
    {
        $this->groups = $groups;
    }
}
