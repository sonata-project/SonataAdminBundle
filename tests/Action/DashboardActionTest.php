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

namespace SensioLabs\AdminBundle\Tests\Action;

use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Action\DashboardAction;
use SensioLabs\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class DashboardActionTest extends TestCase
{
    /**
     * @var MutableTemplateRegistryInterface&Stub
     */
    private MutableTemplateRegistryInterface $templateRegistry;

    private DashboardAction $action;

    protected function setUp(): void
    {
        $this->templateRegistry = static::createStub(MutableTemplateRegistryInterface::class);

        $twig = $this->createMock(Environment::class);

        $this->action = new DashboardAction(
            [],
            $this->templateRegistry,
            $twig
        );
    }

    public function testdashboardActionStandardRequest(): void
    {
        $request = new Request();

        $this->templateRegistry->method('getTemplate')->willReturnMap([
            ['layout', 'layout.html'],
            ['dashboard', 'dashboard.html'],
        ]);

        static::assertInstanceOf(Response::class, ($this->action)($request));
    }

    public function testDashboardActionAjaxLayout(): void
    {
        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $this->templateRegistry->method('getTemplate')->willReturnMap([
            ['ajax', 'ajax.html'],
            ['dashboard', 'dashboard.html'],
        ]);

        static::assertInstanceOf(Response::class, ($this->action)($request));
    }
}
