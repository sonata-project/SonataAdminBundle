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
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Route\DefaultRouteGenerator;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RoutesCache;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

final class DefaultRouteGeneratorTest extends TestCase
{
    private const ROUTER_DOMAIN = 'http://sonata-project';

    private string $cacheTempFolder;

    protected function setUp(): void
    {
        $this->cacheTempFolder = \sprintf('%s/sonata_test_route', sys_get_temp_dir());

        $filesystem = new Filesystem();
        $filesystem->remove($this->cacheTempFolder);
    }

    public function testGenerate(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects(static::once())->method('generate')->willReturn('/foo/bar');

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);

        static::assertSame('/foo/bar', $generator->generate('foo_bar'));
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @dataProvider provideGenerateUrlCases
     */
    public function testGenerateUrl(
        string $expected,
        string $name,
        array $parameters,
        int $referenceType = RouterInterface::ABSOLUTE_PATH,
    ): void {
        $childCollection = new RouteCollection('base.Code.Foo|base.Code.Bar', 'admin_acme_child', '/foo/', 'BundleName:ControllerName');
        $childCollection->add('bar');

        $collection = new RouteCollection('base.Code.Foo', 'admin_acme', '/', 'BundleName:ControllerName');
        $collection->add('foo');
        $collection->addCollection($childCollection);

        $admin = $this->createMock(AdminInterface::class);
        $admin->method('isChild')->willReturn(false);
        $admin->method('getBaseCodeRoute')->willReturn('base.Code.Foo');
        $admin->expects(static::once())->method('hasParentFieldDescription')->willReturn(false);
        $admin->expects(static::once())->method('hasRequest')->willReturn(true);
        $admin->method('getUniqId')->willReturn('foo_uniqueid');
        $admin->expects(static::once())->method('getPersistentParameters')->willReturn(['abc' => 'a123', 'efg' => 'e456']);
        $admin->method('getRoutes')->willReturn($collection);
        $admin->method('getExtensions')->willReturn([]);
        $admin->method('getCode')->willReturn($name);

        $router = $this->createMock(RouterInterface::class);
        $router->expects(static::once())
            ->method('generate')
            ->willReturnCallback(static function (string $name, array $parameters = [], int $referenceType = RouterInterface::ABSOLUTE_PATH): string {
                $params = '';
                $domain = RouterInterface::ABSOLUTE_URL === $referenceType ? self::ROUTER_DOMAIN : '';
                if ([] !== $parameters) {
                    $params .= '?'.http_build_query($parameters);
                }

                return match ($name) {
                    'admin_acme_foo' => \sprintf('%s/foo%s', $domain, $params),
                    'admin_acme_child_bar' => \sprintf('%s/foo/bar%s', $domain, $params),
                    default => throw new \LogicException('Not implemented'),
                };
            });

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);

        static::assertSame($expected, $generator->generateUrl($admin, $name, $parameters, $referenceType));
    }

    /**
     * @phpstan-return iterable<array{0: string, 1: string, 2: array<string, mixed>, 3?: int}>
     */
    public function provideGenerateUrlCases(): iterable
    {
        yield ['/foo?abc=a123&efg=e456&default_param=default_val', 'foo', ['default_param' => 'default_val']];
        yield ['/foo/bar?abc=a123&efg=e456&default_param=default_val', 'base.Code.Bar.bar', ['default_param' => 'default_val']];
        yield ['/foo/bar?abc=a123&efg=e456&default_param=default_val', 'base.Code.Bar.bar', ['default_param' => 'default_val'], RouterInterface::ABSOLUTE_PATH];
        yield [
            \sprintf('%s/foo/bar?abc=a123&efg=e456&default_param=default_val', self::ROUTER_DOMAIN),
            'base.Code.Bar.bar',
            ['default_param' => 'default_val'],
            RouterInterface::ABSOLUTE_URL,
        ];
    }

    public function testGenerateUrlWithException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('unable to find the route `base.Code.Route.foo`');

        $admin = $this->createMock(AdminInterface::class);
        $admin->method('isChild')->willReturn(false);
        $admin->method('getBaseCodeRoute')->willReturn('base.Code.Route');
        $admin->expects(static::once())->method('hasParentFieldDescription')->willReturn(false);
        $admin->expects(static::once())->method('hasRequest')->willReturn(true);
        $admin->expects(static::once())->method('getPersistentParameters')->willReturn([]);
        $admin->expects(static::once())->method('getRoutes')->willReturn(new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'BundleName:ControllerName'));
        $admin->method('getExtensions')->willReturn([]);
        $admin->method('getCode')->willReturn('Code');

        $router = $this->createMock(RouterInterface::class);

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);
        $generator->generateUrl($admin, 'foo', []);
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @dataProvider provideGenerateUrlChildCases
     */
    public function testGenerateUrlChild(string $type, string $expected, string $name, array $parameters): void
    {
        $childCollection = new RouteCollection('base.Code.Parent|base.Code.Child', 'admin_acme_child', '/foo/', 'BundleName:ControllerName');
        $childCollection->add('bar');

        $collection = new RouteCollection('base.Code.Parent', 'admin_acme', '/', 'BundleName:ControllerName');
        $collection->add('foo');
        $collection->addCollection($childCollection);

        $nochildCollection = new RouteCollection('base.Code.Child', 'admin_child', '/foo/', 'BundleName:ControllerName');
        $nochildCollection->add('bar');
        $collection->addCollection($nochildCollection);

        $admin = $this->createMock(AdminInterface::class);
        $admin->method('isChild')->willReturn(true);
        $admin->method('getBaseCodeRoute')->willReturn('base.Code.Parent|base.Code.Child');
        $admin->method('getIdParameter')->willReturn('id');
        $admin->method('hasParentFieldDescription')->willReturn(false);
        $admin->method('hasRequest')->willReturn(true);
        $admin->method('getUniqId')->willReturn('foo_uniqueid');
        $admin->method('getPersistentParameters')->willReturn(['abc' => 'a123', 'efg' => 'e456']);
        $admin->method('getRoutes')->willReturn($childCollection);
        $admin->method('getExtensions')->willReturn([]);

        $parentAdmin = $this->createMock(AdminInterface::class);
        $parentAdmin->method('getIdParameter')->willReturn('childId');
        $parentAdmin->method('getRoutes')->willReturn($collection);
        $parentAdmin->method('getBaseCodeRoute')->willReturn('base.Code.Parent');
        $parentAdmin->method('getExtensions')->willReturn([]);
        $parentAdmin->method('getCode')->willReturn($name);

        // no request attached in this test, so this will not be used
        $parentAdmin->expects(static::never())->method('getPersistentParameters')->willReturn(['from' => 'parent']);

        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->attributes->method('has')->willReturn(true);
        $request->attributes
            ->method('get')
            ->willReturnCallback(static function (string $key): ?string {
                if ('childId' === $key) {
                    return '987654';
                }

                return null;
            });

        $admin->method('getRequest')->willReturn($request);
        $admin->method('getParent')->willReturn($parentAdmin);
        $admin->method('getCode')->willReturn($name);

        $router = $this->createMock(RouterInterface::class);
        $router->expects(static::once())
            ->method('generate')
            ->willReturnCallback(static function (string $name, array $parameters = []): string {
                $params = '';
                if ([] !== $parameters) {
                    $params .= '?'.http_build_query($parameters);
                }

                return match ($name) {
                    'admin_acme_foo' => \sprintf('/foo%s', $params),
                    'admin_acme_child_bar' => \sprintf('/foo/bar%s', $params),
                    'admin_child_bar' => \sprintf('/bar%s', $params),
                    default => throw new \LogicException('Not implemented'),
                };
            });

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);

        static::assertSame($expected, $generator->generateUrl('child' === $type ? $admin : $parentAdmin, $name, $parameters));
    }

    /**
     * @phpstan-return iterable<array{string, string, string, array<string, mixed>}>
     */
    public function provideGenerateUrlChildCases(): iterable
    {
        yield ['parent', '/foo?id=123&default_param=default_val', 'foo', ['id' => 123, 'default_param' => 'default_val']];
        yield ['parent', '/foo?id=123&default_param=default_val', 'base.Code.Parent.foo', ['id' => 123, 'default_param' => 'default_val']];
        yield ['parent', '/foo/bar?id=123&default_param=default_val', 'base.Code.Child.bar', ['id' => 123, 'default_param' => 'default_val']];
        yield ['parent', '/foo/bar?id=123&default_param=default_val', 'base.Code.Parent|base.Code.Child.bar', ['id' => 123, 'default_param' => 'default_val']];
        yield ['child', '/foo/bar?abc=a123&efg=e456&default_param=default_val&childId=987654', 'bar', ['id' => 123, 'default_param' => 'default_val']];
        yield ['child', '/foo/bar?abc=a123&efg=e456&default_param=default_val&childId=987654', 'base.Code.Parent|base.Code.Child.bar', ['id' => 123, 'default_param' => 'default_val']];
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @dataProvider provideGenerateUrlParentFieldDescriptionCases
     */
    public function testGenerateUrlParentFieldDescription(string $expected, string $name, array $parameters): void
    {
        $childCollection = new RouteCollection('base.Code.Parent|base.Code.Child', 'admin_acme_child', '/foo/', 'BundleName:ControllerName');
        $childCollection->add('bar');

        $collection = new RouteCollection('base.Code.Parent', 'admin_acme', '/', 'BundleName:ControllerName');
        $collection->add('foo');
        $collection->addCollection($childCollection);

        $admin = $this->createMock(AdminInterface::class);
        $admin->method('isChild')->willReturn(false);
        $admin->method('getCode')->willReturn('base.Code.Parent');
        $admin->method('getBaseCodeRoute')->willReturn('base.Code.Parent');
        // embeded admin (not nested ...)
        $admin->expects(static::once())->method('hasParentFieldDescription')->willReturn(true);
        $admin->expects(static::once())->method('hasRequest')->willReturn(true);
        $admin->expects(static::any())->method('getUniqId')->willReturn('foo_uniqueid');
        $admin->expects(static::once())->method('getPersistentParameters')->willReturn(['abc' => 'a123', 'efg' => 'e456']);
        $admin->method('getExtensions')->willReturn([]);
        $admin->method('getRoutes')->willReturn($collection);

        $router = $this->createMock(RouterInterface::class);
        $router->expects(static::once())
            ->method('generate')
            ->willReturnCallback(static function (string $name, array $parameters = []): string {
                $params = '';
                if ([] !== $parameters) {
                    $params .= '?'.http_build_query($parameters);
                }

                return match ($name) {
                    'admin_acme_foo' => \sprintf('/foo%s', $params),
                    'admin_acme_child_bar' => \sprintf('/foo/bar%s', $params),
                    default => throw new \LogicException('Not implemented'),
                };
            });

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects(static::once())->method('getOption')->willReturn([]);

        $parentAdmin = $this->createMock(AdminInterface::class);
        $parentAdmin->method('getUniqId')->willReturn('parent_foo_uniqueid');
        $parentAdmin->method('getCode')->willReturn('parent_foo_code');
        $parentAdmin->method('getExtensions')->willReturn([]);

        $fieldDescription->method('getAdmin')->willReturn($parentAdmin);
        $admin->method('getParentFieldDescription')->willReturn($fieldDescription);

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);

        static::assertSame($expected, $generator->generateUrl($admin, $name, $parameters));
    }

    /**
     * @phpstan-return iterable<array{string, string, array<string, mixed>}>
     */
    public function provideGenerateUrlParentFieldDescriptionCases(): iterable
    {
        yield ['/foo?abc=a123&efg=e456&default_param=default_val&uniqid=foo_uniqueid&code=base.Code.Parent&pcode=parent_foo_code&puniqid=parent_foo_uniqueid', 'foo', ['default_param' => 'default_val']];
        // this second test does not make sense as we cannot have embeded admin with nested admin....
        yield ['/foo/bar?abc=a123&efg=e456&default_param=default_val&uniqid=foo_uniqueid&code=base.Code.Parent&pcode=parent_foo_code&puniqid=parent_foo_uniqueid', 'base.Code.Child.bar', ['default_param' => 'default_val']];
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @dataProvider provideGenerateUrlLoadCacheCases
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

        $admin = $this->createMock(AdminInterface::class);
        $admin->method('isChild')->willReturn(true);
        $admin->method('getCode')->willReturn('base.Code.Child');
        $admin->method('getBaseCodeRoute')->willReturn('base.Code.Parent|base.Code.Child');
        $admin->method('getIdParameter')->willReturn('id');
        $admin->method('hasParentFieldDescription')->willReturn(false);
        $admin->method('hasRequest')->willReturn(true);
        $admin->method('getUniqId')->willReturn('foo_uniqueid');
        $admin->method('getPersistentParameters')->willReturn(['abc' => 'a123', 'efg' => 'e456']);
        $admin->method('getRoutes')->willReturn($childCollection);
        $admin->method('getExtensions')->willReturn([]);

        $parentAdmin = $this->createMock(AdminInterface::class);
        $parentAdmin->method('getIdParameter')->willReturn('childId');
        $parentAdmin->method('getRoutes')->willReturn($collection);
        $parentAdmin->method('getCode')->willReturn('base.Code.Parent');
        $parentAdmin->method('getExtensions')->willReturn([]);

        // no request attached in this test, so this will not be used
        $parentAdmin->expects(static::never())->method('getPersistentParameters')->willReturn(['from' => 'parent']);

        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->attributes->method('has')->willReturn(true);
        $request->attributes
            ->method('get')
            ->willReturnCallback(static function (string $key): ?string {
                if ('childId' === $key) {
                    return '987654';
                }

                return null;
            });

        $admin->method('getRequest')->willReturn($request);
        $admin->method('getParent')->willReturn($parentAdmin);

        $standaloneAdmin = $this->createMock(AdminInterface::class);
        $standaloneAdmin->method('isChild')->willReturn(false);
        $standaloneAdmin->method('getBaseCodeRoute')->willReturn('base.Code.Child');
        $standaloneAdmin->expects(static::once())->method('hasParentFieldDescription')->willReturn(false);
        $standaloneAdmin->expects(static::once())->method('hasRequest')->willReturn(true);
        $standaloneAdmin->method('getUniqId')->willReturn('foo_uniqueid');
        $standaloneAdmin->expects(static::once())->method('getPersistentParameters')->willReturn(['abc' => 'a123', 'efg' => 'e456']);
        $standaloneAdmin->method('getRoutes')->willReturn($standaloneCollection);
        $standaloneAdmin->method('getExtensions')->willReturn([]);
        $standaloneAdmin->method('getCode')->willReturn('Code');

        $router = $this->createMock(RouterInterface::class);
        $router->expects(static::exactly(2))
            ->method('generate')
            ->willReturnCallback(static function (string $name, array $parameters = []): string {
                $params = '';
                if ([] !== $parameters) {
                    $params .= '?'.http_build_query($parameters);
                }

                return match ($name) {
                    'admin_acme_child_bar' => \sprintf('/foo/bar%s', $params),
                    'admin_acme_child_standalone_bar' => \sprintf('/bar%s', $params),
                    default => throw new \LogicException('Not implemented'),
                };
            });

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);

        // Generate once to populate cache
        $generator->generateUrl($admin, 'bar', $parameters);
        static::assertSame($expected, $generator->generateUrl($standaloneAdmin, $name, $parameters));
    }

    /**
     * @phpstan-return iterable<array{string, string, array<string, mixed>}>
     */
    public function provideGenerateUrlLoadCacheCases(): iterable
    {
        yield ['/bar?abc=a123&efg=e456&id=123&default_param=default_val', 'bar', ['id' => 123, 'default_param' => 'default_val']];
    }
}
