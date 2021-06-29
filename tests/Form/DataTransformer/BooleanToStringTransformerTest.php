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
    public function provideTransform(): iterable
    {
        return [
            [null, null, '1'],
            [false, null, '1'],
            [true, '1', '1'],
            [true, 'true', 'true'],
            [true, 'yes', 'yes'],
            [true, 'on', 'on'],
        ];
    }

    /**
     * @dataProvider provideTransform
     */
    public function testTransform(?bool $value, ?string $expected, string $trueValue): void
    {
        $transformer = new BooleanToStringTransformer($trueValue);

        self::assertSame($expected, $transformer->transform($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{string|null, bool}>
     */
    public function provideReverseTransform(): iterable
    {
        return [
            [null, false],
            ['1', true],
            ['true', true],
            ['yes', true],
            ['on', true],
            ['0', false],
            ['false', false],
            ['no', false],
            ['off', false],
            ['', false],
        ];
    }

    /**
     * @dataProvider provideReverseTransform
     */
    public function testReverseTransform(?string $value, bool $expected): void
    {
        $transformer = new BooleanToStringTransformer('1');

        self::assertSame($expected, $transformer->reverseTransform($value));
    }
}
