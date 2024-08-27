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

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdPropertyTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooArrayAccess;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

final class ModelToIdPropertyTransformerTest extends TestCase
{
    /**
     * @var ModelManagerInterface<Foo>&MockObject
     */
    private ModelManagerInterface $modelManager;

    protected function setUp(): void
    {
        $this->modelManager = $this->createMock(ModelManagerInterface::class);
    }

    public function testReverseTransform(): void
    {
        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', false);

        $model = new Foo();
        $model->setBar('example');

        $this->modelManager
            ->method('find')
            ->willReturnCallback(static function (string $class, int|string $id) use ($model): ?Foo {
                if (Foo::class === $class && 123 === $id) {
                    return $model;
                }

                return null;
            });

        static::assertNull($transformer->reverseTransform(null));
        static::assertNull($transformer->reverseTransform(''));
        static::assertNull($transformer->reverseTransform(12));
        static::assertSame($model, $transformer->reverseTransform(123));
    }

    /**
     * @param Foo[]                                $expected
     * @param array<int|string|array<string>>|null $params
     *
     * @dataProvider provideReverseTransformMultipleCases
     *
     * @phpstan-param array<int|string|array<string>>|null $params
     * @psalm-param (array{_labels?: array<string>}&array<int|string>)|null $params
     */
    public function testReverseTransformMultiple(array $expected, ?array $params, Foo $entity1, Foo $entity2, Foo $entity3): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $transformer = new ModelToIdPropertyTransformer($modelManager, Foo::class, 'bar', true);
        $proxyQuery = $this->createMock(ProxyQueryInterface::class);
        $modelManager
            ->expects(static::exactly(null !== $params ? 1 : 0))
            ->method('createQuery')
            ->with(static::equalTo(Foo::class))
            ->willReturn($proxyQuery);
        $modelManager
            ->expects(static::exactly(null !== $params ? 1 : 0))
            ->method('executeQuery')
            ->with(static::equalTo($proxyQuery))
            ->willReturnCallback(static function () use ($params, $entity1, $entity2, $entity3): array {
                $collection = [];

                if (\is_array($params) && \in_array(123, $params, true)) {
                    $collection[] = $entity1;
                }

                if (\is_array($params) && \in_array(456, $params, true)) {
                    $collection[] = $entity2;
                }

                if (\is_array($params) && \in_array(789, $params, true)) {
                    $collection[] = $entity3;
                }

                return $collection;
            });
        $modelManager
            ->method('getNormalizedIdentifier')
            ->willReturnMap([
                [$entity1, '123'],
                [$entity2, '456'],
                [$entity3, '789'],
            ]);

        /** @psalm-suppress ArgumentTypeCoercion https://github.com/vimeo/psalm/issues/9503 */
        $result = $transformer->reverseTransform($params);
        static::assertInstanceOf(Collection::class, $result);
        static::assertCount(\count($expected), $result);
        static::assertSame($expected, $result->getValues());
    }

    /**
     * @phpstan-return iterable<array-key, array{array<Foo>, array<int|string|array<string>>|null, Foo, Foo, Foo}>
     * @psalm-return iterable<array-key, array{array<Foo>, (array{_labels?: array<string>}&array<int|string>)|null, Foo, Foo, Foo}>
     */
    public function provideReverseTransformMultipleCases(): iterable
    {
        $entity1 = new Foo();
        $entity1->setBaz(123);
        $entity1->setBar('example');

        $entity2 = new Foo();
        $entity2->setBaz(456);
        $entity2->setBar('example2');

        $entity3 = new Foo();
        $entity3->setBaz(789);
        $entity3->setBar('example3');

        yield [[], null, $entity1, $entity2, $entity3];
        yield [[$entity1], [123, '_labels' => ['example']], $entity1, $entity2, $entity3];
        yield [[$entity1, $entity2, $entity3], [123, 456, 789, '_labels' => ['example', 'example2', 'example3']], $entity1, $entity2, $entity3];
    }

    public function testReverseTransformInvalidTypeTests(): void
    {
        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', false);

        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "int|string", "array" given');

        $transformer->reverseTransform([123]);
    }

    /**
     * @dataProvider provideReverseTransformMultipleInvalidTypeTestsCases
     */
    public function testReverseTransformMultipleInvalidTypeTests(mixed $params, string $type): void
    {
        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', true);

        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(\sprintf('Expected argument of type "array", "%s" given', $type));

        $transformer->reverseTransform($params);
    }

    /**
     * @phpstan-return iterable<array{mixed, string}>
     */
    public function provideReverseTransformMultipleInvalidTypeTestsCases(): iterable
    {
        yield [true, 'bool'];
        yield [12, 'int'];
        yield [12.9, 'float'];
        yield ['_labels', 'string'];
        yield [new \stdClass(), \stdClass::class];
    }

    public function testTransform(): void
    {
        $model = new Foo();
        $model->setBar('example');

        $this->modelManager->expects(static::once())
            ->method('getNormalizedIdentifier')
            ->willReturn('123');

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', false);

        static::assertSame([], $transformer->transform(null));

        static::assertSame(['123', '_labels' => ['example']], $transformer->transform($model));
    }

    public function testTransformWorksWithArrayAccessEntity(): void
    {
        /** @var ModelManagerInterface<FooArrayAccess>&MockObject $modelManager */
        $modelManager = $this->createMock(ModelManagerInterface::class);

        $model = new FooArrayAccess();
        $model->setBar('example');

        $modelManager->expects(static::once())
            ->method('getNormalizedIdentifier')
            ->willReturn('123');

        $transformer = new ModelToIdPropertyTransformer($modelManager, FooArrayAccess::class, 'bar', false);

        static::assertSame(['123', '_labels' => ['example']], $transformer->transform($model));
    }

    public function testTransformToStringCallback(): void
    {
        $model = new Foo();
        $model->setBar('example');
        $model->setBaz('bazz');

        $this->modelManager->expects(static::once())
            ->method('getNormalizedIdentifier')
            ->willReturn('123');

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', false, static fn (Foo $model): string => (string) $model->getBaz());

        static::assertSame(['123', '_labels' => ['bazz']], $transformer->transform($model));
    }

    public function testTransformMultiple(): void
    {
        $entity1 = new Foo();
        $entity1->setBar('foo');

        $entity2 = new Foo();
        $entity2->setBar('bar');

        $entity3 = new Foo();
        $entity3->setBar('baz');

        $this->modelManager->expects(static::exactly(3))
            ->method('getNormalizedIdentifier')
            ->willReturnCallback(static function (Foo $value) use ($entity1, $entity2, $entity3): ?string {
                if ($value === $entity1) {
                    return '123';
                }

                if ($value === $entity2) {
                    return '456';
                }

                if ($value === $entity3) {
                    return '789';
                }

                return null;
            });

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', true);

        static::assertSame([], $transformer->transform(null));

        static::assertSame([
            '123',
            '456',
            '789',
            '_labels' => ['foo', 'bar', 'baz'],
        ], $transformer->transform([$entity1, $entity2, $entity3]));
    }

    public function testTransformCollectionException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A multiple selection must be passed a collection not a single value. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.');

        $model = new Foo();
        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', true);
        $transformer->transform($model);
    }

    public function testTransformArrayAccessException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A multiple selection must be passed a collection not a single value. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.');

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $model = new FooArrayAccess();
        $model->setBar('example');
        $transformer = new ModelToIdPropertyTransformer($modelManager, FooArrayAccess::class, 'bar', true);
        $transformer->transform($model);
    }

    public function testTransformEntityException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A single selection must be passed a single value not a collection. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.');

        $entity1 = new Foo();
        $entity1->setBar('foo');

        $entity2 = new Foo();
        $entity2->setBar('bar');

        $entity3 = new Foo();
        $entity3->setBar('baz');

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', false);

        $transformer->transform([$entity1, $entity2, $entity3]);
    }

    public function testTransformWithMultipleProperties(): void
    {
        $properties = ['bar', 'baz'];

        $transformer = new ModelToIdPropertyTransformer(
            $this->modelManager,
            Foo::class,
            $properties,
            false,
            static function (Foo $model, array $property) use ($properties): string {
                self::assertSame($properties, $property);

                return 'nice_label';
            }
        );

        $model = new Foo();
        $this->modelManager->expects(static::once())
            ->method('getNormalizedIdentifier')
            ->willReturn('123');

        $value = $transformer->transform($model);
        static::assertSame([
            '123',
            '_labels' => ['nice_label'],
        ], $value);
    }
}
