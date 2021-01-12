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

namespace Sonata\AdminBundle\Tests\Route;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Route\AdminPoolLoader;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class AdminPoolLoaderTest extends TestCase
{
    public function testSupports(): void
    {
        $container = new Container();
        $pool = new Pool($container);

        $adminPoolLoader = new AdminPoolLoader($pool, ['foo_admin', 'bar_admin'], $container);

        $this->assertTrue($adminPoolLoader->supports('foo', 'sonata_admin'));
        $this->assertFalse($adminPoolLoader->supports('foo', 'bar'));
    }

    public function testLoad(): void
    {
        $container = new Container();
        $pool = new Pool($container, ['foo_admin', 'bar_admin']);

        $adminPoolLoader = new AdminPoolLoader($pool, ['foo_admin', 'bar_admin'], $container);

        $routeCollection1 = new RouteCollection('base.Code.Route.foo', 'baseRouteNameFoo', 'baseRoutePatternFoo', 'baseControllerNameFoo');
        $routeCollection2 = new RouteCollection('base.Code.Route.bar', 'baseRouteNameBar', 'baseRoutePatternBar', 'baseControllerNameBar');

        $routeCollection1->add('foo');
        $routeCollection2->add('bar');
        $routeCollection2->add('baz');

        $admin1 = $this->getMockForAbstractClass(AdminInterface::class);
        $admin1->expects($this->once())
            ->method('getRoutes')
            ->willReturn($routeCollection1);

        $container->set('foo_admin', $admin1);

        $admin2 = $this->getMockForAbstractClass(AdminInterface::class);
        $admin2->expects($this->once())
            ->method('getRoutes')
            ->willReturn($routeCollection2);

        $container->set('bar_admin', $admin2);

        $collection = $adminPoolLoader->load('foo', 'sonata_admin');

        $this->assertInstanceOf(SymfonyRouteCollection::class, $collection);
        $this->assertInstanceOf(SymfonyRoute::class, $collection->get('baseRouteNameFoo_foo'));
        $this->assertInstanceOf(SymfonyRoute::class, $collection->get('baseRouteNameBar_bar'));
        $this->assertInstanceOf(SymfonyRoute::class, $collection->get('baseRouteNameBar_bar'));
    }
}
