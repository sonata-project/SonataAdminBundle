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

namespace Sonata\AdminBundle\Tests\Action;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Action\DashboardAction;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class DashboardActionTest extends TestCase
{
    /**
     * @var MutableTemplateRegistryInterface
     */
    private $templateRegistry;

    /**
     * @var DashboardAction
     */
    private $action;

    protected function setUp(): void
    {
        $container = new Container();

        $this->templateRegistry = $this->createStub(MutableTemplateRegistryInterface::class);

        $pool = new Pool($container);

        $twig = $this->createMock(Environment::class);

        $breadcrumbsBuilder = $this->createStub(BreadcrumbsBuilderInterface::class);

        $this->action = new DashboardAction(
            [],
            $breadcrumbsBuilder,
            $this->templateRegistry,
            $pool,
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

        $this->assertInstanceOf(Response::class, ($this->action)($request));
    }

    public function testDashboardActionAjaxLayout(): void
    {
        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $this->templateRegistry->method('getTemplate')->willReturnMap([
            ['ajax', 'ajax.html'],
            ['dashboard', 'dashboard.html'],
        ]);

        $this->assertInstanceOf(Response::class, ($this->action)($request));
    }
}
