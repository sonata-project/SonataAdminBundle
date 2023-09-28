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

namespace Sonata\AdminBundle\Tests\ArgumentResolver;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\ArgumentResolver\ProxyQueryResolver;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ProxyQueryResolverTest extends TestCase
{
    private ProxyQueryResolver $proxyQueryResolver;

    protected function setUp(): void
    {
        $this->proxyQueryResolver = new ProxyQueryResolver();
    }

    /**
     * @dataProvider provideWithInvalidDataCases
     */
    public function testWithInvalidData(Request $request, ArgumentMetadata $argumentMetadata): void
    {
        static::assertFalse($this->proxyQueryResolver->supports($request, $argumentMetadata));
        static::assertSame(
            [],
            $this->proxyQueryResolver->resolve($request, $argumentMetadata)
        );
    }

    /**
     * @phpstan-return iterable<array-key, array{Request, ArgumentMetadata}>
     */
    public function provideWithInvalidDataCases(): iterable
    {
        yield 'Object with no type' => [
            static::createRequest(),
            static::createArgumentMetadata('query'),
        ];

        yield 'No ProxyQuery object in the request' => [
            static::createRequest(),
            static::createArgumentMetadata('query', ProxyQueryInterface::class),
        ];

        yield 'Not looking for a query argument' => [
            static::createRequest(['query' => static::createStub(ProxyQueryInterface::class)]),
            static::createArgumentMetadata('query', \stdClass::class),
        ];
    }

    public function testResolveWithProxyQuery(): void
    {
        $proxy = static::createStub(ProxyQueryInterface::class);
        $request = static::createRequest(['query' => $proxy]);
        $argument = static::createArgumentMetadata('query', ProxyQueryInterface::class);

        static::assertTrue($this->proxyQueryResolver->supports($request, $argument));
        static::assertSame(
            [$proxy],
            $this->proxyQueryResolver->resolve($request, $argument)
        );
    }

    public function testResolveWithProxyQueryWithDifferentName(): void
    {
        $proxy = static::createStub(ProxyQueryInterface::class);
        $request = static::createRequest(['query' => $proxy]);
        $argument = static::createArgumentMetadata(uniqid('parameter'), ProxyQueryInterface::class);

        static::assertTrue($this->proxyQueryResolver->supports($request, $argument));
        static::assertSame(
            [$proxy],
            $this->proxyQueryResolver->resolve($request, $argument)
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private static function createRequest(array $attributes = []): Request
    {
        return new Request([], [], $attributes);
    }

    private static function createArgumentMetadata(string $name, ?string $type = null): ArgumentMetadata
    {
        return new ArgumentMetadata($name, $type, false, false, null);
    }
}
