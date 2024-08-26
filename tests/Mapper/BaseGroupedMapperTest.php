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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Tests\Fixtures\Mapper\AbstractDummyGroupedMapper;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Symfony\Component\DependencyInjection\Container;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class BaseGroupedMapperTest extends TestCase
{
    /**
     * @var AbstractDummyGroupedMapper&MockObject
     */
    protected $baseGroupedMapper;

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $tabs;

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $groups;

    protected function setUp(): void
    {
        $admin = $this->createMock(AbstractAdmin::class);

        $labelStrategy = $this->createMock(LabelTranslatorStrategyInterface::class);
        $labelStrategy
            ->method('getLabel')
            ->willReturnCallback(static fn (string $label): string => \sprintf('label_%s', strtolower($label)));

        $admin->setLabelTranslatorStrategy($labelStrategy);

        $container = new Container();
        $container->setParameter('sonata.admin.configuration.translate_group_label', '');
        $configurationPool = new Pool($container);

        $admin->setConfigurationPool($configurationPool);

        $this->baseGroupedMapper = $this->getMockForAbstractClass(
            AbstractDummyGroupedMapper::class,
            [$admin]
        );

        $this->tabs = [];
        $this->groups = [];

        $this->baseGroupedMapper
            ->method('getTabs')
            ->willReturnCallback(fn (): array => $this->getTabs());

        $this->baseGroupedMapper
            ->method('setTabs')
            ->willReturnCallback(function (array $tabs): void {
                $this->setTabs($tabs);
            });

        $this->baseGroupedMapper
            ->method('getGroups')
            ->willReturnCallback(fn (): array => $this->getTestGroups());

        $this->baseGroupedMapper
            ->method('setGroups')
            ->willReturnCallback(function (array $groups): void {
                $this->setTestGroups($groups);
            });
    }

    public function testWith(): void
    {
        static::assertCount(0, $this->tabs);
        static::assertCount(0, $this->groups);
        static::assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->with('fooGroup'));
        static::assertCount(1, $this->tabs);
        static::assertCount(1, $this->groups);
    }

    public function testEnd(): void
    {
        static::assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->with('fooGroup'));
    }

    public function testTab(): void
    {
        static::assertCount(0, $this->tabs);
        static::assertCount(0, $this->groups);
        static::assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->tab('fooTab'));
        static::assertCount(1, $this->tabs);
        static::assertCount(0, $this->groups);
    }

    public function testTab2(): void
    {
        static::assertCount(0, $this->tabs);
        static::assertCount(0, $this->groups);
        static::assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->with('fooTab', ['tab' => true]));
        static::assertCount(1, $this->tabs);
        static::assertCount(0, $this->groups);
    }

    public function testRemoveGroup(): void
    {
        static::assertCount(0, $this->tabs);
        static::assertCount(0, $this->groups);

        $this->baseGroupedMapper
            ->tab('fooTab1')
            ->with('fooGroup1')
            ->add('field1', 'name1')
            ->end()
            ->end();

        static::assertCount(1, $this->tabs);
        static::assertCount(1, $this->groups);

        $this->baseGroupedMapper->expects(static::once())->method('remove')->with('field1');
        $this->baseGroupedMapper->removeGroup('fooGroup1', 'fooTab1');

        static::assertCount(1, $this->tabs);
        static::assertCount(0, $this->groups);
    }

    public function testRemoveTab(): void
    {
        static::assertCount(0, $this->tabs);
        static::assertCount(0, $this->groups);

        $this->baseGroupedMapper
            ->tab('fooTab1')
            ->with('fooGroup1')
            ->add('field1', 'name1')
            ->end()
            ->end();

        static::assertCount(1, $this->tabs);
        static::assertCount(1, $this->groups);

        $this->baseGroupedMapper->expects(static::once())->method('remove')->with('field1');
        $this->baseGroupedMapper->removeTab('fooTab1');

        static::assertCount(0, $this->tabs);
        static::assertCount(0, $this->groups);
    }

    public function testFluidInterface(): void
    {
        static::assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->tab('fooTab')->with('fooGroup1')->end()->with('fooGroup2')->end()->with('fooGroup3')->end()->end()->tab('barTab')->with('barGroup1')->end()->with('barGroup2')->end()->with('barGroup3')->end()->end());
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
        static::assertFalse($this->baseGroupedMapper->hasOpenTab(), '->hasOpenTab() returns false when there are no tabs');

        $this->baseGroupedMapper->tab('fooTab');
        static::assertTrue($this->baseGroupedMapper->hasOpenTab(), '->hasOpenTab() returns true when there is an open tab');

        $this->baseGroupedMapper->end();
        static::assertFalse($this->baseGroupedMapper->hasOpenTab(), '->hasOpenTab() returns false when all tabs are closed');
    }

    public function testIfTrueApply(): void
    {
        $this->baseGroupedMapper->ifTrue(true)->tab('fooTab')->ifEnd();
        static::assertTrue($this->baseGroupedMapper->hasOpenTab());
    }

    public function testIfTrueNotApply(): void
    {
        $this->baseGroupedMapper->ifTrue(false)->tab('fooTab')->ifEnd();
        static::assertFalse($this->baseGroupedMapper->hasOpenTab());
    }

    public function testIfFalseApply(): void
    {
        $this->baseGroupedMapper->ifFalse(false)->tab('fooTab')->ifEnd();
        static::assertTrue($this->baseGroupedMapper->hasOpenTab());
    }

    public function testIfFalseNotApply(): void
    {
        $this->baseGroupedMapper->ifFalse(true)->tab('fooTab')->ifEnd();
        static::assertFalse($this->baseGroupedMapper->hasOpenTab());
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

    /**
     * @phpstan-return iterable<array{string, string, string|null, string}>
     */
    public function provideLabelCases(): iterable
    {
        yield 'nominal use case not translated' => ['label_default', 'fooGroup1', null, 'label_foogroup1'];
        yield 'nominal use case translated' => ['label_default', 'fooGroup1', null, 'label_foogroup1'];
        yield 'custom label not translated' => ['label_default', 'fooGroup1', 'custom_label', 'custom_label'];
        yield 'custom label translated' => ['label_default', 'fooGroup1', 'custom_label', 'custom_label'];
    }

    /**
     * @dataProvider provideLabelCases
     */
    public function testLabel(string $translated, string $name, ?string $label, string $expectedLabel): void
    {
        $options = [];

        if (null !== $label) {
            $options['label'] = $label;
        }

        $this->baseGroupedMapper->with($name, $options);

        static::assertSame($translated, $this->tabs['default']['label']);
        static::assertSame($expectedLabel, $this->groups[$name]['label']);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getTabs(): array
    {
        return $this->tabs;
    }

    /**
     * @param array<string, array<string, mixed>> $tabs
     */
    public function setTabs(array $tabs): void
    {
        $this->tabs = $tabs;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getTestGroups(): array
    {
        return $this->groups;
    }

    /**
     * @param array<string, array<string, mixed>> $groups
     */
    public function setTestGroups(array $groups): void
    {
        $this->groups = $groups;
    }
}
