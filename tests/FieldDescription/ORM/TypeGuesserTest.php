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
use SensioLabs\AdminBundle\FieldDescription\ORM\TypeGuesser;
use Symfony\Component\Form\Guess\Guess;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class TypeGuesserTest extends TestCase
{
    private TypeGuesser $guesser;

    protected function setUp(): void
    {
        $this->guesser = new TypeGuesser();
    }

    /**
     * @param array<string, mixed> $expectedOptions
     */
    #[DataProvider('provideGuessCases')]
    public function testGuess(
        int|string|null $mappingType,
        string $expectedType,
        array $expectedOptions,
        int $expectedConfidence,
    ): void {
        $fieldDescription = static::createStub(FieldDescriptionInterface::class);
        $fieldDescription->method('getFieldName')->willReturn('foo');
        $fieldDescription->method('getMappingType')->willReturn($mappingType);

        $guess = $this->guesser->guess($fieldDescription);

        static::assertSame($expectedType, $guess->getType());
        static::assertSame($expectedOptions, $guess->getOptions());
        static::assertSame($expectedConfidence, $guess->getConfidence());
    }

    /**
     * @phpstan-return iterable<array-key, array{int|string|null, string, array<string, mixed>, int}>
     */
    public static function provideGuessCases(): iterable
    {
        yield [
            null,
            FieldDescriptionInterface::TYPE_STRING,
            [],
            Guess::LOW_CONFIDENCE,
        ];

        yield [
            'array',
            FieldDescriptionInterface::TYPE_ARRAY,
            [],
            Guess::HIGH_CONFIDENCE,
        ];

        yield [
            'time',
            FieldDescriptionInterface::TYPE_TIME,
            [],
            Guess::HIGH_CONFIDENCE,
        ];

        yield [
            'boolean',
            FieldDescriptionInterface::TYPE_BOOLEAN,
            [],
            Guess::HIGH_CONFIDENCE,
        ];

        yield [
            'datetime',
            FieldDescriptionInterface::TYPE_DATETIME,
            [],
            Guess::HIGH_CONFIDENCE,
        ];

        yield [
            'date',
            FieldDescriptionInterface::TYPE_DATE,
            [],
            Guess::HIGH_CONFIDENCE,
        ];

        yield [
            'float',
            FieldDescriptionInterface::TYPE_FLOAT,
            [],
            Guess::MEDIUM_CONFIDENCE,
        ];

        yield [
            'integer',
            FieldDescriptionInterface::TYPE_INTEGER,
            [],
            Guess::MEDIUM_CONFIDENCE,
        ];

        yield [
            'string',
            FieldDescriptionInterface::TYPE_STRING,
            [],
            Guess::MEDIUM_CONFIDENCE,
        ];

        yield [
            'text',
            FieldDescriptionInterface::TYPE_TEXTAREA,
            [],
            Guess::MEDIUM_CONFIDENCE,
        ];

        yield [
            ClassMetadata::ONE_TO_ONE,
            FieldDescriptionInterface::TYPE_ONE_TO_ONE,
            [],
            Guess::HIGH_CONFIDENCE,
        ];

        yield [
            ClassMetadata::ONE_TO_MANY,
            FieldDescriptionInterface::TYPE_ONE_TO_MANY,
            [],
            Guess::HIGH_CONFIDENCE,
        ];

        yield [
            'enum',
            FieldDescriptionInterface::TYPE_ENUM,
            [],
            Guess::HIGH_CONFIDENCE,
        ];
    }
}
