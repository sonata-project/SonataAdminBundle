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
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\BaseFieldDescription;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ListMapperTest extends TestCase
{
    private const DEFAULT_GRANTED_ROLE = 'ROLE_ADMIN_BAZ';

    /**
     * @var ListMapper
     */
    private $listMapper;

    /**
     * @var FieldDescriptionCollection
     */
    private $fieldDescriptionCollection;

    /**
     * @var AdminInterface
     */
    private $admin;

    protected function setUp(): void
    {
        $listBuilder = $this->createMock(ListBuilderInterface::class);
        $this->fieldDescriptionCollection = new FieldDescriptionCollection();

        // NEXT_MAJOR: Change to $this->createStub(AdminInterface::class).
        $this->admin = $this->getMockBuilder(AdminInterface::class)
            ->addMethods(['createFieldDescription', 'hasAccess'])
            ->getMockForAbstractClass();

        $listBuilder
            ->method('addField')
            ->willReturnCallback(static function (
                FieldDescriptionCollection $list,
                ?string $type,
                BaseFieldDescription $fieldDescription
            ): void {
                $fieldDescription->setType($type);
                $list->add($fieldDescription);
            });

        $this->admin
            ->method('createFieldDescription')
            ->willReturnCallback(function (string $name, array $options = []): FieldDescriptionInterface {
                $fieldDescription = $this->getMockForAbstractClass(BaseFieldDescription::class, [$name, []]);
                $fieldDescription->setOptions($options);

                return $fieldDescription;
            });

        $labelTranslatorStrategy = new NoopLabelTranslatorStrategy();

        $this->admin
            ->method('getLabelTranslatorStrategy')
            ->willReturn($labelTranslatorStrategy);

        $this->admin
            ->method('isGranted')
            ->willReturnCallback(static function (string $name, ?object $object = null): bool {
                return self::DEFAULT_GRANTED_ROLE === $name;
            });

        $this->listMapper = new ListMapper($listBuilder, $this->fieldDescriptionCollection, $this->admin);
    }

    public function testGet(): void
    {
        static::assertFalse($this->listMapper->has('fooName'));

        $this->listMapper->add('fooName');
        static::assertInstanceOf(FieldDescriptionInterface::class, $this->listMapper->get('fooName'));
    }

    public function testAddIdentifier(): void
    {
        static::assertFalse($this->listMapper->has('fooName'));

        $this->listMapper->addIdentifier('fooName');
        static::assertTrue($this->listMapper->has('fooName'));

        $fieldDescription = $this->listMapper->get('fooName');
        static::assertTrue($fieldDescription->getOption('identifier'));
    }

    public function testAddOptionIdentifier(): void
    {
        static::assertFalse($this->listMapper->has('fooName'));
        static::assertFalse($this->listMapper->has('barName'));
        static::assertFalse($this->listMapper->has('bazName'));

        $this->listMapper->add('barName');
        static::assertNull($this->listMapper->get('barName')->getOption('identifier'));
        $this->listMapper->add('fooName', null, ['identifier' => true]);
        static::assertTrue($this->listMapper->has('fooName'));
        static::assertTrue($this->listMapper->get('fooName')->getOption('identifier'));
        $this->listMapper->add('bazName', null, ['identifier' => false]);
        static::assertTrue($this->listMapper->has('bazName'));
        static::assertFalse($this->listMapper->get('bazName')->getOption('identifier'));
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation Passing a non boolean value for the "identifier" option is deprecated since sonata-project/admin-bundle 3.51 and will throw an exception in 4.0.
     *
     * @dataProvider getWrongIdentifierOptions
     */
    public function testAddOptionIdentifierWithDeprecatedValue(bool $expected, $value): void
    {
        static::assertFalse($this->listMapper->has('fooName'));
        $this->listMapper->add('fooName', null, ['identifier' => $value]);
        static::assertTrue($this->listMapper->has('fooName'));
        static::assertSame($expected, $this->listMapper->get('fooName')->getOption('identifier'));
    }

    /**
     * @dataProvider getWrongIdentifierOptions
     */
    public function testAddOptionIdentifierWithWrongValue(bool $expected, $value): void
    {
        // NEXT_MAJOR: Remove the following `markTestSkipped()` call and the `testAddOptionIdentifierWithDeprecatedValue()` test
        static::markTestSkipped('This test must be run in 4.0');

        static::assertFalse($this->listMapper->has('fooName'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^Value for "identifier" option must be boolean, .+ given.$/');

        $this->listMapper->add('fooName', null, ['identifier' => $value]);
    }

    public function getWrongIdentifierOptions(): iterable
    {
        return [
            [true, 1],
            [true, 'string'],
            [true, new \stdClass()],
            [true, [null]],
            [false, 0],
            [false, null],
            [false, ''],
            [false, '0'],
            [false, []],
        ];
    }

    public function testAdd(): void
    {
        $this->listMapper->add('fooName');
        $this->listMapper->add('fooNameLabelBar', null, ['label' => 'Foo Bar']);
        $this->listMapper->add('fooNameLabelFalse', null, ['label' => false]);

        static::assertTrue($this->listMapper->has('fooName'));

        $fieldDescription = $this->listMapper->get('fooName');
        $fieldLabelBar = $this->listMapper->get('fooNameLabelBar');
        $fieldLabelFalse = $this->listMapper->get('fooNameLabelFalse');

        static::assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        static::assertSame('fooName', $fieldDescription->getName());
        static::assertSame('fooName', $fieldDescription->getOption('label'));
        static::assertSame('Foo Bar', $fieldLabelBar->getOption('label'));
        static::assertFalse($fieldLabelFalse->getOption('label'));
    }

    /**
     * @group legacy
     */
    public function testLegacyAddViewInlineAction(): void
    {
        static::assertFalse($this->listMapper->has(ListMapper::NAME_ACTIONS));
        $this->listMapper->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, ['actions' => ['view' => []]]);

        static::assertTrue($this->listMapper->has(ListMapper::NAME_ACTIONS));

        $fieldDescription = $this->listMapper->get(ListMapper::NAME_ACTIONS);

        static::assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        static::assertSame(ListMapper::NAME_ACTIONS, $fieldDescription->getName());
        static::assertCount(1, $fieldDescription->getOption('actions'));
        static::assertSame(['show' => []], $fieldDescription->getOption('actions'));
    }

    public function testAddViewInlineAction(): void
    {
        static::assertFalse($this->listMapper->has(ListMapper::NAME_ACTIONS));
        $this->listMapper->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, ['actions' => ['show' => []]]);

        static::assertTrue($this->listMapper->has(ListMapper::NAME_ACTIONS));

        $fieldDescription = $this->listMapper->get(ListMapper::NAME_ACTIONS);

        static::assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        static::assertSame(ListMapper::NAME_ACTIONS, $fieldDescription->getName());
        static::assertCount(1, $fieldDescription->getOption('actions'));
        static::assertSame(['show' => []], $fieldDescription->getOption('actions'));
    }

    public function testAddRemove(): void
    {
        static::assertFalse($this->listMapper->has('fooName'));

        $this->listMapper->add('fooName');
        static::assertTrue($this->listMapper->has('fooName'));

        $this->listMapper->remove('fooName');
        static::assertFalse($this->listMapper->has('fooName'));
    }

    public function testAddDuplicateNameException(): void
    {
        $tmpNames = [];
        $this->admin
            ->method('hasListFieldDescription')
            ->willReturnCallback(static function (string $name) use (&$tmpNames): bool {
                if (isset($tmpNames[$name])) {
                    return true;
                }
                $tmpNames[$name] = $name;

                return false;
            });

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Duplicate field name "fooName" in list mapper. Names should be unique.');

        $this->listMapper->add('fooName');
        $this->listMapper->add('fooName');
    }

    public function testAddWrongTypeException(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Unknown field name in list mapper. Field name should be either of FieldDescriptionInterface interface or string.');

        $this->listMapper->add(12345);
    }

    public function testAutoAddVirtualOption(): void
    {
        foreach (['actions', 'batch', 'select'] as $type) {
            $this->listMapper->add(sprintf('_%s', $type), $type);
        }

        foreach ($this->fieldDescriptionCollection->getElements() as $field) {
            static::assertTrue(
                $field->isVirtual(),
                sprintf('Failed asserting that FieldDescription with type "%s" is tagged with virtual flag.', $field->getType())
            );
        }
    }

    public function testAutoSortOnAssociatedProperty(): void
    {
        $this->listMapper->add('fooName');
        $this->listMapper->add(
            'fooNameAutoSort',
            null,
            [
                'associated_property' => 'fooAssociatedProperty',
            ]
        );
        $this->listMapper->add(
            'fooNameManualSort',
            null,
            [
                'associated_property' => 'fooAssociatedProperty',
                'sortable' => false,
                'sort_parent_association_mappings' => 'fooSortParentAssociationMapping',
                'sort_field_mapping' => 'fooSortFieldMapping',
            ]
        );

        $field = $this->listMapper->get('fooName');
        $fieldAutoSort = $this->listMapper->get('fooNameAutoSort');
        $fieldManualSort = $this->listMapper->get('fooNameManualSort');

        static::assertNull($field->getOption('associated_property'));
        static::assertNull($field->getOption('sortable'));
        static::assertNull($field->getOption('sort_parent_association_mappings'));
        static::assertNull($field->getOption('sort_field_mapping'));

        static::assertSame('fooAssociatedProperty', $fieldAutoSort->getOption('associated_property'));
        static::assertTrue($fieldAutoSort->getOption('sortable'));
        static::assertSame([['fieldName' => $fieldAutoSort->getName()]], $fieldAutoSort->getOption('sort_parent_association_mappings'));
        static::assertSame(['fieldName' => $fieldAutoSort->getOption('associated_property')], $fieldAutoSort->getOption('sort_field_mapping'));

        static::assertSame('fooAssociatedProperty', $fieldManualSort->getOption('associated_property'));
        static::assertFalse($fieldManualSort->getOption('sortable'));
        static::assertSame('fooSortParentAssociationMapping', $fieldManualSort->getOption('sort_parent_association_mappings'));
        static::assertSame('fooSortFieldMapping', $fieldManualSort->getOption('sort_field_mapping'));
    }

    public function testCallableAssociationPropertyCannotBeSortable(): void
    {
        $this->listMapper->add(
            'fooNameNotSortable',
            null,
            [
                'associated_property' => static function ($value) {
                    return (string) $value;
                },
            ]
        );
        $this->listMapper->add(
            'fooNameSortable',
            null,
            [
                'associated_property' => 'fooProperty',
            ]
        );

        $fieldSortable = $this->listMapper->get('fooNameSortable');
        $fieldNotSortable = $this->listMapper->get('fooNameNotSortable');

        static::assertTrue($fieldSortable->getOption('sortable'));
        static::assertFalse($fieldNotSortable->getOption('sortable'));
    }

    public function testKeys(): void
    {
        $this->listMapper->add('fooName1');
        $this->listMapper->add('fooName2');

        static::assertSame(['fooName1', 'fooName2'], $this->listMapper->keys());
    }

    public function testReorder(): void
    {
        $this->listMapper->add('fooName1');
        $this->listMapper->add('fooName2');
        $this->listMapper->add('fooName3');
        $this->listMapper->add('fooName4');

        static::assertSame([
            'fooName1',
            'fooName2',
            'fooName3',
            'fooName4',
        ], array_keys($this->fieldDescriptionCollection->getElements()));

        $this->listMapper->reorder(['fooName3', 'fooName2', 'fooName1', 'fooName4']);

        static::assertSame([
            'fooName3',
            'fooName2',
            'fooName1',
            'fooName4',
        ], array_keys($this->fieldDescriptionCollection->getElements()));
    }

    public function testAddOptionRole(): void
    {
        $this->listMapper->add('bar', 'bar');

        static::assertTrue($this->listMapper->has('bar'));

        $this->listMapper->add('quux', 'bar', ['role' => 'ROLE_QUX']);

        static::assertTrue($this->listMapper->has('bar'));
        static::assertFalse($this->listMapper->has('quux'));

        $this->listMapper
            ->add('foobar', 'bar', ['role' => self::DEFAULT_GRANTED_ROLE])
            ->add('foo', 'bar', ['role' => 'ROLE_QUX'])
            ->add('baz', 'bar');

        static::assertTrue($this->listMapper->has('foobar'));
        static::assertFalse($this->listMapper->has('foo'));
        static::assertTrue($this->listMapper->has('baz'));
    }

    public function testTypeGuessActionField(): void
    {
        $this->listMapper->add(ListMapper::NAME_ACTIONS);

        $field = $this->fieldDescriptionCollection->get(ListMapper::NAME_ACTIONS);

        static::assertTrue(
            $field->isVirtual(),
            'Failed asserting that FieldDescription with name "'.$field->getName().'" is tagged with virtual flag.'
        );
    }
}
