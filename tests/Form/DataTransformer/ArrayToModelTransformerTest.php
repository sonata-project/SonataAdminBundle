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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Form\DataTransformer\ArrayToModelTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class ArrayToModelTransformerTest extends TestCase
{
    /**
     * @var MockObject&ModelManagerInterface<\stdClass>
     */
    private ModelManagerInterface $modelManager;

    protected function setUp(): void
    {
        $this->modelManager = $this->createMock(ModelManagerInterface::class);
    }

    public function testReverseTransformEntity(): void
    {
        $transformer = new ArrayToModelTransformer($this->modelManager, \stdClass::class);

        $model = new \stdClass();
        static::assertSame($model, $transformer->reverseTransform($model));
    }

    /**
     * @param \stdClass|array<string, mixed>|null $value
     *
     * @dataProvider provideReverseTransformCases
     */
    public function testReverseTransform(\stdClass|array|null $value): void
    {
        $transformer = new ArrayToModelTransformer($this->modelManager, \stdClass::class);

        static::assertInstanceOf(\stdClass::class, $transformer->reverseTransform($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{\stdClass|array<string, mixed>|null}>
     */
    public function provideReverseTransformCases(): iterable
    {
        yield [new \stdClass()];
        yield [[]];
        yield [['foo' => 'bar']];
        yield [null];
    }

    /**
     * @dataProvider provideTransformCases
     */
    public function testTransform(?\stdClass $expected, ?\stdClass $value): void
    {
        $transformer = new ArrayToModelTransformer($this->modelManager, \stdClass::class);

        static::assertSame($expected, $transformer->transform($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{\stdClass|null, \stdClass|null}>
     */
    public function provideTransformCases(): iterable
    {
        $foo = new \stdClass();
        yield [$foo, $foo];
        yield [null, null];
    }
}
