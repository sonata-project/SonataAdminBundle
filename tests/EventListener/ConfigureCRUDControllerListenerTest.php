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

namespace Sonata\AdminBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\EventListener\ConfigureCRUDControllerListener;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ConfigureCRUDControllerListenerTest extends TestCase
{
    /**
     * @var ConfigureCRUDControllerListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->listener = new ConfigureCRUDControllerListener();
    }

    public function testItConfiguresCRUDController(): void
    {
        $container = new Container();

        $admin = $this->createStub(AdminInterface::class);
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
            $this->createStub(HttpKernelInterface::class),
            [$controller, 'listAction'],
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $adminFetcher
            ->expects($this->once())
            ->method('get')
            ->with($request)
            ->willReturn($admin);

        $this->listener->onKernelController($controllerEvent);
    }
}
