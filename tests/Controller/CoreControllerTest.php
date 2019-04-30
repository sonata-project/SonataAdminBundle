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
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class CoreControllerTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
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

        $templating = $this->createMock(EngineInterface::class);
        $request = new Request();

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $breadcrumbsBuilder = $this->getMockForAbstractClass(BreadcrumbsBuilderInterface::class);

        $dashboardAction = new DashboardAction(
                [],
                $breadcrumbsBuilder,
                $templateRegistry->reveal(),
                $pool,
                $templating
            );
        $searchAction = new SearchAction(
            $pool,
            new SearchHandler($pool),
            $templateRegistry->reveal(),
            $breadcrumbsBuilder,
            $templating
        );

        $controller = new CoreController(
            $dashboardAction,
            $searchAction,
            $pool,
            new SearchHandler($pool),
            $templateRegistry->reveal(),
            $requestStack
        );

        $this->isInstanceOf(Response::class, $controller->dashboardAction());
    }

    /**
     * @doesNotPerformAssertions
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

        $templating = $this->createMock(EngineInterface::class);
        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $values = [
            DashboardAction::class => $dashboardAction = new DashboardAction(
                [],
                $breadcrumbsBuilder,
                $templateRegistry->reveal(),
                $pool
            ),
            'templating' => $templating,
            'request_stack' => $requestStack,
        ];
        $dashboardAction->setContainer($container);

        $container->expects($this->any())->method('get')->will($this->returnCallback(static function ($id) use ($values) {
            return $values[$id];
        }));

        $container->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(static function ($id) {
                return 'templating' === $id;
            }));

        $controller = new CoreController();
        $controller->setContainer($container);

        $response = $controller->dashboardAction($request);

        $this->isInstanceOf(Response::class, $response);
    }
}
