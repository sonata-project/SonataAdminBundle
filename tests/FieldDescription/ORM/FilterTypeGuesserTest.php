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
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;
use SensioLabs\AdminBundle\Form\Type\Operator\EqualOperatorType;
use SensioLabs\AdminBundle\FieldDescription\ORM\FilterTypeGuesser;
use SensioLabs\AdminBundle\Filter\ORM\BooleanFilter;
use SensioLabs\AdminBundle\Filter\ORM\ChoiceFilter;
use SensioLabs\AdminBundle\Filter\ORM\DateFilter;
use SensioLabs\AdminBundle\Filter\ORM\DateTimeFilter;
use SensioLabs\AdminBundle\Filter\ORM\ModelFilter;
use SensioLabs\AdminBundle\Filter\ORM\NumberFilter;
use SensioLabs\AdminBundle\Filter\ORM\StringFilter;
use SensioLabs\AdminBundle\Filter\ORM\TimeFilter;
use SensioLabs\AdminBundle\Tests\Fixtures\ORM\Entity\Enum\Suit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Guess\Guess;

final class FilterTypeGuesserTest extends TestCase
{
    private FilterTypeGuesser $guesser;

    protected function setUp(): void
    {
        $this->guesser = new FilterTypeGuesser();
    }

    /**
     * @param array<string, mixed> $expectedOptions
     *
     * @phpstan-param class-string $expectedType
     * @phpstan-param array<string, mixed> $fieldMapping
     */
    #[DataProvider('provideGuessCases')]
    public function testGuess(
        int|string|null $mappingType,
        string $expectedType,
        array $expectedOptions,
        int $expectedConfidence,
        array $fieldMapping = [],
    ): void {
        $fieldDescription = static::createStub(FieldDescriptionInterface::class);
        $fieldDescription->method('getFieldName')->willReturn('foo');
        $fieldDescription->method('getMappingType')->willReturn($mappingType);
        $fieldDescription->method('getParentAssociationMappings')->willReturn([]);
        $fieldDescription->method('getTargetModel')->willReturn('Foo');
        $fieldDescription->method('getFieldMapping')->willReturn($fieldMapping);

        $guess = $this->guesser->guess($fieldDescription);

        static::assertSame($expectedType, $guess->getType());
        static::assertSame($expectedOptions, $guess->getOptions());
        static::assertSame($expectedConfidence, $guess->getConfidence());
    }

    /**
     * @phpstan-return iterable<array-key, array{0: int|string|null, 1: class-string, 2: array<string, mixed>, 3: int, 4?: array<string, mixed>}>
     */
    public static function provideGuessCases(): iterable
    {
        yield [
            null,
            StringFilter::class,
            ['field_name' => 'foo', 'parent_association_mappings' => []],
            Guess::LOW_CONFIDENCE,
        ];

        yield [
            'time',
            TimeFilter::class,
            ['field_name' => 'foo', 'parent_association_mappings' => []],
            Guess::HIGH_CONFIDENCE,
        ];

        yield [
            'boolean',
            BooleanFilter::class,
            ['field_name' => 'foo', 'parent_association_mappings' => []],
            Guess::HIGH_CONFIDENCE,
        ];

        yield [
            'datetime',
            DateTimeFilter::class,
            ['field_name' => 'foo', 'parent_association_mappings' => []],
            Guess::HIGH_CONFIDENCE,
        ];

        yield [
            'date',
            DateFilter::class,
            ['field_name' => 'foo', 'parent_association_mappings' => []],
            Guess::HIGH_CONFIDENCE,
        ];

        yield [
            'float',
            NumberFilter::class,
            ['field_name' => 'foo', 'parent_association_mappings' => []],
            Guess::MEDIUM_CONFIDENCE,
        ];

        yield [
            'integer',
            NumberFilter::class,
            ['field_name' => 'foo', 'parent_association_mappings' => [], 'field_type' => IntegerType::class],
            Guess::MEDIUM_CONFIDENCE,
        ];

        yield [
            'string',
            StringFilter::class,
            ['field_name' => 'foo', 'parent_association_mappings' => []],
            Guess::MEDIUM_CONFIDENCE,
        ];

        yield [
            'enum',
            ChoiceFilter::class,
            [
                'field_name' => 'foo',
                'parent_association_mappings' => [],
                'field_type' => EnumType::class,
                'field_options' => [
                    'class' => Suit::class,
                ],
            ],
            Guess::HIGH_CONFIDENCE,
            ['enumType' => Suit::class],
        ];

        yield [
            ClassMetadata::ONE_TO_ONE,
            ModelFilter::class,
            [
                'field_name' => 'foo',
                'parent_association_mappings' => [],
                'operator_type' => EqualOperatorType::class,
                'operator_options' => [],
                'field_type' => EntityType::class,
                'field_options' => ['class' => 'Foo'],
            ],
            Guess::HIGH_CONFIDENCE,
        ];

        yield [
            ClassMetadata::ONE_TO_MANY,
            ModelFilter::class,
            [
                'field_name' => 'foo',
                'parent_association_mappings' => [],
                'operator_type' => EqualOperatorType::class,
                'operator_options' => [],
                'field_type' => EntityType::class,
                'field_options' => ['class' => 'Foo'],
            ],
            Guess::HIGH_CONFIDENCE,
        ];
    }
}
