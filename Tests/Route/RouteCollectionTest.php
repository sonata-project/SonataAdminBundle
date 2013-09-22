<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Route;

use Sonata\AdminBundle\Route\RouteCollection;

class RouteCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetter()
    {
        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        $this->assertEquals('base.Code.Route', $routeCollection->getBaseCodeRoute());
        $this->assertEquals('baseRouteName', $routeCollection->getBaseRouteName());
        $this->assertEquals('baseRoutePattern', $routeCollection->getBaseRoutePattern());
        $this->assertEquals('baseControllerName', $routeCollection->getBaseControllerName());
    }

    public function testActionify()
    {
        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'BundleName:ControllerName');

        $this->assertEquals('fooBar', $routeCollection->actionify('Foo bar'));
        $this->assertEquals('bar', $routeCollection->actionify('Foo.bar'));
    }

    public function testActionifyService()
    {
        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerService');

        $this->assertEquals('fooBarAction', $routeCollection->actionify('Foo bar'));
        $this->assertEquals('barAction', $routeCollection->actionify('Foo.bar'));
    }

    public function testCode()
    {
        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        $this->assertEquals('base.Code.Route.test', $routeCollection->getCode('test'));
        $this->assertEquals('base.Code.Route.test', $routeCollection->getCode('base.Code.Route.test'));
    }

    public function testCollection()
    {
        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        $routeCollection->add('view');
        $this->assertTrue($routeCollection->has('view'));

        $routeCollection->remove('view');
        $this->assertFalse($routeCollection->has('view'));

        $routeCollection->add('create');
        $route = $routeCollection->get('create');

        $this->assertInstanceOf('Symfony\Component\Routing\Route', $route);

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
        $routeCollection->clearExcept(array('create','edit'));
        $this->assertTrue($routeCollection->has('create'));
        $this->assertTrue($routeCollection->has('edit'));
        $this->assertFalse($routeCollection->has('view'));
        $this->assertFalse($routeCollection->has('list'));
    }

    public function testGetWithException()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Element "foo" does not exist.');

        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');
        $routeCollection->get('foo');
    }

    public function testChildCollection()
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

    public function testRoute()
    {
        $routeCollection = new RouteCollection('baseCodeRoute', 'baseRouteName', 'baseRoutePattern', 'BundleName:ControllerName');

        $routeCollection->add('view');

        $route = $routeCollection->get('view');

        $this->assertEquals('BundleName:ControllerName:view', $route->getDefault('_controller'));
        $this->assertEquals('baseCodeRoute', $route->getDefault('_sonata_admin'));
        $this->assertEquals('baseRouteName_view', $route->getDefault('_sonata_name'));
    }

    public function testRouteControllerService()
    {
        $routeCollection = new RouteCollection('baseCodeRoute', 'baseRouteName', 'baseRoutePattern', 'baseControllerServiceName');

        $routeCollection->add('view');

        $route = $routeCollection->get('view');

        $this->assertEquals('baseControllerServiceName:viewAction', $route->getDefault('_controller'));
        $this->assertEquals('baseCodeRoute', $route->getDefault('_sonata_admin'));
        $this->assertEquals('baseRouteName_view', $route->getDefault('_sonata_name'));
    }
}
