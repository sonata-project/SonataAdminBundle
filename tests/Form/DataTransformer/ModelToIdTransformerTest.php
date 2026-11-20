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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class ModelToIdTransformerTest extends TestCase
{
    /**
     * @var ModelManagerInterface<\stdClass>&MockObject
     */
    private ModelManagerInterface $modelManager;

    protected function setUp(): void
    {
        $this->modelManager = $this->createMock(ModelManagerInterface::class);
    }

    #[DataProvider('provideReverseTransformCases')]
    public function testReverseTransform(int|string $value): void
    {
        $className = \stdClass::class;
        $transformer = new ModelToIdTransformer($this->modelManager, $className);

        $found = new \stdClass();
        $this->modelManager
            ->expects(static::once())
            ->method('find')
            ->willReturn($found);

        static::assertSame($found, $transformer->reverseTransform($value));
    }

    /**
     * @return iterable<array{int|string}>
     */
    public static function provideReverseTransformCases(): iterable
    {
        yield [0];
        yield ['0'];
    }

    public function testReverseTransformUnknownIdentifier(): void
    {
        $transformer = new ModelToIdTransformer($this->modelManager, \stdClass::class);

        $this->modelManager->method('find')->willReturn(null);

        static::assertNull($transformer->reverseTransform('42'));
    }

    public function testReverseTransformUnknownIdentifierWhenStrict(): void
    {
        $transformer = new ModelToIdTransformer($this->modelManager, \stdClass::class, true);

        $this->modelManager->method('find')->with(\stdClass::class, '42')->willReturn(null);

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Unable to find a model of class "stdClass" with the identifier "42".');

        $transformer->reverseTransform('42');
    }

    public function testReverseTransformEmptyWhenStrict(): void
    {
        $transformer = new ModelToIdTransformer($this->modelManager, \stdClass::class, true);

        $this->modelManager->expects(static::never())->method('find');

        static::assertNull($transformer->reverseTransform(null));
        static::assertNull($transformer->reverseTransform(''));
    }

    public function testReverseTransformEmpty(): void
    {
        $className = \stdClass::class;
        $transformer = new ModelToIdTransformer($this->modelManager, $className);

        $this->modelManager->expects(static::never())->method('find');

        static::assertNull($transformer->reverseTransform(null));
        static::assertNull($transformer->reverseTransform(''));
    }

    public function testTransform(): void
    {
        $this->modelManager->expects(static::once())
            ->method('getNormalizedIdentifier')
            ->willReturn('123');

        $className = \stdClass::class;
        $transformer = new ModelToIdTransformer($this->modelManager, $className);

        static::assertNull($transformer->transform(null));

        static::assertSame('123', $transformer->transform(new \stdClass()));
    }
}
