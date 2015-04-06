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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sonata\AdminBundle\Util\AdminObjectAclManipulator;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Tests\Fixtures\Controller\BatchAdminController;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

/**
 * Test for CRUDController
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class CRUDControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CRUDController
     */
    private $controller;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Sonata\AdminBundle\Model\AuditManager
     */
    private $auditManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var AdminObjectAclManipulator
     */
    private $adminObjectAclManipulator;

    /**
     * @var string
     */
    private $template;

    /**
     * @var array
     */
    private $protectedTestedMethods;

    /**
     * @var CsrfProviderInterface
     */
    private $csrfProvider;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->request = new Request();
        $this->pool = new Pool($this->container, 'title', 'logo.png');
        $this->pool->setAdminServiceIds(array('foo.admin'));
        $this->request->attributes->set('_sonata_admin', 'foo.admin');
        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $this->parameters = array();
        $this->template = '';

        // php 5.3 BC
        $params = &$this->parameters;
        $template = &$this->template;

        $templating = $this->getMock(
            'Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine',
            array(),
            array($this->container, array())
        );

        $templating->expects($this->any())
            ->method('renderResponse')
            ->will($this->returnCallback(function (
                $view,
                array $parameters = array(),
                Response $response = null
            ) use (
                &$params,
                &$template
            ) {
                $template = $view;

                if (null === $response) {
                    $response = new Response();
                }

                $params = $parameters;

                return $response;
            }));

        $this->session = new Session(new MockArraySessionStorage());

        // php 5.3 BC
        $pool = $this->pool;
        $request = $this->request;
        $admin = $this->admin;
        $session = $this->session;

        $twig = $this->getMockBuilder('Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $twigRenderer = $this->getMock('Symfony\Bridge\Twig\Form\TwigRendererInterface');

        $formExtension = new FormExtension($twigRenderer);

        $twig->expects($this->any())
            ->method('getExtension')
            ->will($this->returnCallback(function ($name) use ($formExtension) {
                switch ($name) {
                    case 'form':
                        return $formExtension;
                }

                return null;
            }));

        $exporter = $this->getMock('Sonata\AdminBundle\Export\Exporter');

        $exporter->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue(new StreamedResponse()));

        $this->auditManager = $this->getMockBuilder('Sonata\AdminBundle\Model\AuditManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->adminObjectAclManipulator = $this->getMockBuilder('Sonata\AdminBundle\Util\AdminObjectAclManipulator')
            ->disableOriginalConstructor()
            ->getMock();

        // php 5.3 BC
        $auditManager = $this->auditManager;
        $adminObjectAclManipulator = $this->adminObjectAclManipulator;

        $this->csrfProvider = $this->getMockBuilder(
            'Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface'
        )
            ->getMock();

        $this->csrfProvider->expects($this->any())
            ->method('generateCsrfToken')
            ->will($this->returnCallback(function ($intention) {
                return 'csrf-token-123_'.$intention;
            }));

        $this->csrfProvider->expects($this->any())
            ->method('isCsrfTokenValid')
            ->will($this->returnCallback(function ($intention, $token) {
                if ($token == 'csrf-token-123_'.$intention) {
                    return true;
                }

                return false;
            }));

        // php 5.3 BC
        $csrfProvider = $this->csrfProvider;

        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger       = $this->logger; // php 5.3 BC


        $requestStack = null;
        if (Kernel::MINOR_VERSION > 3) {
            $requestStack = new \Symfony\Component\HttpFoundation\RequestStack();
            $requestStack->push($request);
        }

        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use (
                $pool,
                $request,
                $admin,
                $templating,
                $twig,
                $session,
                $exporter,
                $auditManager,
                $adminObjectAclManipulator,
                $requestStack,
                $csrfProvider,
                $logger
            ) {
                switch ($id) {
                    case 'sonata.admin.pool':
                        return $pool;
                    case 'request_stack':
                        return $requestStack;
                    case 'request':
                        return $request;
                    case 'foo.admin':
                        return $admin;
                    case 'templating':
                        return $templating;
                    case 'twig':
                        return $twig;
                    case 'session':
                        return $session;
                    case 'sonata.admin.exporter':
                        return $exporter;
                    case 'sonata.admin.audit.manager':
                        return $auditManager;
                    case 'sonata.admin.object.manipulator.acl.admin':
                        return $adminObjectAclManipulator;
                    case 'form.csrf_provider':
                        return $csrfProvider;
                    case 'logger':
                        return $logger;
                }

                return null;
            }));

        // php 5.3
        $tthis = $this;

        $this->container->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(function ($id) use ($tthis) {
                if ($id == 'form.csrf_provider' && $tthis->getCsrfProvider()!==null) {
                    return true;
                }

                if ($id == 'logger') {
                    return true;
                }

                return false;
            }));

        $this->admin->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnCallback(function ($name) {
                switch ($name) {
                    case 'ajax':
                        return 'SonataAdminBundle::ajax_layout.html.twig';
                    case 'layout':
                        return 'SonataAdminBundle::standard_layout.html.twig';
                    case 'show':
                        return 'SonataAdminBundle:CRUD:show.html.twig';
                    case 'show_compare':
                        return 'SonataAdminBundle:CRUD:show_compare.html.twig';
                    case 'edit':
                        return 'SonataAdminBundle:CRUD:edit.html.twig';
                    case 'dashboard':
                        return 'SonataAdminBundle:Core:dashboard.html.twig';
                    case 'search':
                        return 'SonataAdminBundle:Core:search.html.twig';
                    case 'list':
                        return 'SonataAdminBundle:CRUD:list.html.twig';
                    case 'preview':
                        return 'SonataAdminBundle:CRUD:preview.html.twig';
                    case 'history':
                        return 'SonataAdminBundle:CRUD:history.html.twig';
                    case 'acl':
                        return 'SonataAdminBundle:CRUD:acl.html.twig';
                    case 'delete':
                        return 'SonataAdminBundle:CRUD:delete.html.twig';
                    case 'batch':
                        return 'SonataAdminBundle:CRUD:list__batch.html.twig';
                    case 'batch_confirmation':
                        return 'SonataAdminBundle:CRUD:batch_confirmation.html.twig';
                }

                return null;
            }));

        $this->admin->expects($this->any())
            ->method('getIdParameter')
            ->will($this->returnValue('id'));

        $this->admin->expects($this->any())
            ->method('generateUrl')
            ->will(
                $this->returnCallback(
                    function ($name, array $parameters = array(), $absolute = false) {
                        $result = $name;
                        if (!empty($parameters)) {
                            $result .= '?'.http_build_query($parameters);
                        }

                        return $result;
                    }
                )
            );

        $this->admin->expects($this->any())
            ->method('generateObjectUrl')
            ->will(
                $this->returnCallback(
                    function ($name, $object, array $parameters = array(), $absolute = false) {
                        $result = get_class($object).'_'.$name;
                        if (!empty($parameters)) {
                            $result .= '?'.http_build_query($parameters);
                        }

                        return $result;
                    }
                )
            );

        $this->controller = new CRUDController();
        $this->controller->setContainer($this->container);

        // Make some methods public to test them
        $testedMethods = array(
            'renderJson',
            'isXmlHttpRequest',
            'configure',
            'getBaseTemplate',
            'redirectTo',
            'addFlash'
        );
        foreach ($testedMethods as $testedMethod) {
            $method = new \ReflectionMethod('Sonata\\AdminBundle\\Controller\\CRUDController', $testedMethod);
            $method->setAccessible(true);
            $this->protectedTestedMethods[$testedMethod] = $method;
        }
    }

    public function testRenderJson1()
    {
        $data = array('example'=>'123', 'foo'=>'bar');

        $this->request->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data);

        $this->assertEquals($response->headers->get('Content-Type'), 'application/json');
        $this->assertEquals(json_encode($data), $response->getContent());
    }

    public function testRenderJson2()
    {
        $data = array('example'=>'123', 'foo'=>'bar');

        $this->request->headers->set('Content-Type', 'multipart/form-data');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data);

        $this->assertEquals($response->headers->get('Content-Type'), 'application/json');
        $this->assertEquals(json_encode($data), $response->getContent());
    }

    public function testRenderJsonAjax()
    {
        $data = array('example'=>'123', 'foo'=>'bar');

        $this->request->attributes->set('_xml_http_request', true);
        $this->request->headers->set('Content-Type', 'multipart/form-data');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data);

        $this->assertEquals($response->headers->get('Content-Type'), 'text/plain');
        $this->assertEquals(json_encode($data), $response->getContent());
    }

    public function testIsXmlHttpRequest()
    {
        $this->assertFalse($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller));

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $this->assertTrue($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller));

        $this->request->headers->remove('X-Requested-With');
        $this->assertFalse($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller));

        $this->request->attributes->set('_xml_http_request', true);
        $this->assertTrue($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller));
    }

    public function testConfigure()
    {
        $uniqueId = '';

        $this->admin->expects($this->once())
            ->method('setUniqid')
            ->will($this->returnCallback(function ($uniqid) use (&$uniqueId) {
                $uniqueId = $uniqid;
            }));

        $this->request->query->set('uniqid', 123456);
        $this->protectedTestedMethods['configure']->invoke($this->controller);

        $this->assertEquals(123456, $uniqueId);
        $this->assertAttributeEquals($this->admin, 'admin', $this->controller);
    }

    public function testConfigureChild()
    {
        $uniqueId = '';

        $this->admin->expects($this->once())
            ->method('setUniqid')
            ->will($this->returnCallback(function ($uniqid) use (&$uniqueId) {
                $uniqueId = $uniqid;
            }));

        $this->admin->expects($this->once())
            ->method('isChild')
            ->will($this->returnValue(true));

        $adminParent = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $this->admin->expects($this->once())
            ->method('getParent')
            ->will($this->returnValue($adminParent));

        $this->request->query->set('uniqid', 123456);
        $this->protectedTestedMethods['configure']->invoke($this->controller);

        $this->assertEquals(123456, $uniqueId);
        $this->assertAttributeEquals($adminParent, 'admin', $this->controller);
    }

    public function testConfigureWithException()
    {
        $this->setExpectedException(
            'RuntimeException',
            'There is no `_sonata_admin` defined for the controller `Sonata\AdminBundle\Controller\CRUDController`'
        );

        $this->request->attributes->remove('_sonata_admin');
        $this->protectedTestedMethods['configure']->invoke($this->controller);
    }

    public function testConfigureWithException2()
    {
        $this->setExpectedException(
            'RuntimeException',
            'Unable to find the admin class related to the current controller ' .
            '(Sonata\AdminBundle\Controller\CRUDController)'
        );

        $this->pool->setAdminServiceIds(array('nonexistent.admin'));
        $this->request->attributes->set('_sonata_admin', 'nonexistent.admin');
        $this->protectedTestedMethods['configure']->invoke($this->controller);
    }

    public function testGetBaseTemplate()
    {
        $this->assertEquals(
            'SonataAdminBundle::standard_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller)
        );

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->assertEquals(
            'SonataAdminBundle::ajax_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller)
        );

        $this->request->headers->remove('X-Requested-With');
        $this->assertEquals(
            'SonataAdminBundle::standard_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller)
        );

        $this->request->attributes->set('_xml_http_request', true);
        $this->assertEquals(
            'SonataAdminBundle::ajax_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller)
        );
    }

    public function testRender()
    {
        $this->parameters = array();
        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\Response',
            $this->controller->render('FooAdminBundle::foo.html.twig', array())
        );
        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);
        $this->assertEquals('FooAdminBundle::foo.html.twig', $this->template);
    }

    public function testRenderWithResponse()
    {
        $this->parameters = array();
        $response = $response = new Response();
        $response->headers->set('X-foo', 'bar');
        $responseResult = $this->controller->render('FooAdminBundle::foo.html.twig', array(), $response);

        $this->assertEquals($response, $responseResult);
        $this->assertEquals('bar', $responseResult->headers->get('X-foo'));
        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);
        $this->assertEquals('FooAdminBundle::foo.html.twig', $this->template);
    }

    public function testRenderCustomParams()
    {
        $this->parameters = array();
        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\Response',
            $this->controller->render('FooAdminBundle::foo.html.twig',
            array('foo'=>'bar'))
        );
        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);
        $this->assertEquals('bar', $this->parameters['foo']);
        $this->assertEquals('FooAdminBundle::foo.html.twig', $this->template);
    }

    public function testRenderAjax()
    {
        $this->parameters = array();
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->render('FooAdminBundle::foo.html.twig', array('foo'=>'bar')));
        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::ajax_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);
        $this->assertEquals('bar', $this->parameters['foo']);
        $this->assertEquals('FooAdminBundle::foo.html.twig', $this->template);
    }

    public function testListActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('LIST'))
            ->will($this->returnValue(false));

        $this->controller->listAction();
    }

    public function testListAction()
    {
        $datagrid = $this->getMock('Sonata\AdminBundle\Datagrid\DatagridInterface');

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('LIST'))
            ->will($this->returnValue(true));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

         $form->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($this->getMock('Symfony\Component\Form\FormView')));

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $datagrid->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->parameters = array();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->listAction());

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('list', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertInstanceOf('Sonata\AdminBundle\Datagrid\DatagridInterface', $this->parameters['datagrid']);
        $this->assertEquals('csrf-token-123_sonata.batch', $this->parameters['csrf_token']);
        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:list.html.twig', $this->template);
    }

    public function testBatchActionDeleteAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'))
            ->will($this->returnValue(false));

        $this->controller->batchActionDelete($this->getMock('Sonata\AdminBundle\Datagrid\ProxyQueryInterface'));
    }

    public function testBatchActionDelete()
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $this->admin->expects($this->once())
            ->method('getFilterParameters')
            ->will($this->returnValue(array('foo'=>'bar')));

        $result = $this->controller->batchActionDelete($this->getMock('Sonata\AdminBundle\Datagrid\ProxyQueryInterface'));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
        $this->assertSame(array('flash_batch_delete_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertEquals('list?filter%5Bfoo%5D=bar', $result->getTargetUrl());
    }

    private function assertLoggerLogsModelManagerException($subject, $method)
    {
        $exception = new ModelManagerException(
            $message           = 'message',
            1234,
            new \Exception($previousExceptionMessage = 'very useful message')
        );

        $subject->expects($this->once())
            ->method($method)
            ->will($this->returnCallback(function () use ($exception) {
                    throw $exception;
                }));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($message, array(
                'exception' => $exception,
                'previous_exception_message' => $previousExceptionMessage
            ));
    }

    public function testBatchActionDeleteWithModelManagerException()
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $this->assertLoggerLogsModelManagerException($modelManager, 'batchDelete');

        $this->admin->expects($this->once())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $this->admin->expects($this->once())
            ->method('getFilterParameters')
            ->will($this->returnValue(array('foo'=>'bar')));

        $result = $this->controller->batchActionDelete($this->getMock('Sonata\AdminBundle\Datagrid\ProxyQueryInterface'));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
        $this->assertSame(array('flash_batch_delete_error'), $this->session->getFlashBag()->get('sonata_flash_error'));
        $this->assertEquals('list?filter%5Bfoo%5D=bar', $result->getTargetUrl());
    }

    public function testShowActionNotFoundException()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->showAction();
    }

    public function testShowActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(new \stdClass()));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('VIEW'))
            ->will($this->returnValue(false));

        $this->controller->showAction();
    }

    public function testShowAction()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('VIEW'))
            ->will($this->returnValue(true));

        $show = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionCollection');

        $this->admin->expects($this->once())
            ->method('getShow')
            ->will($this->returnValue($show));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->showAction());

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('show', $this->parameters['action']);
        $this->assertInstanceOf('Sonata\AdminBundle\Admin\FieldDescriptionCollection', $this->parameters['elements']);
        $this->assertEquals($object, $this->parameters['object']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:show.html.twig', $this->template);
    }

    /**
     * @dataProvider getRedirectToTests
     */
    public function testRedirectTo($expected, $queryParams, $hasActiveSubclass)
    {
        $this->admin->expects($this->any())
            ->method('hasActiveSubclass')
            ->will($this->returnValue($hasActiveSubclass));

        $object = new \stdClass();

        foreach ($queryParams as $key => $value) {
            $this->request->query->set($key, $value);
        }

        $response = $this->protectedTestedMethods['redirectTo']->invoke($this->controller, $object);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals($expected, $response->getTargetUrl());
    }

    public function getRedirectToTests()
    {
        return array(
            array('stdClass_edit', array(), false),
            array('list', array('btn_update_and_list'=>true), false),
            array('list', array('btn_create_and_list'=>true), false),
            array('create', array('btn_create_and_create'=>true), false),
            array('create?subclass=foo', array('btn_create_and_create'=>true, 'subclass'=>'foo'), true),
        );
    }

    public function testAddFlash()
    {
        $this->protectedTestedMethods['addFlash']->invoke($this->controller, 'foo', 'bar');
        $this->assertSame(array('bar'), $this->session->getFlashBag()->get('foo'));
    }

    public function testDeleteActionNotFoundException()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->deleteAction(1);
    }

    public function testDeleteActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(new \stdClass()));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'))
            ->will($this->returnValue(false));

        $this->controller->deleteAction(1);
    }

    public function testDeleteAction()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'))
            ->will($this->returnValue(true));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->deleteAction(1));

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('delete', $this->parameters['action']);
        $this->assertEquals($object, $this->parameters['object']);
        $this->assertEquals('csrf-token-123_sonata.delete', $this->parameters['csrf_token']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:delete.html.twig', $this->template);
    }

    public function testDeleteActionNoCsrfToken()
    {
        $this->csrfProvider = null;

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'))
            ->will($this->returnValue(true));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->deleteAction(1));

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('delete', $this->parameters['action']);
        $this->assertEquals($object, $this->parameters['object']);
        $this->assertEquals('', $this->parameters['csrf_token']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:delete.html.twig', $this->template);
    }

    public function testDeleteActionAjaxSuccess1()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'))
            ->will($this->returnValue(true));

        $this->request->setMethod('DELETE');

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(json_encode(array('result'=>'ok')), $response->getContent());
        $this->assertEquals(array(), $this->session->getFlashBag()->all());
    }

    public function testDeleteActionAjaxSuccess2()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'))
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');
        $this->request->request->set('_method', 'DELETE');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(json_encode(array('result'=>'ok')), $response->getContent());
        $this->assertEquals(array(), $this->session->getFlashBag()->all());
    }

    public function testDeleteActionAjaxError()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'))
            ->will($this->returnValue(true));

        $this->assertLoggerLogsModelManagerException($this->admin, 'delete');

        $this->request->setMethod('DELETE');

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(json_encode(array('result'=>'error')), $response->getContent());
        $this->assertEquals(array(), $this->session->getFlashBag()->all());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testDeleteActionSuccess1($expectedToStringValue, $toStringValue)
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_delete_success', array('%name%' => $expectedToStringValue), 'SonataAdminBundle');

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'))
            ->will($this->returnValue(true));

        $this->request->setMethod('DELETE');

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(array('flash_delete_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertEquals('list', $response->getTargetUrl());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testDeleteActionSuccess2($expectedToStringValue, $toStringValue)
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_delete_success', array('%name%' => $expectedToStringValue), 'SonataAdminBundle');

        $this->request->setMethod('POST');
        $this->request->request->set('_method', 'DELETE');

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(array('flash_delete_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertEquals('list', $response->getTargetUrl());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testDeleteActionSuccessNoCsrfTokenProvider($expectedToStringValue, $toStringValue)
    {
        $this->csrfProvider = null;

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_delete_success', array('%name%' => $expectedToStringValue), 'SonataAdminBundle');

        $this->request->setMethod('POST');
        $this->request->request->set('_method', 'DELETE');

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(array('flash_delete_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertEquals('list', $response->getTargetUrl());
    }

    public function testDeleteActionWrongRequestMethod()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'))
            ->will($this->returnValue(true));

        //without POST request parameter "_method" should not be used as real REST method
        $this->request->query->set('_method', 'DELETE');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->deleteAction(1));

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('delete', $this->parameters['action']);
        $this->assertEquals($object, $this->parameters['object']);
        $this->assertEquals('csrf-token-123_sonata.delete', $this->parameters['csrf_token']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:delete.html.twig', $this->template);
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testDeleteActionError($expectedToStringValue, $toStringValue)
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_delete_error', array('%name%' => $expectedToStringValue), 'SonataAdminBundle');

        $this->assertLoggerLogsModelManagerException($this->admin, 'delete');

        $this->request->setMethod('DELETE');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(array('flash_delete_error'), $this->session->getFlashBag()->get('sonata_flash_error'));
        $this->assertEquals('list', $response->getTargetUrl());
    }

    public function testDeleteActionInvalidCsrfToken()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'))
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');
        $this->request->request->set('_method', 'DELETE');
        $this->request->request->set('_sonata_csrf_token', 'CSRF-INVALID');

        try {
            $this->controller->deleteAction(1);
        } catch (HttpException $e) {
            $this->assertEquals('The csrf token is not valid, CSRF attack?', $e->getMessage());
            $this->assertEquals(400, $e->getStatusCode());
        }
    }

    public function testEditActionNotFoundException()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->editAction();
    }

    public function testEditActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(new \stdClass()));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(false));

        $this->controller->editAction();
    }

    public function testEditAction()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $formView = $this->getMock('Symfony\Component\Form\FormView');

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->editAction());

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('edit', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertEquals($object, $this->parameters['object']);
        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:edit.html.twig', $this->template);
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testEditActionSuccess($expectedToStringValue, $toStringValue)
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('update')
            ->will($this->returnArgument(0));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_edit_success', array('%name%' => $expectedToStringValue), 'SonataAdminBundle');

        $this->request->setMethod('POST');

        $response = $this->controller->editAction();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(array('flash_edit_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertEquals('stdClass_edit', $response->getTargetUrl());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testEditActionError($expectedToStringValue, $toStringValue)
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_edit_error', array('%name%' => $expectedToStringValue), 'SonataAdminBundle');

        $this->request->setMethod('POST');

        $formView = $this->getMock('Symfony\Component\Form\FormView');

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->editAction());

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('edit', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertEquals($object, $this->parameters['object']);

        $this->assertEquals(array('sonata_flash_error'=>array('flash_edit_error')), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:edit.html.twig', $this->template);
    }

    public function testEditActionAjaxSuccess()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('update')
            ->will($this->returnArgument(0));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('getNormalizedIdentifier')
            ->with($this->equalTo($object))
            ->will($this->returnValue('foo_normalized'));

        $this->request->setMethod('POST');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->editAction();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(json_encode(array('result'=>'ok', 'objectId'  => 'foo_normalized')), $response->getContent());
        $this->assertEquals(array(), $this->session->getFlashBag()->all());
    }

    public function testEditActionAjaxError()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->request->setMethod('POST');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $formView = $this->getMock('Symfony\Component\Form\FormView');

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->editAction());

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::ajax_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('edit', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertEquals($object, $this->parameters['object']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:edit.html.twig', $this->template);
    }

    public function testEditActionWithPreview()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->admin->expects($this->once())
            ->method('supportsPreviewMode')
            ->will($this->returnValue(true));

        $formView = $this->getMock('Symfony\Component\Form\FormView');

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');
        $this->request->request->set('btn_preview', 'Preview');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->editAction());

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('edit', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertEquals($object, $this->parameters['object']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:preview.html.twig', $this->template);
    }

    public function testCreateActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('CREATE'))
            ->will($this->returnValue(false));

        $this->controller->createAction();
    }

    public function testCreateAction()
    {
        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('CREATE'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->will($this->returnValue($object));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $formView = $this->getMock('Symfony\Component\Form\FormView');

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->createAction());

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('create', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertEquals($object, $this->parameters['object']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:edit.html.twig', $this->template);
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testCreateActionSuccess($expectedToStringValue, $toStringValue)
    {
        $object = new \stdClass();

        $this->admin->expects($this->exactly(2))
            ->method('isGranted')
            ->will($this->returnCallback(function ($name, $objectIn = null) use ($object) {
                if ($name != 'CREATE') {
                    return false;
                }

                if ($objectIn === null) {
                    return true;
                }

                return ($objectIn === $object);
            }));

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('create')
            ->will($this->returnArgument(0));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_create_success', array('%name%' => $expectedToStringValue), 'SonataAdminBundle');

        $this->request->setMethod('POST');

        $response = $this->controller->createAction();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(array('flash_create_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertEquals('stdClass_edit', $response->getTargetUrl());
    }

    public function testCreateActionAccessDenied2()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $object = new \stdClass();

        $this->admin->expects($this->any())
            ->method('isGranted')
            ->will($this->returnCallback(function ($name, $object = null) {
                if ($name != 'CREATE') {
                    return false;
                }
                if ($object === null) {
                    return true;
                }

                return false;
            }));

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->will($this->returnValue($object));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');

        $this->controller->createAction();
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testCreateActionError($expectedToStringValue, $toStringValue)
    {
        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('CREATE'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->will($this->returnValue($object));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_create_error', array('%name%' => $expectedToStringValue), 'SonataAdminBundle');

        $this->request->setMethod('POST');

        $formView = $this->getMock('Symfony\Component\Form\FormView');

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->createAction());

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('create', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertEquals($object, $this->parameters['object']);

        $this->assertEquals(array('sonata_flash_error'=>array('flash_create_error')), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:edit.html.twig', $this->template);
    }

    public function testCreateActionAjaxSuccess()
    {
        $object = new \stdClass();

        $this->admin->expects($this->exactly(2))
            ->method('isGranted')
            ->will($this->returnCallback(function ($name, $objectIn = null) use ($object) {
                if ($name != 'CREATE') {
                    return false;
                }

                if ($objectIn === null) {
                    return true;
                }

                return ($objectIn === $object);
            }));

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('create')
            ->will($this->returnArgument(0));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('getNormalizedIdentifier')
            ->with($this->equalTo($object))
            ->will($this->returnValue('foo_normalized'));

        $this->request->setMethod('POST');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->createAction();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(json_encode(array('result'=>'ok', 'objectId'  => 'foo_normalized')), $response->getContent());
        $this->assertEquals(array(), $this->session->getFlashBag()->all());
    }

    public function testCreateActionAjaxError()
    {
        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('CREATE'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->will($this->returnValue($object));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->request->setMethod('POST');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $formView = $this->getMock('Symfony\Component\Form\FormView');

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->createAction());

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::ajax_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('create', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertEquals($object, $this->parameters['object']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:edit.html.twig', $this->template);
    }

    public function testCreateActionWithPreview()
    {
        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('CREATE'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->will($this->returnValue($object));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->admin->expects($this->once())
            ->method('supportsPreviewMode')
            ->will($this->returnValue(true));

        $formView = $this->getMock('Symfony\Component\Form\FormView');

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');
        $this->request->request->set('btn_preview', 'Preview');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->createAction());

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('create', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertEquals($object, $this->parameters['object']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:preview.html.twig', $this->template);
    }

    public function testExportActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EXPORT'))
            ->will($this->returnValue(false));

        $this->controller->exportAction($this->request);
    }

    public function testExportActionWrongFormat()
    {
        $this->setExpectedException('RuntimeException', 'Export in format `csv` is not allowed for class: `Foo`. Allowed formats are: `json`');

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EXPORT'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('getExportFormats')
            ->will($this->returnValue(array('json')));

        $this->admin->expects($this->once())
            ->method('getClass')
            ->will($this->returnValue('Foo'));

        $this->request->query->set('format', 'csv');

        $this->controller->exportAction($this->request);
    }

    public function testExportAction()
    {
        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EXPORT'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('getExportFormats')
            ->will($this->returnValue(array('json')));

        $dataSourceIterator = $this->getMock('Exporter\Source\SourceIteratorInterface');

        $this->admin->expects($this->once())
            ->method('getDataSourceIterator')
            ->will($this->returnValue($dataSourceIterator));

        $this->request->query->set('format', 'json');

        $response = $this->controller->exportAction($this->request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\StreamedResponse', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array(), $this->session->getFlashBag()->all());
    }

    public function testHistoryActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->any())
            ->method('getObject')
            ->will($this->returnValue(new \StdClass()));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(false));

        $this->controller->historyAction();
    }

    public function testHistoryActionNotFoundException()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->historyAction();
    }

    public function testHistoryActionNoReader()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'unable to find the audit reader for class : Foo');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Foo'));

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue(false));

        $this->controller->historyAction();
    }

    public function testHistoryAction()
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Foo'));

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue(true));

        $reader = $this->getMock('Sonata\AdminBundle\Model\AuditReaderInterface');

        $this->auditManager->expects($this->once())
            ->method('getReader')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue($reader));

        $reader->expects($this->once())
            ->method('findRevisions')
            ->with($this->equalTo('Foo'), $this->equalTo(123))
            ->will($this->returnValue(array()));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->historyAction());

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('history', $this->parameters['action']);
        $this->assertEquals(array(), $this->parameters['revisions']);
        $this->assertEquals($object, $this->parameters['object']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:history.html.twig', $this->template);
    }

    public function testAclActionAclNotEnabled()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'ACL are not enabled for this admin');

        $this->controller->aclAction();
    }

    public function testAclActionNotFoundException()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $this->admin->expects($this->once())
            ->method('isAclEnabled')
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->aclAction();
    }

    public function testAclActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('isAclEnabled')
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('MASTER'), $this->equalTo($object))
            ->will($this->returnValue(false));

        $this->controller->aclAction();
    }

    public function testAclAction()
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('isAclEnabled')
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(true));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('getMaskBuilderClass')
            ->will($this->returnValue('\Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap'));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

         $form->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($this->getMock('Symfony\Component\Form\FormView')));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('createForm')
            ->with($this->isInstanceOf('Sonata\AdminBundle\Util\AdminObjectAclData'))
            ->will($this->returnValue($form));

        $aclSecurityHandler = $this->getMockBuilder('Sonata\AdminBundle\Security\Handler\AclSecurityHandler')
            ->disableOriginalConstructor()
            ->getMock();

        $aclSecurityHandler->expects($this->any())
            ->method('getObjectPermissions')
            ->will($this->returnValue(array()));

        $this->admin->expects($this->any())
            ->method('getSecurityHandler')
            ->will($this->returnValue($aclSecurityHandler));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->aclAction());

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('acl', $this->parameters['action']);
        $this->assertEquals(array(), $this->parameters['permissions']);
        $this->assertEquals($object, $this->parameters['object']);
        $this->assertInstanceOf('\ArrayIterator', $this->parameters['users']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:acl.html.twig', $this->template);
    }

    public function testAclActionInvalidUpdate()
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('isAclEnabled')
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(true));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('getMaskBuilderClass')
            ->will($this->returnValue('\Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap'));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

         $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

         $form->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($this->getMock('Symfony\Component\Form\FormView')));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('createForm')
            ->with($this->isInstanceOf('Sonata\AdminBundle\Util\AdminObjectAclData'))
            ->will($this->returnValue($form));

        $aclSecurityHandler = $this->getMockBuilder('Sonata\AdminBundle\Security\Handler\AclSecurityHandler')
            ->disableOriginalConstructor()
            ->getMock();

        $aclSecurityHandler->expects($this->any())
            ->method('getObjectPermissions')
            ->will($this->returnValue(array()));

        $this->admin->expects($this->any())
            ->method('getSecurityHandler')
            ->will($this->returnValue($aclSecurityHandler));

        $this->request->setMethod('POST');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->aclAction());

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('acl', $this->parameters['action']);
        $this->assertEquals(array(), $this->parameters['permissions']);
        $this->assertEquals($object, $this->parameters['object']);
        $this->assertInstanceOf('\ArrayIterator', $this->parameters['users']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:acl.html.twig', $this->template);
    }

    public function testAclActionSuccessfulUpdate()
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('isAclEnabled')
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(true));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('getMaskBuilderClass')
            ->will($this->returnValue('\Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap'));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

         $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($this->getMock('Symfony\Component\Form\FormView')));

         $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('createForm')
            ->with($this->isInstanceOf('Sonata\AdminBundle\Util\AdminObjectAclData'))
            ->will($this->returnValue($form));

        $aclSecurityHandler = $this->getMockBuilder('Sonata\AdminBundle\Security\Handler\AclSecurityHandler')
            ->disableOriginalConstructor()
            ->getMock();

        $aclSecurityHandler->expects($this->any())
            ->method('getObjectPermissions')
            ->will($this->returnValue(array()));

        $this->admin->expects($this->any())
            ->method('getSecurityHandler')
            ->will($this->returnValue($aclSecurityHandler));

        $this->request->setMethod('POST');

        $response = $this->controller->aclAction();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);

        $this->assertSame(array('flash_acl_edit_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertEquals('stdClass_acl', $response->getTargetUrl());
    }

    public function testHistoryViewRevisionActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->any())
            ->method('getObject')
            ->will($this->returnValue(new \StdClass()));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(false));

        $this->controller->historyViewRevisionAction();
    }

    public function testHistoryViewRevisionActionNotFoundException()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'unable to find the object with id : 123');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->historyViewRevisionAction();
    }

    public function testHistoryViewRevisionActionNoReader()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'unable to find the audit reader for class : Foo');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Foo'));

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue(false));

        $this->controller->historyViewRevisionAction();
    }

    public function testHistoryViewRevisionActionNotFoundRevision()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'unable to find the targeted object `123` from the revision `456` with classname : `Foo`');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Foo'));

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue(true));

        $reader = $this->getMock('Sonata\AdminBundle\Model\AuditReaderInterface');

        $this->auditManager->expects($this->once())
            ->method('getReader')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue($reader));

        $reader->expects($this->once())
            ->method('find')
            ->with($this->equalTo('Foo'), $this->equalTo(123), $this->equalTo(456))
            ->will($this->returnValue(null));

        $this->controller->historyViewRevisionAction(123, 456);
    }

    public function testHistoryViewRevisionAction()
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Foo'));

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue(true));

        $reader = $this->getMock('Sonata\AdminBundle\Model\AuditReaderInterface');

        $this->auditManager->expects($this->once())
            ->method('getReader')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue($reader));

        $objectRevision = new \stdClass();
        $objectRevision->revision = 456;

        $reader->expects($this->once())
            ->method('find')
            ->with($this->equalTo('Foo'), $this->equalTo(123), $this->equalTo(456))
            ->will($this->returnValue($objectRevision));

        $this->admin->expects($this->once())
            ->method('setSubject')
            ->with($this->equalTo($objectRevision))
            ->will($this->returnValue(null));

        $fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin->expects($this->once())
            ->method('getShow')
            ->will($this->returnValue($fieldDescriptionCollection));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->historyViewRevisionAction(123, 456));

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('show', $this->parameters['action']);
        $this->assertEquals($objectRevision, $this->parameters['object']);
        $this->assertEquals($fieldDescriptionCollection, $this->parameters['elements']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:show.html.twig', $this->template);
    }

    public function testhistoryCompareRevisionsActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(false));

        $this->controller->historyCompareRevisionsAction();
    }

    public function testhistoryCompareRevisionsActionNotFoundException()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'unable to find the object with id : 123');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->historyCompareRevisionsAction();
    }

    public function testhistoryCompareRevisionsActionNoReader()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'unable to find the audit reader for class : Foo');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Foo'));

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue(false));

        $this->controller->historyCompareRevisionsAction();
    }

    public function testhistoryCompareRevisionsActionNotFoundBaseRevision()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'unable to find the targeted object `123` from the revision `456` with classname : `Foo`');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Foo'));

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue(true));

        $reader = $this->getMock('Sonata\AdminBundle\Model\AuditReaderInterface');

        $this->auditManager->expects($this->once())
            ->method('getReader')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue($reader));

        // once because it will not be found and therefore the second call won't be executed
        $reader->expects($this->once())
            ->method('find')
            ->with($this->equalTo('Foo'), $this->equalTo(123), $this->equalTo(456))
            ->will($this->returnValue(null));

        $this->controller->historyCompareRevisionsAction(123, 456, 789);
    }

    public function testhistoryCompareRevisionsActionNotFoundCompareRevision()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'unable to find the targeted object `123` from the revision `789` with classname : `Foo`');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Foo'));

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue(true));

        $reader = $this->getMock('Sonata\AdminBundle\Model\AuditReaderInterface');

        $this->auditManager->expects($this->once())
            ->method('getReader')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue($reader));

        $objectRevision = new \stdClass();
        $objectRevision->revision = 456;

        // first call should return, so the second call will throw an exception
        $reader->expects($this->at(0))
            ->method('find')
            ->with($this->equalTo('Foo'), $this->equalTo(123), $this->equalTo(456))
            ->will($this->returnValue($objectRevision));

        $reader->expects($this->at(1))
            ->method('find')
            ->with($this->equalTo('Foo'), $this->equalTo(123), $this->equalTo(789))
            ->will($this->returnValue(null));

        $this->controller->historyCompareRevisionsAction(123, 456, 789);
    }

    public function testhistoryCompareRevisionsActionAction()
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Foo'));

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue(true));

        $reader = $this->getMock('Sonata\AdminBundle\Model\AuditReaderInterface');

        $this->auditManager->expects($this->once())
            ->method('getReader')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue($reader));

        $objectRevision = new \stdClass();
        $objectRevision->revision = 456;

        $compareObjectRevision = new \stdClass();
        $compareObjectRevision->revision = 789;

        $reader->expects($this->at(0))
            ->method('find')
            ->with($this->equalTo('Foo'), $this->equalTo(123), $this->equalTo(456))
            ->will($this->returnValue($objectRevision));

        $reader->expects($this->at(1))
            ->method('find')
            ->with($this->equalTo('Foo'), $this->equalTo(123), $this->equalTo(789))
            ->will($this->returnValue($compareObjectRevision));

        $this->admin->expects($this->once())
            ->method('setSubject')
            ->with($this->equalTo($objectRevision))
            ->will($this->returnValue(null));

        $fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin->expects($this->once())
            ->method('getShow')
            ->will($this->returnValue($fieldDescriptionCollection));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->historyCompareRevisionsAction(123, 456, 789));

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('show', $this->parameters['action']);
        $this->assertEquals($objectRevision, $this->parameters['object']);
        $this->assertEquals($compareObjectRevision, $this->parameters['object_compare']);
        $this->assertEquals($fieldDescriptionCollection, $this->parameters['elements']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:show_compare.html.twig', $this->template);
    }

    public function testBatchActionWrongMethod()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'Invalid request type "GET", POST expected');

        $this->controller->batchAction();
    }

    public function testBatchActionActionNotDefined()
    {
        $this->setExpectedException('RuntimeException', 'The `foo` batch action is not defined');

        $batchActions = array();

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $this->request->setMethod('POST');
        $this->request->request->set('data', json_encode(array('action'=>'foo', 'idx'=>array('123', '456'), 'all_elements'=>false)));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $this->controller->batchAction();
    }

    public function testBatchActionActionInvalidCsrfToken()
    {
        $this->request->setMethod('POST');
        $this->request->request->set('data', json_encode(array('action'=>'foo', 'idx'=>array('123', '456'), 'all_elements'=>false)));
        $this->request->request->set('_sonata_csrf_token', 'CSRF-INVALID');

        try {
            $this->controller->batchAction();
        } catch (HttpException $e) {
            $this->assertEquals('The csrf token is not valid, CSRF attack?', $e->getMessage());
            $this->assertEquals(400, $e->getStatusCode());
        }
    }

    public function testBatchActionMethodNotExist()
    {
        $this->setExpectedException('RuntimeException', 'A `Sonata\AdminBundle\Controller\CRUDController::batchActionFoo` method must be created');

        $batchActions = array('foo'=>array('label'=>'Foo Bar', 'ask_confirmation' => false));

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $datagrid = $this->getMock('\Sonata\AdminBundle\Datagrid\DatagridInterface');
        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $this->request->setMethod('POST');
        $this->request->request->set('data', json_encode(array('action'=>'foo', 'idx'=>array('123', '456'), 'all_elements'=>false)));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $this->controller->batchAction();
    }

    public function testBatchActionWithoutConfirmation()
    {
        $batchActions = array('delete'=>array('label'=>'Foo Bar', 'ask_confirmation' => false));

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $datagrid = $this->getMock('\Sonata\AdminBundle\Datagrid\DatagridInterface');

        $query = $this->getMock('\Sonata\AdminBundle\Datagrid\ProxyQueryInterface');
        $datagrid->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Foo'));

        $modelManager->expects($this->once())
            ->method('addIdentifiersToQuery')
            ->with($this->equalTo('Foo'), $this->equalTo($query), $this->equalTo(array('123', '456')))
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');
        $this->request->request->set('data', json_encode(array('action'=>'delete', 'idx'=>array('123', '456'), 'all_elements'=>false)));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
        $this->assertSame(array('flash_batch_delete_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertEquals('list?', $result->getTargetUrl());
    }

    public function testBatchActionWithoutConfirmation2()
    {
        $batchActions = array('delete'=>array('label'=>'Foo Bar', 'ask_confirmation' => false));

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $datagrid = $this->getMock('\Sonata\AdminBundle\Datagrid\DatagridInterface');

        $query = $this->getMock('\Sonata\AdminBundle\Datagrid\ProxyQueryInterface');
        $datagrid->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Foo'));

        $modelManager->expects($this->once())
            ->method('addIdentifiersToQuery')
            ->with($this->equalTo('Foo'), $this->equalTo($query), $this->equalTo(array('123', '456')))
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');
        $this->request->request->set('action', 'delete');
        $this->request->request->set('idx', array('123', '456'));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
        $this->assertSame(array('flash_batch_delete_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertEquals('list?', $result->getTargetUrl());
    }

    public function testBatchActionWithConfirmation()
    {
        $batchActions = array('delete'=>array('label'=>'Foo Bar', 'ask_confirmation' => true));

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $data = array('action'=>'delete', 'idx'=>array('123', '456'), 'all_elements'=>false);

        $this->request->setMethod('POST');
        $this->request->request->set('data', json_encode($data));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $datagrid = $this->getMock('\Sonata\AdminBundle\Datagrid\DatagridInterface');

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

         $form->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($this->getMock('Symfony\Component\Form\FormView')));

        $datagrid->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->batchAction());

        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);

        $this->assertEquals('list', $this->parameters['action']);
        $this->assertEquals($datagrid, $this->parameters['datagrid']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertEquals($data, $this->parameters['data']);
        $this->assertEquals('csrf-token-123_sonata.batch', $this->parameters['csrf_token']);
        $this->assertEquals('Foo Bar', $this->parameters['action_label']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
        $this->assertEquals('SonataAdminBundle:CRUD:batch_confirmation.html.twig', $this->template);
    }

    public function testBatchActionNonRelevantAction()
    {
        $controller = new BatchAdminController();
        $controller->setContainer($this->container);

        $batchActions = array('foo'=>array('label'=>'Foo Bar', 'ask_confirmation' => false));

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $datagrid = $this->getMock('\Sonata\AdminBundle\Datagrid\DatagridInterface');

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $this->request->setMethod('POST');
        $this->request->request->set('action', 'foo');
        $this->request->request->set('idx', array('789'));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $controller->batchAction();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
        $this->assertSame(array('flash_batch_empty'), $this->session->getFlashBag()->get('sonata_flash_info'));
        $this->assertEquals('list?', $result->getTargetUrl());
    }

    public function testBatchActionNonRelevantAction2()
    {
        $controller = new BatchAdminController();
        $controller->setContainer($this->container);

        $batchActions = array('foo'=>array('label'=>'Foo Bar', 'ask_confirmation' => false));

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $datagrid = $this->getMock('\Sonata\AdminBundle\Datagrid\DatagridInterface');

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $this->request->setMethod('POST');
        $this->request->request->set('action', 'foo');
        $this->request->request->set('idx', array('999'));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $controller->batchAction();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
        $this->assertSame(array('flash_foo_error'), $this->session->getFlashBag()->get('sonata_flash_info'));
        $this->assertEquals('list?', $result->getTargetUrl());
    }

    public function testBatchActionNoItems()
    {
        $batchActions = array('delete'=>array('label'=>'Foo Bar', 'ask_confirmation' => true));

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $datagrid = $this->getMock('\Sonata\AdminBundle\Datagrid\DatagridInterface');

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $this->request->setMethod('POST');
        $this->request->request->set('action', 'delete');
        $this->request->request->set('idx', array());
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
        $this->assertSame(array('flash_batch_empty'), $this->session->getFlashBag()->get('sonata_flash_info'));
        $this->assertEquals('list?', $result->getTargetUrl());
    }

    public function testBatchActionNoItemsEmptyQuery()
    {
        $controller = new BatchAdminController();
        $controller->setContainer($this->container);

        $batchActions = array('bar'=>array('label'=>'Foo Bar', 'ask_confirmation' => false));

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $datagrid = $this->getMock('\Sonata\AdminBundle\Datagrid\DatagridInterface');

        $query = $this->getMock('\Sonata\AdminBundle\Datagrid\ProxyQueryInterface');
        $datagrid->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Foo'));

        $this->request->setMethod('POST');
        $this->request->request->set('action', 'bar');
        $this->request->request->set('idx', array());
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $controller->batchAction();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $result);
        $this->assertEquals('batchActionBar executed', $result->getContent());
    }

    public function getCsrfProvider()
    {
        return $this->csrfProvider;
    }

    public function getToStringValues()
    {
        return array(
            array('', ''),
            array('Foo', 'Foo'),
            array('&lt;a href=&quot;http://foo&quot;&gt;Bar&lt;/a&gt;', '<a href="http://foo">Bar</a>'),
            array('&lt;&gt;&amp;&quot;&#039;abcdefghijklmnopqrstuvwxyz*-+.,?_()[]\/', '<>&"\'abcdefghijklmnopqrstuvwxyz*-+.,?_()[]\/'),
        );
    }

    private function expectTranslate($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $this->admin->expects($this->once())
            ->method('trans')
            ->with($this->equalTo($id), $this->equalTo($parameters), $this->equalTo($domain), $this->equalTo($locale))
            ->will($this->returnValue($id));
    }
}
