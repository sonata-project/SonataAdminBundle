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

use PHPUnit\Framework\MockObject\MockObject;
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
final class ListMapperTest extends TestCase
{
    private const DEFAULT_GRANTED_ROLE = 'ROLE_ADMIN_BAZ';

    /**
     * @var ListMapper<object>
     */
    private ListMapper $listMapper;

    /**
     * @var FieldDescriptionCollection<FieldDescriptionInterface>
     */
    private FieldDescriptionCollection $fieldDescriptionCollection;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private AdminInterface $admin;

    protected function setUp(): void
    {
        $listBuilder = $this->createMock(ListBuilderInterface::class);
        $this->fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin = $this->createMock(AdminInterface::class);

        $listBuilder
            ->method('addField')
            ->willReturnCallback(static function (
                FieldDescriptionCollection $list,
                ?string $type,
                FieldDescriptionInterface $fieldDescription,
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

        $this->admin->method('getLabelTranslatorStrategy')->willReturn($labelTranslatorStrategy);

        $this->admin
            ->method('isGranted')
            ->willReturnCallback(static fn (string $name, ?object $object = null): bool => self::DEFAULT_GRANTED_ROLE === $name);

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

    public function testAddOptionIdentifierWithWrongValue(): void
    {
        static::assertFalse($this->listMapper->has('fooName'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^Value for "identifier" option must be boolean, .+ given.$/');

        $this->listMapper->add('fooName', null, ['identifier' => 1]);
    }

    public function testAdd(): void
    {
        $this->listMapper->add('fooName');
        $this->listMapper->add('fooNameLabelBar', null, ['label' => 'Foo Bar']);
        $this->listMapper->add('fooNameLabelEmpty', null, ['label' => '']);

        static::assertTrue($this->listMapper->has('fooName'));

        $fieldDescription = $this->listMapper->get('fooName');
        $fieldLabelBar = $this->listMapper->get('fooNameLabelBar');
        $fieldLabelFalse = $this->listMapper->get('fooNameLabelEmpty');

        static::assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        static::assertSame('fooName', $fieldDescription->getName());
        static::assertSame('fooName', $fieldDescription->getOption('label'));
        static::assertSame('Foo Bar', $fieldLabelBar->getOption('label'));
        static::assertEmpty($fieldLabelFalse->getOption('label'));
    }

    public function testAddViewInlineAction(): void
    {
        static::assertFalse($this->listMapper->has(ListMapper::NAME_ACTIONS));
        $this->listMapper->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, ['actions' => ['show' => []]]);

        static::assertTrue($this->listMapper->has(ListMapper::NAME_ACTIONS));

        $fieldDescription = $this->listMapper->get(ListMapper::NAME_ACTIONS);

        static::assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        static::assertSame(ListMapper::NAME_ACTIONS, $fieldDescription->getName());

        $actions = $fieldDescription->getOption('actions');
        static::assertIsArray($actions);
        static::assertCount(1, $actions);
        static::assertSame(['show' => []], $actions);
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

    public function testAutoAddVirtualOption(): void
    {
        foreach (['actions', 'batch', 'select'] as $type) {
            $this->listMapper->add(\sprintf('_%s', $type), $type);
        }

        foreach ($this->fieldDescriptionCollection->getElements() as $field) {
            static::assertTrue(
                $field->getOption('virtual_field', false),
                \sprintf('Failed asserting that FieldDescription with name "%s" is tagged with virtual flag.', $field->getName())
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
                'sort_parent_association_mappings' => [['fooSortParentAssociationMapping' => null]],
                'sort_field_mapping' => ['fooSortFieldMapping' => null],
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
        static::assertSame([['fooSortParentAssociationMapping' => null]], $fieldManualSort->getOption('sort_parent_association_mappings'));
        static::assertSame(['fooSortFieldMapping' => null], $fieldManualSort->getOption('sort_field_mapping'));
    }

    public function testCallableAssociationPropertyCannotBeSortable(): void
    {
        $this->listMapper->add(
            'fooNameNotSortable',
            null,
            [
                'associated_property' => static fn (object $value): string => $value instanceof \Stringable ? (string) $value : '',
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
            $field->getOption('virtual_field', false),
            'Failed asserting that FieldDescription with name "'.$field->getName().'" is tagged with virtual flag.'
        );
    }
}
