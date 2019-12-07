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
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Route\DefaultRouteGenerator;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RoutesCache;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class DefaultRouteGeneratorTest extends TestCase
{
    protected $cacheTempFolder;

    public function setUp(): void
    {
        $this->cacheTempFolder = sys_get_temp_dir().'/sonata_test_route';

        exec('rm -rf '.$this->cacheTempFolder);
    }

    public function testGenerate(): void
    {
        $router = $this->getMockForAbstractClass(RouterInterface::class);
        $router->expects($this->once())->method('generate')->willReturn('/foo/bar');

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);

        $this->assertSame('/foo/bar', $generator->generate('foo_bar'));
    }

    /**
     * @dataProvider getGenerateUrlTests
     */
    public function testGenerateUrl($expected, $name, array $parameters): void
    {
        $childCollection = new RouteCollection('base.Code.Foo|base.Code.Bar', 'admin_acme_child', '/foo/', 'BundleName:ControllerName');
        $childCollection->add('bar');

        $collection = new RouteCollection('base.Code.Foo', 'admin_acme', '/', 'BundleName:ControllerName');
        $collection->add('foo');
        $collection->addCollection($childCollection);

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->expects($this->any())->method('isChild')->willReturn(false);
        $admin->expects($this->any())->method('getCode')->willReturn('base.Code.Foo');
        $admin->expects($this->once())->method('hasParentFieldDescription')->willReturn(false);
        $admin->expects($this->once())->method('hasRequest')->willReturn(true);
        $admin->expects($this->any())->method('getUniqid')->willReturn('foo_uniqueid');
        $admin->expects($this->any())->method('getCode')->willReturn('foo_code');
        $admin->expects($this->once())->method('getPersistentParameters')->willReturn(['abc' => 'a123', 'efg' => 'e456']);
        $admin->expects($this->any())->method('getRoutes')->willReturn($collection);
        $admin->expects($this->any())->method('getExtensions')->willReturn([]);

        $router = $this->getMockForAbstractClass(RouterInterface::class);
        $router->expects($this->once())
            ->method('generate')
            ->willReturnCallback(static function ($name, array $parameters = []) {
                $params = '';
                if (!empty($parameters)) {
                    $params .= '?'.http_build_query($parameters);
                }

                switch ($name) {
                    case 'admin_acme_foo':
                        return '/foo'.$params;
                    case 'admin_acme_child_bar':
                        return '/foo/bar'.$params;
                }
            });

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);

        $this->assertSame($expected, $generator->generateUrl($admin, $name, $parameters));
    }

    public function getGenerateUrlTests()
    {
        return [
            ['/foo?abc=a123&efg=e456&default_param=default_val', 'foo', ['default_param' => 'default_val']],
            ['/foo/bar?abc=a123&efg=e456&default_param=default_val', 'base.Code.Bar.bar', ['default_param' => 'default_val']],
        ];
    }

    public function testGenerateUrlWithException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('unable to find the route `base.Code.Route.foo`');

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->expects($this->any())->method('isChild')->willReturn(false);
        $admin->expects($this->any())->method('getCode')->willReturn('base.Code.Route');
        $admin->expects($this->once())->method('hasParentFieldDescription')->willReturn(false);
        $admin->expects($this->once())->method('hasRequest')->willReturn(true);
        $admin->expects($this->once())->method('getPersistentParameters')->willReturn([]);
        $admin->expects($this->exactly(2))->method('getRoutes')->willReturn(new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'BundleName:ControllerName'));
        $admin->expects($this->any())->method('getExtensions')->willReturn([]);

        $router = $this->getMockForAbstractClass(RouterInterface::class);

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);
        $generator->generateUrl($admin, 'foo', []);
    }

    /**
     * @dataProvider getGenerateUrlChildTests
     */
    public function testGenerateUrlChild($type, $expected, $name, array $parameters): void
    {
        $childCollection = new RouteCollection('base.Code.Parent|base.Code.Child', 'admin_acme_child', '/foo/', 'BundleName:ControllerName');
        $childCollection->add('bar');

        $collection = new RouteCollection('base.Code.Parent', 'admin_acme', '/', 'BundleName:ControllerName');
        $collection->add('foo');
        $collection->addCollection($childCollection);

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->expects($this->any())->method('isChild')->willReturn(true);
        $admin->expects($this->any())->method('getCode')->willReturn('base.Code.Child');
        $admin->expects($this->any())->method('getBaseCodeRoute')->willReturn('base.Code.Parent|base.Code.Child');
        $admin->expects($this->any())->method('getIdParameter')->willReturn('id');
        $admin->expects($this->any())->method('hasParentFieldDescription')->willReturn(false);
        $admin->expects($this->any())->method('hasRequest')->willReturn(true);
        $admin->expects($this->any())->method('getUniqid')->willReturn('foo_uniqueid');
        $admin->expects($this->any())->method('getCode')->willReturn('foo_code');
        $admin->expects($this->any())->method('getPersistentParameters')->willReturn(['abc' => 'a123', 'efg' => 'e456']);
        $admin->expects($this->any())->method('getRoutes')->willReturn($childCollection);
        $admin->expects($this->any())->method('getExtensions')->willReturn([]);

        $parentAdmin = $this->getMockForAbstractClass(AdminInterface::class);
        $parentAdmin->expects($this->any())->method('getIdParameter')->willReturn('childId');
        $parentAdmin->expects($this->any())->method('getRoutes')->willReturn($collection);
        $parentAdmin->expects($this->any())->method('getCode')->willReturn('base.Code.Parent');
        $parentAdmin->expects($this->any())->method('getExtensions')->willReturn([]);

        // no request attached in this test, so this will not be used
        $parentAdmin->expects($this->never())->method('getPersistentParameters')->willReturn(['from' => 'parent']);

        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->attributes->expects($this->any())->method('has')->willReturn(true);
        $request->attributes->expects($this->any())
            ->method('get')
            ->willReturnCallback(static function ($key) {
                if ('childId' === $key) {
                    return '987654';
                }
            });

        $admin->expects($this->any())->method('getRequest')->willReturn($request);
        $admin->expects($this->any())->method('getParent')->willReturn($parentAdmin);

        $router = $this->getMockForAbstractClass(RouterInterface::class);
        $router->expects($this->once())
            ->method('generate')
            ->willReturnCallback(static function ($name, array $parameters = []) {
                $params = '';
                if (!empty($parameters)) {
                    $params .= '?'.http_build_query($parameters);
                }

                switch ($name) {
                    case 'admin_acme_foo':
                        return '/foo'.$params;
                    case 'admin_acme_child_bar':
                        return '/foo/bar'.$params;
                }
            });

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);

        $this->assertSame($expected, $generator->generateUrl('child' === $type ? $admin : $parentAdmin, $name, $parameters));
    }

    public function getGenerateUrlChildTests()
    {
        return [
            ['parent', '/foo?id=123&default_param=default_val', 'foo', ['id' => 123, 'default_param' => 'default_val']],
            ['parent', '/foo/bar?id=123&default_param=default_val', 'base.Code.Child.bar', ['id' => 123, 'default_param' => 'default_val']],
            ['child', '/foo/bar?abc=a123&efg=e456&default_param=default_val&childId=987654', 'bar', ['id' => 123, 'default_param' => 'default_val']],
        ];
    }

    /**
     * @dataProvider getGenerateUrlParentFieldDescriptionTests
     */
    public function testGenerateUrlParentFieldDescription($expected, $name, array $parameters): void
    {
        $childCollection = new RouteCollection('base.Code.Parent|base.Code.Child', 'admin_acme_child', '/foo/', 'BundleName:ControllerName');
        $childCollection->add('bar');

        $collection = new RouteCollection('base.Code.Parent', 'admin_acme', '/', 'BundleName:ControllerName');
        $collection->add('foo');
        $collection->addCollection($childCollection);

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->expects($this->any())->method('isChild')->willReturn(false);
        $admin->expects($this->any())->method('getCode')->willReturn('base.Code.Parent');
        // embeded admin (not nested ...)
        $admin->expects($this->once())->method('hasParentFieldDescription')->willReturn(true);
        $admin->expects($this->once())->method('hasRequest')->willReturn(true);
        $admin->expects($this->any())->method('getUniqid')->willReturn('foo_uniqueid');
        $admin->expects($this->any())->method('getCode')->willReturn('foo_code');
        $admin->expects($this->once())->method('getPersistentParameters')->willReturn(['abc' => 'a123', 'efg' => 'e456']);
        $admin->expects($this->any())->method('getExtensions')->willReturn([]);
        $admin->expects($this->any())->method('getRoutes')->willReturn($collection);

        $router = $this->getMockForAbstractClass(RouterInterface::class);
        $router->expects($this->once())
            ->method('generate')
            ->willReturnCallback(static function ($name, array $parameters = []) {
                $params = '';
                if (!empty($parameters)) {
                    $params .= '?'.http_build_query($parameters);
                }

                switch ($name) {
                    case 'admin_acme_foo':
                        return '/foo'.$params;
                    case 'admin_acme_child_bar':
                        return '/foo/bar'.$params;
                }
            });

        $fieldDescription = $this->getMockForAbstractClass(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getOption')->willReturn([]);

        $parentAdmin = $this->getMockForAbstractClass(AdminInterface::class);
        $parentAdmin->expects($this->any())->method('getUniqid')->willReturn('parent_foo_uniqueid');
        $parentAdmin->expects($this->any())->method('getCode')->willReturn('parent_foo_code');
        $parentAdmin->expects($this->any())->method('getExtensions')->willReturn([]);

        $fieldDescription->expects($this->any())->method('getAdmin')->willReturn($parentAdmin);
        $admin->expects($this->any())->method('getParentFieldDescription')->willReturn($fieldDescription);

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);

        $this->assertSame($expected, $generator->generateUrl($admin, $name, $parameters));
    }

    public function getGenerateUrlParentFieldDescriptionTests()
    {
        return [
            ['/foo?abc=a123&efg=e456&default_param=default_val&uniqid=foo_uniqueid&code=base.Code.Parent&pcode=parent_foo_code&puniqid=parent_foo_uniqueid', 'foo', ['default_param' => 'default_val']],
            // this second test does not make sense as we cannot have embeded admin with nested admin....
            ['/foo/bar?abc=a123&efg=e456&default_param=default_val&uniqid=foo_uniqueid&code=base.Code.Parent&pcode=parent_foo_code&puniqid=parent_foo_uniqueid', 'base.Code.Child.bar', ['default_param' => 'default_val']],
        ];
    }

    /**
     * @dataProvider getGenerateUrlLoadCacheTests
     */
    public function testGenerateUrlLoadCache($expected, $name, array $parameters): void
    {
        $childCollection = new RouteCollection('base.Code.Parent|base.Code.Child', 'admin_acme_child', '/foo', 'BundleName:ControllerName');
        $childCollection->add('bar');

        $collection = new RouteCollection('base.Code.Parent', 'admin_acme', '/', 'BundleName:ControllerName');
        $collection->add('foo');
        $collection->addCollection($childCollection);

        $standaloneCollection = new RouteCollection('base.Code.Child', 'admin_acme_child_standalone', '/', 'BundleName:ControllerName');
        $standaloneCollection->add('bar');

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->expects($this->any())->method('isChild')->willReturn(true);
        $admin->expects($this->any())->method('getCode')->willReturn('base.Code.Child');
        $admin->expects($this->any())->method('getBaseCodeRoute')->willReturn('base.Code.Parent|base.Code.Child');
        $admin->expects($this->any())->method('getIdParameter')->willReturn('id');
        $admin->expects($this->any())->method('hasParentFieldDescription')->willReturn(false);
        $admin->expects($this->any())->method('hasRequest')->willReturn(true);
        $admin->expects($this->any())->method('getUniqid')->willReturn('foo_uniqueid');
        $admin->expects($this->any())->method('getPersistentParameters')->willReturn(['abc' => 'a123', 'efg' => 'e456']);
        $admin->expects($this->any())->method('getRoutes')->willReturn($childCollection);
        $admin->expects($this->any())->method('getExtensions')->willReturn([]);

        $parentAdmin = $this->getMockForAbstractClass(AdminInterface::class);
        $parentAdmin->expects($this->any())->method('getIdParameter')->willReturn('childId');
        $parentAdmin->expects($this->any())->method('getRoutes')->willReturn($collection);
        $parentAdmin->expects($this->any())->method('getCode')->willReturn('base.Code.Parent');
        $parentAdmin->expects($this->any())->method('getExtensions')->willReturn([]);

        // no request attached in this test, so this will not be used
        $parentAdmin->expects($this->never())->method('getPersistentParameters')->willReturn(['from' => 'parent']);

        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->attributes->expects($this->any())->method('has')->willReturn(true);
        $request->attributes->expects($this->any())
            ->method('get')
            ->willReturnCallback(static function ($key) {
                if ('childId' === $key) {
                    return '987654';
                }
            });

        $admin->expects($this->any())->method('getRequest')->willReturn($request);
        $admin->expects($this->any())->method('getParent')->willReturn($parentAdmin);

        $standaloneAdmin = $this->getMockForAbstractClass(AdminInterface::class);
        $standaloneAdmin->expects($this->any())->method('isChild')->willReturn(false);
        $standaloneAdmin->expects($this->any())->method('getCode')->willReturn('base.Code.Child');
        $standaloneAdmin->expects($this->once())->method('hasParentFieldDescription')->willReturn(false);
        $standaloneAdmin->expects($this->once())->method('hasRequest')->willReturn(true);
        $standaloneAdmin->expects($this->any())->method('getUniqid')->willReturn('foo_uniqueid');
        $standaloneAdmin->expects($this->once())->method('getPersistentParameters')->willReturn(['abc' => 'a123', 'efg' => 'e456']);
        $standaloneAdmin->expects($this->any())->method('getRoutes')->willReturn($standaloneCollection);
        $standaloneAdmin->expects($this->any())->method('getExtensions')->willReturn([]);

        $router = $this->getMockForAbstractClass(RouterInterface::class);
        $router->expects($this->exactly(2))
            ->method('generate')
            ->willReturnCallback(static function ($name, array $parameters = []) {
                $params = '';
                if (!empty($parameters)) {
                    $params .= '?'.http_build_query($parameters);
                }

                switch ($name) {
                    case 'admin_acme_child_bar':
                        return '/foo/bar'.$params;
                    case 'admin_acme_child_standalone_bar':
                        return '/bar'.$params;
                }
            });

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);

        // Generate once to populate cache
        $generator->generateUrl($admin, 'bar', $parameters);
        $this->assertSame($expected, $generator->generateUrl($standaloneAdmin, $name, $parameters));
    }

    public function getGenerateUrlLoadCacheTests()
    {
        return [
            ['/bar?abc=a123&efg=e456&id=123&default_param=default_val', 'bar', ['id' => 123, 'default_param' => 'default_val']],
        ];
    }
}
