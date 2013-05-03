<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Controller;

use Sonata\AdminBundle\Controller\CoreController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;

class CoreControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testdashboardActionStandardRequest()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $pool = new Pool($container, 'title', 'logo.png');
        $pool->setTemplates(array(
            'ajax' => 'ajax.html',
        ));

        $templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $request = new Request();

        $values = array(
            'sonata.admin.pool' => $pool,
            'templating'        => $templating,
            'request'           => $request
        );

        $container->expects($this->any())->method('get')->will($this->returnCallback(function($id) use ($values) {
            return $values[$id];
        }));

        $controller = new CoreController();
        $controller->setContainer($container);

        $response = $controller->dashboardAction();

        $this->isInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
    }

    public function testdashboardActionAjaxLayout()
    {

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $pool = new Pool($container, 'title', 'logo.png');
        $pool->setTemplates(array(
            'ajax' => 'ajax.html',
        ));

        $templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $values = array(
            'sonata.admin.pool' => $pool,
            'templating'        => $templating,
            'request'           => $request
        );

        $container->expects($this->any())->method('get')->will($this->returnCallback(function($id) use ($values) {
            return $values[$id];
        }));

        $controller = new CoreController();
        $controller->setContainer($container);

        $response = $controller->dashboardAction();

        $this->isInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
    }

}
