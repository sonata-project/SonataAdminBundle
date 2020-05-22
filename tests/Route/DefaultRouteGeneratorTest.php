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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class DefaultRouteGeneratorTest extends TestCase
{
    private const ROUTER_DOMAIN = 'http://sonata-project';

    protected $cacheTempFolder;

    protected function setUp(): void
    {
        $this->cacheTempFolder = sys_get_temp_dir().'/sonata_test_route';

        $filesystem = new Filesystem();
        $filesystem->remove($this->cacheTempFolder);
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
    public function testGenerateUrl(
        string $expected,
        string $name,
        array $parameters,
        int $referenceType = RouterInterface::ABSOLUTE_PATH
    ): void {
        $childCollection = new RouteCollection('base.Code.Foo|base.Code.Bar', 'admin_acme_child', '/foo/', 'BundleName:ControllerName');
        $childCollection->add('bar');

        $collection = new RouteCollection('base.Code.Foo', 'admin_acme', '/', 'BundleName:ControllerName');
        $collection->add('foo');
        $collection->addCollection($childCollection);

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->method('isChild')->willReturn(false);
        $admin->method('getCode')->willReturn('base.Code.Foo');
        $admin->expects($this->once())->method('hasParentFieldDescription')->willReturn(false);
        $admin->expects($this->once())->method('hasRequest')->willReturn(true);
        $admin->method('getUniqid')->willReturn('foo_uniqueid');
        $admin->method('getCode')->willReturn('foo_code');
        $admin->expects($this->once())->method('getPersistentParameters')->willReturn(['abc' => 'a123', 'efg' => 'e456']);
        $admin->method('getRoutes')->willReturn($collection);
        $admin->method('getExtensions')->willReturn([]);

        $router = $this->getMockForAbstractClass(RouterInterface::class);
        $router->expects($this->once())
            ->method('generate')
            ->willReturnCallback(static function (string $name, array $parameters = [], int $referenceType = RouterInterface::ABSOLUTE_PATH): string {
                $params = '';
                $domain = RouterInterface::ABSOLUTE_URL === $referenceType ? self::ROUTER_DOMAIN : '';
                if (!empty($parameters)) {
                    $params .= '?'.http_build_query($parameters);
                }

                switch ($name) {
                    case 'admin_acme_foo':
                        return $domain.'/foo'.$params;
                    case 'admin_acme_child_bar':
                        return $domain.'/foo/bar'.$params;
                }
            });

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);

        $this->assertSame($expected, $generator->generateUrl($admin, $name, $parameters, $referenceType));
    }

    public function getGenerateUrlTests(): array
    {
        return [
            ['/foo?abc=a123&efg=e456&default_param=default_val', 'foo', ['default_param' => 'default_val']],
            ['/foo/bar?abc=a123&efg=e456&default_param=default_val', 'base.Code.Bar.bar', ['default_param' => 'default_val']],
            ['/foo/bar?abc=a123&efg=e456&default_param=default_val', 'base.Code.Bar.bar', ['default_param' => 'default_val'], RouterInterface::ABSOLUTE_PATH],
            [
                self::ROUTER_DOMAIN.'/foo/bar?abc=a123&efg=e456&default_param=default_val',
                'base.Code.Bar.bar',
                ['default_param' => 'default_val'],
                RouterInterface::ABSOLUTE_URL,
            ],
        ];
    }

    public function testGenerateUrlWithException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('unable to find the route `base.Code.Route.foo`');

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->method('isChild')->willReturn(false);
        $admin->method('getCode')->willReturn('base.Code.Route');
        $admin->expects($this->once())->method('hasParentFieldDescription')->willReturn(false);
        $admin->expects($this->once())->method('hasRequest')->willReturn(true);
        $admin->expects($this->once())->method('getPersistentParameters')->willReturn([]);
        $admin->expects($this->exactly(2))->method('getRoutes')->willReturn(new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'BundleName:ControllerName'));
        $admin->method('getExtensions')->willReturn([]);

        $router = $this->getMockForAbstractClass(RouterInterface::class);

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);
        $generator->generateUrl($admin, 'foo', []);
    }

    /**
     * @dataProvider getGenerateUrlChildTests
     */
    public function testGenerateUrlChild(string $type, string $expected, string $name, array $parameters): void
    {
        $childCollection = new RouteCollection('base.Code.Parent|base.Code.Child', 'admin_acme_child', '/foo/', 'BundleName:ControllerName');
        $childCollection->add('bar');

        $collection = new RouteCollection('base.Code.Parent', 'admin_acme', '/', 'BundleName:ControllerName');
        $collection->add('foo');
        $collection->addCollection($childCollection);

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->method('isChild')->willReturn(true);
        $admin->method('getCode')->willReturn('base.Code.Child');
        $admin->method('getBaseCodeRoute')->willReturn('base.Code.Parent|base.Code.Child');
        $admin->method('getIdParameter')->willReturn('id');
        $admin->method('hasParentFieldDescription')->willReturn(false);
        $admin->method('hasRequest')->willReturn(true);
        $admin->method('getUniqid')->willReturn('foo_uniqueid');
        $admin->method('getCode')->willReturn('foo_code');
        $admin->method('getPersistentParameters')->willReturn(['abc' => 'a123', 'efg' => 'e456']);
        $admin->method('getRoutes')->willReturn($childCollection);
        $admin->method('getExtensions')->willReturn([]);

        $parentAdmin = $this->getMockForAbstractClass(AdminInterface::class);
        $parentAdmin->method('getIdParameter')->willReturn('childId');
        $parentAdmin->method('getRoutes')->willReturn($collection);
        $parentAdmin->method('getCode')->willReturn('base.Code.Parent');
        $parentAdmin->method('getExtensions')->willReturn([]);

        // no request attached in this test, so this will not be used
        $parentAdmin->expects($this->never())->method('getPersistentParameters')->willReturn(['from' => 'parent']);

        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->attributes->method('has')->willReturn(true);
        $request->attributes
            ->method('get')
            ->willReturnCallback(static function (string $key): string {
                if ('childId' === $key) {
                    return '987654';
                }
            });

        $admin->method('getRequest')->willReturn($request);
        $admin->method('getParent')->willReturn($parentAdmin);

        $router = $this->getMockForAbstractClass(RouterInterface::class);
        $router->expects($this->once())
            ->method('generate')
            ->willReturnCallback(static function (string $name, array $parameters = []) {
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

    public function getGenerateUrlChildTests(): array
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
    public function testGenerateUrlParentFieldDescription(string $expected, string $name, array $parameters): void
    {
        $childCollection = new RouteCollection('base.Code.Parent|base.Code.Child', 'admin_acme_child', '/foo/', 'BundleName:ControllerName');
        $childCollection->add('bar');

        $collection = new RouteCollection('base.Code.Parent', 'admin_acme', '/', 'BundleName:ControllerName');
        $collection->add('foo');
        $collection->addCollection($childCollection);

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->method('isChild')->willReturn(false);
        $admin->method('getCode')->willReturn('base.Code.Parent');
        // embeded admin (not nested ...)
        $admin->expects($this->once())->method('hasParentFieldDescription')->willReturn(true);
        $admin->expects($this->once())->method('hasRequest')->willReturn(true);
        $admin->method('getUniqid')->willReturn('foo_uniqueid');
        $admin->method('getCode')->willReturn('foo_code');
        $admin->expects($this->once())->method('getPersistentParameters')->willReturn(['abc' => 'a123', 'efg' => 'e456']);
        $admin->method('getExtensions')->willReturn([]);
        $admin->method('getRoutes')->willReturn($collection);

        $router = $this->getMockForAbstractClass(RouterInterface::class);
        $router->expects($this->once())
            ->method('generate')
            ->willReturnCallback(static function (string $name, array $parameters = []): string {
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
        $parentAdmin->method('getUniqid')->willReturn('parent_foo_uniqueid');
        $parentAdmin->method('getCode')->willReturn('parent_foo_code');
        $parentAdmin->method('getExtensions')->willReturn([]);

        $fieldDescription->method('getAdmin')->willReturn($parentAdmin);
        $admin->method('getParentFieldDescription')->willReturn($fieldDescription);

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);

        $this->assertSame($expected, $generator->generateUrl($admin, $name, $parameters));
    }

    public function getGenerateUrlParentFieldDescriptionTests(): array
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
    public function testGenerateUrlLoadCache(string $expected, string $name, array $parameters): void
    {
        $childCollection = new RouteCollection('base.Code.Parent|base.Code.Child', 'admin_acme_child', '/foo', 'BundleName:ControllerName');
        $childCollection->add('bar');

        $collection = new RouteCollection('base.Code.Parent', 'admin_acme', '/', 'BundleName:ControllerName');
        $collection->add('foo');
        $collection->addCollection($childCollection);

        $standaloneCollection = new RouteCollection('base.Code.Child', 'admin_acme_child_standalone', '/', 'BundleName:ControllerName');
        $standaloneCollection->add('bar');

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->method('isChild')->willReturn(true);
        $admin->method('getCode')->willReturn('base.Code.Child');
        $admin->method('getBaseCodeRoute')->willReturn('base.Code.Parent|base.Code.Child');
        $admin->method('getIdParameter')->willReturn('id');
        $admin->method('hasParentFieldDescription')->willReturn(false);
        $admin->method('hasRequest')->willReturn(true);
        $admin->method('getUniqid')->willReturn('foo_uniqueid');
        $admin->method('getPersistentParameters')->willReturn(['abc' => 'a123', 'efg' => 'e456']);
        $admin->method('getRoutes')->willReturn($childCollection);
        $admin->method('getExtensions')->willReturn([]);

        $parentAdmin = $this->getMockForAbstractClass(AdminInterface::class);
        $parentAdmin->method('getIdParameter')->willReturn('childId');
        $parentAdmin->method('getRoutes')->willReturn($collection);
        $parentAdmin->method('getCode')->willReturn('base.Code.Parent');
        $parentAdmin->method('getExtensions')->willReturn([]);

        // no request attached in this test, so this will not be used
        $parentAdmin->expects($this->never())->method('getPersistentParameters')->willReturn(['from' => 'parent']);

        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->attributes->method('has')->willReturn(true);
        $request->attributes
            ->method('get')
            ->willReturnCallback(static function (string $key): string {
                if ('childId' === $key) {
                    return '987654';
                }
            });

        $admin->method('getRequest')->willReturn($request);
        $admin->method('getParent')->willReturn($parentAdmin);

        $standaloneAdmin = $this->getMockForAbstractClass(AdminInterface::class);
        $standaloneAdmin->method('isChild')->willReturn(false);
        $standaloneAdmin->method('getCode')->willReturn('base.Code.Child');
        $standaloneAdmin->expects($this->once())->method('hasParentFieldDescription')->willReturn(false);
        $standaloneAdmin->expects($this->once())->method('hasRequest')->willReturn(true);
        $standaloneAdmin->method('getUniqid')->willReturn('foo_uniqueid');
        $standaloneAdmin->expects($this->once())->method('getPersistentParameters')->willReturn(['abc' => 'a123', 'efg' => 'e456']);
        $standaloneAdmin->method('getRoutes')->willReturn($standaloneCollection);
        $standaloneAdmin->method('getExtensions')->willReturn([]);

        $router = $this->getMockForAbstractClass(RouterInterface::class);
        $router->expects($this->exactly(2))
            ->method('generate')
            ->willReturnCallback(static function (string $name, array $parameters = []): string {
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

    public function getGenerateUrlLoadCacheTests(): array
    {
        return [
            ['/bar?abc=a123&efg=e456&id=123&default_param=default_val', 'bar', ['id' => 123, 'default_param' => 'default_val']],
        ];
    }
}
