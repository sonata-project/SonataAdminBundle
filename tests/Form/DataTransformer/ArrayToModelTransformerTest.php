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
     * @var MockObject&ModelManagerInterface<object>
     */
    private $modelManager;

    protected function setUp(): void
    {
        $this->modelManager = $this->createMock(ModelManagerInterface::class);
    }

    public function testReverseTransformEntity(): void
    {
        $transformer = new ArrayToModelTransformer($this->modelManager, \stdClass::class);

        $model = new \stdClass();
        self::assertSame($model, $transformer->reverseTransform($model));
    }

    /**
     * @param \stdClass|array<string, mixed>|null $value
     *
     * @dataProvider getReverseTransformTests
     */
    public function testReverseTransform($value): void
    {
        $transformer = new ArrayToModelTransformer($this->modelManager, \stdClass::class);

        self::assertInstanceOf(\stdClass::class, $transformer->reverseTransform($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{\stdClass|array<string, mixed>|null}>
     */
    public function getReverseTransformTests(): iterable
    {
        return [
            [new \stdClass()],
            [[]],
            [['foo' => 'bar']],
            [null],
        ];
    }

    /**
     * @dataProvider getTransformTests
     */
    public function testTransform(?\stdClass $expected, ?\stdClass $value): void
    {
        $transformer = new ArrayToModelTransformer($this->modelManager, \stdClass::class);

        self::assertSame($expected, $transformer->transform($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{\stdClass|null, \stdClass|null}>
     */
    public function getTransformTests(): iterable
    {
        $foo = new \stdClass();

        return [
            [$foo, $foo],
            [null, null],
        ];
    }
}
