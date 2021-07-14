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

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Tests\App\Model\Foo;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\FilesystemLoader;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class SonataAdminExtensionTest extends TestCase
{
    /**
     * @var SonataAdminExtension
     */
    private $twigExtension;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private $admin;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private $adminBar;

    /**
     * @var \stdClass
     */
    private $object;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var Container
     */
    private $container;

    protected function setUp(): void
    {
        date_default_timezone_set('Europe/London');

        $this->container = new Container();

        $this->pool = new Pool($this->container, ['sonata_admin_foo_service'], [], [Foo::class => ['sonata_admin_foo_service']]);

        $this->twigExtension = new SonataAdminExtension($this->pool);

        $request = $this->createMock(Request::class);
        $request->method('get')->with('_sonata_admin')->willReturn('sonata_admin_foo_service');

        $loader = new FilesystemLoader([
            __DIR__.'/../../../src/Resources/views/CRUD',
            __DIR__.'/../../Fixtures/Resources/views/CRUD',
        ]);
        $loader->addPath(__DIR__.'/../../../src/Resources/views/', 'SonataAdmin');
        $loader->addPath(__DIR__.'/../../Fixtures/Resources/views/', 'App');

        $this->environment = new Environment($loader, [
            'strict_variables' => true,
            'cache' => false,
            'autoescape' => 'html',
            'optimizations' => 0,
        ]);
        $this->environment->addExtension($this->twigExtension);
        $this->environment->addExtension(new FakeTemplateRegistryExtension());

        // routing extension
        $xmlFileLoader = new XmlFileLoader(new FileLocator([sprintf('%s/../../../src/Resources/config/routing', __DIR__)]));
        $routeCollection = $xmlFileLoader->load('sonata_admin.xml');

        $xmlFileLoader = new XmlFileLoader(new FileLocator([sprintf('%s/../../Fixtures/Resources/config/routing', __DIR__)]));
        $testRouteCollection = $xmlFileLoader->load('routing.xml');

        $routeCollection->addCollection($testRouteCollection);
        $this->environment->addExtension(new StringExtension());

        // initialize object
        $this->object = new \stdClass();

        // initialize admin
        $this->admin = $this->createMock(AdminInterface::class);

        $this->admin
            ->method('getCode')
            ->willReturn('sonata_admin_foo_service');

        $this->admin
            ->method('id')
            ->with(self::equalTo($this->object))
            ->willReturn('12345');

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(self::equalTo($this->object))
            ->willReturn('12345');

        $this->adminBar = $this->createMock(AdminInterface::class);
        $this->adminBar
            ->method('hasAccess')
            ->willReturn(true);
        $this->adminBar
            ->method('getNormalizedIdentifier')
            ->with(self::equalTo($this->object))
            ->willReturn('12345');

        $this->container->set('sonata_admin_foo_service', $this->admin);
        $this->container->set('sonata_admin_bar_service', $this->adminBar);
    }

    public function testGetUrlsafeIdentifier(): void
    {
        $model = new \stdClass();

        $pool = new Pool(
            $this->container,
            ['sonata_admin_foo_service'],
            [],
            [\stdClass::class => ['sonata_admin_foo_service']]
        );

        $this->admin->expects(self::once())
            ->method('getUrlSafeIdentifier')
            ->with(self::equalTo($model))
            ->willReturn('1234567');

        $this->container->set('sonata_admin_foo_service', $this->admin);

        $twigExtension = new SonataAdminExtension($pool);

        self::assertSame('1234567', $twigExtension->getUrlSafeIdentifier($model));
    }

    public function testGetUrlsafeIdentifier_GivenAdmin_Foo(): void
    {
        $model = new \stdClass();

        $pool = new Pool(
            $this->container,
            [
                'sonata_admin_foo_service',
                'sonata_admin_bar_service',
            ],
            [],
            [\stdClass::class => [
                'sonata_admin_foo_service',
                'sonata_admin_bar_service',
            ]]
        );

        $this->admin->expects(self::once())
            ->method('getUrlSafeIdentifier')
            ->with(self::equalTo($model))
            ->willReturn('1234567');

        $this->adminBar->expects(self::never())
            ->method('getUrlSafeIdentifier');

        $twigExtension = new SonataAdminExtension($pool);

        self::assertSame('1234567', $twigExtension->getUrlSafeIdentifier($model, $this->admin));
    }

    public function testGetUrlsafeIdentifier_GivenAdmin_Bar(): void
    {
        $model = new \stdClass();

        $pool = new Pool(
            $this->container,
            ['sonata_admin_foo_service', 'sonata_admin_bar_service'],
            [],
            [\stdClass::class => [
                'sonata_admin_foo_service',
                'sonata_admin_bar_service',
            ]]
        );

        $this->admin->expects(self::never())
            ->method('getUrlSafeIdentifier');

        $this->adminBar->expects(self::once())
            ->method('getUrlSafeIdentifier')
            ->with(self::equalTo($model))
            ->willReturn('1234567');

        $twigExtension = new SonataAdminExtension($pool);

        self::assertSame('1234567', $twigExtension->getUrlSafeIdentifier($model, $this->adminBar));
    }
}
