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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdPropertyTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooArrayAccess;

class ModelToIdPropertyTransformerTest extends TestCase
{
    /**
     * @var ModelManagerInterface<object>&MockObject
     */
    private $modelManager;

    protected function setUp(): void
    {
        $this->modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
    }

    public function testReverseTransform(): void
    {
        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', false);

        $model = new Foo();
        $model->setBar('example');

        $this->modelManager
            ->method('find')
            ->willReturnCallback(static function (string $class, $id) use ($model): ?Foo {
                if (Foo::class === $class && 123 === $id) {
                    return $model;
                }

                return null;
            });

        $this->assertNull($transformer->reverseTransform(null));
        $this->assertNull($transformer->reverseTransform(''));
        $this->assertNull($transformer->reverseTransform(12));
        $this->assertSame($model, $transformer->reverseTransform(123));
    }

    /**
     * @dataProvider getReverseTransformMultipleTests
     */
    public function testReverseTransformMultiple(array $expected, $params, Foo $entity1, Foo $entity2, Foo $entity3): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $transformer = new ModelToIdPropertyTransformer($modelManager, Foo::class, 'bar', true);
        $proxyQuery = $this->createMock(ProxyQueryInterface::class);
        $modelManager
            ->expects($this->exactly($params ? 1 : 0))
            ->method('createQuery')
            ->with($this->equalTo(Foo::class))
            ->willReturn($proxyQuery);
        $modelManager
            ->expects($this->exactly($params ? 1 : 0))
            ->method('executeQuery')
            ->with($this->equalTo($proxyQuery))
            ->willReturnCallback(static function () use ($params, $entity1, $entity2, $entity3): array {
                $collection = [];

                if (\in_array(123, $params, true)) {
                    $collection[] = $entity1;
                }

                if (\in_array(456, $params, true)) {
                    $collection[] = $entity2;
                }

                if (\in_array(789, $params, true)) {
                    $collection[] = $entity3;
                }

                return $collection;
            });

        $result = $transformer->reverseTransform($params);
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(\count($expected), $result);
        $this->assertSame($expected, $result->getValues());
    }

    public function getReverseTransformMultipleTests(): iterable
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

    /**
     * @dataProvider getReverseTransformMultipleInvalidTypeTests
     */
    public function testReverseTransformMultipleInvalidTypeTests(array $expected, $params, string $type): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf('Value should be array, %s given.', $type));

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', true);

        $result = $transformer->reverseTransform($params);
        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertSame($expected, $result->getValues());
    }

    public function getReverseTransformMultipleInvalidTypeTests(): array
    {
        return [
            [[], true, 'boolean'],
            [[], 12, 'integer'],
            [[], 12.9, 'double'],
            [[], '_labels', 'string'],
            [[], new \stdClass(), 'object'],
        ];
    }

    public function testTransform(): void
    {
        $model = new Foo();
        $model->setBar('example');

        $this->modelManager->expects($this->once())
            ->method('getIdentifierValues')
            ->willReturn([123]);

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', false);

        $this->assertSame([], $transformer->transform(null));

        $this->assertSame([123, '_labels' => ['example']], $transformer->transform($model));
    }

    public function testTransformWorksWithArrayAccessEntity(): void
    {
        $model = new FooArrayAccess();
        $model->setBar('example');

        $this->modelManager->expects($this->once())
            ->method('getIdentifierValues')
            ->willReturn([123]);

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, FooArrayAccess::class, 'bar', false);

        $this->assertSame([123, '_labels' => ['example']], $transformer->transform($model));
    }

    public function testTransformToStringCallback(): void
    {
        $model = new Foo();
        $model->setBar('example');
        $model->setBaz('bazz');

        $this->modelManager->expects($this->once())
            ->method('getIdentifierValues')
            ->willReturn([123]);

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', false, static function ($model) {
            return $model->getBaz();
        });

        $this->assertSame([123, '_labels' => ['bazz']], $transformer->transform($model));
    }

    public function testTransformMultiple(): void
    {
        $entity1 = new Foo();
        $entity1->setBar('foo');

        $entity2 = new Foo();
        $entity2->setBar('bar');

        $entity3 = new Foo();
        $entity3->setBar('baz');

        $this->modelManager->expects($this->exactly(3))
            ->method('getIdentifierValues')
            ->willReturnCallback(static function (Foo $value) use ($entity1, $entity2, $entity3): array {
                if ($value === $entity1) {
                    return [123];
                }

                if ($value === $entity2) {
                    return [456];
                }

                if ($value === $entity3) {
                    return [789];
                }

                return [999];
            });

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', true);

        $this->assertSame([], $transformer->transform(null));

        $this->assertSame([
            123,
            '_labels' => ['foo', 'bar', 'baz'],
            456,
            789,
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

        $model = new FooArrayAccess();
        $model->setBar('example');
        $transformer = new ModelToIdPropertyTransformer($this->modelManager, FooArrayAccess::class, 'bar', true);
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
            function ($model, $property) use ($properties) {
                $this->assertSame($properties, $property);

                return 'nice_label';
            }
        );

        $model = new Foo();
        $this->modelManager->expects($this->once())
            ->method('getIdentifierValues')
            ->willReturn([123]);

        $value = $transformer->transform($model);
        $this->assertSame([
            123,
            '_labels' => ['nice_label'],
        ], $value);
    }
}
