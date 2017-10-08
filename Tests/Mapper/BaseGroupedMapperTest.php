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

use Sonata\AdminBundle\Admin\Pool;
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
        $admin = $this->getMockBuilder('Sonata\AdminBundle\Admin\AbstractAdmin')
            ->disableOriginalConstructor()
            ->getMock();

        $labelStrategy = $this->getMock('Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface');
        $labelStrategy->expects($this->any())
            ->method('getLabel')
            ->will($this->returnCallback(function ($label) {
                return 'label_'.strtolower($label);
            }));

        $admin->expects($this->any())
            ->method('getLabelTranslatorStrategy')
            ->will($this->returnValue($labelStrategy));

        $container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');
        $configurationPool = new Pool($container, 'myTitle', 'myLogoTitle');

        $admin->expects($this->any())
            ->method('getConfigurationPool')
            ->will($this->returnValue($configurationPool));

        $builder = $this->getMockForAbstractClass('Sonata\AdminBundle\Builder\BuilderInterface');

        $this->baseGroupedMapper = $this->getMockForAbstractClass('Sonata\AdminBundle\Mapper\BaseGroupedMapper', [$builder, $admin]);

        // php 5.3 BC
        $object = $this;
        $this->tabs = [];
        $this->groups = [];

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
                return $object->getTestGroups();
            }));

        $this->baseGroupedMapper->expects($this->any())
            ->method('setGroups')
            ->will($this->returnCallback(function (array $groups) use ($object) {
                $object->setTestGroups($groups);
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
        $this->assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->with('fooTab', ['tab' => true]));
        $this->assertCount(1, $this->tabs);
        $this->assertCount(0, $this->groups);
    }

    public function testFluidInterface()
    {
        $this->assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->tab('fooTab')->with('fooGroup1')->end()->with('fooGroup2')->end()->with('fooGroup3')->end()->end()->tab('barTab')->with('barGroup1')->end()->with('barGroup2')->end()->with('barGroup3')->end()->end());
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage You should close previous group "fooGroup1" with end() before adding new tab "fooGroup2".
     */
    public function testGroupNotClosedException()
    {
        $this->baseGroupedMapper->with('fooGroup1');
        $this->baseGroupedMapper->with('fooGroup2');
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage New tab was added automatically when you have added field or group. You should close current tab before adding new one OR add tabs before adding groups and fields.
     */
    public function testGroupInTabException()
    {
        $this->baseGroupedMapper->with('fooGroup');
        $this->baseGroupedMapper->tab('fooTab');
    }

    /**
     * @expectedException        \RuntimeException
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
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage No open tabs or groups, you cannot use end()
     */
    public function testEndException()
    {
        $this->baseGroupedMapper->end();
    }

    public function labelDataProvider()
    {
        return [
            'nominal use case not translated' => [false, 'fooGroup1', null, 'fooGroup1'],
            'nominal use case translated' => [true, 'fooGroup1', null, 'label_foogroup1'],
            'custom label not translated' => [false, 'fooGroup1', 'custom_label', 'custom_label'],
            'custom label translated' => [true, 'fooGroup1', 'custom_label', 'custom_label'],
        ];
    }

    /**
     * @dataProvider labelDataProvider
     */
    public function testLabel($translated, $name, $label, $expectedLabel)
    {
        $container = $this->baseGroupedMapper
            ->getAdmin()
            ->getConfigurationPool()
            ->getContainer();

        $container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnValue($translated));

        $options = [];

        if (null !== $label) {
            $options['label'] = $label;
        }

        $this->baseGroupedMapper->with($name, $options);

        $this->assertSame($translated ? 'label_default' : 'default', $this->tabs['default']['label']);
        $this->assertSame($expectedLabel, $this->groups[$name]['label']);
    }

    public function getTabs()
    {
        return $this->tabs;
    }

    public function setTabs($tabs)
    {
        $this->tabs = $tabs;
    }

    public function getTestGroups()
    {
        return $this->groups;
    }

    public function setTestGroups($groups)
    {
        $this->groups = $groups;
    }
}
