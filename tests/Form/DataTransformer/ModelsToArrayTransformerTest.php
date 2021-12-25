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
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\DataTransformer\ModelsToArrayTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Entity;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

final class ModelsToArrayTransformerTest extends TestCase
{
    public function testConstructor(): void
    {
        $transformer = new ModelsToArrayTransformer(
            $this->createStub(ModelManagerInterface::class),
            Foo::class
        );

        static::assertInstanceOf(ModelsToArrayTransformer::class, $transformer);
    }

    public function testReverseTransformWithNull(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);

        $transformer = new ModelsToArrayTransformer(
            $modelManager,
            Foo::class
        );

        static::assertNull($transformer->reverseTransform(null));
    }

    public function testReverseTransformWithEmptyArray(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->expects(static::never())
            ->method('createQuery');
        $modelManager
            ->expects(static::never())
            ->method('addIdentifiersToQuery');
        $modelManager
            ->expects(static::never())
            ->method('executeQuery');

        $transformer = new ModelsToArrayTransformer(
            $modelManager,
            Foo::class
        );

        $result = $transformer->reverseTransform([]);

        static::assertInstanceOf(Collection::class, $result);
        static::assertCount(0, $result);
    }

    public function testReverseTransformRespectOrder(): void
    {
        $object1 = new Entity(1);
        $object2 = new Entity(2);
        $object3 = new Entity(3);

        $proxyQuery = $this->createStub(ProxyQueryInterface::class);
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->method('createQuery')
            ->willReturn($proxyQuery);
        $modelManager
            ->method('executeQuery')
            ->willReturn([$object1, $object2, $object3]);
        $modelManager
            ->method('getNormalizedIdentifier')
            ->willReturnMap([
                [$object1, '1'],
                [$object2, '2'],
                [$object3, '3'],
            ]);

        $transformer = new ModelsToArrayTransformer(
            $modelManager,
            Entity::class
        );

        $result = $transformer->reverseTransform([1, 3, 2]);

        static::assertInstanceOf(Collection::class, $result);
        static::assertSame([$object1, $object3, $object2], $result->toArray());
    }

    /**
     * @psalm-suppress InvalidArgument
     */
    public function testReverseTransformUnexpectedType(): void
    {
        $value = 'unexpected';
        $modelManager = $this->createStub(ModelManagerInterface::class);

        $transformer = new ModelsToArrayTransformer(
            $modelManager,
            Foo::class
        );

        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "array", "string" given');

        // @phpstan-ignore-next-line
        $transformer->reverseTransform($value);
    }

    public function testReverseTransformFailed(): void
    {
        $object1 = new Entity(1);
        $object2 = new Entity(2);

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $proxyQuery = $this->createStub(ProxyQueryInterface::class);
        $modelManager
            ->method('createQuery')
            ->with(static::equalTo(Foo::class))
            ->willReturn($proxyQuery);
        $modelManager
            ->method('executeQuery')
            ->with(static::equalTo($proxyQuery))
            ->willReturn([$object1]);
        $modelManager
            ->method('getNormalizedIdentifier')
            ->willReturnMap([
                [$object1, '1'],
                [$object2, '2'],
            ]);

        $transformer = new ModelsToArrayTransformer(
            $modelManager,
            Foo::class
        );

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('No model was found for the identifier "2".');

        $transformer->reverseTransform([1, 2]);
    }
}
