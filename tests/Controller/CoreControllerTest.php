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

namespace Sonata\AdminBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Action\DashboardAction;
use Sonata\AdminBundle\Action\SearchAction;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\CoreController;
use Sonata\AdminBundle\Search\SearchHandler;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class CoreControllerTest extends TestCase
{
    /**
     * @group legacy
     */
    public function testdashboardActionStandardRequest(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $templateRegistry = $this->prophesize(MutableTemplateRegistryInterface::class);
        $templateRegistry->getTemplate('ajax')->willReturn('ajax.html');
        $templateRegistry->getTemplate('dashboard')->willReturn('dashboard.html');
        $templateRegistry->getTemplate('layout')->willReturn('layout.html');

        $pool = new Pool($container, 'title', 'logo.png');
        $pool->setTemplateRegistry($templateRegistry->reveal());

        $twig = $this->createMock(Environment::class);
        $request = new Request();

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $breadcrumbsBuilder = $this->getMockForAbstractClass(BreadcrumbsBuilderInterface::class);

        $values = [
            DashboardAction::class => $dashboardAction = new DashboardAction(
                [],
                $breadcrumbsBuilder,
                $templateRegistry->reveal(),
                $pool,
                $twig
            ),
            'request_stack' => $requestStack,
        ];

        $container->expects($this->any())->method('get')->willReturnCallback(static function ($id) use ($values) {
            return $values[$id];
        });

        $container->expects($this->any())
            ->method('has')
            ->willReturnCallback(static function ($id) {
                return 'templating' === $id;
            });

        $controller = new CoreController();
        $controller->setContainer($container);

        $this->assertInstanceOf(Response::class, $controller->dashboardAction());
    }

    /**
     * @group legacy
     */
    public function testdashboardActionAjaxLayout(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $templateRegistry = $this->prophesize(MutableTemplateRegistryInterface::class);
        $templateRegistry->getTemplate('ajax')->willReturn('ajax.html');
        $templateRegistry->getTemplate('dashboard')->willReturn('dashboard.html');
        $templateRegistry->getTemplate('layout')->willReturn('layout.html');
        $breadcrumbsBuilder = $this->getMockForAbstractClass(BreadcrumbsBuilderInterface::class);

        $pool = new Pool($container, 'title', 'logo.png');
        $pool->setTemplateRegistry($templateRegistry->reveal());

        $twig = $this->createMock(Environment::class);
        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $values = [
            DashboardAction::class => $dashboardAction = new DashboardAction(
                [],
                $breadcrumbsBuilder,
                $templateRegistry->reveal(),
                $pool,
                $twig
            ),
            'request_stack' => $requestStack,
        ];

        $container->expects($this->any())->method('get')->willReturnCallback(static function ($id) use ($values) {
            return $values[$id];
        });

        $controller = new CoreController();
        $controller->setContainer($container);

        $response = $controller->dashboardAction($request);

        $this->assertInstanceOf(Response::class, $response);
    }
}
