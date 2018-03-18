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
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\CoreController;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class CoreControllerTest extends TestCase
{
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

        $values = [
            'sonata.admin.breadcrumbs_builder' => $breadcrumbsBuilder,
            'sonata.admin.pool' => $pool,
            'templating' => $templating,
            'request_stack' => $requestStack,
            'sonata.admin.global_template_registry' => $templateRegistry->reveal(),
        ];

        $container->expects($this->any())->method('get')->will($this->returnCallback(function ($id) use ($values) {
            return $values[$id];
        }));

        $container->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(function ($id) {
                if ('templating' == $id) {
                    return true;
                }

                return false;
            }));

        $container->expects($this->any())->method('getParameter')->will($this->returnCallback(function ($name) {
            if ('sonata.admin.configuration.dashboard_blocks' == $name) {
                return [];
            }
        }));
        $container->expects($this->any())->method('has')->will($this->returnCallback(function ($id) {
            if ('templating' == $id) {
                return true;
            }

            return false;
        }));

        $controller = new CoreController();
        $controller->setContainer($container);

        $response = $controller->dashboardAction($request);

        $this->isInstanceOf(Response::class, $response);
    }

    public function testdashboardActionAjaxLayout(): void
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
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $values = [
            'sonata.admin.pool' => $pool,
            'templating' => $templating,
            'request_stack' => $requestStack,
            'sonata.admin.global_template_registry' => $templateRegistry->reveal(),
        ];

        $container->expects($this->any())->method('get')->will($this->returnCallback(function ($id) use ($values) {
            return $values[$id];
        }));

        $container->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(function ($id) {
                if ('templating' == $id) {
                    return true;
                }

                return false;
            }));

        $container->expects($this->any())->method('getParameter')->will($this->returnCallback(function ($name) {
            if ('sonata.admin.configuration.dashboard_blocks' == $name) {
                return [];
            }
        }));
        $container->expects($this->any())->method('has')->will($this->returnCallback(function ($id) {
            if ('templating' == $id) {
                return true;
            }

            return false;
        }));

        $controller = new CoreController();
        $controller->setContainer($container);

        $response = $controller->dashboardAction($request);

        $this->isInstanceOf(Response::class, $response);
    }
}
