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
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdPropertyTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooArrayAccess;

class ModelToIdPropertyTransformerTest extends TestCase
{
    private $modelManager = null;

    public function setUp(): void
    {
        $this->modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
    }

    public function testReverseTransform(): void
    {
        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', false);

        $entity = new Foo();
        $entity->setBar('example');

        $this->modelManager
            ->method('find')
            ->willReturnCallback(static function ($class, $id) use ($entity) {
                if (Foo::class === $class && 123 === $id) {
                    return $entity;
                }
            });

        $this->assertNull($transformer->reverseTransform(null));
        $this->assertNull($transformer->reverseTransform(false));
        $this->assertNull($transformer->reverseTransform(''));
        $this->assertNull($transformer->reverseTransform(12));
        $this->assertNull($transformer->reverseTransform([123]));
        $this->assertNull($transformer->reverseTransform([123, 456, 789]));
        $this->assertSame($entity, $transformer->reverseTransform(123));
    }

    /**
     * @dataProvider getReverseTransformMultipleTests
     */
    public function testReverseTransformMultiple($expected, $params, $entity1, $entity2, $entity3): void
    {
        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', true);

        $this->modelManager
            ->method('find')
            ->willReturnCallback(static function ($className, $value) use ($entity1, $entity2, $entity3) {
                if (Foo::class !== $className) {
                    return;
                }

                if (123 === $value) {
                    return $entity1;
                }

                if (456 === $value) {
                    return $entity2;
                }

                if (789 === $value) {
                    return $entity3;
                }
            });

        $collection = new ArrayCollection();
        $this->modelManager
            ->method('getModelCollectionInstance')
            ->with($this->equalTo(Foo::class))
            ->willReturn($collection);

        $result = $transformer->reverseTransform($params);
        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertSame($expected, $result->getValues());
    }

    public function getReverseTransformMultipleTests()
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

        return [
            [[], null, $entity1, $entity2, $entity3],
            [[], false, $entity1, $entity2, $entity3],
            [[$entity1], [123, '_labels' => ['example']], $entity1, $entity2, $entity3],
            [[$entity1, $entity2, $entity3], [123, 456, 789, '_labels' => ['example', 'example2', 'example3']], $entity1, $entity2, $entity3],
        ];
    }

    /**
     * @dataProvider getReverseTransformMultipleInvalidTypeTests
     */
    public function testReverseTransformMultipleInvalidTypeTests($expected, $params, $type): void
    {
        $this->expectException(
            \UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf('Value should be array, %s given.', $type)
        );

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', true);

        $collection = new ArrayCollection();
        $this->modelManager
            ->method('getModelCollectionInstance')
            ->with($this->equalTo(Foo::class))
            ->willReturn($collection);

        $result = $transformer->reverseTransform($params);
        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertSame($expected, $result->getValues());
    }

    public function getReverseTransformMultipleInvalidTypeTests()
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
        $entity = new Foo();
        $entity->setBar('example');

        $this->modelManager->expects($this->once())
            ->method('getIdentifierValues')
            ->willReturn([123]);

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', false);

        $this->assertSame([], $transformer->transform(null));
        $this->assertSame([], $transformer->transform(false));
        $this->assertSame([], $transformer->transform(''));
        $this->assertSame([], $transformer->transform(0));
        $this->assertSame([], $transformer->transform('0'));

        $this->assertSame([123, '_labels' => ['example']], $transformer->transform($entity));
    }

    public function testTransformWorksWithArrayAccessEntity(): void
    {
        $entity = new FooArrayAccess();
        $entity->setBar('example');

        $this->modelManager->expects($this->once())
            ->method('getIdentifierValues')
            ->willReturn([123]);

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, FooArrayAccess::class, 'bar', false);

        $this->assertSame([123, '_labels' => ['example']], $transformer->transform($entity));
    }

    public function testTransformToStringCallback(): void
    {
        $entity = new Foo();
        $entity->setBar('example');
        $entity->setBaz('bazz');

        $this->modelManager->expects($this->once())
            ->method('getIdentifierValues')
            ->willReturn([123]);

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', false, static function ($entity) {
            return $entity->getBaz();
        });

        $this->assertSame([123, '_labels' => ['bazz']], $transformer->transform($entity));
    }

    public function testTransformToStringCallbackException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Callback in "to_string_callback" option doesn`t contain callable function.');

        $entity = new Foo();
        $entity->setBar('example');
        $entity->setBaz('bazz');

        $this->modelManager->expects($this->once())
            ->method('getIdentifierValues')
            ->willReturn([123]);

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', false, '987654');

        $transformer->transform($entity);
    }

    public function testTransformMultiple(): void
    {
        $entity1 = new Foo();
        $entity1->setBar('foo');

        $entity2 = new Foo();
        $entity2->setBar('bar');

        $entity3 = new Foo();
        $entity3->setBar('baz');

        $collection = new ArrayCollection();
        $collection[] = $entity1;
        $collection[] = $entity2;
        $collection[] = $entity3;

        $this->modelManager->expects($this->exactly(3))
            ->method('getIdentifierValues')
            ->willReturnCallback(static function ($value) use ($entity1, $entity2, $entity3) {
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
        $this->assertSame([], $transformer->transform(false));
        $this->assertSame([], $transformer->transform(''));
        $this->assertSame([], $transformer->transform(0));
        $this->assertSame([], $transformer->transform('0'));

        $this->assertSame([
            123,
            '_labels' => ['foo', 'bar', 'baz'],
            456,
            789,
        ], $transformer->transform($collection));
    }

    public function testTransformCollectionException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A multiple selection must be passed a collection not a single value. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.');

        $entity = new Foo();
        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', true);
        $transformer->transform($entity);
    }

    public function testTransformArrayAccessException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A multiple selection must be passed a collection not a single value. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.');

        $entity = new FooArrayAccess();
        $entity->setBar('example');
        $transformer = new ModelToIdPropertyTransformer($this->modelManager, FooArrayAccess::class, 'bar', true);
        $transformer->transform($entity);
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

        $collection = new ArrayCollection();
        $collection[] = $entity1;
        $collection[] = $entity2;
        $collection[] = $entity3;

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, Foo::class, 'bar', false);

        $transformer->transform($collection);
    }
}
