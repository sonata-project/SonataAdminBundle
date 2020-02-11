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

class RouteCollectionTest extends TestCase
{
    public function testGetter(): void
    {
        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        $this->assertSame('base.Code.Route', $routeCollection->getBaseCodeRoute());
        $this->assertSame('baseRouteName', $routeCollection->getBaseRouteName());
        $this->assertSame('baseRoutePattern', $routeCollection->getBaseRoutePattern());
        $this->assertSame('baseControllerName', $routeCollection->getBaseControllerName());
    }

    public function testActionify(): void
    {
        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'BundleName:ControllerName');

        $this->assertSame('fooBar', $routeCollection->actionify('Foo bar'));
        $this->assertSame('bar', $routeCollection->actionify('Foo.bar'));
    }

    public function testActionifyService(): void
    {
        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerService');

        $this->assertSame('fooBarAction', $routeCollection->actionify('Foo bar'));
        $this->assertSame('barAction', $routeCollection->actionify('Foo.bar'));
    }

    public function testCode(): void
    {
        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        $this->assertSame('base.Code.Route.test', $routeCollection->getCode('test'));
        $this->assertSame('base.Code.Route.test', $routeCollection->getCode('base.Code.Route.test'));
    }

    public function testCollection(): void
    {
        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        $routeCollection->add('view');
        $this->assertTrue($routeCollection->has('view'));
        $this->assertTrue($routeCollection->hasCached('view'));

        $routeCollection->remove('view');
        $this->assertFalse($routeCollection->has('view'));
        $this->assertTrue($routeCollection->hasCached('view'));

        $routeCollection->restore('view');
        $this->assertTrue($routeCollection->has('view'));
        $this->assertTrue($routeCollection->hasCached('view'));

        $routeCollection->add('create');
        $route = $routeCollection->get('create');

        $this->assertInstanceOf(Route::class, $route);

        $routeCollection->add('view');
        $routeCollection->add('edit');
        $routeCollection->clear();
        $this->assertFalse($routeCollection->has('create'));
        $this->assertFalse($routeCollection->has('view'));
        $this->assertFalse($routeCollection->has('edit'));

        $routeCollection->add('create');
        $routeCollection->add('view');
        $routeCollection->add('edit');
        $routeCollection->add('list');
        $routeCollection->clearExcept(['create', 'edit']);
        $this->assertTrue($routeCollection->has('create'));
        $this->assertTrue($routeCollection->has('edit'));
        $this->assertFalse($routeCollection->has('view'));
        $this->assertFalse($routeCollection->has('list'));

        $routeCollection->clearExcept('create');
        $this->assertTrue($routeCollection->has('create'));
        $this->assertFalse($routeCollection->has('edit'));
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

        $this->assertTrue($parentCollection->has('view'));
        $this->assertTrue($parentCollection->has('edit'));
        $this->assertFalse($parentCollection->has('create'));

        $this->assertFalse($parentCollection->has('baseCodeRouteChild.edit'));
    }

    public function testRoute(): void
    {
        $routeCollection = new RouteCollection('baseCodeRoute', 'baseRouteName', 'baseRoutePattern', 'BundleName:ControllerName');

        $routeCollection->add('view');

        $route = $routeCollection->get('view');

        $this->assertSame('BundleName:ControllerName:view', $route->getDefault('_controller'));
        $this->assertSame('baseCodeRoute', $route->getDefault('_sonata_admin'));
        $this->assertSame('baseRouteName_view', $route->getDefault('_sonata_name'));
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

        $combinedPattern = '/'.$baseRoutePattern.'/'.($pattern ?: $name);

        $this->assertSame($combinedPattern, $route->getPath());
        $this->assertArrayHasKey('_controller', $route->getDefaults());
        $this->assertArrayHasKey('page', $route->getRequirements());
        $this->assertArrayHasKey('debug', $route->getOptions());
        $this->assertSame($host, $route->getHost());
        $this->assertSame($methods, $route->getMethods());
        if (method_exists($route, 'getCondition')) {
            $this->assertSame($condition, $route->getCondition());
        }
    }

    public function testRouteControllerService(): void
    {
        $routeCollection = new RouteCollection('baseCodeRoute', 'baseRouteName', 'baseRoutePattern', 'baseControllerServiceName');

        $routeCollection->add('view');

        $route = $routeCollection->get('view');

        $this->assertSame('baseControllerServiceName:viewAction', $route->getDefault('_controller'));
        $this->assertSame('baseCodeRoute', $route->getDefault('_sonata_admin'));
        $this->assertSame('baseRouteName_view', $route->getDefault('_sonata_name'));
    }

    public function testControllerWithFQCN(): void
    {
        $routeCollection = new RouteCollection('baseCodeRoute', 'baseRouteName', 'baseRoutePattern', CRUDController::class);
        $routeCollection->add('view');
        $route = $routeCollection->get('view');

        $this->assertSame('Sonata\AdminBundle\Controller\CRUDController::viewAction', $route->getDefault('_controller'));
    }

    public function testControllerWithBundleSubFolder(): void
    {
        $routeCollection = new RouteCollection('baseCodeRoute', 'baseRouteName', 'baseRoutePattern', 'AppBundle\Admin:Test');
        $routeCollection->add('view');
        $route = $routeCollection->get('view');

        $this->assertSame('AppBundle\Admin:Test:view', $route->getDefault('_controller'));
    }
}
