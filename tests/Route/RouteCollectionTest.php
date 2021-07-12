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
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

final class RouteCollectionTest extends TestCase
{
    public function testGetter(): void
    {
        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        self::assertSame('base.Code.Route', $routeCollection->getBaseCodeRoute());
        self::assertSame('baseRouteName', $routeCollection->getBaseRouteName());
        self::assertSame('baseRoutePattern', $routeCollection->getBaseRoutePattern());
        self::assertSame('baseControllerName', $routeCollection->getBaseControllerName());
    }

    public function testActionify(): void
    {
        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'BundleName:ControllerName');

        self::assertSame('fooBar', $routeCollection->actionify('Foo bar'));
        self::assertSame('bar', $routeCollection->actionify('Foo.bar'));
    }

    public function testActionifyService(): void
    {
        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerService');

        self::assertSame('fooBarAction', $routeCollection->actionify('Foo bar'));
        self::assertSame('barAction', $routeCollection->actionify('Foo.bar'));
    }

    public function testCode(): void
    {
        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        self::assertSame('base.Code.Route.test', $routeCollection->getCode('test'));
        self::assertSame('base.Code.Route.test', $routeCollection->getCode('base.Code.Route.test'));
    }

    public function testCollection(): void
    {
        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        $routeCollection->add('view');
        self::assertTrue($routeCollection->has('view'));
        self::assertTrue($routeCollection->hasCached('view'));

        $routeCollection->remove('view');
        self::assertFalse($routeCollection->has('view'));
        self::assertTrue($routeCollection->hasCached('view'));

        $routeCollection->restore('view');
        self::assertTrue($routeCollection->has('view'));
        self::assertTrue($routeCollection->hasCached('view'));

        $routeCollection->add('create');
        $route = $routeCollection->get('create');

        self::assertInstanceOf(Route::class, $route);

        $routeCollection->add('view');
        $routeCollection->add('edit');
        $routeCollection->clear();
        self::assertFalse($routeCollection->has('create'));
        self::assertFalse($routeCollection->has('view'));
        self::assertFalse($routeCollection->has('edit'));

        $routeCollection->add('create');
        $routeCollection->add('view');
        $routeCollection->add('edit');
        $routeCollection->add('list');
        $routeCollection->clearExcept(['create', 'edit']);
        self::assertTrue($routeCollection->has('create'));
        self::assertTrue($routeCollection->has('edit'));
        self::assertFalse($routeCollection->has('view'));
        self::assertFalse($routeCollection->has('list'));

        $routeCollection->clearExcept('create');
        self::assertTrue($routeCollection->has('create'));
        self::assertFalse($routeCollection->has('edit'));
    }

    public function testGetWithException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Element "foo" does not exist.');

        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');
        $routeCollection->get('foo');
    }

    public function testChildCollection(): void
    {
        $childCollection = new RouteCollection('baseCodeRouteChild', 'baseRouteNameChild', 'baseRoutePatternChild', 'baseControllerNameChild');
        $childCollection->add('view');
        $childCollection->add('create');

        $parentCollection = new RouteCollection('baseCodeRoute', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');
        $parentCollection->add('view');
        $parentCollection->add('edit');

        $parentCollection->addCollection($childCollection);

        self::assertTrue($parentCollection->has('view'));
        self::assertTrue($parentCollection->has('edit'));
        self::assertFalse($parentCollection->has('create'));

        self::assertFalse($parentCollection->has('baseCodeRouteChild.edit'));
    }

    public function testRoute(): void
    {
        $routeCollection = new RouteCollection('baseCodeRoute', 'baseRouteName', 'baseRoutePattern', 'BundleName:ControllerName');

        $routeCollection->add('view');

        $route = $routeCollection->get('view');

        self::assertSame('BundleName:ControllerName:view', $route->getDefault('_controller'));
        self::assertSame('baseCodeRoute', $route->getDefault('_sonata_admin'));
        self::assertSame('baseRouteName_view', $route->getDefault('_sonata_name'));
    }

    public function testRouteWithAllConstructorParameters(): void
    {
        $baseCodeRoute = 'baseCodeRoute';
        $baseRouteName = 'baseRouteName';
        $baseRoutePattern = 'baseRoutePattern';
        $routeCollection = new RouteCollection($baseCodeRoute, $baseRouteName, $baseRoutePattern, 'BundleName:ControllerName');

        $name = 'view';
        $pattern = 'view';
        $defaults = [
            '_controller' => 'BundleName:ControllerName:viewAction',
        ];
        $requirements = [
            'page' => '\d+',
        ];
        $options = [
            'debug' => true,
        ];
        $host = 'test.local';
        $schemes = [
            'https',
        ];
        $methods = [
            Request::METHOD_GET,
            Request::METHOD_POST,
        ];
        $condition = "context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'";

        $routeCollection->add($name, $pattern, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);

        $route = $routeCollection->get($name);

        $combinedPattern = sprintf('/%s/%s', $baseRoutePattern, $pattern);

        self::assertSame($combinedPattern, $route->getPath());
        self::assertArrayHasKey('_controller', $route->getDefaults());
        self::assertArrayHasKey('page', $route->getRequirements());
        self::assertArrayHasKey('debug', $route->getOptions());
        self::assertSame($host, $route->getHost());
        self::assertSame($methods, $route->getMethods());
        if (method_exists($route, 'getCondition')) {
            self::assertSame($condition, $route->getCondition());
        }
    }

    public function testRouteControllerService(): void
    {
        $routeCollection = new RouteCollection('baseCodeRoute', 'baseRouteName', 'baseRoutePattern', 'baseControllerServiceName');

        $routeCollection->add('view');

        $route = $routeCollection->get('view');

        self::assertSame('baseControllerServiceName::viewAction', $route->getDefault('_controller'));
        self::assertSame('baseCodeRoute', $route->getDefault('_sonata_admin'));
        self::assertSame('baseRouteName_view', $route->getDefault('_sonata_name'));
    }

    public function testControllerWithFQCN(): void
    {
        $routeCollection = new RouteCollection('baseCodeRoute', 'baseRouteName', 'baseRoutePattern', CRUDController::class);
        $routeCollection->add('view');
        $route = $routeCollection->get('view');

        self::assertSame('Sonata\AdminBundle\Controller\CRUDController::viewAction', $route->getDefault('_controller'));
    }

    public function testControllerWithBundleSubFolder(): void
    {
        $routeCollection = new RouteCollection('baseCodeRoute', 'baseRouteName', 'baseRoutePattern', 'AppBundle\Admin:Test');
        $routeCollection->add('view');
        $route = $routeCollection->get('view');

        self::assertSame('AppBundle\Admin:Test:view', $route->getDefault('_controller'));
    }
}
