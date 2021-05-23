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
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class ModelsToArrayTransformerTest extends TestCase
{
    public function testConstructor(): void
    {
        $transformer = new ModelsToArrayTransformer(
            $this->createStub(ModelManagerInterface::class),
            Foo::class
        );

        $this->assertInstanceOf(ModelsToArrayTransformer::class, $transformer);
    }

    /**
     * @param array<int|string>|null $value
     *
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform(?array $value): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);

        if (null !== $value) {
            $proxyQuery = $this->createStub(ProxyQueryInterface::class);
            $modelManager
                ->method('createQuery')
                ->with($this->equalTo(Foo::class))
                ->willReturn($proxyQuery);
            $modelManager
                ->method('executeQuery')
                ->with($this->equalTo($proxyQuery))
                ->willReturn($value);
        }

        $transformer = new ModelsToArrayTransformer(
            $modelManager,
            Foo::class
        );

        $result = $transformer->reverseTransform($value);

        if (null === $value) {
            $this->assertNull($result);
        } else {
            $this->assertInstanceOf(Collection::class, $result);
            $this->assertCount(\count($value), $result);
        }
    }

    /**
     * @phpstan-return iterable<array{array<int|string>|null}>
     */
    public function reverseTransformProvider(): iterable
    {
        yield [['a']];
        yield [['a', 'b', 3]];
        yield [null];
    }

    public function testReverseTransformWithEmptyArray(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->expects($this->never())
            ->method('createQuery');
        $modelManager
            ->expects($this->never())
            ->method('addIdentifiersToQuery');
        $modelManager
            ->expects($this->never())
            ->method('executeQuery');

        $transformer = new ModelsToArrayTransformer(
            $modelManager,
            Foo::class
        );

        $result = $transformer->reverseTransform([]);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
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
        $value = ['a', 'b'];
        $reverseTransformCollection = ['a'];
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $proxyQuery = $this->createStub(ProxyQueryInterface::class);
        $modelManager
            ->method('createQuery')
            ->with($this->equalTo(Foo::class))
            ->willReturn($proxyQuery);
        $modelManager
            ->method('executeQuery')
            ->with($this->equalTo($proxyQuery))
            ->willReturn($reverseTransformCollection);

        $transformer = new ModelsToArrayTransformer(
            $modelManager,
            Foo::class
        );

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('1 keys could not be found in the provided values: "a", "b".');

        $transformer->reverseTransform($value);
    }
}
