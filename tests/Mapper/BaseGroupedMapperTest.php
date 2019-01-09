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

namespace Sonata\AdminBundle\Tests\Mapper;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Builder\BuilderInterface;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;
use Sonata\AdminBundle\Tests\Fixtures\Admin\AbstractDummyGroupedMapper;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Test for BaseGroupedMapper.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class BaseGroupedMapperTest extends TestCase
{
    /**
     * @var BaseGroupedMapper
     */
    protected $baseGroupedMapper;

    private $tabs;
    private $groups;

    public function setUp()
    {
        $admin = $this->getMockBuilder(AbstractAdmin::class)
            ->disableOriginalConstructor()
            ->getMock();

        $labelStrategy = $this->createMock(LabelTranslatorStrategyInterface::class);
        $labelStrategy->expects($this->any())
            ->method('getLabel')
            ->will($this->returnCallback(function ($label) {
                return 'label_'.strtolower($label);
            }));

        $admin->expects($this->any())
            ->method('getLabelTranslatorStrategy')
            ->will($this->returnValue($labelStrategy));

        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $configurationPool = new Pool($container, 'myTitle', 'myLogoTitle');

        $admin->expects($this->any())
            ->method('getConfigurationPool')
            ->will($this->returnValue($configurationPool));

        $builder = $this->getMockForAbstractClass(BuilderInterface::class);

        $this->baseGroupedMapper = $this->getMockForAbstractClass(
            AbstractDummyGroupedMapper::class,
            [$builder, $admin]
        );

        $this->tabs = [];
        $this->groups = [];

        $this->baseGroupedMapper->expects($this->any())
            ->method('getTabs')
            ->will($this->returnCallback(function () {
                return $this->getTabs();
            }));

        $this->baseGroupedMapper->expects($this->any())
            ->method('setTabs')
            ->will($this->returnCallback(function (array $tabs) {
                $this->setTabs($tabs);
            }));

        $this->baseGroupedMapper->expects($this->any())
            ->method('getGroups')
            ->will($this->returnCallback(function () {
                return $this->getTestGroups();
            }));

        $this->baseGroupedMapper->expects($this->any())
            ->method('setGroups')
            ->will($this->returnCallback(function (array $groups) {
                $this->setTestGroups($groups);
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

    public function testGroupNotClosedException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You should close previous group "fooGroup1" with end() before adding new tab "fooGroup2".');

        $this->baseGroupedMapper->with('fooGroup1');
        $this->baseGroupedMapper->with('fooGroup2');
    }

    public function testGroupInTabException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('New tab was added automatically when you have added field or group. You should close current tab before adding new one OR add tabs before adding groups and fields.');

        $this->baseGroupedMapper->with('fooGroup');
        $this->baseGroupedMapper->tab('fooTab');
    }

    public function testTabInTabException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You should close previous tab "fooTab" with end() before adding new tab "barTab".');

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

    public function testEndException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No open tabs or groups, you cannot use end()');

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
