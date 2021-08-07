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
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\ArgumentResolver\AdminValueResolver;
use Sonata\AdminBundle\Request\AdminFetcher;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CommentAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\PostAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Post;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class AdminValueResolverTest extends TestCase
{
    /**
     * @var PostAdmin
     */
    private $admin;

    /**
     * @var AdminValueResolver
     */
    private $adminValueResolver;

    protected function setUp(): void
    {
        $this->admin = new PostAdmin('sonata.admin.post', Post::class, '');

        $container = new Container();
        $container->set('sonata.admin.post', $this->admin);

        $adminFetcher = new AdminFetcher(new Pool($container, ['sonata.admin.post']));

        $this->adminValueResolver = new AdminValueResolver($adminFetcher);
    }

    /**
     * @dataProvider supportDataProvider
     */
    public function testSupports(bool $expectedSupport, Request $request, ArgumentMetadata $argumentMetadata): void
    {
        self::assertSame($expectedSupport, $this->adminValueResolver->supports($request, $argumentMetadata));
    }

    /**
     * @phpstan-return iterable<array-key, array{bool, Request, ArgumentMetadata}>
     */
    public function supportDataProvider(): iterable
    {
        yield 'Object must implement AdminInterface' => [
            false,
            new Request(),
            new ArgumentMetadata('_sonata_admin', __CLASS__, false, false, null),
        ];

        $request = Request::create('/');
        $request->attributes->set('_sonata_admin', 'non_existing');

        yield 'Admin code must exist' => [
            false,
            $request,
            new ArgumentMetadata('_sonata_admin', PostAdmin::class, false, false, null),
        ];

        $request = new Request();
        $request->attributes->set('_sonata_admin', 'non_existing');

        yield 'Admin fetched must be of the type specified in the action' => [
            false,
            $request,
            new ArgumentMetadata('_sonata_admin', CommentAdmin::class, false, false, null),
        ];

        $request = new Request();
        $request->attributes->set('_sonata_admin', 'sonata.admin.post');

        yield 'Admin class is valid' => [
            true,
            $request,
            new ArgumentMetadata('_sonata_admin', PostAdmin::class, false, false, null),
        ];
    }

    public function testResolveAdmin(): void
    {
        $request = new Request();
        $request->attributes->set('_sonata_admin', 'sonata.admin.post');

        $argument = new ArgumentMetadata('_sonata_admin', PostAdmin::class, false, false, null);

        self::assertTrue($this->adminValueResolver->supports($request, $argument));
        self::assertSame(
            [$this->admin],
            $this->iterableToArray($this->adminValueResolver->resolve($request, $argument))
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
