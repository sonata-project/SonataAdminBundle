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

use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\HelperController;
use \Twig_Environment as Twig;
use \Twig_ExtensionInterface as Twig_ExtensionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;

class AdminControllerHelper_Foo
{
    public function getAdminTitle()
    {
        return 'bar';
    }

    public function setEnabled($value)
    {

    }
}

class HelperControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testgetShortObjectDescriptionActionInvalidAdmin()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $twig = new Twig;
        $request = new Request(array(
            'code'     => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid'   => 'asdasd123'
        ));
        $pool = new Pool($container, 'title', 'logo');
        $helper = new AdminHelper($pool);
        $controller = new HelperController($twig, $pool, $helper);

        $controller->getShortObjectDescriptionAction($request);
    }

    public function testgetShortObjectDescriptionActionObjectDoesNotExist()
    {
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('setUniqid');
        $admin->expects($this->once())->method('getObject')->will($this->returnValue(false));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $twig = new Twig;
        $request = new Request(array(
            'code'     => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid'   => 'asdasd123'
        ));

        $pool = new Pool($container, 'title', 'logo');

        $helper = new AdminHelper($pool);

        $controller = new HelperController($twig, $pool, $helper);

        $response = $controller->getShortObjectDescriptionAction($request);

        $this->assertEmpty($response->getContent());
    }

    public function testgetShortObjectDescriptionActionObject()
    {
        $mockTemplate = 'AdminHelperTest:mock-short-object-description.html.twig';

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('setUniqid');
        $admin->expects($this->once())->method('getTemplate')->will($this->returnValue($mockTemplate));
        $admin->expects($this->once())->method('getObject')->will($this->returnValue(new AdminControllerHelper_Foo));
        $admin->expects($this->once())->method('generateUrl')->will($this->returnCallback(function($name, $parameters) {
            if ($name != 'edit') {
                return 'invalid name';
            }

            if (!isset($parameters['id'])) {
                return 'id parameter not set';
            }

            return '/ok/url';
        }));
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $twig = $this->getMock('Twig_Environment');

        $twig->expects($this->once())->method('render')
            ->with($mockTemplate)
            ->will($this->returnCallback(function($templateName, $templateParams) {
                return sprintf('<a href="%s" target="new">%s</a>', $templateParams['url'], $templateParams['description']);
            }));

        $request = new Request(array(
            'code'     => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid'   => 'asdasd123'
        ));

        $pool = new Pool($container, 'title', 'logo');

        $helper = new AdminHelper($pool);

        $controller = new HelperController($twig, $pool, $helper);

        $response = $controller->getShortObjectDescriptionAction($request);

        $expected = '<a href="/ok/url" target="new">bar</a>';
        $this->assertEquals($expected, $response->getContent());
    }

    public function testsetObjectFieldValueAction()
    {
        $object = new AdminControllerHelper_Foo;

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getOption')->will($this->returnValue(true));

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getObject')->will($this->returnValue($object));
        $admin->expects($this->once())->method('isGranted')->will($this->returnValue(true));
        $admin->expects($this->once())->method('getListFieldDescription')->will($this->returnValue($fieldDescription));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $adminExtension = $this->getMock('Twig_ExtensionInterface', array('renderListElement', 'initRuntime', 'getTokenParsers', 'getNodeVisitors', 'getFilters', 'getTests', 'getFunctions', 'getOperators', 'getGlobals', 'getName'));
        $adminExtension->expects($this->once())->method('getName')->will($this->returnValue('sonata_admin'));
        $adminExtension->expects($this->once())->method('renderListElement')->will($this->returnValue('<foo />'));

        $twig = new Twig;
        $twig->addExtension($adminExtension);
        $request = new Request(array(
            'code'     => 'sonata.post.admin',
            'objectId' => 42,
            'field'   => 'enabled',
            'value'   => 1,
            'context' => 'list',
        ), array(), array(), array(), array(), array('REQUEST_METHOD' => 'POST'));

        $pool = new Pool($container, 'title', 'logo');

        $helper = new AdminHelper($pool);

        $controller = new HelperController($twig, $pool, $helper);

        $response = $controller->setObjectFieldValueAction($request);

        $this->assertEquals('{"status":"OK","content":"\u003Cfoo \/\u003E"}', $response->getContent() );
    }

    public function testappendFormFieldElementAction()
    {
        $object = new AdminControllerHelper_Foo;

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->expects($this->once())->method('find')->will($this->returnValue($object));

        $mockTheme = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getModelManager')->will($this->returnValue($modelManager));
        $admin->expects($this->once())->method('setRequest');
        $admin->expects($this->once())->method('setSubject');
        $admin->expects($this->once())->method('getFormTheme')->will($this->returnValue($mockTheme));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $mockRenderer = $this->getMockBuilder('Symfony\Bridge\Twig\Form\TwigRendererInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mockRenderer->expects($this->once())
            ->method('searchAndRenderBlock')
            ->will($this->returnValue(new Response));

        $formExtension = $this->getMock('Twig_ExtensionInterface', array('renderListElement', 'initRuntime', 'getTokenParsers', 'getNodeVisitors', 'getFilters', 'getTests', 'getFunctions', 'getOperators', 'getGlobals', 'getName'));

        $formExtension->expects($this->once())->method('getName')->will($this->returnValue('form'));
        $formExtension->expects($this->never())->method('searchAndRenderBlock');
        $formExtension->expects($this->never())->method('setTheme');
        $formExtension->renderer = $mockRenderer;

        $twig = new Twig;
        $twig->addExtension($formExtension);
        $request = new Request(array(
            'code'     => 'sonata.post.admin',
            'objectId' => 42,
            'field'   => 'enabled',
            'value'   => 1,
            'context' => 'list',
        ), array(), array(), array(), array(), array('REQUEST_METHOD' => 'POST'));

        $pool = new Pool($container, 'title', 'logo');

        $mockView = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();

        $mockForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $mockForm->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($mockView));

        $helper = $this->getMock('Sonata\AdminBundle\Admin\AdminHelper', array('appendFormFieldElement', 'getChildFormView'), array($pool));
        $helper->expects($this->once())->method('appendFormFieldElement')->will($this->returnValue(array(
            $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface'),
            $mockForm
        )));
        $helper->expects($this->once())->method('getChildFormView')->will($this->returnValue($mockView));

        $controller = new HelperController($twig, $pool, $helper);
        $response = $controller->appendFormFieldElementAction($request);

        $this->isInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
    }

    public function testretrieveFormFieldElementAction()
    {
        $object = new AdminControllerHelper_Foo;

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->expects($this->once())->method('find')->will($this->returnValue($object));

        $mockView = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();

        $mockForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $mockForm->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($mockView));

        $formBuilder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $formBuilder->expects($this->once())->method('getForm')->will($this->returnValue($mockForm));

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getModelManager')->will($this->returnValue($modelManager));
        $admin->expects($this->once())->method('getFormBuilder')->will($this->returnValue($formBuilder));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $mockRenderer = $this->getMockBuilder('Symfony\Bridge\Twig\Form\TwigRendererInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mockRenderer->expects($this->once())
            ->method('searchAndRenderBlock')
            ->will($this->returnValue(new Response));

        $formExtension = $this->getMock('Twig_ExtensionInterface', array('renderListElement', 'initRuntime', 'getTokenParsers', 'getNodeVisitors', 'getFilters', 'getTests', 'getFunctions', 'getOperators', 'getGlobals', 'getName'));
        $formExtension->expects($this->once())->method('getName')->will($this->returnValue('form'));
        $formExtension->expects($this->never())->method('searchAndRenderBlock');
        $formExtension->expects($this->never())->method('setTheme');
        $formExtension->renderer = $mockRenderer;

        $twig = new Twig;
        $twig->addExtension($formExtension);
        $request = new Request(array(
            'code'     => 'sonata.post.admin',
            'objectId' => 42,
            'field'   => 'enabled',
            'value'   => 1,
            'context' => 'list',
        ), array(), array(), array(), array(), array('REQUEST_METHOD' => 'POST'));

        $pool = new Pool($container, 'title', 'logo');

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $helper = $this->getMock('Sonata\AdminBundle\Admin\AdminHelper', array('getChildFormView'), array($pool));
        $helper->expects($this->once())->method('getChildFormView')->will($this->returnValue($mockView));

        $controller = new HelperController($twig, $pool, $helper);
        $response = $controller->retrieveFormFieldElementAction($request);

        $this->isInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
    }
}
