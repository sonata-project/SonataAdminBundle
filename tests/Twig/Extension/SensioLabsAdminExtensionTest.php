<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Twig\Extension;

use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Tests\App\Model\Foo;
use SensioLabs\AdminBundle\Twig\Extension\SensioLabsAdminExtension;
use SensioLabs\AdminBundle\Twig\SensioLabsAdminRuntime;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\FilesystemLoader;

/**
 * NEXT_MAJOR: Remove this test.
 */
#[IgnoreDeprecations]
final class SensioLabsAdminExtensionTest extends TestCase
{
    private SensioLabsAdminExtension $twigExtension;

    private Environment $environment;

    /**
     * @var AdminInterface<\stdClass>&MockObject
     */
    private AdminInterface $admin;

    /**
     * @var AdminInterface<\stdClass>&MockObject
     */
    private AdminInterface $adminBar;

    private \stdClass $object;

    private Pool $pool;

    private Container $container;

    protected function setUp(): void
    {
        date_default_timezone_set('Europe/London');

        $this->container = new Container();

        $this->pool = new Pool($this->container, ['sensiolabs_admin_foo_service'], [], [Foo::class => ['sensiolabs_admin_foo_service']]);

        $this->twigExtension = new SensioLabsAdminExtension(new SensioLabsAdminRuntime($this->pool));

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
        $phpFileLoader = new PhpFileLoader(new FileLocator([\sprintf('%s/../../../src/Resources/config/routing', __DIR__)]));
        $routeCollection = $phpFileLoader->load('sensiolabs_admin.php');

        $phpFileLoader = new PhpFileLoader(new FileLocator([\sprintf('%s/../../Fixtures/Resources/config/routing', __DIR__)]));
        $testRouteCollection = $phpFileLoader->load('routing.php');

        $routeCollection->addCollection($testRouteCollection);
        $this->environment->addExtension(new StringExtension());

        // initialize object
        $this->object = new \stdClass();

        // initialize admin
        $this->admin = $this->createMock(AdminInterface::class);

        $this->admin
            ->method('getCode')
            ->willReturn('sensiolabs_admin_foo_service');

        $this->admin
            ->method('id')
            ->with(static::equalTo($this->object))
            ->willReturn('12345');

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($this->object))
            ->willReturn('12345');

        $this->adminBar = $this->createMock(AdminInterface::class);
        $this->adminBar
            ->method('hasAccess')
            ->willReturn(true);
        $this->adminBar
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($this->object))
            ->willReturn('12345');

        $this->container->set('sensiolabs_admin_foo_service', $this->admin);
        $this->container->set('sensiolabs_admin_bar_service', $this->adminBar);
    }

    public function testGetUrlsafeIdentifier(): void
    {
        $model = new \stdClass();

        $pool = new Pool(
            $this->container,
            ['sensiolabs_admin_foo_service'],
            [],
            [\stdClass::class => ['sensiolabs_admin_foo_service']]
        );

        $this->admin->expects(static::once())
            ->method('getUrlSafeIdentifier')
            ->with(static::equalTo($model))
            ->willReturn('1234567');

        $this->container->set('sensiolabs_admin_foo_service', $this->admin);

        $twigExtension = new SensioLabsAdminExtension(new SensioLabsAdminRuntime($pool));

        static::assertSame('1234567', $twigExtension->getUrlSafeIdentifier($model));
    }

    public function testGetUrlsafeIdentifierGivenAdminFoo(): void
    {
        $model = new \stdClass();

        $pool = new Pool(
            $this->container,
            [
                'sensiolabs_admin_foo_service',
                'sensiolabs_admin_bar_service',
            ],
            [],
            [\stdClass::class => [
                'sensiolabs_admin_foo_service',
                'sensiolabs_admin_bar_service',
            ]]
        );

        $this->admin->expects(static::once())
            ->method('getUrlSafeIdentifier')
            ->with(static::equalTo($model))
            ->willReturn('1234567');

        $this->adminBar->expects(static::never())
            ->method('getUrlSafeIdentifier');

        $twigExtension = new SensioLabsAdminExtension(new SensioLabsAdminRuntime($pool));

        static::assertSame('1234567', $twigExtension->getUrlSafeIdentifier($model, $this->admin));
    }

    public function testGetUrlsafeIdentifierGivenAdminBar(): void
    {
        $model = new \stdClass();

        $pool = new Pool(
            $this->container,
            ['sensiolabs_admin_foo_service', 'sensiolabs_admin_bar_service'],
            [],
            [\stdClass::class => [
                'sensiolabs_admin_foo_service',
                'sensiolabs_admin_bar_service',
            ]]
        );

        $this->admin->expects(static::never())
            ->method('getUrlSafeIdentifier');

        $this->adminBar->expects(static::once())
            ->method('getUrlSafeIdentifier')
            ->with(static::equalTo($model))
            ->willReturn('1234567');

        $twigExtension = new SensioLabsAdminExtension(new SensioLabsAdminRuntime($pool));

        static::assertSame('1234567', $twigExtension->getUrlSafeIdentifier($model, $this->adminBar));
    }
}
