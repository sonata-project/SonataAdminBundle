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

namespace SensioLabs\AdminBundle\Tests\FieldDescription\ORM;

use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Exception\NoValueException;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;
use SensioLabs\AdminBundle\FieldDescription\ORM\FieldDescription;
use SensioLabs\AdminBundle\Tests\Fixtures\ORM\Entity\Enum\Suit;

final class FieldDescriptionTest extends TestCase
{
    public function testOptions(): void
    {
        $field = new FieldDescription('name', [
            'template' => 'foo',
            'type' => 'bar',
            'misc' => 'foobar',
        ]);

        // test method shortcut
        static::assertNull($field->getOption('template'));
        static::assertNull($field->getOption('type'));

        static::assertSame('foo', $field->getTemplate());
        static::assertSame('bar', $field->getType());

        // test the default value option
        static::assertSame('default', $field->getOption('template', 'default'));

        // test the merge options
        $field->setOption('array', ['key1' => 'val1']);
        $field->mergeOption('array', ['key1' => 'key_1', 'key2' => 'key_2']);

        static::assertSame(['key1' => 'key_1', 'key2' => 'key_2'], $field->getOption('array'));

        $field->mergeOption('non_existent', ['key1' => 'key_1', 'key2' => 'key_2']);

        static::assertSame(['key1' => 'key_1', 'key2' => 'key_2'], $field->getOption('array'));

        $field->setOption('integer', 1);

        try {
            $field->mergeOption('integer', []);
            static::fail('no exception raised !!');
        } catch (\RuntimeException) {
        }

        $expected = [
            'misc' => 'foobar',
            'array' => [
                'key1' => 'key_1',
                'key2' => 'key_2',
            ],
            'non_existent' => [
                'key1' => 'key_1',
                'key2' => 'key_2',
            ],
            'integer' => 1,
        ];

        static::assertSame($expected, $field->getOptions());
    }

    public function testGetParent(): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $field = new FieldDescription('name');
        $field->setParent($adminMock);

        static::assertSame($adminMock, $field->getParent());
    }

    public function testGetAdmin(): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $field = new FieldDescription('name');
        $field->setAdmin($adminMock);

        static::assertSame($adminMock, $field->getAdmin());
    }

    public function testGetAssociationAdmin(): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock->expects(static::once())
            ->method('setParentFieldDescription')
            ->with(static::isInstanceOf(FieldDescriptionInterface::class));

        $field = new FieldDescription('name');
        $field->setAssociationAdmin($adminMock);

        static::assertSame($adminMock, $field->getAssociationAdmin());
    }

    public function testHasAssociationAdmin(): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock->expects(static::once())
            ->method('setParentFieldDescription')
            ->with(static::isInstanceOf(FieldDescriptionInterface::class));

        $field = new FieldDescription('name');

        static::assertFalse($field->hasAssociationAdmin());

        $field->setAssociationAdmin($adminMock);

        static::assertTrue($field->hasAssociationAdmin());
    }

    public function testSetFieldMapping(): void
    {
        $fieldMapping = ['type' => 'integer'];

        $field = new FieldDescription('position', [], $fieldMapping);

        static::assertSame('integer', $field->getType());
        static::assertSame('integer', $field->getMappingType());
        static::assertSame($fieldMapping, $field->getFieldMapping());
    }

    public function testSetAssociationMapping(): void
    {
        $associationMapping = ['type' => 'integer'];

        $field = new FieldDescription('name', [], [], $associationMapping);

        static::assertSame('integer', $field->getType());
        static::assertSame('integer', $field->getMappingType());
        static::assertSame($associationMapping, $field->getAssociationMapping());
    }

    public function testSetParentAssociationMappings(): void
    {
        $parentAssociationMappings = [['fieldName' => 'subObject']];

        $field = new FieldDescription('name', [], [], [], $parentAssociationMappings);

        static::assertSame($parentAssociationMappings, $field->getParentAssociationMappings());
    }

    public function testSetInvalidParentAssociationMappings(): void
    {
        $parentAssociationMappings = ['subObject'];

        $this->expectException(\InvalidArgumentException::class);
        new FieldDescription('name', [], [], [], $parentAssociationMappings);
    }

    public function testGetTargetModel(): void
    {
        $associationMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
            'targetEntity' => \stdClass::class,
        ];

        $field = new FieldDescription('position', [], [], $associationMapping);

        static::assertSame(\stdClass::class, $field->getTargetModel());
    }

    public function testIsIdentifierFromFieldMapping(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
            'id' => true,
        ];

        $field = new FieldDescription('position', [], $fieldMapping);

        static::assertTrue($field->isIdentifier());
    }

    public function testGetValue(): void
    {
        $object = new class {
            public function getFoo(): string
            {
                return 'myMethodValue';
            }
        };

        $field = new FieldDescription('name', ['accessor' => 'foo']);

        static::assertSame('myMethodValue', $field->getValue($object));
    }

    public function testGetValueWithParentAssociationMappings(): void
    {
        $subObject = new class {
            public function getFieldName(): string
            {
                return 'value';
            }
        };

        $parentObject = new class($subObject) {
            public function __construct(private object $subObject)
            {
            }

            public function getSubObject(): object
            {
                return $this->subObject;
            }
        };

        $field = new FieldDescription('name', [], [], [], [['fieldName' => 'subObject']], 'fieldName');

        static::assertSame('value', $field->getValue($parentObject));
    }

    public function testGetValueWhenCannotRetrieve(): void
    {
        $object = new class {
            public function myMethod(): string
            {
                return 'myMethodValue';
            }
        };

        $admin = static::createStub(AdminInterface::class);
        $field = new FieldDescription('name');
        $field->setAdmin($admin);

        $this->expectException(NoValueException::class);
        static::assertSame('myMethodValue', $field->getValue($object));
    }

    public function testGetValueForEmbeddedObject(): void
    {
        $subObject = new class {
            public function getMyMethod(): string
            {
                return 'myMethodValue';
            }
        };

        $parentObject = new class($subObject) {
            public function __construct(private object $subObject)
            {
            }

            public function getMyEmbeddedObject(): object
            {
                return $this->subObject;
            }
        };

        $field = new FieldDescription('myMethod', [], [], [], [], 'myEmbeddedObject.myMethod');

        static::assertSame('myMethodValue', $field->getValue($parentObject));
    }

    public function testGetValueForMultiLevelEmbeddedObject(): void
    {
        $subSubObject = new class {
            public function getMyMethod(): string
            {
                return 'myMethodValue';
            }
        };

        $subObject = new class($subSubObject) {
            public function __construct(private object $subObject)
            {
            }

            public function getChild(): object
            {
                return $this->subObject;
            }
        };

        $parentObject = new class($subObject) {
            public function __construct(private object $subObject)
            {
            }

            public function getMyEmbeddedObject(): object
            {
                return $this->subObject;
            }
        };

        $field = new FieldDescription('myMethod', [], [], [], [], 'myEmbeddedObject.child.myMethod');

        static::assertSame('myMethodValue', $field->getValue($parentObject));
    }

    public function testEnum(): void
    {
        $fieldMapping = ['type' => 'string', 'enumType' => Suit::class];

        $field = new FieldDescription('bar', [], $fieldMapping);

        static::assertSame('string', $field->getType());
        static::assertSame('enum', $field->getMappingType());
        static::assertSame($fieldMapping, $field->getFieldMapping());
    }

    #[DataProvider('provideDescribesSingleValuedAssociationCases')]
    public function testDescribesSingleValuedAssociation(string|int $mappingType, bool $expected): void
    {
        $fd = new FieldDescription('foo', [], [], [
            'fieldName' => 'foo',
            'type' => $mappingType,
        ]);
        static::assertSame($expected, $fd->describesSingleValuedAssociation());
    }

    /**
     * @phpstan-return iterable<array-key, array{0: string|int, 1: bool}>
     */
    public static function provideDescribesSingleValuedAssociationCases(): iterable
    {
        yield 'one to one' => [ClassMetadata::ONE_TO_ONE, true];
        yield 'many to one' => [ClassMetadata::MANY_TO_ONE, true];
        yield 'one to many' => [ClassMetadata::ONE_TO_MANY, false];
        yield 'many to many' => [ClassMetadata::MANY_TO_MANY, false];
        yield 'string' => ['string', false];
    }

    #[DataProvider('provideDescribesCollectionValuedAssociationCases')]
    public function testDescribesCollectionValuedAssociation(string|int $mappingType, bool $expected): void
    {
        $fd = new FieldDescription('foo', [], [], [
            'fieldName' => 'foo',
            'type' => $mappingType,
        ]);
        static::assertSame($expected, $fd->describesCollectionValuedAssociation());
    }

    /**
     * @phpstan-return iterable<array-key, array{0: string|int, 1: bool}>
     */
    public static function provideDescribesCollectionValuedAssociationCases(): iterable
    {
        yield 'one to one' => [ClassMetadata::ONE_TO_ONE, false];
        yield 'many to one' => [ClassMetadata::MANY_TO_ONE, false];
        yield 'one to many' => [ClassMetadata::ONE_TO_MANY, true];
        yield 'many to many' => [ClassMetadata::MANY_TO_MANY, true];
        yield 'string' => ['string', false];
    }
}
