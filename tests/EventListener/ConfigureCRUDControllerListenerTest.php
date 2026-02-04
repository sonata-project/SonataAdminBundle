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

namespace SensioLabs\AdminBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Controller\CRUDController;
use SensioLabs\AdminBundle\EventListener\ConfigureCRUDControllerListener;
use SensioLabs\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;

final class ConfigureCRUDControllerListenerTest extends TestCase
{
    private ConfigureCRUDControllerListener $listener;

    protected function setUp(): void
    {
        $this->listener = new ConfigureCRUDControllerListener();
    }

    public function testItConfiguresCRUDController(): void
    {
        $container = new Container();

        $admin = static::createStub(AdminInterface::class);
        $admin
            ->method('hasTemplateRegistry')
            ->willReturn(true);

        $adminFetcher = $this->createMock(AdminFetcherInterface::class);
        $container->set('sonata.admin.request.fetcher', $adminFetcher);

        $request = new Request([], [], [
            '_sonata_admin' => 'admin.code',
        ]);

        $controller = new CRUDController();
        $controller->setContainer($container);

        $controllerEvent = new ControllerEvent(
            static::createStub(HttpKernelInterface::class),
            $controller->listAction(...),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $adminFetcher
            ->expects(static::once())
            ->method('get')
            ->with($request)
            ->willReturn($admin);

        $twig = $this->createMock(Environment::class);
        $container->set('twig', $twig);

        $callNumber = 0;
        $twig
            ->expects(static::exactly(2))
            ->method('addGlobal')
            ->willReturnCallback(static function (string $name) use (&$callNumber) {
                ++$callNumber;
                match ($callNumber) {
                    1 => static::assertSame('admin', $name),
                    2 => static::assertSame('base_template', $name),
                    default => throw new \LogicException('Exactly 2 calls'),
                };
            });

        $this->listener->onKernelController($controllerEvent);
    }
}
