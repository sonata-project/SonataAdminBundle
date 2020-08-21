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
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BaseFieldDescription;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionCollectionInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Model\ModelManagerInterface;
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
     * @var FieldDescriptionCollectionInterface
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
        $this->admin = $this->createMock(AbstractAdmin::class);

        $listBuilder
            ->method('addField')
            ->willReturnCallback(static function (
                FieldDescriptionCollection $list,
                ?string $type,
                BaseFieldDescription $fieldDescription,
                AbstractAdmin $admin
            ): void {
                $fieldDescription->setType($type);
                $list->add($fieldDescription);
            });

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $modelManager
            ->method('getNewFieldDescriptionInstance')
            ->willReturnCallback(function (?string $class, string $name, array $options = []): BaseFieldDescription {
                $fieldDescription = $this->getFieldDescriptionMock();
                $fieldDescription->setName($name);
                $fieldDescription->setOptions($options);

                return $fieldDescription;
            });

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

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

    public function testFluidInterface(): void
    {
        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->assertSame($this->listMapper, $this->listMapper->add($fieldDescription));
        $this->assertSame($this->listMapper, $this->listMapper->remove('fooName'));
        $this->assertSame($this->listMapper, $this->listMapper->reorder([]));
    }

    public function testGet(): void
    {
        $this->assertFalse($this->listMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->listMapper->add($fieldDescription);
        $this->assertSame($fieldDescription, $this->listMapper->get('fooName'));
    }

    public function testAddIdentifier(): void
    {
        $this->assertFalse($this->listMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->listMapper->addIdentifier($fieldDescription);
        $this->assertTrue($this->listMapper->has('fooName'));

        $fieldDescription = $this->listMapper->get('fooName');
        $this->assertTrue($fieldDescription->getOption('identifier'));
    }

    public function testAddOptionIdentifier(): void
    {
        $this->assertFalse($this->listMapper->has('fooName'));
        $this->assertFalse($this->listMapper->has('barName'));
        $this->assertFalse($this->listMapper->has('bazName'));

        $this->listMapper->add('barName');
        $this->assertNull($this->listMapper->get('barName')->getOption('identifier'));
        $this->listMapper->add('fooName', null, ['identifier' => true]);
        $this->assertTrue($this->listMapper->has('fooName'));
        $this->assertTrue($this->listMapper->get('fooName')->getOption('identifier'));
        $this->listMapper->add('bazName', null, ['identifier' => false]);
        $this->assertTrue($this->listMapper->has('bazName'));
        $this->assertFalse($this->listMapper->get('bazName')->getOption('identifier'));
    }

    /**
     * @dataProvider getWrongIdentifierOptions
     */
    public function testAddOptionIdentifierWithWrongValue(bool $expected, $value): void
    {
        $this->assertFalse($this->listMapper->has('fooName'));

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
        $this->listMapper->add('fooNameLabelEmpty', null, ['label' => '']);

        $this->assertTrue($this->listMapper->has('fooName'));

        $fieldDescription = $this->listMapper->get('fooName');
        $fieldLabelBar = $this->listMapper->get('fooNameLabelBar');
        $fieldLabelFalse = $this->listMapper->get('fooNameLabelEmpty');

        $this->assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        $this->assertSame('fooName', $fieldDescription->getName());
        $this->assertSame('fooName', $fieldDescription->getOption('label'));
        $this->assertSame('Foo Bar', $fieldLabelBar->getOption('label'));
        $this->assertEmpty($fieldLabelFalse->getOption('label'));
    }

    public function testAddViewInlineAction(): void
    {
        $this->assertFalse($this->listMapper->has('_action'));
        $this->listMapper->add('_action', 'actions', ['actions' => ['show' => []]]);

        $this->assertTrue($this->listMapper->has('_action'));

        $fieldDescription = $this->listMapper->get('_action');

        $this->assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        $this->assertSame('_action', $fieldDescription->getName());
        $this->assertCount(1, $fieldDescription->getOption('actions'));
        $this->assertSame(['show' => []], $fieldDescription->getOption('actions'));
    }

    public function testAddRemove(): void
    {
        $this->assertFalse($this->listMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->listMapper->add($fieldDescription);
        $this->assertTrue($this->listMapper->has('fooName'));

        $this->listMapper->remove('fooName');
        $this->assertFalse($this->listMapper->has('fooName'));
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
            $this->assertTrue(
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

        $this->assertNull($field->getOption('associated_property'));
        $this->assertNull($field->getOption('sortable'));
        $this->assertNull($field->getOption('sort_parent_association_mappings'));
        $this->assertNull($field->getOption('sort_field_mapping'));

        $this->assertSame('fooAssociatedProperty', $fieldAutoSort->getOption('associated_property'));
        $this->assertTrue($fieldAutoSort->getOption('sortable'));
        $this->assertSame([['fieldName' => $fieldAutoSort->getName()]], $fieldAutoSort->getOption('sort_parent_association_mappings'));
        $this->assertSame(['fieldName' => $fieldAutoSort->getOption('associated_property')], $fieldAutoSort->getOption('sort_field_mapping'));

        $this->assertSame('fooAssociatedProperty', $fieldManualSort->getOption('associated_property'));
        $this->assertFalse($fieldManualSort->getOption('sortable'));
        $this->assertSame('fooSortParentAssociationMapping', $fieldManualSort->getOption('sort_parent_association_mappings'));
        $this->assertSame('fooSortFieldMapping', $fieldManualSort->getOption('sort_field_mapping'));
    }

    public function testKeys(): void
    {
        $fieldDescription1 = $this->getFieldDescriptionMock('fooName1', 'fooLabel1');
        $fieldDescription2 = $this->getFieldDescriptionMock('fooName2', 'fooLabel2');

        $this->listMapper->add($fieldDescription1);
        $this->listMapper->add($fieldDescription2);

        $this->assertSame(['fooName1', 'fooName2'], $this->listMapper->keys());
    }

    public function testReorder(): void
    {
        $fieldDescription1 = $this->getFieldDescriptionMock('fooName1', 'fooLabel1');
        $fieldDescription2 = $this->getFieldDescriptionMock('fooName2', 'fooLabel2');
        $fieldDescription3 = $this->getFieldDescriptionMock('fooName3', 'fooLabel3');
        $fieldDescription4 = $this->getFieldDescriptionMock('fooName4', 'fooLabel4');

        $this->listMapper->add($fieldDescription1);
        $this->listMapper->add($fieldDescription2);
        $this->listMapper->add($fieldDescription3);
        $this->listMapper->add($fieldDescription4);

        $this->assertSame([
            'fooName1' => $fieldDescription1,
            'fooName2' => $fieldDescription2,
            'fooName3' => $fieldDescription3,
            'fooName4' => $fieldDescription4,
        ], $this->fieldDescriptionCollection->getElements());

        $this->listMapper->reorder(['fooName3', 'fooName2', 'fooName1', 'fooName4']);

        // print_r is used to compare order of items in associative arrays
        $this->assertSame(print_r([
            'fooName3' => $fieldDescription3,
            'fooName2' => $fieldDescription2,
            'fooName1' => $fieldDescription1,
            'fooName4' => $fieldDescription4,
        ], true), print_r($this->fieldDescriptionCollection->getElements(), true));
    }

    public function testAddOptionRole(): void
    {
        $this->listMapper->add('bar', 'bar');

        $this->assertTrue($this->listMapper->has('bar'));

        $this->listMapper->add('quux', 'bar', ['role' => 'ROLE_QUX']);

        $this->assertTrue($this->listMapper->has('bar'));
        $this->assertFalse($this->listMapper->has('quux'));

        $this->listMapper
            ->add('foobar', 'bar', ['role' => self::DEFAULT_GRANTED_ROLE])
            ->add('foo', 'bar', ['role' => 'ROLE_QUX'])
            ->add('baz', 'bar');

        $this->assertTrue($this->listMapper->has('foobar'));
        $this->assertFalse($this->listMapper->has('foo'));
        $this->assertTrue($this->listMapper->has('baz'));
    }

    public function testTypeGuessActionField(): void
    {
        $this->listMapper->add('_action', null);

        $field = $this->fieldDescriptionCollection->get('_action');

        $this->assertTrue(
            $field->isVirtual(),
            'Failed asserting that FieldDescription with name "'.$field->getName().'" is tagged with virtual flag.'
        );
    }

    private function getFieldDescriptionMock(?string $name = null, ?string $label = null): BaseFieldDescription
    {
        $fieldDescription = $this->getMockForAbstractClass(BaseFieldDescription::class);

        if (null !== $name) {
            $fieldDescription->setName($name);
        }

        if (null !== $label) {
            $fieldDescription->setOption('label', $label);
        }

        return $fieldDescription;
    }
}
