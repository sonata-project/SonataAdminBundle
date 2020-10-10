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
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform(?array $value): void
    {
        $modelManager = $this->createStub(ModelManagerInterface::class);

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

    public function reverseTransformProvider(): iterable
    {
        yield [['a']];
        yield [['a', 'b', 3]];
        yield [null];
    }

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

        $transformer->reverseTransform($value);
    }

    public function testReverseTransformFailed(): void
    {
        $value = ['a', 'b'];
        $reverseTransformCollection = ['a'];
        $modelManager = $this->createStub(ModelManagerInterface::class);
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
