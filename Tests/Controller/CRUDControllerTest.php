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

use Exporter\Exporter;
use Exporter\Writer\JsonWriter;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Tests\Fixtures\Controller\BatchAdminController;
use Sonata\AdminBundle\Tests\Fixtures\Controller\PreCRUDController;
use Sonata\AdminBundle\Util\AdminObjectAclManipulator;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * Test for CRUDController.
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
     * @var \Sonata\AdminBundle\Model\AuditManager
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
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->request = new Request();
        $this->pool = new Pool($this->container, 'title', 'logo.png');
        $this->pool->setAdminServiceIds(array('foo.admin'));
        $this->request->attributes->set('_sonata_admin', 'foo.admin');
        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $this->translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
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
        $translator = $this->translator;

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
            }));

        // NEXT_MAJOR : require sonata/exporter ^1.7 and remove conditional
        if (class_exists('Exporter\Exporter')) {
            $exporter = new Exporter(array(new JsonWriter('/tmp/sonataadmin/export.json')));
        } else {
            $exporter = $this->getMock('Sonata\AdminBundle\Export\Exporter');

            $exporter->expects($this->any())
                ->method('getResponse')
                ->will($this->returnValue(new StreamedResponse()));
        }

        $this->auditManager = $this->getMockBuilder('Sonata\AdminBundle\Model\AuditManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->adminObjectAclManipulator = $this->getMockBuilder('Sonata\AdminBundle\Util\AdminObjectAclManipulator')
            ->disableOriginalConstructor()
            ->getMock();

        // php 5.3 BC
        $request = $this->request;
        $auditManager = $this->auditManager;
        $adminObjectAclManipulator = $this->adminObjectAclManipulator;

        // Prefer Symfony 2.x interfaces
        if (interface_exists('Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface')) {
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
        } else {
            $this->csrfProvider = $this->getMockBuilder(
                'Symfony\Component\Security\Csrf\CsrfTokenManagerInterface'
            )
                ->getMock();

            $this->csrfProvider->expects($this->any())
                ->method('getToken')
                ->will($this->returnCallback(function ($intention) {
                    return new CsrfToken($intention, 'csrf-token-123_'.$intention);
                }));

            $this->csrfProvider->expects($this->any())
                ->method('isTokenValid')
                ->will($this->returnCallback(function (CsrfToken $token) {
                    if ($token->getValue() == 'csrf-token-123_'.$token->getId()) {
                        return true;
                    }

                    return false;
                }));
        }

        // php 5.3 BC
        $csrfProvider = $this->csrfProvider;

        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger = $this->logger; // php 5.3 BC

        $requestStack = null;
        if (class_exists('Symfony\Component\HttpFoundation\RequestStack')) {
            $requestStack = new RequestStack();
            $requestStack->push($request);
        }

        $this->kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $kernel = $this->kernel; // php 5.3 BC

        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use (
                $pool,
                $admin,
                $request,
                $templating,
                $twig,
                $session,
                $exporter,
                $auditManager,
                $adminObjectAclManipulator,
                $requestStack,
                $csrfProvider,
                $logger,
                $kernel,
                $translator
            ) {
                switch ($id) {
                    case 'sonata.admin.pool':
                        return $pool;
                    case 'request':
                        return $request;
                    case 'request_stack':
                        return $requestStack;
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
                    case 'security.csrf.token_manager':
                        return $csrfProvider;
                    case 'logger':
                        return $logger;
                    case 'kernel':
                        return $kernel;
                    case 'translator':
                        return $translator;
                }
            }));

        // php 5.3
        $tthis = $this;

        $this->container->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(function ($id) use ($tthis) {
                if ($id == 'form.csrf_provider' && Kernel::MAJOR_VERSION == 2 && $tthis->getCsrfProvider() !== null) {
                    return true;
                }

                if ($id == 'security.csrf.token_manager' && Kernel::MAJOR_VERSION >= 3 && $tthis->getCsrfProvider() !== null) {
                    return true;
                }

                if ($id == 'logger') {
                    return true;
                }

                if ($id == 'session') {
                    return true;
                }

                if ($id == 'templating') {
                    return true;
                }

                if ($id == 'translator') {
                    return true;
                }

                return false;
            }));

        $this->container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnCallback(function ($name) {
                switch ($name) {
                    case 'security.role_hierarchy.roles':
                       return array('ROLE_SUPER_ADMIN' => array('ROLE_USER', 'ROLE_SONATA_ADMIN', 'ROLE_ADMIN'));
                }
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
            }));

        $this->admin->expects($this->any())
            ->method('getIdParameter')
            ->will($this->returnValue('id'));

        $this->admin->expects($this->any())
            ->method('getAccessMapping')
            ->will($this->returnValue(array()));

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
            'addFlash',
        );
        foreach ($testedMethods as $testedMethod) {
            $method = new \ReflectionMethod('Sonata\\AdminBundle\\Controller\\CRUDController', $testedMethod);
            $method->setAccessible(true);
            $this->protectedTestedMethods[$testedMethod] = $method;
        }
    }

    public function testRenderJson1()
    {
        $data = array('example' => '123', 'foo' => 'bar');

        $this->request->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data, 200, array(), $this->request);

        $this->assertSame($response->headers->get('Content-Type'), 'application/json');
        $this->assertSame(json_encode($data), $response->getContent());
    }

    public function testRenderJson2()
    {
        $data = array('example' => '123', 'foo' => 'bar');

        $this->request->headers->set('Content-Type', 'multipart/form-data');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data, 200, array(), $this->request);

        $this->assertSame($response->headers->get('Content-Type'), 'application/json');
        $this->assertSame(json_encode($data), $response->getContent());
    }

    public function testRenderJsonAjax()
    {
        $data = array('example' => '123', 'foo' => 'bar');

        $this->request->attributes->set('_xml_http_request', true);
        $this->request->headers->set('Content-Type', 'multipart/form-data');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data, 200, array(), $this->request);

        $this->assertSame($response->headers->get('Content-Type'), 'application/json');
        $this->assertSame(json_encode($data), $response->getContent());
    }

    public function testIsXmlHttpRequest()
    {
        $this->assertFalse($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller, $this->request));

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $this->assertTrue($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller, $this->request));

        $this->request->headers->remove('X-Requested-With');
        $this->assertFalse($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller, $this->request));

        $this->request->attributes->set('_xml_http_request', true);
        $this->assertTrue($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller, $this->request));
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

        $this->assertSame(123456, $uniqueId);
        $this->assertAttributeSame($this->admin, 'admin', $this->controller);
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

        $this->assertSame(123456, $uniqueId);
        $this->assertAttributeInstanceOf(get_class($adminParent), 'admin', $this->controller);
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
            'Unable to find the admin class related to the current controller '.
            '(Sonata\AdminBundle\Controller\CRUDController)'
        );

        $this->pool->setAdminServiceIds(array('nonexistent.admin'));
        $this->request->attributes->set('_sonata_admin', 'nonexistent.admin');
        $this->protectedTestedMethods['configure']->invoke($this->controller);
    }

    public function testGetBaseTemplate()
    {
        $this->assertSame(
            'SonataAdminBundle::standard_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller, $this->request)
        );

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->assertSame(
            'SonataAdminBundle::ajax_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller, $this->request)
        );

        $this->request->headers->remove('X-Requested-With');
        $this->assertSame(
            'SonataAdminBundle::standard_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller, $this->request)
        );

        $this->request->attributes->set('_xml_http_request', true);
        $this->assertSame(
            'SonataAdminBundle::ajax_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller, $this->request)
        );
    }

    public function testRender()
    {
        $this->parameters = array();
        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\Response',
            $this->controller->render('FooAdminBundle::foo.html.twig', array(), null, $this->request)
        );
        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);
        $this->assertSame('FooAdminBundle::foo.html.twig', $this->template);
    }

    public function testRenderWithResponse()
    {
        $this->parameters = array();
        $response = $response = new Response();
        $response->headers->set('X-foo', 'bar');
        $responseResult = $this->controller->render('FooAdminBundle::foo.html.twig', array(), $response, $this->request);

        $this->assertSame($response, $responseResult);
        $this->assertSame('bar', $responseResult->headers->get('X-foo'));
        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);
        $this->assertSame('FooAdminBundle::foo.html.twig', $this->template);
    }

    public function testRenderCustomParams()
    {
        $this->parameters = array();
        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\Response',
            $this->controller->render('FooAdminBundle::foo.html.twig',
            array('foo' => 'bar'), null, $this->request)
        );
        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);
        $this->assertSame('bar', $this->parameters['foo']);
        $this->assertSame('FooAdminBundle::foo.html.twig', $this->template);
    }

    public function testRenderAjax()
    {
        $this->parameters = array();
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->render('FooAdminBundle::foo.html.twig', array('foo' => 'bar'), null, $this->request));
        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::ajax_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);
        $this->assertSame('bar', $this->parameters['foo']);
        $this->assertSame('FooAdminBundle::foo.html.twig', $this->template);
    }

    public function testListActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('list'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->listAction($this->request);
    }

    public function testPreList()
    {
        $this->admin->expects($this->any())
            ->method('hasRoute')
            ->with($this->equalTo('list'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('list'))
            ->will($this->returnValue(true));

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->listAction($this->request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame('preList called', $response->getContent());
    }

    public function testListAction()
    {
        $datagrid = $this->getMock('Sonata\AdminBundle\Datagrid\DatagridInterface');

        $this->admin->expects($this->any())
            ->method('hasRoute')
            ->with($this->equalTo('list'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('list'))
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
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->listAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('list', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertInstanceOf('Sonata\AdminBundle\Datagrid\DatagridInterface', $this->parameters['datagrid']);
        $this->assertSame('csrf-token-123_sonata.batch', $this->parameters['csrf_token']);
        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:list.html.twig', $this->template);
    }

    public function testBatchActionDeleteAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('batchDelete'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->batchActionDelete($this->getMock('Sonata\AdminBundle\Datagrid\ProxyQueryInterface'));
    }

    public function testBatchActionDelete()
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('batchDelete'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $this->admin->expects($this->once())
            ->method('getFilterParameters')
            ->will($this->returnValue(array('foo' => 'bar')));

        $result = $this->controller->batchActionDelete($this->getMock('Sonata\AdminBundle\Datagrid\ProxyQueryInterface'));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
        $this->assertSame(array('flash_batch_delete_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list?filter%5Bfoo%5D=bar', $result->getTargetUrl());
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
            ->will($this->returnValue(array('foo' => 'bar')));

        $result = $this->controller->batchActionDelete($this->getMock('Sonata\AdminBundle\Datagrid\ProxyQueryInterface'));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
        $this->assertSame(array('flash_batch_delete_error'), $this->session->getFlashBag()->get('sonata_flash_error'));
        $this->assertSame('list?filter%5Bfoo%5D=bar', $result->getTargetUrl());
    }

    public function testBatchActionDeleteWithModelManagerExceptionInDebugMode()
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $this->setExpectedException('Sonata\AdminBundle\Exception\ModelManagerException');

        $modelManager->expects($this->once())
            ->method('batchDelete')
            ->will($this->returnCallback(function () {
                throw new ModelManagerException();
            }));

        $this->admin->expects($this->once())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $this->kernel->expects($this->once())
            ->method('isDebug')
            ->will($this->returnValue(true));

        $this->controller->batchActionDelete($this->getMock('Sonata\AdminBundle\Datagrid\ProxyQueryInterface'));
    }

    public function testShowActionNotFoundException()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->showAction(null, $this->request);
    }

    public function testShowActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(new \stdClass()));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('show'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->showAction(null, $this->request);
    }

    public function testPreShow()
    {
        $object = new \stdClass();
        $object->foo = 123456;

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('show'))
            ->will($this->returnValue(true));

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->showAction(null, $this->request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame('preShow called: 123456', $response->getContent());
    }

    public function testShowAction()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('show'))
            ->will($this->returnValue(true));

        $show = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionCollection');

        $this->admin->expects($this->once())
            ->method('getShow')
            ->will($this->returnValue($show));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->showAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('show', $this->parameters['action']);
        $this->assertInstanceOf('Sonata\AdminBundle\Admin\FieldDescriptionCollection', $this->parameters['elements']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:show.html.twig', $this->template);
    }

    /**
     * @dataProvider getRedirectToTests
     */
    public function testRedirectTo($expected, $route, $queryParams, $hasActiveSubclass)
    {
        $this->admin->expects($this->any())
            ->method('hasActiveSubclass')
            ->will($this->returnValue($hasActiveSubclass));

        $object = new \stdClass();

        foreach ($queryParams as $key => $value) {
            $this->request->query->set($key, $value);
        }

        $this->admin->expects($this->any())
            ->method('hasRoute')
            ->with($this->equalTo($route))
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('isGranted')
            ->with($this->equalTo(strtoupper($route)))
            ->will($this->returnValue(true));

        $response = $this->protectedTestedMethods['redirectTo']->invoke($this->controller, $object, $this->request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame($expected, $response->getTargetUrl());
    }

    public function testRedirectToWithObject()
    {
        $this->admin->expects($this->any())
            ->method('hasActiveSubclass')
            ->will($this->returnValue(false));

        $object = new \stdClass();

        $this->admin->expects($this->at(0))
            ->method('hasRoute')
            ->with($this->equalTo('edit'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('isGranted')
            ->with($this->equalTo(strtoupper('edit')), $object)
            ->will($this->returnValue(false));

        $response = $this->protectedTestedMethods['redirectTo']->invoke($this->controller, $object, $this->request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame('list', $response->getTargetUrl());
    }

    public function getRedirectToTests()
    {
        return array(
            array('stdClass_edit', 'edit', array(), false),
            array('list', 'list', array('btn_update_and_list' => true), false),
            array('list', 'list', array('btn_create_and_list' => true), false),
            array('create', 'create', array('btn_create_and_create' => true), false),
            array('create?subclass=foo', 'create', array('btn_create_and_create' => true, 'subclass' => 'foo'), true),
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

        $this->controller->deleteAction(1, $this->request);
    }

    public function testDeleteActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(new \stdClass()));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->deleteAction(1, $this->request);
    }

    public function testPreDelete()
    {
        $object = new \stdClass();
        $object->foo = 123456;

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->will($this->returnValue(true));

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->deleteAction(null, $this->request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame('preDelete called: 123456', $response->getContent());
    }

    public function testDeleteAction()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->will($this->returnValue(true));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->deleteAction(1, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('delete', $this->parameters['action']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertSame('csrf-token-123_sonata.delete', $this->parameters['csrf_token']);

        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:delete.html.twig', $this->template);
    }

    public function testDeleteActionNoCsrfToken()
    {
        $this->csrfProvider = null;

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->will($this->returnValue(true));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->deleteAction(1, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('delete', $this->parameters['action']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertSame(false, $this->parameters['csrf_token']);

        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:delete.html.twig', $this->template);
    }

    public function testDeleteActionAjaxSuccess1()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->will($this->returnValue(true));

        $this->request->setMethod('DELETE');

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(1, $this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame(json_encode(array('result' => 'ok')), $response->getContent());
        $this->assertSame(array(), $this->session->getFlashBag()->all());
    }

    public function testDeleteActionAjaxSuccess2()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');
        $this->request->request->set('_method', 'DELETE');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->deleteAction(1, $this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame(json_encode(array('result' => 'ok')), $response->getContent());
        $this->assertSame(array(), $this->session->getFlashBag()->all());
    }

    public function testDeleteActionAjaxError()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

        $this->assertLoggerLogsModelManagerException($this->admin, 'delete');

        $this->request->setMethod('DELETE');

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->deleteAction(1, $this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame(json_encode(array('result' => 'error')), $response->getContent());
        $this->assertSame(array(), $this->session->getFlashBag()->all());
    }

    public function testDeleteActionWithModelManagerExceptionInDebugMode()
    {
        $this->setExpectedException('Sonata\AdminBundle\Exception\ModelManagerException');

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('delete')
            ->will($this->returnCallback(function () {
                throw new ModelManagerException();
            }));

        $this->kernel->expects($this->once())
            ->method('isDebug')
            ->will($this->returnValue(true));

        $this->request->setMethod('DELETE');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $this->controller->deleteAction(1, $this->request);
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
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->will($this->returnValue(true));

        $this->request->setMethod('DELETE');

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(1, $this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(array('flash_delete_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list', $response->getTargetUrl());
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
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_delete_success', array('%name%' => $expectedToStringValue), 'SonataAdminBundle');

        $this->request->setMethod('POST');
        $this->request->request->set('_method', 'DELETE');

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(1, $this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(array('flash_delete_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list', $response->getTargetUrl());
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
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_delete_success', array('%name%' => $expectedToStringValue), 'SonataAdminBundle');

        $this->request->setMethod('POST');
        $this->request->request->set('_method', 'DELETE');

        $response = $this->controller->deleteAction(1, $this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(array('flash_delete_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list', $response->getTargetUrl());
    }

    public function testDeleteActionWrongRequestMethod()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->will($this->returnValue(true));

        //without POST request parameter "_method" should not be used as real REST method
        $this->request->query->set('_method', 'DELETE');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->deleteAction(1, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('delete', $this->parameters['action']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertSame('csrf-token-123_sonata.delete', $this->parameters['csrf_token']);

        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:delete.html.twig', $this->template);
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
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_delete_error', array('%name%' => $expectedToStringValue), 'SonataAdminBundle');

        $this->assertLoggerLogsModelManagerException($this->admin, 'delete');

        $this->request->setMethod('DELETE');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(1, $this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(array('flash_delete_error'), $this->session->getFlashBag()->get('sonata_flash_error'));
        $this->assertSame('list', $response->getTargetUrl());
    }

    public function testDeleteActionInvalidCsrfToken()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');
        $this->request->request->set('_method', 'DELETE');
        $this->request->request->set('_sonata_csrf_token', 'CSRF-INVALID');

        try {
            $this->controller->deleteAction(1, $this->request);
        } catch (HttpException $e) {
            $this->assertSame('The csrf token is not valid, CSRF attack?', $e->getMessage());
            $this->assertSame(400, $e->getStatusCode());
        }
    }

    public function testEditActionNotFoundException()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->editAction(null, $this->request);
    }

    public function testEditActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(new \stdClass()));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->editAction(null, $this->request);
    }

    public function testPreEdit()
    {
        $object = new \stdClass();
        $object->foo = 123456;

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->will($this->returnValue(true));

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->editAction(null, $this->request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame('preEdit called: 123456', $response->getContent());
    }

    public function testEditAction()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
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

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->editAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('edit', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:edit.html.twig', $this->template);
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
            ->method('checkAccess')
            ->with($this->equalTo('edit'));

        $this->admin->expects($this->once())
            ->method('hasRoute')
            ->with($this->equalTo('edit'))
            ->will($this->returnValue(true));

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
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_edit_success', array('%name%' => $expectedToStringValue), 'SonataAdminBundle');

        $this->request->setMethod('POST');

        $response = $this->controller->editAction(null, $this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(array('flash_edit_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('stdClass_edit', $response->getTargetUrl());
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
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->will($this->returnValue(true));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

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

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->editAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('edit', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(array('sonata_flash_error' => array('flash_edit_error')), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:edit.html.twig', $this->template);
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
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->will($this->returnValue(true));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('getNormalizedIdentifier')
            ->with($this->equalTo($object))
            ->will($this->returnValue('foo_normalized'));

        $this->admin->expects($this->once())
            ->method('toString')
            ->will($this->returnValue('foo'));

        $this->request->setMethod('POST');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->editAction(null, $this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame(json_encode(array('result' => 'ok', 'objectId' => 'foo_normalized', 'objectName' => 'foo')), $response->getContent());
        $this->assertSame(array(), $this->session->getFlashBag()->all());
    }

    public function testEditActionAjaxError()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->will($this->returnValue(true));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->request->setMethod('POST');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $formView = $this->getMock('Symfony\Component\Form\FormView');

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->editAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::ajax_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('edit', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:edit.html.twig', $this->template);
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testEditActionWithModelManagerException($expectedToStringValue, $toStringValue)
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

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

        $this->expectTranslate('flash_edit_error', array('%name%' => $expectedToStringValue), 'SonataAdminBundle');

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));
        $this->request->setMethod('POST');

        $formView = $this->getMock('Symfony\Component\Form\FormView');

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertLoggerLogsModelManagerException($this->admin, 'update');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->editAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('edit', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(array('sonata_flash_error' => array('flash_edit_error')), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:edit.html.twig', $this->template);
    }

    public function testEditActionWithPreview()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
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
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');
        $this->request->request->set('btn_preview', 'Preview');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->editAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('edit', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:preview.html.twig', $this->template);
    }

    public function testEditActionWithLockException()
    {
        $object = new \stdClass();
        $class = get_class($object);

        $this->admin->expects($this->any())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->any())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue($class));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->any())
            ->method('isSubmitted')
            ->will($this->returnValue(true));
        $this->request->setMethod('POST');

        $this->admin->expects($this->any())
            ->method('update')
            ->will($this->throwException(new LockException()));

        $this->admin->expects($this->any())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($class));

        $formView = $this->getMock('Symfony\Component\Form\FormView');

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->expectTranslate('flash_lock_error', array(
            '%name%' => $class,
            '%link_start%' => '<a href="stdClass_edit">',
            '%link_end%' => '</a>',
        ), 'SonataAdminBundle');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->editAction(null, $this->request));
    }

    public function testCreateActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->createAction($this->request);
    }

    public function testPreCreate()
    {
        $object = new \stdClass();
        $object->foo = 123456;

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->will($this->returnValue($object));

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->createAction($this->request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame('preCreate called: 123456', $response->getContent());
    }

    public function testCreateAction()
    {
        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

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

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->createAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('create', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:edit.html.twig', $this->template);
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testCreateActionSuccess($expectedToStringValue, $toStringValue)
    {
        $object = new \stdClass();

        $this->admin->expects($this->exactly(2))
            ->method('checkAccess')
            ->will($this->returnCallback(function ($name, $objectIn = null) use ($object) {
                if ($name == 'edit') {
                    return true;
                }

                if ($name != 'create') {
                    return false;
                }

                if ($objectIn === null) {
                    return true;
                }

                return $objectIn === $object;
            }));

        $this->admin->expects($this->once())
            ->method('hasRoute')
            ->with($this->equalTo('edit'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('create')
            ->will($this->returnArgument(0));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_create_success', array('%name%' => $expectedToStringValue), 'SonataAdminBundle');

        $this->request->setMethod('POST');

        $response = $this->controller->createAction($this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(array('flash_create_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('stdClass_edit', $response->getTargetUrl());
    }

    public function testCreateActionAccessDenied2()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $object = new \stdClass();

        $this->admin->expects($this->any())
            ->method('checkAccess')
            ->will($this->returnCallback(function ($name, $object = null) {
                if ($name != 'create') {
                    throw new AccessDeniedException();
                }
                if ($object === null) {
                    return true;
                }

                throw new AccessDeniedException();
            }));

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->will($this->returnValue($object));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');

        $this->controller->createAction($this->request);
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testCreateActionError($expectedToStringValue, $toStringValue)
    {
        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

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
            ->method('isSubmitted')
            ->will($this->returnValue(true));

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

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->createAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('create', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(array('sonata_flash_error' => array('flash_create_error')), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:edit.html.twig', $this->template);
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testCreateActionWithModelManagerException($expectedToStringValue, $toStringValue)
    {
        $this->admin->expects($this->exactly(2))
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

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
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_create_error', array('%name%' => $expectedToStringValue), 'SonataAdminBundle');

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');

        $formView = $this->getMock('Symfony\Component\Form\FormView');

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertLoggerLogsModelManagerException($this->admin, 'create');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->createAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('create', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(array('sonata_flash_error' => array('flash_create_error')), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:edit.html.twig', $this->template);
    }

    public function testCreateActionAjaxSuccess()
    {
        $object = new \stdClass();

        $this->admin->expects($this->exactly(2))
            ->method('checkAccess')
            ->will($this->returnCallback(function ($name, $objectIn = null) use ($object) {
                if ($name != 'create') {
                    return false;
                }

                if ($objectIn === null) {
                    return true;
                }

                return $objectIn === $object;
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
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

        $this->admin->expects($this->once())
            ->method('getNormalizedIdentifier')
            ->with($this->equalTo($object))
            ->will($this->returnValue('foo_normalized'));

        $this->request->setMethod('POST');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->createAction($this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame(json_encode(array('result' => 'ok', 'objectId' => 'foo_normalized')), $response->getContent());
        $this->assertSame(array(), $this->session->getFlashBag()->all());
    }

    public function testCreateActionAjaxError()
    {
        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->will($this->returnValue($object));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->request->setMethod('POST');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $formView = $this->getMock('Symfony\Component\Form\FormView');

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->createAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::ajax_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('create', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:edit.html.twig', $this->template);
    }

    public function testCreateActionWithPreview()
    {
        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->will($this->returnValue($object));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

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
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');
        $this->request->request->set('btn_preview', 'Preview');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->createAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('create', $this->parameters['action']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:preview.html.twig', $this->template);
    }

    public function testExportActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('export'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->exportAction($this->request);
    }

    public function testExportActionWrongFormat()
    {
        $this->setExpectedException('RuntimeException', 'Export in format `csv` is not allowed for class: `Foo`. Allowed formats are: `json`');

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('export'))
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
            ->method('checkAccess')
            ->with($this->equalTo('export'))
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
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(array(), $this->session->getFlashBag()->all());
    }

    public function testHistoryActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->any())
            ->method('getObject')
            ->will($this->returnValue(new \StdClass()));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('history'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->historyAction(null, $this->request);
    }

    public function testHistoryActionNotFoundException()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->historyAction(null, $this->request);
    }

    public function testHistoryActionNoReader()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'unable to find the audit reader for class : Foo');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('history'))
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

        $this->controller->historyAction(null, $this->request);
    }

    public function testHistoryAction()
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('history'))
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

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->historyAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('history', $this->parameters['action']);
        $this->assertSame(array(), $this->parameters['revisions']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:history.html.twig', $this->template);
    }

    public function testAclActionAclNotEnabled()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'ACL are not enabled for this admin');

        $this->controller->aclAction(null, $this->request);
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

        $this->controller->aclAction(null, $this->request);
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
            ->method('checkAccess')
            ->with($this->equalTo('acl'), $this->equalTo($object))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->aclAction(null, $this->request);
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
            ->method('checkAccess')
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getSecurityInformation')
            ->will($this->returnValue(array()));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('getMaskBuilderClass')
            ->will($this->returnValue('\Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap'));

        $aclUsersForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $aclUsersForm->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($this->getMock('Symfony\Component\Form\FormView')));

        $aclRolesForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $aclRolesForm->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($this->getMock('Symfony\Component\Form\FormView')));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('createAclUsersForm')
            ->with($this->isInstanceOf('Sonata\AdminBundle\Util\AdminObjectAclData'))
            ->will($this->returnValue($aclUsersForm));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('createAclRolesForm')
            ->with($this->isInstanceOf('Sonata\AdminBundle\Util\AdminObjectAclData'))
            ->will($this->returnValue($aclRolesForm));

        $aclSecurityHandler = $this->getMockBuilder('Sonata\AdminBundle\Security\Handler\AclSecurityHandler')
            ->disableOriginalConstructor()
            ->getMock();

        $aclSecurityHandler->expects($this->any())
            ->method('getObjectPermissions')
            ->will($this->returnValue(array()));

        $this->admin->expects($this->any())
            ->method('getSecurityHandler')
            ->will($this->returnValue($aclSecurityHandler));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->aclAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('acl', $this->parameters['action']);
        $this->assertSame(array(), $this->parameters['permissions']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertInstanceOf('\ArrayIterator', $this->parameters['users']);
        $this->assertInstanceOf('\ArrayIterator', $this->parameters['roles']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['aclUsersForm']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['aclRolesForm']);

        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:acl.html.twig', $this->template);
    }

    public function testAclActionInvalidUpdate()
    {
        $this->request->query->set('id', 123);
        $this->request->request->set(AdminObjectAclManipulator::ACL_USERS_FORM_NAME, array());

        $this->admin->expects($this->once())
            ->method('isAclEnabled')
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->any())
            ->method('checkAccess')
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getSecurityInformation')
            ->will($this->returnValue(array()));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('getMaskBuilderClass')
            ->will($this->returnValue('\Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap'));

        $aclUsersForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $aclUsersForm->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $aclUsersForm->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($this->getMock('Symfony\Component\Form\FormView')));

        $aclRolesForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $aclRolesForm->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($this->getMock('Symfony\Component\Form\FormView')));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('createAclUsersForm')
            ->with($this->isInstanceOf('Sonata\AdminBundle\Util\AdminObjectAclData'))
            ->will($this->returnValue($aclUsersForm));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('createAclRolesForm')
            ->with($this->isInstanceOf('Sonata\AdminBundle\Util\AdminObjectAclData'))
            ->will($this->returnValue($aclRolesForm));

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

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->aclAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('acl', $this->parameters['action']);
        $this->assertSame(array(), $this->parameters['permissions']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertInstanceOf('\ArrayIterator', $this->parameters['users']);
        $this->assertInstanceOf('\ArrayIterator', $this->parameters['roles']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['aclUsersForm']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['aclRolesForm']);

        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:acl.html.twig', $this->template);
    }

    public function testAclActionSuccessfulUpdate()
    {
        $this->request->query->set('id', 123);
        $this->request->request->set(AdminObjectAclManipulator::ACL_ROLES_FORM_NAME, array());

        $this->admin->expects($this->once())
            ->method('isAclEnabled')
            ->will($this->returnValue(true));

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->any())
            ->method('checkAccess')
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getSecurityInformation')
            ->will($this->returnValue(array()));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('getMaskBuilderClass')
            ->will($this->returnValue('\Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap'));

        $aclUsersForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $aclUsersForm->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($this->getMock('Symfony\Component\Form\FormView')));

        $aclRolesForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $aclRolesForm->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($this->getMock('Symfony\Component\Form\FormView')));

        $aclRolesForm->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('createAclUsersForm')
            ->with($this->isInstanceOf('Sonata\AdminBundle\Util\AdminObjectAclData'))
            ->will($this->returnValue($aclUsersForm));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('createAclRolesForm')
            ->with($this->isInstanceOf('Sonata\AdminBundle\Util\AdminObjectAclData'))
            ->will($this->returnValue($aclRolesForm));

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

        $response = $this->controller->aclAction(null, $this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);

        $this->assertSame(array('flash_acl_edit_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('stdClass_acl', $response->getTargetUrl());
    }

    public function testHistoryViewRevisionActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->any())
            ->method('getObject')
            ->will($this->returnValue(new \StdClass()));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyViewRevision'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->historyViewRevisionAction(null, null, $this->request);
    }

    public function testHistoryViewRevisionActionNotFoundException()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'unable to find the object with id : 123');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->historyViewRevisionAction(null, null, $this->request);
    }

    public function testHistoryViewRevisionActionNoReader()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'unable to find the audit reader for class : Foo');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyViewRevision'))
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

        $this->controller->historyViewRevisionAction(null, null, $this->request);
    }

    public function testHistoryViewRevisionActionNotFoundRevision()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'unable to find the targeted object `123` from the revision `456` with classname : `Foo`');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyViewRevision'))
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

        $this->controller->historyViewRevisionAction(123, 456, $this->request);
    }

    public function testHistoryViewRevisionAction()
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyViewRevision'))
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

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->historyViewRevisionAction(123, 456, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('show', $this->parameters['action']);
        $this->assertSame($objectRevision, $this->parameters['object']);
        $this->assertSame($fieldDescriptionCollection, $this->parameters['elements']);

        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:show.html.twig', $this->template);
    }

    public function testHistoryCompareRevisionsActionAccessDenied()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->historyCompareRevisionsAction(null, null, null, $this->request);
    }

    public function testHistoryCompareRevisionsActionNotFoundException()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'unable to find the object with id : 123');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->historyCompareRevisionsAction(null, null, null, $this->request);
    }

    public function testHistoryCompareRevisionsActionNoReader()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'unable to find the audit reader for class : Foo');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'))
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

        $this->controller->historyCompareRevisionsAction(null, null, null, $this->request);
    }

    public function testHistoryCompareRevisionsActionNotFoundBaseRevision()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'unable to find the targeted object `123` from the revision `456` with classname : `Foo`');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'))
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

        $this->controller->historyCompareRevisionsAction(123, 456, 789, $this->request);
    }

    public function testHistoryCompareRevisionsActionNotFoundCompareRevision()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'unable to find the targeted object `123` from the revision `789` with classname : `Foo`');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'))
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

        $this->controller->historyCompareRevisionsAction(123, 456, 789, $this->request);
    }

    public function testHistoryCompareRevisionsActionAction()
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'))
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

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->historyCompareRevisionsAction(123, 456, 789, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('show', $this->parameters['action']);
        $this->assertSame($objectRevision, $this->parameters['object']);
        $this->assertSame($compareObjectRevision, $this->parameters['object_compare']);
        $this->assertSame($fieldDescriptionCollection, $this->parameters['elements']);

        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:show_compare.html.twig', $this->template);
    }

    public function testBatchActionWrongMethod()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'Invalid request type "GET", POST expected');

        $this->controller->batchAction($this->request);
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionActionNotDefined()
    {
        $this->setExpectedException('RuntimeException', 'The `foo` batch action is not defined');

        $batchActions = array();

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $this->request->setMethod('POST');
        $this->request->request->set('data', json_encode(array('action' => 'foo', 'idx' => array('123', '456'), 'all_elements' => false)));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $this->controller->batchAction($this->request);
    }

    public function testBatchActionActionInvalidCsrfToken()
    {
        $this->request->setMethod('POST');
        $this->request->request->set('data', json_encode(array('action' => 'foo', 'idx' => array('123', '456'), 'all_elements' => false)));
        $this->request->request->set('_sonata_csrf_token', 'CSRF-INVALID');

        try {
            $this->controller->batchAction($this->request);
        } catch (HttpException $e) {
            $this->assertSame('The csrf token is not valid, CSRF attack?', $e->getMessage());
            $this->assertSame(400, $e->getStatusCode());
        }
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionMethodNotExist()
    {
        $this->setExpectedException('RuntimeException', 'A `Sonata\AdminBundle\Controller\CRUDController::batchActionFoo` method must be callable');

        $batchActions = array('foo' => array('label' => 'Foo Bar', 'ask_confirmation' => false));

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $datagrid = $this->getMock('\Sonata\AdminBundle\Datagrid\DatagridInterface');
        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $this->request->setMethod('POST');
        $this->request->request->set('data', json_encode(array('action' => 'foo', 'idx' => array('123', '456'), 'all_elements' => false)));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $this->controller->batchAction($this->request);
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionWithoutConfirmation()
    {
        $batchActions = array('delete' => array('label' => 'Foo Bar', 'ask_confirmation' => false));

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
            ->method('checkAccess')
            ->with($this->equalTo('batchDelete'))
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
        $this->request->request->set('data', json_encode(array('action' => 'delete', 'idx' => array('123', '456'), 'all_elements' => false)));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction($this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
        $this->assertSame(array('flash_batch_delete_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list?', $result->getTargetUrl());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionWithoutConfirmation2()
    {
        $batchActions = array('delete' => array('label' => 'Foo Bar', 'ask_confirmation' => false));

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
            ->method('checkAccess')
            ->with($this->equalTo('batchDelete'))
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

        $result = $this->controller->batchAction($this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
        $this->assertSame(array('flash_batch_delete_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list?', $result->getTargetUrl());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionWithConfirmation()
    {
        $batchActions = array('delete' => array('label' => 'Foo Bar', 'translation_domain' => 'FooBarBaz', 'ask_confirmation' => true));

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $data = array('action' => 'delete', 'idx' => array('123', '456'), 'all_elements' => false);

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

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->controller->batchAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('SonataAdminBundle::standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('list', $this->parameters['action']);
        $this->assertSame($datagrid, $this->parameters['datagrid']);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->parameters['form']);
        $this->assertSame($data, $this->parameters['data']);
        $this->assertSame('csrf-token-123_sonata.batch', $this->parameters['csrf_token']);
        $this->assertSame('Foo Bar', $this->parameters['action_label']);

        $this->assertSame(array(), $this->session->getFlashBag()->all());
        $this->assertSame('SonataAdminBundle:CRUD:batch_confirmation.html.twig', $this->template);
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionNonRelevantAction()
    {
        $controller = new BatchAdminController();
        $controller->setContainer($this->container);

        $batchActions = array('foo' => array('label' => 'Foo Bar', 'ask_confirmation' => false));

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

        $result = $controller->batchAction($this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
        $this->assertSame(array('flash_batch_empty'), $this->session->getFlashBag()->get('sonata_flash_info'));
        $this->assertSame('list?', $result->getTargetUrl());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionNonRelevantAction2()
    {
        $controller = new BatchAdminController();
        $controller->setContainer($this->container);

        $batchActions = array('foo' => array('label' => 'Foo Bar', 'ask_confirmation' => false));

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

        $result = $controller->batchAction($this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
        $this->assertSame(array('flash_foo_error'), $this->session->getFlashBag()->get('sonata_flash_info'));
        $this->assertSame('list?', $result->getTargetUrl());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionNoItems()
    {
        $batchActions = array('delete' => array('label' => 'Foo Bar', 'ask_confirmation' => true));

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

        $result = $this->controller->batchAction($this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
        $this->assertSame(array('flash_batch_empty'), $this->session->getFlashBag()->get('sonata_flash_info'));
        $this->assertSame('list?', $result->getTargetUrl());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionNoItemsEmptyQuery()
    {
        $controller = new BatchAdminController();
        $controller->setContainer($this->container);

        $batchActions = array('bar' => array('label' => 'Foo Bar', 'ask_confirmation' => false));

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

        $result = $controller->batchAction($this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $result);
        $this->assertSame('batchActionBar executed', $result->getContent());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionWithRequesData()
    {
        $batchActions = array('delete' => array('label' => 'Foo Bar', 'ask_confirmation' => false));

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
            ->method('checkAccess')
            ->with($this->equalTo('batchDelete'))
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
        $this->request->request->set('data', json_encode(array('action' => 'delete', 'idx' => array('123', '456'), 'all_elements' => false)));
        $this->request->request->set('foo', 'bar');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction($this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
        $this->assertSame(array('flash_batch_delete_success'), $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list?', $result->getTargetUrl());
        $this->assertSame('bar', $this->request->request->get('foo'));
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

    private function assertLoggerLogsModelManagerException($subject, $method)
    {
        $exception = new ModelManagerException(
            $message = 'message',
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
                'previous_exception_message' => $previousExceptionMessage,
            ));
    }

    private function expectTranslate($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $this->translator->expects($this->once())
            ->method('trans')
            ->with($this->equalTo($id), $this->equalTo($parameters), $this->equalTo($domain), $this->equalTo($locale))
            ->will($this->returnValue($id));
    }
}
