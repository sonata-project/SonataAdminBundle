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
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\ArgumentResolver\AdminValueResolver;
use Sonata\AdminBundle\Request\AdminFetcher;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CommentAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\PostAdmin;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class AdminValueResolverTest extends TestCase
{
    /**
     * @dataProvider provideInvalidData
     */
    public function testWithInvalidData(Request $request, ArgumentMetadata $argumentMetadata): void
    {
        $admin = new PostAdmin();
        $admin->setCode('sonata.admin.post');

        $container = new Container();
        $container->set('sonata.admin.post', $admin);

        $adminFetcher = new AdminFetcher(new Pool($container, ['sonata.admin.post']));
        $adminValueResolver = new AdminValueResolver($adminFetcher);

        static::assertFalse($adminValueResolver->supports($request, $argumentMetadata));
        static::assertSame(
            [],
            $adminValueResolver->resolve($request, $argumentMetadata)
        );
    }

    /**
     * @phpstan-return iterable<array-key, array{Request, ArgumentMetadata}>
     */
    public function provideInvalidData(): iterable
    {
        yield 'Object with no type' => [
            static::createRequest(),
            static::createArgumentMetadata('_sonata_admin'),
        ];

        yield 'Object must implement AdminInterface' => [
            static::createRequest(),
            static::createArgumentMetadata('_sonata_admin', self::class),
        ];

        yield 'Admin code must be passed' => [
            static::createRequest(),
            static::createArgumentMetadata('_sonata_admin', PostAdmin::class),
        ];

        yield 'Admin code must exist' => [
            static::createRequest(['_sonata_admin' => 'non_existing']),
            static::createArgumentMetadata('_sonata_admin', PostAdmin::class),
        ];

        yield 'Admin fetched must be of the type specified in the action' => [
            static::createRequest(['_sonata_admin' => 'sonata.admin.post']),
            static::createArgumentMetadata('_sonata_admin', CommentAdmin::class),
        ];
    }

    public function testResolvesAdminClass(): void
    {
        $admin = new PostAdmin();
        $admin->setCode('sonata.admin.post');

        $container = new Container();
        $container->set('sonata.admin.post', $admin);

        $adminFetcher = new AdminFetcher(new Pool($container, ['sonata.admin.post']));
        $adminValueResolver = new AdminValueResolver($adminFetcher);

        $request = static::createRequest(['_sonata_admin' => 'sonata.admin.post']);
        $argumentMetadata = static::createArgumentMetadata('_sonata_admin', PostAdmin::class);

        static::assertTrue($adminValueResolver->supports($request, $argumentMetadata));
        static::assertSame(
            [$admin],
            $adminValueResolver->resolve($request, $argumentMetadata)
        );
    }

    public function testResolvesAdminInterface(): void
    {
        $admin = new PostAdmin();
        $admin->setCode('sonata.admin.post');

        $container = new Container();
        $container->set('sonata.admin.post', $admin);

        $adminFetcher = new AdminFetcher(new Pool($container, ['sonata.admin.post']));
        $adminValueResolver = new AdminValueResolver($adminFetcher);

        $request = static::createRequest(['_sonata_admin' => 'sonata.admin.post']);
        $argumentMetadata = static::createArgumentMetadata('_sonata_admin', AdminInterface::class);

        static::assertTrue($adminValueResolver->supports($request, $argumentMetadata));
        static::assertSame(
            [$admin],
            $adminValueResolver->resolve($request, $argumentMetadata)
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
