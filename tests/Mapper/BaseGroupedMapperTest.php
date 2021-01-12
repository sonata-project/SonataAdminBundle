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
use Symfony\Component\DependencyInjection\Container;

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

    protected function setUp(): void
    {
        $admin = $this->getMockBuilder(AbstractAdmin::class)
            ->disableOriginalConstructor()
            ->getMock();

        $labelStrategy = $this->createMock(LabelTranslatorStrategyInterface::class);
        $labelStrategy
            ->method('getLabel')
            ->willReturnCallback(static function (string $label): string {
                return sprintf('label_%s', strtolower($label));
            });

        $admin
            ->method('getLabelTranslatorStrategy')
            ->willReturn($labelStrategy);

        $container = new Container();
        $container->setParameter('sonata.admin.configuration.translate_group_label', '');
        $configurationPool = new Pool($container);

        $admin
            ->method('getConfigurationPool')
            ->willReturn($configurationPool);

        $builder = $this->getMockForAbstractClass(BuilderInterface::class);

        $this->baseGroupedMapper = $this->getMockForAbstractClass(
            AbstractDummyGroupedMapper::class,
            [$builder, $admin]
        );

        $this->tabs = [];
        $this->groups = [];

        $this->baseGroupedMapper
            ->method('getTabs')
            ->willReturnCallback(function () {
                return $this->getTabs();
            });

        $this->baseGroupedMapper
            ->method('setTabs')
            ->willReturnCallback(function (array $tabs): void {
                $this->setTabs($tabs);
            });

        $this->baseGroupedMapper
            ->method('getGroups')
            ->willReturnCallback(function () {
                return $this->getTestGroups();
            });

        $this->baseGroupedMapper
            ->method('setGroups')
            ->willReturnCallback(function (array $groups): void {
                $this->setTestGroups($groups);
            });
    }

    public function testWith(): void
    {
        $this->assertCount(0, $this->tabs);
        $this->assertCount(0, $this->groups);
        $this->assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->with('fooGroup'));
        $this->assertCount(1, $this->tabs);
        $this->assertCount(1, $this->groups);
    }

    public function testEnd(): void
    {
        $this->assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->with('fooGroup'));
    }

    public function testTab(): void
    {
        $this->assertCount(0, $this->tabs);
        $this->assertCount(0, $this->groups);
        $this->assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->tab('fooTab'));
        $this->assertCount(1, $this->tabs);
        $this->assertCount(0, $this->groups);
    }

    public function testTab2(): void
    {
        $this->assertCount(0, $this->tabs);
        $this->assertCount(0, $this->groups);
        $this->assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->with('fooTab', ['tab' => true]));
        $this->assertCount(1, $this->tabs);
        $this->assertCount(0, $this->groups);
    }

    public function testFluidInterface(): void
    {
        $this->assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->tab('fooTab')->with('fooGroup1')->end()->with('fooGroup2')->end()->with('fooGroup3')->end()->end()->tab('barTab')->with('barGroup1')->end()->with('barGroup2')->end()->with('barGroup3')->end()->end());
    }

    public function testGroupNotClosedException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('You should close previous group "fooGroup1" with end() before adding new tab "fooGroup2".');

        $this->baseGroupedMapper->with('fooGroup1');
        $this->baseGroupedMapper->with('fooGroup2');
    }

    public function testGroupInTabException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('New tab was added automatically when you have added field or group. You should close current tab before adding new one OR add tabs before adding groups and fields.');

        $this->baseGroupedMapper->with('fooGroup');
        $this->baseGroupedMapper->tab('fooTab');
    }

    public function testTabInTabException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('You should close previous tab "fooTab" with end() before adding new tab "barTab".');

        $this->baseGroupedMapper->tab('fooTab');
        $this->baseGroupedMapper->tab('barTab');
    }

    public function testHasOpenTab(): void
    {
        $this->assertFalse($this->baseGroupedMapper->hasOpenTab(), '->hasOpenTab() returns false when there are no tabs');

        $this->baseGroupedMapper->tab('fooTab');
        $this->assertTrue($this->baseGroupedMapper->hasOpenTab(), '->hasOpenTab() returns true when there is an open tab');

        $this->baseGroupedMapper->end();
        $this->assertFalse($this->baseGroupedMapper->hasOpenTab(), '->hasOpenTab() returns false when all tabs are closed');
    }

    public function testIfTrueApply(): void
    {
        $this->baseGroupedMapper->ifTrue(true)->tab('fooTab')->ifEnd();
        $this->assertTrue($this->baseGroupedMapper->hasOpenTab());
    }

    public function testIfTrueNotApply(): void
    {
        $this->baseGroupedMapper->ifTrue(false)->tab('fooTab')->ifEnd();
        $this->assertFalse($this->baseGroupedMapper->hasOpenTab());
    }

    public function testIfFalseApply(): void
    {
        $this->baseGroupedMapper->ifFalse(false)->tab('fooTab')->ifEnd();
        $this->assertTrue($this->baseGroupedMapper->hasOpenTab());
    }

    public function testIfFalseNotApply(): void
    {
        $this->baseGroupedMapper->ifFalse(true)->tab('fooTab')->ifEnd();
        $this->assertFalse($this->baseGroupedMapper->hasOpenTab());
    }

    public function testEndException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No open tabs or groups, you cannot use end()');

        $this->baseGroupedMapper->end();
    }

    public function testIfEndException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No open ifTrue() or ifFalse(), you cannot use ifEnd()');

        $this->baseGroupedMapper->ifEnd();
    }

    public function labelDataProvider(): array
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
    public function testLabel(bool $translated, string $name, ?string $label, string $expectedLabel): void
    {
        // NEXT_MAJOR: Remove $container variable and the call to setParameter.
        $container = $this->baseGroupedMapper
            ->getAdmin()
            ->getConfigurationPool()
            ->getContainer('sonata_deprecation_mute');

        $container->setParameter('sonata.admin.configuration.translate_group_label', $translated);

        $options = [];

        if (null !== $label) {
            $options['label'] = $label;
        }

        $this->baseGroupedMapper->with($name, $options);

        $this->assertSame($translated ? 'label_default' : 'default', $this->tabs['default']['label']);
        $this->assertSame($expectedLabel, $this->groups[$name]['label']);
    }

    public function getTabs(): array
    {
        return $this->tabs;
    }

    public function setTabs(array $tabs): void
    {
        $this->tabs = $tabs;
    }

    public function getTestGroups(): array
    {
        return $this->groups;
    }

    public function setTestGroups(array $groups): void
    {
        $this->groups = $groups;
    }
}
