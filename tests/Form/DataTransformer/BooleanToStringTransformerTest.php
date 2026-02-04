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

namespace SensioLabs\AdminBundle\Tests\Form\DataTransformer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Form\DataTransformer\BooleanToStringTransformer;

/**
 * @author Peter Gribanov <info@peter-gribanov.ru>
 */
final class BooleanToStringTransformerTest extends TestCase
{
    /**
     * @phpstan-return iterable<array-key, array{bool|null, string|null, string}>
     */
    public static function provideTransformCases(): iterable
    {
        yield [null, null, '1'];
        yield [false, null, '1'];
        yield [true, '1', '1'];
        yield [true, 'true', 'true'];
        yield [true, 'yes', 'yes'];
        yield [true, 'on', 'on'];
    }

    #[DataProvider('provideTransformCases')]
    public function testTransform(?bool $value, ?string $expected, string $trueValue): void
    {
        $transformer = new BooleanToStringTransformer($trueValue);

        static::assertSame($expected, $transformer->transform($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{string|null, bool}>
     */
    public static function provideReverseTransformCases(): iterable
    {
        yield [null, false];
        yield ['1', true];
        yield ['true', true];
        yield ['yes', true];
        yield ['on', true];
        yield ['0', false];
        yield ['false', false];
        yield ['no', false];
        yield ['off', false];
        yield ['', false];
    }

    #[DataProvider('provideReverseTransformCases')]
    public function testReverseTransform(?string $value, bool $expected): void
    {
        $transformer = new BooleanToStringTransformer('1');

        static::assertSame($expected, $transformer->reverseTransform($value));
    }
}
