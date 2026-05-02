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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Form\DataTransformer\ArrayToModelTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class ArrayToModelTransformerTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function testReverseTransformEntity(): void
    {
        $transformer = new ArrayToModelTransformer(static::createStub(ModelManagerInterface::class), \stdClass::class);

        $model = new \stdClass();
        static::assertSame($model, $transformer->reverseTransform($model));
    }

    /**
     * @param \stdClass|array<string, mixed>|null $value
     */
    #[DataProvider('provideReverseTransformCases')]
    public function testReverseTransform(\stdClass|array|null $value): void
    {
        $transformer = new ArrayToModelTransformer(static::createStub(ModelManagerInterface::class), \stdClass::class);

        static::assertInstanceOf(\stdClass::class, $transformer->reverseTransform($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{\stdClass|array<string, mixed>|null}>
     */
    public static function provideReverseTransformCases(): iterable
    {
        yield [new \stdClass()];
        yield [[]];
        yield [['foo' => 'bar']];
        yield [null];
    }

    #[DataProvider('provideTransformCases')]
    public function testTransform(?\stdClass $expected, ?\stdClass $value): void
    {
        $transformer = new ArrayToModelTransformer(static::createStub(ModelManagerInterface::class), \stdClass::class);

        static::assertSame($expected, $transformer->transform($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{\stdClass|null, \stdClass|null}>
     */
    public static function provideTransformCases(): iterable
    {
        $foo = new \stdClass();
        yield [$foo, $foo];
        yield [null, null];
    }
}
