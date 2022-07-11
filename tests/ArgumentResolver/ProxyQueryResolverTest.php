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
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ProxyQueryResolverTest extends TestCase
{
    private ProxyQueryResolver $proxyQueryResolver;

    protected function setUp(): void
    {
        $this->proxyQueryResolver = new ProxyQueryResolver();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            ArgumentValueResolverInterface::class,
            $this->proxyQueryResolver
        );
    }

    /**
     * @dataProvider supportDataProvider
     */
    public function testSupports(bool $expectedSupport, Request $request, ArgumentMetadata $argumentMetadata): void
    {
        static::assertSame($expectedSupport, $this->proxyQueryResolver->supports($request, $argumentMetadata));
    }

    /**
     * @phpstan-return iterable<array-key, array{bool, Request, ArgumentMetadata}>
     */
    public function supportDataProvider(): iterable
    {
        yield 'No ProxyQuery object in the request' => [
            false,
            new Request(),
            new ArgumentMetadata('query', ProxyQueryInterface::class, false, false, null),
        ];

        yield 'Not looking for a query argument' => [
            false,
            new Request([], [], ['query' => static::createMock(ProxyQueryInterface::class)]),
            new ArgumentMetadata('query', \stdClass::class, false, false, null),
        ];

        yield 'ProxyQuery attributes under query name' => [
            true,
            new Request([], [], ['query' => static::createMock(ProxyQueryInterface::class)]),
            new ArgumentMetadata('query', ProxyQueryInterface::class, false, false, null),
        ];

        yield 'ProxyQuery attributes under any names' => [
            true,
            new Request([], [], ['query' => static::createMock(ProxyQueryInterface::class)]),
            new ArgumentMetadata(uniqid('parameter'), ProxyQueryInterface::class, false, false, null),
        ];
    }

    public function testResolve(): void
    {
        $request = new Request();
        $request->attributes->set('query', $proxy = static::createMock(ProxyQueryInterface::class));

        $argument = new ArgumentMetadata('query', ProxyQueryInterface::class, false, false, null);

        static::assertTrue($this->proxyQueryResolver->supports($request, $argument));

        static::assertSame(
            [$proxy],
            $this->iterableToArray($this->proxyQueryResolver->resolve($request, $argument))
        );
    }

    /**
     * @phpstan-template T
     * @phpstan-param iterable<T> $iterable
     * @phpstan-return array<T>
     */
    private function iterableToArray(iterable $iterable): array
    {
        $array = [];
        foreach ($iterable as $admin) {
            $array[] = $admin;
        }

        return $array;
    }
}
