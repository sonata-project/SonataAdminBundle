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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\EventListener\AdminEventListener;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class AdminEventListenerTest extends TestCase
{
    private Environment $twig;

    /**
     * @var AdminFetcherInterface&MockObject
     */
    private AdminFetcherInterface $adminFetcher;

    private AdminEventListener $listener;

    protected function setUp(): void
    {
        $this->twig = new Environment(new ArrayLoader([
        ]));
        $this->adminFetcher = $this->createMock(AdminFetcherInterface::class);

        $this->listener = new AdminEventListener(
            $this->twig,
            $this->adminFetcher
        );
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame([
            KernelEvents::REQUEST => [['onKernelRequest', -50]],
        ], AdminEventListener::getSubscribedEvents());
    }

    public function testOnKernelRequest(): void
    {
        $request = new Request();

        $event = new KernelEvent(self::createStub(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST);

        $admin = self::createStub(AdminInterface::class);

        $this->adminFetcher->method('get')->willReturn($admin);

        $templateRegistry = $this->createMock(MutableTemplateRegistryInterface::class);
        $templateRegistry->expects(static::once())->method('getTemplate')->with('layout')
            ->willReturn('layout.html.twig');

        $admin->method('getTemplateRegistry')->willReturn($templateRegistry);

        $this->listener->onKernelRequest($event);

        $global = $this->twig->getGlobals();

        static::assertSame($admin, $global['admin']);
        static::assertSame('layout.html.twig', $global['base_template']);
    }

    public function testOnAjaxKernelRequest(): void
    {
        $request = new Request();
        $request->attributes->set('_xml_http_request', true);

        $event = new KernelEvent(self::createStub(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST);

        $admin = self::createStub(AdminInterface::class);

        $this->adminFetcher->method('get')->willReturn($admin);

        $templateRegistry = $this->createMock(MutableTemplateRegistryInterface::class);
        $templateRegistry->expects(static::once())->method('getTemplate')->with('ajax')
            ->willReturn('ajax.html.twig');

        $admin->method('getTemplateRegistry')->willReturn($templateRegistry);

        $this->listener->onKernelRequest($event);

        $global = $this->twig->getGlobals();

        static::assertSame($admin, $global['admin']);
        static::assertSame('ajax.html.twig', $global['base_template']);
    }
}
