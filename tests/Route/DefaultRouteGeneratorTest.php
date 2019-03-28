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
        $router->expects($this->once())->method('generate')->will($this->returnValue('/foo/bar'));

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
        $admin->expects($this->any())->method('isChild')->will($this->returnValue(false));
        $admin->expects($this->any())->method('getBaseCodeRoute')->will($this->returnValue('base.Code.Foo'));
        $admin->expects($this->once())->method('hasParentFieldDescription')->will($this->returnValue(false));
        $admin->expects($this->once())->method('hasRequest')->will($this->returnValue(true));
        $admin->expects($this->any())->method('getUniqid')->will($this->returnValue('foo_uniqueid'));
        $admin->expects($this->once())->method('getPersistentParameters')->will($this->returnValue(['abc' => 'a123', 'efg' => 'e456']));
        $admin->expects($this->any())->method('getRoutes')->will($this->returnValue($collection));
        $admin->expects($this->any())->method('getExtensions')->will($this->returnValue([]));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue($name));

        $router = $this->getMockForAbstractClass(RouterInterface::class);
        $router->expects($this->once())
            ->method('generate')
            ->will($this->returnCallback(function ($name, array $parameters = []) {
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
            }));

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
        $this->expectException(\RuntimeException::class, 'unable to find the route `base.Code.Route.foo`');

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->expects($this->any())->method('isChild')->will($this->returnValue(false));
        $admin->expects($this->any())->method('getBaseCodeRoute')->will($this->returnValue('base.Code.Route'));
        $admin->expects($this->once())->method('hasParentFieldDescription')->will($this->returnValue(false));
        $admin->expects($this->once())->method('hasRequest')->will($this->returnValue(true));
        $admin->expects($this->once())->method('getPersistentParameters')->will($this->returnValue([]));
        $admin->expects($this->exactly(2))->method('getRoutes')->will($this->returnValue(new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'BundleName:ControllerName')));
        $admin->expects($this->any())->method('getExtensions')->will($this->returnValue([]));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('Code'));

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
        $admin->expects($this->any())->method('isChild')->will($this->returnValue(true));
        $admin->expects($this->any())->method('getBaseCodeRoute')->will($this->returnValue('base.Code.Parent|base.Code.Child'));
        $admin->expects($this->any())->method('getIdParameter')->will($this->returnValue('id'));
        $admin->expects($this->any())->method('hasParentFieldDescription')->will($this->returnValue(false));
        $admin->expects($this->any())->method('hasRequest')->will($this->returnValue(true));
        $admin->expects($this->any())->method('getUniqid')->will($this->returnValue('foo_uniqueid'));
        $admin->expects($this->any())->method('getPersistentParameters')->will($this->returnValue(['abc' => 'a123', 'efg' => 'e456']));
        $admin->expects($this->any())->method('getRoutes')->will($this->returnValue($childCollection));
        $admin->expects($this->any())->method('getExtensions')->will($this->returnValue([]));

        $parentAdmin = $this->getMockForAbstractClass(AdminInterface::class);
        $parentAdmin->expects($this->any())->method('getIdParameter')->will($this->returnValue('childId'));
        $parentAdmin->expects($this->any())->method('getRoutes')->will($this->returnValue($collection));
        $parentAdmin->expects($this->any())->method('getBaseCodeRoute')->will($this->returnValue('base.Code.Parent'));
        $parentAdmin->expects($this->any())->method('getExtensions')->will($this->returnValue([]));
        $parentAdmin->expects($this->any())->method('getCode')->will($this->returnValue($name));

        // no request attached in this test, so this will not be used
        $parentAdmin->expects($this->never())->method('getPersistentParameters')->will($this->returnValue(['from' => 'parent']));

        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->attributes->expects($this->any())->method('has')->will($this->returnValue(true));
        $request->attributes->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($key) {
                if ('childId' === $key) {
                    return '987654';
                }
            }));

        $admin->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $admin->expects($this->any())->method('getParent')->will($this->returnValue($parentAdmin));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue($name));

        $router = $this->getMockForAbstractClass(RouterInterface::class);
        $router->expects($this->once())
            ->method('generate')
            ->will($this->returnCallback(function ($name, array $parameters = []) {
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
            }));

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
        $admin->expects($this->any())->method('isChild')->will($this->returnValue(false));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('base.Code.Parent'));
        $admin->expects($this->any())->method('getBaseCodeRoute')->will($this->returnValue('base.Code.Parent'));
        // embeded admin (not nested ...)
        $admin->expects($this->once())->method('hasParentFieldDescription')->will($this->returnValue(true));
        $admin->expects($this->once())->method('hasRequest')->will($this->returnValue(true));
        $admin->expects($this->any())->method('getUniqid')->will($this->returnValue('foo_uniqueid'));
        $admin->expects($this->once())->method('getPersistentParameters')->will($this->returnValue(['abc' => 'a123', 'efg' => 'e456']));
        $admin->expects($this->any())->method('getExtensions')->will($this->returnValue([]));
        $admin->expects($this->any())->method('getRoutes')->will($this->returnValue($collection));

        $router = $this->getMockForAbstractClass(RouterInterface::class);
        $router->expects($this->once())
            ->method('generate')
            ->will($this->returnCallback(function ($name, array $parameters = []) {
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
            }));

        $fieldDescription = $this->getMockForAbstractClass(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getOption')->will($this->returnValue([]));

        $parentAdmin = $this->getMockForAbstractClass(AdminInterface::class);
        $parentAdmin->expects($this->any())->method('getUniqid')->will($this->returnValue('parent_foo_uniqueid'));
        $parentAdmin->expects($this->any())->method('getCode')->will($this->returnValue('parent_foo_code'));
        $parentAdmin->expects($this->any())->method('getExtensions')->will($this->returnValue([]));

        $fieldDescription->expects($this->any())->method('getAdmin')->will($this->returnValue($parentAdmin));
        $admin->expects($this->any())->method('getParentFieldDescription')->will($this->returnValue($fieldDescription));

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
        $admin->expects($this->any())->method('isChild')->will($this->returnValue(true));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('base.Code.Child'));
        $admin->expects($this->any())->method('getBaseCodeRoute')->will($this->returnValue('base.Code.Parent|base.Code.Child'));
        $admin->expects($this->any())->method('getIdParameter')->will($this->returnValue('id'));
        $admin->expects($this->any())->method('hasParentFieldDescription')->will($this->returnValue(false));
        $admin->expects($this->any())->method('hasRequest')->will($this->returnValue(true));
        $admin->expects($this->any())->method('getUniqid')->will($this->returnValue('foo_uniqueid'));
        $admin->expects($this->any())->method('getPersistentParameters')->will($this->returnValue(['abc' => 'a123', 'efg' => 'e456']));
        $admin->expects($this->any())->method('getRoutes')->will($this->returnValue($childCollection));
        $admin->expects($this->any())->method('getExtensions')->will($this->returnValue([]));

        $parentAdmin = $this->getMockForAbstractClass(AdminInterface::class);
        $parentAdmin->expects($this->any())->method('getIdParameter')->will($this->returnValue('childId'));
        $parentAdmin->expects($this->any())->method('getRoutes')->will($this->returnValue($collection));
        $parentAdmin->expects($this->any())->method('getCode')->will($this->returnValue('base.Code.Parent'));
        $parentAdmin->expects($this->any())->method('getExtensions')->will($this->returnValue([]));

        // no request attached in this test, so this will not be used
        $parentAdmin->expects($this->never())->method('getPersistentParameters')->will($this->returnValue(['from' => 'parent']));

        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->attributes->expects($this->any())->method('has')->will($this->returnValue(true));
        $request->attributes->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($key) {
                if ('childId' === $key) {
                    return '987654';
                }
            }));

        $admin->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $admin->expects($this->any())->method('getParent')->will($this->returnValue($parentAdmin));

        $standaloneAdmin = $this->getMockForAbstractClass(AdminInterface::class);
        $standaloneAdmin->expects($this->any())->method('isChild')->will($this->returnValue(false));
        $standaloneAdmin->expects($this->any())->method('getBaseCodeRoute')->will($this->returnValue('base.Code.Child'));
        $standaloneAdmin->expects($this->once())->method('hasParentFieldDescription')->will($this->returnValue(false));
        $standaloneAdmin->expects($this->once())->method('hasRequest')->will($this->returnValue(true));
        $standaloneAdmin->expects($this->any())->method('getUniqid')->will($this->returnValue('foo_uniqueid'));
        $standaloneAdmin->expects($this->once())->method('getPersistentParameters')->will($this->returnValue(['abc' => 'a123', 'efg' => 'e456']));
        $standaloneAdmin->expects($this->any())->method('getRoutes')->will($this->returnValue($standaloneCollection));
        $standaloneAdmin->expects($this->any())->method('getExtensions')->will($this->returnValue([]));
        $standaloneAdmin->expects($this->any())->method('getCode')->will($this->returnValue('Code'));

        $router = $this->getMockForAbstractClass(RouterInterface::class);
        $router->expects($this->exactly(2))
            ->method('generate')
            ->will($this->returnCallback(function ($name, array $parameters = []) {
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
            }));

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
