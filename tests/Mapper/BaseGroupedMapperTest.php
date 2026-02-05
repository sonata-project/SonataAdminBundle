<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Mapper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AbstractAdmin;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Tests\Fixtures\Mapper\AbstractDummyGroupedMapper;
use SensioLabs\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Symfony\Component\DependencyInjection\Container;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class BaseGroupedMapperTest extends TestCase
{
    private AbstractDummyGroupedMapper $baseGroupedMapper;

    protected function setUp(): void
    {
        $admin = $this->createMock(AbstractAdmin::class);

        $labelStrategy = $this->createMock(LabelTranslatorStrategyInterface::class);
        $labelStrategy
            ->method('getLabel')
            ->willReturnCallback(static fn (string $label): string => \sprintf('label_%s', strtolower($label)));

        $admin->setLabelTranslatorStrategy($labelStrategy);

        $container = new Container();
        $container->setParameter('sensiolabs.admin.configuration.translate_group_label', '');
        $configurationPool = new Pool($container);

        $admin->setConfigurationPool($configurationPool);

        $this->baseGroupedMapper = new class($admin) extends AbstractDummyGroupedMapper {
            /**
             * @var array<string, array<string, mixed>>
             */
            public array $groups = [];

            /**
             * @var array<string, array<string, mixed>>
             */
            public array $tabs = [];

            /**
             * @var string[]
             */
            public array $removed = [];

            /**
             * @return array<string, array<string, mixed>>
             */
            protected function getGroups(): array
            {
                return $this->groups;
            }

            /**
             * @return array<string, array<string, mixed>>
             */
            protected function getTabs(): array
            {
                return $this->tabs;
            }

            /**
             * @param array<string, array<string, mixed>> $groups
             */
            protected function setGroups(array $groups): void
            {
                $this->groups = $groups;
            }

            /**
             * @param array<string, array<string, mixed>> $tabs
             */
            protected function setTabs(array $tabs): void
            {
                $this->tabs = $tabs;
            }

            public function get(string $key)
            {
                return null;
            }

            public function has(string $key): bool
            {
                return false;
            }

            public function remove(string $key)
            {
                $this->removed[] = $key;

                return $this;
            }

            public function keys(): array
            {
                return [];
            }

            public function reorder(array $keys)
            {
                return $this;
            }
        };
    }

    public function testWith(): void
    {
        static::assertCount(0, $this->getTabs());
        static::assertCount(0, $this->getTestGroups());
        static::assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->with('fooGroup'));
        static::assertCount(1, $this->getTabs());
        static::assertCount(1, $this->getTestGroups());
    }

    public function testEnd(): void
    {
        static::assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->with('fooGroup'));
    }

    public function testTab(): void
    {
        static::assertCount(0, $this->getTabs());
        static::assertCount(0, $this->getTestGroups());
        static::assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->tab('fooTab'));
        static::assertCount(1, $this->getTabs());
        static::assertCount(0, $this->getTestGroups());
    }

    public function testTab2(): void
    {
        static::assertCount(0, $this->getTabs());
        static::assertCount(0, $this->getTestGroups());
        static::assertSame($this->baseGroupedMapper, $this->baseGroupedMapper->with('fooTab', ['tab' => true]));
        static::assertCount(1, $this->getTabs());
        static::assertCount(0, $this->getTestGroups());
    }

    public function testRemoveGroup(): void
    {
        static::assertCount(0, $this->getTabs());
        static::assertCount(0, $this->getTestGroups());

        $this->baseGroupedMapper
            ->tab('fooTab1')
            ->with('fooGroup1')
            ->add('field1', 'name1')
            ->end()
            ->end();

        static::assertCount(1, $this->getTabs());
        static::assertCount(1, $this->getTestGroups());

        $this->baseGroupedMapper->removeGroup('fooGroup1', 'fooTab1');
        /*
         * @phpstan-ignore property.notFound
         */
        static::assertSame(['field1'], $this->baseGroupedMapper->removed);

        static::assertCount(1, $this->getTabs());
        static::assertCount(0, $this->getTestGroups());
    }

    public function testRemoveTab(): void
    {
        static::assertCount(0, $this->getTabs());
        static::assertCount(0, $this->getTestGroups());

        $this->baseGroupedMapper
            ->tab('fooTab1')
            ->with('fooGroup1')
            ->add('field1', 'name1')
            ->end()
            ->end();

        static::assertCount(1, $this->getTabs());
        static::assertCount(1, $this->getTestGroups());

        $this->baseGroupedMapper->removeTab('fooTab1');
        /*
         * @phpstan-ignore property.notFound
         */
        static::assertSame(['field1'], $this->baseGroupedMapper->removed);

        static::assertCount(0, $this->getTabs());
        static::assertCount(0, $this->getTestGroups());
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
    public static function provideLabelCases(): iterable
    {
        yield 'nominal use case not translated' => ['label_default', 'fooGroup1', null, 'label_foogroup1'];
        yield 'nominal use case translated' => ['label_default', 'fooGroup1', null, 'label_foogroup1'];
        yield 'custom label not translated' => ['label_default', 'fooGroup1', 'custom_label', 'custom_label'];
        yield 'custom label translated' => ['label_default', 'fooGroup1', 'custom_label', 'custom_label'];
    }

    #[DataProvider('provideLabelCases')]
    public function testLabel(string $translated, string $name, ?string $label, string $expectedLabel): void
    {
        $options = [];

        if (null !== $label) {
            $options['label'] = $label;
        }

        $this->baseGroupedMapper->with($name, $options);

        static::assertSame($translated, $this->getTabs()['default']['label']);
        static::assertSame($expectedLabel, $this->getTestGroups()[$name]['label']);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getTabs(): array
    {
        /*
         * @phpstan-ignore property.notFound
         */
        return $this->baseGroupedMapper->tabs;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getTestGroups(): array
    {
        /*
         * @phpstan-ignore property.notFound
         */
        return $this->baseGroupedMapper->groups;
    }
}
