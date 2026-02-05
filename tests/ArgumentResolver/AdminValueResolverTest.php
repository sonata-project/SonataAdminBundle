<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\ArgumentResolver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\ArgumentResolver\AdminValueResolver;
use SensioLabs\AdminBundle\Request\AdminFetcher;
use SensioLabs\AdminBundle\Tests\Fixtures\Admin\CommentAdmin;
use SensioLabs\AdminBundle\Tests\Fixtures\Admin\PostAdmin;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class AdminValueResolverTest extends TestCase
{
    #[DataProvider('provideWithInvalidDataCases')]
    public function testWithInvalidData(Request $request, ArgumentMetadata $argumentMetadata): void
    {
        $admin = new PostAdmin();
        $admin->setCode('sensiolabs.admin.post');

        $container = new Container();
        $container->set('sensiolabs.admin.post', $admin);

        $adminFetcher = new AdminFetcher(new Pool($container, ['sensiolabs.admin.post']));
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
    public static function provideWithInvalidDataCases(): iterable
    {
        yield 'Object with no type' => [
            static::createRequest(),
            static::createArgumentMetadata('_sensiolabs_admin'),
        ];

        yield 'Object must implement AdminInterface' => [
            static::createRequest(),
            static::createArgumentMetadata('_sensiolabs_admin', self::class),
        ];

        yield 'Admin code must be passed' => [
            static::createRequest(),
            static::createArgumentMetadata('_sensiolabs_admin', PostAdmin::class),
        ];

        yield 'Admin code must exist' => [
            static::createRequest(['_sensiolabs_admin' => 'non_existing']),
            static::createArgumentMetadata('_sensiolabs_admin', PostAdmin::class),
        ];

        yield 'Admin fetched must be of the type specified in the action' => [
            static::createRequest(['_sensiolabs_admin' => 'sensiolabs.admin.post']),
            static::createArgumentMetadata('_sensiolabs_admin', CommentAdmin::class),
        ];
    }

    public function testResolvesAdminClass(): void
    {
        $admin = new PostAdmin();
        $admin->setCode('sensiolabs.admin.post');

        $container = new Container();
        $container->set('sensiolabs.admin.post', $admin);

        $adminFetcher = new AdminFetcher(new Pool($container, ['sensiolabs.admin.post']));
        $adminValueResolver = new AdminValueResolver($adminFetcher);

        $request = static::createRequest(['_sensiolabs_admin' => 'sensiolabs.admin.post']);
        $argumentMetadata = static::createArgumentMetadata('_sensiolabs_admin', PostAdmin::class);

        static::assertTrue($adminValueResolver->supports($request, $argumentMetadata));
        static::assertSame(
            [$admin],
            $adminValueResolver->resolve($request, $argumentMetadata)
        );
    }

    public function testResolvesAdminInterface(): void
    {
        $admin = new PostAdmin();
        $admin->setCode('sensiolabs.admin.post');

        $container = new Container();
        $container->set('sensiolabs.admin.post', $admin);

        $adminFetcher = new AdminFetcher(new Pool($container, ['sensiolabs.admin.post']));
        $adminValueResolver = new AdminValueResolver($adminFetcher);

        $request = static::createRequest(['_sensiolabs_admin' => 'sensiolabs.admin.post']);
        $argumentMetadata = static::createArgumentMetadata('_sensiolabs_admin', AdminInterface::class);

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
