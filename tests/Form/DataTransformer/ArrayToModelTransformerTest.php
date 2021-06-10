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
use Sonata\AdminBundle\Tests\Fixtures\Entity\Form\FooEntity;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ArrayToModelTransformerTest extends TestCase
{
    /**
     * @var MockObject&ModelManagerInterface<object>
     */
    private $modelManager;

    protected function setUp(): void
    {
        $this->modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
    }

    public function testReverseTransformEntity(): void
    {
        $transformer = new ArrayToModelTransformer($this->modelManager, FooEntity::class);

        $model = new FooEntity();
        $this->assertSame($model, $transformer->reverseTransform($model));
    }

    /**
     * @param FooEntity|array<string, mixed>|null $value
     *
     * @dataProvider getReverseTransformTests
     */
    public function testReverseTransform($value): void
    {
        $transformer = new ArrayToModelTransformer($this->modelManager, FooEntity::class);

        $this->assertInstanceOf(FooEntity::class, $transformer->reverseTransform($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{FooEntity|array<string, mixed>|null}>
     */
    public function getReverseTransformTests(): iterable
    {
        return [
            [new FooEntity()],
            [[]],
            [['foo' => 'bar']],
            [null],
        ];
    }

    /**
     * @dataProvider getTransformTests
     */
    public function testTransform(?FooEntity $expected, ?FooEntity $value): void
    {
        $transformer = new ArrayToModelTransformer($this->modelManager, FooEntity::class);

        $this->assertSame($expected, $transformer->transform($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{FooEntity|null, FooEntity|null}>
     */
    public function getTransformTests(): iterable
    {
        $foo = new FooEntity();

        return [
            [$foo, $foo],
            [null, null],
        ];
    }
}
