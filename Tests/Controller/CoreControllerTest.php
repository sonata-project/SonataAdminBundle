<?php

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
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\CoreController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CoreControllerTest extends TestCase
{
    public function testdashboardActionStandardRequest()
    {
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $pool = new Pool($container, 'title', 'logo.png');
        $pool->setTemplates([
            'ajax' => 'ajax.html',
        ]);

        $templating = $this->createMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $request = new Request();

        $requestStack = null;
        if (class_exists('Symfony\Component\HttpFoundation\RequestStack')) {
            $requestStack = new RequestStack();
            $requestStack->push($request);
        }

        $breadcrumbsBuilder = $this->getMockForAbstractClass('Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface');

        $values = [
            'sonata.admin.breadcrumbs_builder' => $breadcrumbsBuilder,
            'sonata.admin.pool' => $pool,
            'templating' => $templating,
            'request' => $request,
            'request_stack' => $requestStack,
        ];

        $container->expects($this->any())->method('get')->will($this->returnCallback(function ($id) use ($values) {
            return $values[$id];
        }));

        $container->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(function ($id) {
                if ($id == 'templating') {
                    return true;
                }

                return false;
            }));

        $container->expects($this->any())->method('getParameter')->will($this->returnCallback(function ($name) {
            if ($name == 'sonata.admin.configuration.dashboard_blocks') {
                return [];
            }
        }));
        $container->expects($this->any())->method('has')->will($this->returnCallback(function ($id) {
            if ($id == 'templating') {
                return true;
            }

            return false;
        }));

        $controller = new CoreController();
        $controller->setContainer($container);

        $response = $controller->dashboardAction($request);

        $this->isInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
    }

    public function testdashboardActionAjaxLayout()
    {
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $pool = new Pool($container, 'title', 'logo.png');
        $pool->setTemplates([
            'ajax' => 'ajax.html',
        ]);

        $templating = $this->createMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $requestStack = null;
        if (class_exists('Symfony\Component\HttpFoundation\RequestStack')) {
            $requestStack = new RequestStack();
            $requestStack->push($request);
        }

        $values = [
            'sonata.admin.pool' => $pool,
            'templating' => $templating,
            'request' => $request,
            'request_stack' => $requestStack,
        ];

        $container->expects($this->any())->method('get')->will($this->returnCallback(function ($id) use ($values) {
            return $values[$id];
        }));

        $container->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(function ($id) {
                if ($id == 'templating') {
                    return true;
                }

                return false;
            }));

        $container->expects($this->any())->method('getParameter')->will($this->returnCallback(function ($name) {
            if ($name == 'sonata.admin.configuration.dashboard_blocks') {
                return [];
            }
        }));
        $container->expects($this->any())->method('has')->will($this->returnCallback(function ($id) {
            if ($id == 'templating') {
                return true;
            }

            return false;
        }));

        $controller = new CoreController();
        $controller->setContainer($container);

        $response = $controller->dashboardAction($request);

        $this->isInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
    }
}
