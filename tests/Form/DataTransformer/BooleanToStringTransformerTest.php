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

namespace Sonata\AdminBundle\Tests\Form\DataTransformer;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Form\DataTransformer\BooleanToStringTransformer;

/**
 * @author Peter Gribanov <info@peter-gribanov.ru>
 */
final class BooleanToStringTransformerTest extends TestCase
{
    /**
     * @phpstan-return iterable<array-key, array{bool|null, string|null, string}>
     */
    public function provideTransformCases(): iterable
    {
        yield [null, null, '1'];
        yield [false, null, '1'];
        yield [true, '1', '1'];
        yield [true, 'true', 'true'];
        yield [true, 'yes', 'yes'];
        yield [true, 'on', 'on'];
    }

    /**
     * @dataProvider provideTransformCases
     */
    public function testTransform(?bool $value, ?string $expected, string $trueValue): void
    {
        $transformer = new BooleanToStringTransformer($trueValue);

        static::assertSame($expected, $transformer->transform($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{string|null, bool}>
     */
    public function provideReverseTransformCases(): iterable
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

    /**
     * @dataProvider provideReverseTransformCases
     */
    public function testReverseTransform(?string $value, bool $expected): void
    {
        $transformer = new BooleanToStringTransformer('1');

        static::assertSame($expected, $transformer->reverseTransform($value));
    }
}
