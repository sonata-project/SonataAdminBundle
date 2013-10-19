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
     * @var Sonata\AdminBundle\Admin\AdminInterface
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
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->request = new Request();
        $this->pool = new Pool($container, 'title', 'logo.png');
        $this->request->attributes->set('_sonata_admin', 'foo.admin');
        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $this->parameters = array();

        // php 5.3 BC
        $params = &$this->parameters;

        $templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine', array(), array($container, array()));

        $templating->expects($this->any())
            ->method('renderResponse')
            ->will($this->returnCallback(function($view, array $parameters = array(), Response $response = null) use (&$params) {
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
            ->will($this->returnCallback(function($name) use ($formExtension) {
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

        // php 5.3 BC
        $auditManager = $this->auditManager;

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function($id) use ($pool, $request, $admin, $templating, $twig, $session, $exporter, $auditManager) {
                    switch ($id) {
                        case 'sonata.admin.pool':
                            return $pool;
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
                    }

                    return null;
                }));

        $this->admin->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnCallback(function($name) {
                    switch ($name) {
                        case 'ajax':
                            return 'SonataAdminBundle::ajax_layout.html.twig';
                        case 'layout':
                            return 'SonataAdminBundle::standard_layout.html.twig';
                        case 'show':
                            return 'SonataAdminBundle:CRUD:show.html.twig';
                        case 'edit':
                            return 'SonataAdminBundle:CRUD:edit.html.twig';
                    }

                    return null;
                }));

        $this->admin->expects($this->any())
            ->method('getIdParameter')
            ->will($this->returnValue('id'));

        $this->admin->expects($this->any())
            ->method('generateUrl')
            ->will($this->returnCallback(function($name, array $parameters = array(), $absolute = false) {
                    $result = $name;
                    if (!empty($parameters)) {
                        $result .= '?'.http_build_query($parameters);
                    }

                    return $result;
                }));

        $this->admin->expects($this->any())
            ->method('generateObjectUrl')
            ->will($this->returnCallback(function($name, $object, array $parameters = array(), $absolute = false) {
                    $result = get_class($object).'_'.$name;
                    if (!empty($parameters)) {
                        $result .= '?'.http_build_query($parameters);
                    }

                    return $result;
                }));

        $this->controller = new CRUDController();
        $this->controller->setContainer($container);
    }

    public function testRenderJson1()
    {
        $data = array('example'=>'123', 'foo'=>'bar');

        $this->request->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->controller->renderJson($data);

        $this->assertEquals($response->headers->get('Content-Type'), 'application/json');
        $this->assertEquals(json_encode($data), $response->getContent());
    }

    public function testRenderJson2()
    {
        $data = array('example'=>'123', 'foo'=>'bar');

        $this->request->headers->set('Content-Type', 'multipart/form-data');
        $response = $this->controller->renderJson($data);

        $this->assertEquals($response->headers->get('Content-Type'), 'application/json');
        $this->assertEquals(json_encode($data), $response->getContent());
    }

    public function testRenderJsonAjax()
    {
        $data = array('example'=>'123', 'foo'=>'bar');

        $this->request->attributes->set('_xml_http_request', true);
        $this->request->headers->set('Content-Type', 'multipart/form-data');
        $response = $this->controller->renderJson($data);

        $this->assertEquals($response->headers->get('Content-Type'), 'text/plain');
        $this->assertEquals(json_encode($data), $response->getContent());
    }

    public function testIsXmlHttpRequest()
    {
        $this->assertFalse($this->controller->isXmlHttpRequest());

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->assertTrue($this->controller->isXmlHttpRequest());

        $this->request->headers->remove('X-Requested-With');
        $this->assertFalse($this->controller->isXmlHttpRequest());

        $this->request->attributes->set('_xml_http_request', true);
        $this->assertTrue($this->controller->isXmlHttpRequest());
    }

    public function testConfigure()
    {
        $uniqueId = '';

        $this->admin->expects($this->once())
            ->method('setUniqid')
            ->will($this->returnCallback(function($uniqid) use (&$uniqueId) {
                    $uniqueId = $uniqid;
                }));

        $this->request->query->set('uniqid', 123456);
        $this->controller->configure();

        $this->assertEquals(123456, $uniqueId);
        $this->assertAttributeEquals($this->admin, 'admin', $this->controller);
    }

    public function testConfigureChild()
    {
        $uniqueId = '';

        $this->admin->expects($this->once())
            ->method('setUniqid')
            ->will($this->returnCallback(function($uniqid) use (&$uniqueId) {
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
        $this->controller->configure();

        $this->assertEquals(123456, $uniqueId);
        $this->assertAttributeEquals($adminParent, 'admin', $this->controller);
    }

    public function testConfigureWithException()
    {
        $this->setExpectedException('RuntimeException', 'There is no `_sonata_admin` defined for the controller `Sonata\AdminBundle\Controller\CRUDController`');

        $this->request->attributes->remove('_sonata_admin');
        $this->controller->configure();
    }

    public function testConfigureWithException2()
    {
        $this->setExpectedException('RuntimeException', 'Unable to find the admin class related to the current controller (Sonata\AdminBundle\Controller\CRUDController)');

        $this->request->attributes->set('_sonata_admin', 'nonexistent.admin');
        $this->controller->configure();
    }

    public function testGetBaseTemplate()
    {
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->controller->getBaseTemplate());

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->assertEquals('SonataAdminBundle::ajax_layout.html.twig', $this->controller->getBaseTemplate());

        $this->request->headers->remove('X-Requested-With');
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->controller->getBaseTemplate());

        $this->request->attributes->set('_xml_http_request', true);
        $this->assertEquals('SonataAdminBundle::ajax_layout.html.twig', $this->controller->getBaseTemplate());
    }

    public function testRender()
    {
        $this->parameters = array();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->render('FooAdminBundle::foo.html.twig', array()));
        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);
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
    }

    public function testRenderCustomParams()
    {
        $this->parameters = array();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->render('FooAdminBundle::foo.html.twig', array('foo'=>'bar')));
        $this->assertEquals($this->admin, $this->parameters['admin']);
        $this->assertEquals('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertEquals($this->pool, $this->parameters['admin_pool']);
        $this->assertEquals('bar', $this->parameters['foo']);
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
        $this->assertEquals('', $this->parameters['csrf_token']);
        $this->assertEquals(array(), $this->session->getFlashBag()->all());
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

    public function testBatchActionDeleteWithModelManagerException()
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        $modelManager->expects($this->once())
            ->method('batchDelete')
            ->will($this->returnCallback(function() {
                    throw new ModelManagerException();
                }));

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

        $response = $this->controller->redirectTo($object);
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
        $this->controller->addFlash('foo', 'bar');
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
        $this->assertEquals('', $this->parameters['csrf_token']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
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

        $this->admin->expects($this->once())
            ->method('delete')
            ->will($this->returnCallback(function() {
                    throw new ModelManagerException();
                }));

        $this->request->setMethod('DELETE');

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(json_encode(array('result'=>'error')), $response->getContent());
        $this->assertEquals(array(), $this->session->getFlashBag()->all());
    }

    public function testDeleteActionSuccess1()
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

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(array('flash_delete_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertEquals('list', $response->getTargetUrl());
    }

    public function testDeleteActionSuccess2()
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
        $this->assertEquals('', $this->parameters['csrf_token']);

        $this->assertEquals(array(), $this->session->getFlashBag()->all());
    }

    public function testDeleteActionError()
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
            ->method('delete')
            ->will($this->returnCallback(function() {
                    throw new ModelManagerException();
                }));

        $this->request->setMethod('DELETE');

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(array('flash_delete_error'), $this->session->getFlashBag()->get('sonata_flash_error'));
        $this->assertEquals('list', $response->getTargetUrl());
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
    }

    public function testEditActionSuccess()
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
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');

        $response = $this->controller->editAction();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(array('flash_edit_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertEquals('stdClass_edit', $response->getTargetUrl());
    }

    public function testEditActionError()
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
    }

    public function testEditActionAjaxSuccess()
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
    }

    public function testCreateActionSuccess()
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
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');

        $response = $this->controller->createAction();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(array('flash_create_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertEquals('stdClass_edit', $response->getTargetUrl());
    }

    public function testCreateActionError()
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
    }

    public function testCreateActionAjaxSuccess()
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
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

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
    }
}
