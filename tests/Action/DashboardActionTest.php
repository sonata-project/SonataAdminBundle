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
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardActionTest extends TestCase
{
    private $action;

    protected function setUp(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $templateRegistry = $this->prophesize(MutableTemplateRegistryInterface::class);
        $templateRegistry->getTemplate('ajax')->willReturn('ajax.html');
        $templateRegistry->getTemplate('dashboard')->willReturn('dashboard.html');
        $templateRegistry->getTemplate('layout')->willReturn('layout.html');

        $pool = new Pool($container, 'title', 'logo.png');
        $pool->setTemplateRegistry($templateRegistry->reveal());

        $templating = $this->createMock(EngineInterface::class);

        $breadcrumbsBuilder = $this->getMockForAbstractClass(BreadcrumbsBuilderInterface::class);

        $this->action = new DashboardAction(
            [],
            $breadcrumbsBuilder,
            $templateRegistry->reveal(),
            $pool,
            $templating
        );
    }

    public function testdashboardActionStandardRequest()
    {
        $request = new Request();

        $this->assertInstanceOf(Response::class, ($this->action)($request));
    }

    public function testDashboardActionAjaxLayout(): void
    {
        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $this->assertInstanceOf(Response::class, ($this->action)($request));
    }
}
