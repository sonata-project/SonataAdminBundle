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
    public function provideTransform(): array
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
    public function testTransform($value, ?string $expected, string $trueValue): void
    {
        $transformer = new BooleanToStringTransformer($trueValue);

        $this->assertSame($expected, $transformer->transform($value));
    }

    public function provideReverseTransform(): array
    {
        return [
            [null, false],
            [true, true],
            [1, true],
            ['1', true],
            ['true', true],
            ['yes', true],
            ['on', true],
            [false, false],
            [0, false],
            ['0', false],
            ['false', false],
            ['no', false],
            ['off', false],
            ['', false],
            // invalid values
            ['foo', false],
            [new \DateTime(), false],
            [\PHP_INT_MAX, false],
        ];
    }

    /**
     * @dataProvider provideReverseTransform
     */
    public function testReverseTransform($value, bool $expected): void
    {
        $transformer = new BooleanToStringTransformer('1');

        $this->assertSame($expected, $transformer->reverseTransform($value));
    }
}
