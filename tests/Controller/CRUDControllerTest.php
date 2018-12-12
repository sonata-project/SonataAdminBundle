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
use Exporter\Source\SourceIteratorInterface;
use Exporter\Writer\JsonWriter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Export\Exporter as SonataExporter;
use Sonata\AdminBundle\Model\AuditManager;
use Sonata\AdminBundle\Model\AuditReaderInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandler;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\AdminBundle\Tests\Fixtures\Admin\PostAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Controller\BatchAdminController;
use Sonata\AdminBundle\Tests\Fixtures\Controller\PreCRUDController;
use Sonata\AdminBundle\Util\AdminObjectAclData;
use Sonata\AdminBundle\Util\AdminObjectAclManipulator;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Test for CRUDController.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 *
 * @group legacy
 */
class CRUDControllerTest extends TestCase
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
     * @var AbstractAdmin
     */
    private $admin;

    /**
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;

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
     * @var AuditManager
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
     * @var CsrfTokenManagerInterface
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
        $this->container = $this->createMock(ContainerInterface::class);

        $this->request = new Request();
        $this->pool = new Pool($this->container, 'title', 'logo.png');
        $this->pool->setAdminServiceIds(['foo.admin']);
        $this->request->attributes->set('_sonata_admin', 'foo.admin');
        $this->admin = $this->getMockBuilder(AbstractAdmin::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->parameters = [];
        $this->template = '';

        $this->templateRegistry = $this->prophesize(TemplateRegistryInterface::class);

        $templating = $this->getMockBuilder(DelegatingEngine::class)
            ->setConstructorArgs([$this->container, []])
            ->getMock();

        $templatingRenderReturnCallback = $this->returnCallback(function (
            $view,
            array $parameters = [],
            Response $response = null
        ) {
            $this->template = $view;

            if (null === $response) {
                $response = new Response();
            }

            $this->parameters = $parameters;

            return $response;
        });

        // SF < 3.3.10 BC
        $templating->expects($this->any())
            ->method('renderResponse')
            ->will($templatingRenderReturnCallback);

        $templating->expects($this->any())
            ->method('render')
            ->will($templatingRenderReturnCallback);

        $this->session = new Session(new MockArraySessionStorage());

        $twig = $this->getMockBuilder(\Twig_Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twig->expects($this->any())
            ->method('getExtension')
            ->will($this->returnCallback(function ($name) {
                switch ($name) {
                    case FormExtension::class:
                        return new FormExtension($this->createMock(TwigRenderer::class));
                }
            }));

        $twig->expects($this->any())
            ->method('getRuntime')
            ->will($this->returnCallback(function ($name) {
                switch ($name) {
                    case TwigRenderer::class:
                        return $this->createMock(TwigRenderer::class);
                    case FormRenderer::class:
                        return $this->createMock(FormRenderer::class);
                }
            }));

        // NEXT_MAJOR : require sonata/exporter ^1.7 and remove conditional
        if (class_exists(Exporter::class)) {
            $exporter = new Exporter([new JsonWriter('/tmp/sonataadmin/export.json')]);
        } else {
            $exporter = $this->createMock(SonataExporter::class);

            $exporter->expects($this->any())
                ->method('getResponse')
                ->will($this->returnValue(new StreamedResponse()));
        }

        $this->auditManager = $this->getMockBuilder(AuditManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->adminObjectAclManipulator = $this->getMockBuilder(AdminObjectAclManipulator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->csrfProvider = $this->getMockBuilder(CsrfTokenManagerInterface::class)
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

        $this->logger = $this->createMock(LoggerInterface::class);

        $requestStack = new RequestStack();
        $requestStack->push($this->request);

        $this->kernel = $this->createMock(KernelInterface::class);

        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use (
                $templating,
                $twig,
                $exporter,
                $requestStack
            ) {
                switch ($id) {
                    case 'sonata.admin.pool':
                        return $this->pool;
                    case 'request_stack':
                        return $requestStack;
                    case 'foo.admin':
                        return $this->admin;
                    case 'foo.admin.template_registry':
                        return $this->templateRegistry->reveal();
                    case 'templating':
                        return $templating;
                    case 'twig':
                        return $twig;
                    case 'session':
                        return $this->session;
                    case 'sonata.admin.exporter':
                        return $exporter;
                    case 'sonata.admin.audit.manager':
                        return $this->auditManager;
                    case 'sonata.admin.object.manipulator.acl.admin':
                        return $this->adminObjectAclManipulator;
                    case 'security.csrf.token_manager':
                        return $this->csrfProvider;
                    case 'logger':
                        return $this->logger;
                    case 'kernel':
                        return $this->kernel;
                    case 'translator':
                        return $this->translator;
                }
            }));

        $this->container->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(function ($id) {
                if ('security.csrf.token_manager' == $id && null !== $this->getCsrfProvider()) {
                    return true;
                }

                if ('logger' == $id) {
                    return true;
                }

                if ('session' == $id) {
                    return true;
                }

                if ('templating' == $id) {
                    return true;
                }

                if ('translator' == $id) {
                    return true;
                }

                return false;
            }));

        $this->container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnCallback(function ($name) {
                switch ($name) {
                    case 'security.role_hierarchy.roles':
                       return ['ROLE_SUPER_ADMIN' => ['ROLE_USER', 'ROLE_SONATA_ADMIN', 'ROLE_ADMIN']];
                }
            }));

        $this->templateRegistry->getTemplate('ajax')->willReturn('@SonataAdmin/ajax_layout.html.twig');
        $this->templateRegistry->getTemplate('layout')->willReturn('@SonataAdmin/standard_layout.html.twig');
        $this->templateRegistry->getTemplate('show')->willReturn('@SonataAdmin/CRUD/show.html.twig');
        $this->templateRegistry->getTemplate('show_compare')->willReturn('@SonataAdmin/CRUD/show_compare.html.twig');
        $this->templateRegistry->getTemplate('edit')->willReturn('@SonataAdmin/CRUD/edit.html.twig');
        $this->templateRegistry->getTemplate('dashboard')->willReturn('@SonataAdmin/Core/dashboard.html.twig');
        $this->templateRegistry->getTemplate('search')->willReturn('@SonataAdmin/Core/search.html.twig');
        $this->templateRegistry->getTemplate('list')->willReturn('@SonataAdmin/CRUD/list.html.twig');
        $this->templateRegistry->getTemplate('preview')->willReturn('@SonataAdmin/CRUD/preview.html.twig');
        $this->templateRegistry->getTemplate('history')->willReturn('@SonataAdmin/CRUD/history.html.twig');
        $this->templateRegistry->getTemplate('acl')->willReturn('@SonataAdmin/CRUD/acl.html.twig');
        $this->templateRegistry->getTemplate('delete')->willReturn('@SonataAdmin/CRUD/delete.html.twig');
        $this->templateRegistry->getTemplate('batch')->willReturn('@SonataAdmin/CRUD/list__batch.html.twig');
        $this->templateRegistry->getTemplate('batch_confirmation')->willReturn('@SonataAdmin/CRUD/batch_confirmation.html.twig');

        // NEXT_MAJOR: Remove this call
        $this->admin->method('getTemplate')->willReturnMap([
            ['ajax', '@SonataAdmin/ajax_layout.html.twig'],
            ['layout', '@SonataAdmin/standard_layout.html.twig'],
            ['show', '@SonataAdmin/CRUD/show.html.twig'],
            ['show_compare', '@SonataAdmin/CRUD/show_compare.html.twig'],
            ['edit', '@SonataAdmin/CRUD/edit.html.twig'],
            ['dashboard', '@SonataAdmin/Core/dashboard.html.twig'],
            ['search', '@SonataAdmin/Core/search.html.twig'],
            ['list', '@SonataAdmin/CRUD/list.html.twig'],
            ['preview', '@SonataAdmin/CRUD/preview.html.twig'],
            ['history', '@SonataAdmin/CRUD/history.html.twig'],
            ['acl', '@SonataAdmin/CRUD/acl.html.twig'],
            ['delete', '@SonataAdmin/CRUD/delete.html.twig'],
            ['batch', '@SonataAdmin/CRUD/list__batch.html.twig'],
            ['batch_confirmation', '@SonataAdmin/CRUD/batch_confirmation.html.twig'],
        ]);

        $this->admin->expects($this->any())
            ->method('getIdParameter')
            ->will($this->returnValue('id'));

        $this->admin->expects($this->any())
            ->method('getAccessMapping')
            ->will($this->returnValue([]));

        $this->admin->expects($this->any())
            ->method('generateUrl')
            ->will(
                $this->returnCallback(
                    function ($name, array $parameters = [], $absolute = false) {
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
                    function ($name, $object, array $parameters = [], $absolute = false) {
                        $result = \get_class($object).'_'.$name;
                        if (!empty($parameters)) {
                            $result .= '?'.http_build_query($parameters);
                        }

                        return $result;
                    }
                )
            );

        $this->admin->expects($this->any())
            ->method('getCode')
            ->willReturn('foo.admin');

        $this->controller = new CRUDController();
        $this->controller->setContainer($this->container);

        // Make some methods public to test them
        $testedMethods = [
            'renderJson',
            'isXmlHttpRequest',
            'configure',
            'getBaseTemplate',
            'redirectTo',
            'addFlash',
        ];
        foreach ($testedMethods as $testedMethod) {
            // NEXT_MAJOR: Remove this check and only use CRUDController
            if (method_exists(CRUDController::class, $testedMethod)) {
                $method = new \ReflectionMethod(CRUDController::class, $testedMethod);
            } else {
                $method = new \ReflectionMethod(Controller::class, $testedMethod);
            }

            $method->setAccessible(true);
            $this->protectedTestedMethods[$testedMethod] = $method;
        }
    }

    public function testRenderJson1()
    {
        $data = ['example' => '123', 'foo' => 'bar'];

        $this->request->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data, 200, [], $this->request);

        $this->assertSame($response->headers->get('Content-Type'), 'application/json');
        $this->assertSame(json_encode($data), $response->getContent());
    }

    public function testRenderJson2()
    {
        $data = ['example' => '123', 'foo' => 'bar'];

        $this->request->headers->set('Content-Type', 'multipart/form-data');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data, 200, [], $this->request);

        $this->assertSame($response->headers->get('Content-Type'), 'application/json');
        $this->assertSame(json_encode($data), $response->getContent());
    }

    public function testRenderJsonAjax()
    {
        $data = ['example' => '123', 'foo' => 'bar'];

        $this->request->attributes->set('_xml_http_request', true);
        $this->request->headers->set('Content-Type', 'multipart/form-data');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data, 200, [], $this->request);

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

        $adminParent = $this->getMockBuilder(AbstractAdmin::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->admin->expects($this->once())
            ->method('getParent')
            ->will($this->returnValue($adminParent));

        $this->request->query->set('uniqid', 123456);
        $this->protectedTestedMethods['configure']->invoke($this->controller);

        $this->assertSame(123456, $uniqueId);
        $this->assertAttributeInstanceOf(\get_class($adminParent), 'admin', $this->controller);
    }

    public function testConfigureWithException()
    {
        $this->expectException(
            \RuntimeException::class,
            'There is no `_sonata_admin` defined for the controller `Sonata\AdminBundle\Controller\CRUDController`'
        );

        $this->request->attributes->remove('_sonata_admin');
        $this->protectedTestedMethods['configure']->invoke($this->controller);
    }

    public function testConfigureWithException2()
    {
        $this->expectException(
            \InvalidArgumentException::class,
            'Found service "nonexistent.admin" is not a valid admin service'
        );

        $this->pool->setAdminServiceIds(['nonexistent.admin']);
        $this->request->attributes->set('_sonata_admin', 'nonexistent.admin');
        $this->protectedTestedMethods['configure']->invoke($this->controller);
    }

    public function testGetBaseTemplate()
    {
        $this->assertSame(
            '@SonataAdmin/standard_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller, $this->request)
        );

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->assertSame(
            '@SonataAdmin/ajax_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller, $this->request)
        );

        $this->request->headers->remove('X-Requested-With');
        $this->assertSame(
            '@SonataAdmin/standard_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller, $this->request)
        );

        $this->request->attributes->set('_xml_http_request', true);
        $this->assertSame(
            '@SonataAdmin/ajax_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller, $this->request)
        );
    }

    public function testRender()
    {
        $this->parameters = [];
        $this->assertInstanceOf(
            Response::class,
            $this->controller->renderWithExtraParams('@FooAdmin/foo.html.twig', [], null)
        );
        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);
        $this->assertSame('@FooAdmin/foo.html.twig', $this->template);
    }

    public function testRenderWithResponse()
    {
        $this->parameters = [];
        $response = $response = new Response();
        $response->headers->set('X-foo', 'bar');
        $responseResult = $this->controller->renderWithExtraParams('@FooAdmin/foo.html.twig', [], $response);

        $this->assertSame($response, $responseResult);
        $this->assertSame('bar', $responseResult->headers->get('X-foo'));
        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);
        $this->assertSame('@FooAdmin/foo.html.twig', $this->template);
    }

    public function testRenderCustomParams()
    {
        $this->parameters = [];
        $this->assertInstanceOf(
            Response::class,
            $this->controller->renderWithExtraParams(
                '@FooAdmin/foo.html.twig',
                ['foo' => 'bar'],
                null
            )
        );
        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);
        $this->assertSame('bar', $this->parameters['foo']);
        $this->assertSame('@FooAdmin/foo.html.twig', $this->template);
    }

    public function testRenderAjax()
    {
        $this->parameters = [];
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->assertInstanceOf(
            Response::class,
            $this->controller->renderWithExtraParams(
                '@FooAdmin/foo.html.twig',
                ['foo' => 'bar'],
                null
            )
        );
        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/ajax_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);
        $this->assertSame('bar', $this->parameters['foo']);
        $this->assertSame('@FooAdmin/foo.html.twig', $this->template);
    }

    public function testListActionAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

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
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('preList called', $response->getContent());
    }

    public function testListAction()
    {
        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects($this->any())
            ->method('hasRoute')
            ->with($this->equalTo('list'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('list'))
            ->will($this->returnValue(true));

        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($this->createMock(FormView::class)));

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $datagrid->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->parameters = [];
        $this->assertInstanceOf(Response::class, $this->controller->listAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('list', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertInstanceOf(DatagridInterface::class, $this->parameters['datagrid']);
        $this->assertSame('csrf-token-123_sonata.batch', $this->parameters['csrf_token']);
        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/list.html.twig', $this->template);
    }

    public function testBatchActionDeleteAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('batchDelete'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->batchActionDelete($this->createMock(ProxyQueryInterface::class));
    }

    public function testBatchActionDelete()
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('batchDelete'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $this->admin->expects($this->once())
            ->method('getFilterParameters')
            ->will($this->returnValue(['foo' => 'bar']));

        $this->expectTranslate('flash_batch_delete_success', [], 'SonataAdminBundle');

        $result = $this->controller->batchActionDelete($this->createMock(ProxyQueryInterface::class));

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list?filter%5Bfoo%5D=bar', $result->getTargetUrl());
    }

    public function testBatchActionDeleteWithModelManagerException()
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $this->assertLoggerLogsModelManagerException($modelManager, 'batchDelete');

        $this->admin->expects($this->once())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $this->admin->expects($this->once())
            ->method('getFilterParameters')
            ->will($this->returnValue(['foo' => 'bar']));

        $this->expectTranslate('flash_batch_delete_error', [], 'SonataAdminBundle');

        $result = $this->controller->batchActionDelete($this->createMock(ProxyQueryInterface::class));

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_delete_error'], $this->session->getFlashBag()->get('sonata_flash_error'));
        $this->assertSame('list?filter%5Bfoo%5D=bar', $result->getTargetUrl());
    }

    public function testBatchActionDeleteWithModelManagerExceptionInDebugMode()
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $this->expectException(ModelManagerException::class);

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

        $this->controller->batchActionDelete($this->createMock(ProxyQueryInterface::class));
    }

    public function testShowActionNotFoundException()
    {
        $this->expectException(NotFoundHttpException::class);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->showAction(null, $this->request);
    }

    public function testShowActionAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(new \stdClass()));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('show'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->showAction(null, $this->request);
    }

    /**
     * @group legacy
     * @expectedDeprecation Calling this method without implementing "configureShowFields" is not supported since 3.x and will no longer be possible in 4.0
     */
    public function testShowActionDeprecation()
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('show'))
            ->will($this->returnValue(true));

        $show = $this->createMock(FieldDescriptionCollection::class);

        $this->admin->expects($this->once())
            ->method('getShow')
            ->will($this->returnValue($show));

        $show->expects($this->once())
            ->method('getElements')
            ->willReturn([]);

        $show->expects($this->once())
            ->method('count')
            ->willReturn(0);

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
        $this->assertInstanceOf(Response::class, $response);
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

        $show = $this->createMock(FieldDescriptionCollection::class);

        $this->admin->expects($this->once())
            ->method('getShow')
            ->will($this->returnValue($show));

        $show->expects($this->once())
            ->method('getElements')
            ->willReturn(['field' => 'fielddata']);

        $this->assertInstanceOf(Response::class, $this->controller->showAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('show', $this->parameters['action']);
        $this->assertInstanceOf(FieldDescriptionCollection::class, $this->parameters['elements']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/show.html.twig', $this->template);
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
            ->method('hasAccess')
            ->with($this->equalTo($route))
            ->will($this->returnValue(true));

        $response = $this->protectedTestedMethods['redirectTo']->invoke($this->controller, $object, $this->request);
        $this->assertInstanceOf(RedirectResponse::class, $response);
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
            ->method('hasAccess')
            ->with($this->equalTo('edit'), $object)
            ->will($this->returnValue(false));

        $response = $this->protectedTestedMethods['redirectTo']->invoke($this->controller, $object, $this->request);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('list', $response->getTargetUrl());
    }

    public function getRedirectToTests()
    {
        return [
            ['stdClass_edit', 'edit', [], false],
            ['list', 'list', ['btn_update_and_list' => true], false],
            ['list', 'list', ['btn_create_and_list' => true], false],
            ['create', 'create', ['btn_create_and_create' => true], false],
            ['create?subclass=foo', 'create', ['btn_create_and_create' => true, 'subclass' => 'foo'], true],
        ];
    }

    public function testDeleteActionNotFoundException()
    {
        $this->expectException(NotFoundHttpException::class);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->deleteAction(1, $this->request);
    }

    public function testDeleteActionAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

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
        $this->assertInstanceOf(Response::class, $response);
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

        $this->assertInstanceOf(Response::class, $this->controller->deleteAction(1, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('delete', $this->parameters['action']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertSame('csrf-token-123_sonata.delete', $this->parameters['csrf_token']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/delete.html.twig', $this->template);
    }

    /**
     * @group legacy
     * @expectedDeprecation Accessing a child that isn't connected to a given parent is deprecated since 3.34 and won't be allowed in 4.0.
     */
    public function testDeleteActionChildDeprecation()
    {
        $object = new \stdClass();
        $object->parent = 'test';

        $object2 = new \stdClass();

        $admin = $this->createMock(PostAdmin::class);

        $admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object2));

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('getParent')
            ->will($this->returnValue($admin));

        $this->admin->expects($this->exactly(2))
            ->method('getParentAssociationMapping')
            ->will($this->returnValue('parent'));

        $this->controller->deleteAction(1, $this->request);
    }

    public function testDeleteActionNoParentMappings()
    {
        $object = new \stdClass();

        $admin = $this->createMock(PostAdmin::class);

        $admin->expects($this->never())
            ->method('getObject');

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('getParent')
            ->will($this->returnValue($admin));

        $this->admin->expects($this->once())
            ->method('getParentAssociationMapping')
            ->will($this->returnValue(false));

        $this->controller->deleteAction(1, $this->request);
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

        $this->assertInstanceOf(Response::class, $this->controller->deleteAction(1, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('delete', $this->parameters['action']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertFalse($this->parameters['csrf_token']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/delete.html.twig', $this->template);
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

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(json_encode(['result' => 'ok']), $response->getContent());
        $this->assertSame([], $this->session->getFlashBag()->all());
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

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(json_encode(['result' => 'ok']), $response->getContent());
        $this->assertSame([], $this->session->getFlashBag()->all());
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

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(json_encode(['result' => 'error']), $response->getContent());
        $this->assertSame([], $this->session->getFlashBag()->all());
    }

    public function testDeleteActionWithModelManagerExceptionInDebugMode()
    {
        $this->expectException(ModelManagerException::class);

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

        $this->expectTranslate('flash_delete_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->will($this->returnValue(true));

        $this->request->setMethod('DELETE');

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(1, $this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['flash_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
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

        $this->expectTranslate('flash_delete_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod('POST');
        $this->request->request->set('_method', 'DELETE');

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(1, $this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['flash_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
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

        $this->expectTranslate('flash_delete_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod('POST');
        $this->request->request->set('_method', 'DELETE');

        $response = $this->controller->deleteAction(1, $this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['flash_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
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

        $this->assertInstanceOf(Response::class, $this->controller->deleteAction(1, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('delete', $this->parameters['action']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertSame('csrf-token-123_sonata.delete', $this->parameters['csrf_token']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/delete.html.twig', $this->template);
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

        $this->expectTranslate('flash_delete_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->assertLoggerLogsModelManagerException($this->admin, 'delete');

        $this->request->setMethod('DELETE');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(1, $this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['flash_delete_error'], $this->session->getFlashBag()->get('sonata_flash_error'));
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
        $this->expectException(NotFoundHttpException::class);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->editAction(null, $this->request);
    }

    public function testEditActionRuntimeException()
    {
        $this->expectException(\RuntimeException::class);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(new \stdClass()));

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->will($this->returnValue(true));

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('all')
            ->willReturn([]);

        $this->controller->editAction(null, $this->request);
    }

    public function testEditActionAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

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
        $this->assertInstanceOf(Response::class, $response);
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

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $formView = $this->createMock(FormView::class);

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $form->expects($this->once())
            ->method('all')
            ->willReturn(['field' => 'fielddata']);

        $this->assertInstanceOf(Response::class, $this->controller->editAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('edit', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
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
            ->method('hasAccess')
            ->with($this->equalTo('edit'))
            ->will($this->returnValue(true));

        $form = $this->createMock(Form::class);

        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('all')
            ->willReturn(['field' => 'fielddata']);

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_edit_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod('POST');

        $response = $this->controller->editAction(null, $this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['flash_edit_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
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

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $form->expects($this->once())
            ->method('all')
            ->willReturn(['field' => 'fielddata']);

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_edit_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod('POST');

        $formView = $this->createMock(FormView::class);

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertInstanceOf(Response::class, $this->controller->editAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('edit', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(['sonata_flash_error' => ['flash_edit_error']], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
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

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($object));

        $form->expects($this->once())
            ->method('all')
            ->willReturn(['field' => 'fielddata']);

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

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(json_encode(['result' => 'ok', 'objectId' => 'foo_normalized', 'objectName' => 'foo']), $response->getContent());
        $this->assertSame([], $this->session->getFlashBag()->all());
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

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $form->expects($this->once())
            ->method('all')
            ->willReturn(['field' => 'fielddata']);

        $this->request->setMethod('POST');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $formView = $this->createMock(FormView::class);

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertInstanceOf(Response::class, $this->controller->editAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/ajax_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('edit', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
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

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($object));

        $form->expects($this->once())
            ->method('all')
            ->willReturn(['field' => 'fielddata']);

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_edit_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));
        $this->request->setMethod('POST');

        $formView = $this->createMock(FormView::class);

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertLoggerLogsModelManagerException($this->admin, 'update');
        $this->assertInstanceOf(Response::class, $this->controller->editAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('edit', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(['sonata_flash_error' => ['flash_edit_error']], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
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

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->admin->expects($this->once())
            ->method('supportsPreviewMode')
            ->will($this->returnValue(true));

        $formView = $this->createMock(FormView::class);

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('all')
            ->willReturn(['field' => 'fielddata']);

        $this->request->setMethod('POST');
        $this->request->request->set('btn_preview', 'Preview');

        $this->assertInstanceOf(Response::class, $this->controller->editAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('edit', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/preview.html.twig', $this->template);
    }

    public function testEditActionWithLockException()
    {
        $object = new \stdClass();
        $class = \get_class($object);

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

        $form = $this->createMock(Form::class);

        $form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($object));

        $form->expects($this->once())
            ->method('all')
            ->willReturn(['field' => 'fielddata']);

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

        $formView = $this->createMock(FormView::class);

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->expectTranslate('flash_lock_error', [
            '%name%' => $class,
            '%link_start%' => '<a href="stdClass_edit">',
            '%link_end%' => '</a>',
        ], 'SonataAdminBundle');

        $this->assertInstanceOf(Response::class, $this->controller->editAction(null, $this->request));
    }

    public function testCreateActionAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->createAction($this->request);
    }

    public function testCreateActionRuntimeException()
    {
        $this->expectException(\RuntimeException::class);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->will($this->returnValue(new \stdClass()));

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('all')
            ->willReturn([]);

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
        $this->assertInstanceOf(Response::class, $response);
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

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('all')
            ->willReturn(['field' => 'fielddata']);

        $formView = $this->createMock(FormView::class);

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertInstanceOf(Response::class, $this->controller->createAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('create', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
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
                if ('edit' == $name) {
                    return true;
                }

                if ('create' != $name) {
                    return false;
                }

                if (null === $objectIn) {
                    return true;
                }

                return $objectIn === $object;
            }));

        $this->admin->expects($this->once())
            ->method('hasRoute')
            ->with($this->equalTo('edit'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('hasAccess')
            ->with($this->equalTo('edit'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('create')
            ->will($this->returnArgument(0));

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('all')
            ->willReturn(['field' => 'fielddata']);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($object));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_create_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod('POST');

        $response = $this->controller->createAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['flash_create_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('stdClass_edit', $response->getTargetUrl());
    }

    public function testCreateActionAccessDenied2()
    {
        $this->expectException(AccessDeniedException::class);

        $object = new \stdClass();

        $this->admin->expects($this->any())
            ->method('checkAccess')
            ->will($this->returnCallback(function ($name, $object = null) {
                if ('create' != $name) {
                    throw new AccessDeniedException();
                }
                if (null === $object) {
                    return true;
                }

                throw new AccessDeniedException();
            }));

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->will($this->returnValue($object));

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('all')
            ->willReturn(['field' => 'fielddata']);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($object));

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

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('all')
            ->willReturn(['field' => 'fielddata']);

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

        $this->expectTranslate('flash_create_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod('POST');

        $formView = $this->createMock(FormView::class);

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertInstanceOf(Response::class, $this->controller->createAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('create', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(['sonata_flash_error' => ['flash_create_error']], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
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

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('all')
            ->willReturn(['field' => 'fielddata']);

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->will($this->returnValue($toStringValue));

        $this->expectTranslate('flash_create_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($object));

        $this->request->setMethod('POST');

        $formView = $this->createMock(FormView::class);

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertLoggerLogsModelManagerException($this->admin, 'create');

        $this->assertInstanceOf(Response::class, $this->controller->createAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('create', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(['sonata_flash_error' => ['flash_create_error']], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
    }

    public function testCreateActionAjaxSuccess()
    {
        $object = new \stdClass();

        $this->admin->expects($this->exactly(2))
            ->method('checkAccess')
            ->will($this->returnCallback(function ($name, $objectIn = null) use ($object) {
                if ('create' != $name) {
                    return false;
                }

                if (null === $objectIn) {
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

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('all')
            ->willReturn(['field' => 'fielddata']);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($object));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

        $this->admin->expects($this->once())
            ->method('getNormalizedIdentifier')
            ->with($this->equalTo($object))
            ->will($this->returnValue('foo_normalized'));

        $this->admin->expects($this->once())
            ->method('toString')
            ->will($this->returnValue('foo'));

        $this->request->setMethod('POST');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->createAction($this->request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(json_encode(['result' => 'ok', 'objectId' => 'foo_normalized', 'objectName' => 'foo']), $response->getContent());
        $this->assertSame([], $this->session->getFlashBag()->all());
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

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('all')
            ->willReturn(['field' => 'fielddata']);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->request->setMethod('POST');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $formView = $this->createMock(FormView::class);

        $form->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($formView));

        $this->assertInstanceOf(Response::class, $this->controller->createAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/ajax_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('create', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
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

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('all')
            ->willReturn(['field' => 'fielddata']);

        $this->admin->expects($this->once())
            ->method('supportsPreviewMode')
            ->will($this->returnValue(true));

        $formView = $this->createMock(FormView::class);

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

        $this->assertInstanceOf(Response::class, $this->controller->createAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('create', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/preview.html.twig', $this->template);
    }

    public function testExportActionAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('export'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->exportAction($this->request);
    }

    public function testExportActionWrongFormat()
    {
        $this->expectException(\RuntimeException::class, 'Export in format `csv` is not allowed for class: `Foo`. Allowed formats are: `json`');

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('export'))
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('getExportFormats')
            ->will($this->returnValue(['json']));

        $this->admin->expects($this->any())
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
            ->will($this->returnValue(['json']));

        $dataSourceIterator = $this->createMock(SourceIteratorInterface::class);

        $this->admin->expects($this->once())
            ->method('getDataSourceIterator')
            ->will($this->returnValue($dataSourceIterator));

        $this->request->query->set('format', 'json');

        $response = $this->controller->exportAction($this->request);
        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], $this->session->getFlashBag()->all());
    }

    public function testHistoryActionAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

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
        $this->expectException(NotFoundHttpException::class);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->historyAction(null, $this->request);
    }

    public function testHistoryActionNoReader()
    {
        $this->expectException(NotFoundHttpException::class, 'unable to find the audit reader for class : Foo');

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

        $reader = $this->createMock(AuditReaderInterface::class);

        $this->auditManager->expects($this->once())
            ->method('getReader')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue($reader));

        $reader->expects($this->once())
            ->method('findRevisions')
            ->with($this->equalTo('Foo'), $this->equalTo(123))
            ->will($this->returnValue([]));

        $this->assertInstanceOf(Response::class, $this->controller->historyAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('history', $this->parameters['action']);
        $this->assertSame([], $this->parameters['revisions']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/history.html.twig', $this->template);
    }

    public function testAclActionAclNotEnabled()
    {
        $this->expectException(NotFoundHttpException::class, 'ACL are not enabled for this admin');

        $this->controller->aclAction(null, $this->request);
    }

    public function testAclActionNotFoundException()
    {
        $this->expectException(NotFoundHttpException::class);

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
        $this->expectException(AccessDeniedException::class);

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
            ->will($this->returnValue([]));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('getMaskBuilderClass')
            ->will($this->returnValue(AdminPermissionMap::class));

        $aclUsersForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclUsersForm->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($this->createMock(FormView::class)));

        $aclRolesForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclRolesForm->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($this->createMock(FormView::class)));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('createAclUsersForm')
            ->with($this->isInstanceOf(AdminObjectAclData::class))
            ->will($this->returnValue($aclUsersForm));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('createAclRolesForm')
            ->with($this->isInstanceOf(AdminObjectAclData::class))
            ->will($this->returnValue($aclRolesForm));

        $aclSecurityHandler = $this->getMockBuilder(AclSecurityHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclSecurityHandler->expects($this->any())
            ->method('getObjectPermissions')
            ->will($this->returnValue([]));

        $this->admin->expects($this->any())
            ->method('getSecurityHandler')
            ->will($this->returnValue($aclSecurityHandler));

        $this->assertInstanceOf(Response::class, $this->controller->aclAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('acl', $this->parameters['action']);
        $this->assertSame([], $this->parameters['permissions']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertInstanceOf(\ArrayIterator::class, $this->parameters['users']);
        $this->assertInstanceOf(\ArrayIterator::class, $this->parameters['roles']);
        $this->assertInstanceOf(FormView::class, $this->parameters['aclUsersForm']);
        $this->assertInstanceOf(FormView::class, $this->parameters['aclRolesForm']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/acl.html.twig', $this->template);
    }

    public function testAclActionInvalidUpdate()
    {
        $this->request->query->set('id', 123);
        $this->request->request->set(AdminObjectAclManipulator::ACL_USERS_FORM_NAME, []);

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
            ->will($this->returnValue([]));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('getMaskBuilderClass')
            ->will($this->returnValue(AdminPermissionMap::class));

        $aclUsersForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclUsersForm->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $aclUsersForm->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($this->createMock(FormView::class)));

        $aclRolesForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclRolesForm->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($this->createMock(FormView::class)));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('createAclUsersForm')
            ->with($this->isInstanceOf(AdminObjectAclData::class))
            ->will($this->returnValue($aclUsersForm));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('createAclRolesForm')
            ->with($this->isInstanceOf(AdminObjectAclData::class))
            ->will($this->returnValue($aclRolesForm));

        $aclSecurityHandler = $this->getMockBuilder(AclSecurityHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclSecurityHandler->expects($this->any())
            ->method('getObjectPermissions')
            ->will($this->returnValue([]));

        $this->admin->expects($this->any())
            ->method('getSecurityHandler')
            ->will($this->returnValue($aclSecurityHandler));

        $this->request->setMethod('POST');

        $this->assertInstanceOf(Response::class, $this->controller->aclAction(null, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('acl', $this->parameters['action']);
        $this->assertSame([], $this->parameters['permissions']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertInstanceOf(\ArrayIterator::class, $this->parameters['users']);
        $this->assertInstanceOf(\ArrayIterator::class, $this->parameters['roles']);
        $this->assertInstanceOf(FormView::class, $this->parameters['aclUsersForm']);
        $this->assertInstanceOf(FormView::class, $this->parameters['aclRolesForm']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/acl.html.twig', $this->template);
    }

    public function testAclActionSuccessfulUpdate()
    {
        $this->request->query->set('id', 123);
        $this->request->request->set(AdminObjectAclManipulator::ACL_ROLES_FORM_NAME, []);

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
            ->will($this->returnValue([]));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('getMaskBuilderClass')
            ->will($this->returnValue(AdminPermissionMap::class));

        $aclUsersForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclUsersForm->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($this->createMock(FormView::class)));

        $aclRolesForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclRolesForm->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($this->createMock(FormView::class)));

        $aclRolesForm->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('createAclUsersForm')
            ->with($this->isInstanceOf(AdminObjectAclData::class))
            ->will($this->returnValue($aclUsersForm));

        $this->adminObjectAclManipulator->expects($this->once())
            ->method('createAclRolesForm')
            ->with($this->isInstanceOf(AdminObjectAclData::class))
            ->will($this->returnValue($aclRolesForm));

        $aclSecurityHandler = $this->getMockBuilder(AclSecurityHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclSecurityHandler->expects($this->any())
            ->method('getObjectPermissions')
            ->will($this->returnValue([]));

        $this->admin->expects($this->any())
            ->method('getSecurityHandler')
            ->will($this->returnValue($aclSecurityHandler));

        $this->expectTranslate('flash_acl_edit_success', [], 'SonataAdminBundle');

        $this->request->setMethod('POST');

        $response = $this->controller->aclAction(null, $this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertSame(['flash_acl_edit_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('stdClass_acl', $response->getTargetUrl());
    }

    public function testHistoryViewRevisionActionAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

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
        $this->expectException(NotFoundHttpException::class, 'unable to find the object with id: 123');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue(false));

        $this->controller->historyViewRevisionAction(null, null, $this->request);
    }

    public function testHistoryViewRevisionActionNoReader()
    {
        $this->expectException(NotFoundHttpException::class, 'unable to find the audit reader for class : Foo');

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
        $this->expectException(NotFoundHttpException::class, 'unable to find the targeted object `123` from the revision `456` with classname : `Foo`');

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

        $reader = $this->createMock(AuditReaderInterface::class);

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

        $reader = $this->createMock(AuditReaderInterface::class);

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

        $this->assertInstanceOf(Response::class, $this->controller->historyViewRevisionAction(123, 456, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('show', $this->parameters['action']);
        $this->assertSame($objectRevision, $this->parameters['object']);
        $this->assertSame($fieldDescriptionCollection, $this->parameters['elements']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/show.html.twig', $this->template);
    }

    public function testHistoryCompareRevisionsActionAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->historyCompareRevisionsAction(null, null, null, $this->request);
    }

    public function testHistoryCompareRevisionsActionNotFoundException()
    {
        $this->expectException(NotFoundHttpException::class, 'unable to find the object with id: 123');

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
        $this->expectException(NotFoundHttpException::class, 'unable to find the audit reader for class : Foo');

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
        $this->expectException(NotFoundHttpException::class, 'unable to find the targeted object `123` from the revision `456` with classname : `Foo`');

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

        $reader = $this->createMock(AuditReaderInterface::class);

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
        $this->expectException(NotFoundHttpException::class, 'unable to find the targeted object `123` from the revision `789` with classname : `Foo`');

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

        $reader = $this->createMock(AuditReaderInterface::class);

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

        $reader = $this->createMock(AuditReaderInterface::class);

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

        $this->assertInstanceOf(Response::class, $this->controller->historyCompareRevisionsAction(123, 456, 789, $this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('show', $this->parameters['action']);
        $this->assertSame($objectRevision, $this->parameters['object']);
        $this->assertSame($compareObjectRevision, $this->parameters['object_compare']);
        $this->assertSame($fieldDescriptionCollection, $this->parameters['elements']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/show_compare.html.twig', $this->template);
    }

    public function testBatchActionWrongMethod()
    {
        $this->expectException(NotFoundHttpException::class, 'Invalid request type "GET", POST expected');

        $this->controller->batchAction($this->request);
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionActionNotDefined()
    {
        $this->expectException(\RuntimeException::class, 'The `foo` batch action is not defined');

        $batchActions = [];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $this->request->setMethod('POST');
        $this->request->request->set('data', json_encode(['action' => 'foo', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $this->controller->batchAction($this->request);
    }

    public function testBatchActionActionInvalidCsrfToken()
    {
        $this->request->setMethod('POST');
        $this->request->request->set('data', json_encode(['action' => 'foo', 'idx' => ['123', '456'], 'all_elements' => false]));
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
        $this->expectException(\RuntimeException::class, 'A `Sonata\AdminBundle\Controller\CRUDController::batchActionFoo` method must be callable');

        $batchActions = ['foo' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $datagrid = $this->createMock(DatagridInterface::class);
        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $this->request->setMethod('POST');
        $this->request->request->set('data', json_encode(['action' => 'foo', 'idx' => ['123', '456'], 'all_elements' => false]));
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
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $modelManager = $this->createMock(ModelManagerInterface::class);

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
            ->with($this->equalTo('Foo'), $this->equalTo($query), $this->equalTo(['123', '456']))
            ->will($this->returnValue(true));

        $this->expectTranslate('flash_batch_delete_success', [], 'SonataAdminBundle');

        $this->request->setMethod('POST');
        $this->request->request->set('data', json_encode(['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list', $result->getTargetUrl());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionWithoutConfirmation2()
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $modelManager = $this->createMock(ModelManagerInterface::class);

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
            ->with($this->equalTo('Foo'), $this->equalTo($query), $this->equalTo(['123', '456']))
            ->will($this->returnValue(true));

        $this->expectTranslate('flash_batch_delete_success', [], 'SonataAdminBundle');

        $this->request->setMethod('POST');
        $this->request->request->set('action', 'delete');
        $this->request->request->set('idx', ['123', '456']);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list', $result->getTargetUrl());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionWithConfirmation()
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'translation_domain' => 'FooBarBaz', 'ask_confirmation' => true]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $data = ['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => false];

        $this->request->setMethod('POST');
        $this->request->request->set('data', json_encode($data));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($this->createMock(FormView::class)));

        $datagrid->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->assertInstanceOf(Response::class, $this->controller->batchAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('list', $this->parameters['action']);
        $this->assertSame($datagrid, $this->parameters['datagrid']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($data, $this->parameters['data']);
        $this->assertSame('csrf-token-123_sonata.batch', $this->parameters['csrf_token']);
        $this->assertSame('Foo Bar', $this->parameters['action_label']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/batch_confirmation.html.twig', $this->template);
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

        $batchActions = ['foo' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $this->expectTranslate('flash_batch_empty', [], 'SonataAdminBundle');

        $this->request->setMethod('POST');
        $this->request->request->set('action', 'foo');
        $this->request->request->set('idx', ['789']);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $controller->batchAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_empty'], $this->session->getFlashBag()->get('sonata_flash_info'));
        $this->assertSame('list', $result->getTargetUrl());
    }

    public function testBatchActionWithCustomConfirmationTemplate()
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => true, 'template' => 'custom_template.html.twig']];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $data = ['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => false];

        $this->request->setMethod('POST');
        $this->request->request->set('data', json_encode($data));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $form = $this->createMock(Form::class);

        $form->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($this->createMock(FormView::class)));

        $datagrid->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->controller->batchAction($this->request);

        $this->assertSame('custom_template.html.twig', $this->template);
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

        $batchActions = ['foo' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $this->expectTranslate('flash_foo_error', [], 'SonataAdminBundle');

        $this->request->setMethod('POST');
        $this->request->request->set('action', 'foo');
        $this->request->request->set('idx', ['999']);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $controller->batchAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_foo_error'], $this->session->getFlashBag()->get('sonata_flash_info'));
        $this->assertSame('list', $result->getTargetUrl());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionNoItems()
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => true]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $this->expectTranslate('flash_batch_empty', [], 'SonataAdminBundle');

        $this->request->setMethod('POST');
        $this->request->request->set('action', 'delete');
        $this->request->request->set('idx', []);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_empty'], $this->session->getFlashBag()->get('sonata_flash_info'));
        $this->assertSame('list', $result->getTargetUrl());
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

        $batchActions = ['bar' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Foo'));

        $this->request->setMethod('POST');
        $this->request->request->set('action', 'bar');
        $this->request->request->set('idx', []);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $this->expectTranslate('flash_batch_no_elements_processed', [], 'SonataAdminBundle');
        $result = $controller->batchAction($this->request);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertRegExp('/Redirecting to list/', $result->getContent());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionWithRequesData()
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->will($this->returnValue($batchActions));

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $modelManager = $this->createMock(ModelManagerInterface::class);

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
            ->with($this->equalTo('Foo'), $this->equalTo($query), $this->equalTo(['123', '456']))
            ->will($this->returnValue(true));

        $this->expectTranslate('flash_batch_delete_success', [], 'SonataAdminBundle');

        $this->request->setMethod('POST');
        $this->request->request->set('data', json_encode(['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('foo', 'bar');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list', $result->getTargetUrl());
        $this->assertSame('bar', $this->request->request->get('foo'));
    }

    public function testItThrowsWhenCallingAnUndefinedMethod()
    {
        $this->expectException(
            \LogicException::class
        );
        $this->expectExceptionMessage(
            'Call to undefined method Sonata\AdminBundle\Controller\CRUDController::doesNotExist'
        );
        $this->controller->doesNotExist();
    }

    /**
     * @expectedDeprecation Method Sonata\AdminBundle\Controller\CRUDController::render has been renamed to Sonata\AdminBundle\Controller\CRUDController::renderWithExtraParams.
     */
    public function testRenderIsDeprecated()
    {
        $this->controller->render('toto.html.twig');
    }

    public function getCsrfProvider()
    {
        return $this->csrfProvider;
    }

    public function getToStringValues()
    {
        return [
            ['', ''],
            ['Foo', 'Foo'],
            ['&lt;a href=&quot;http://foo&quot;&gt;Bar&lt;/a&gt;', '<a href="http://foo">Bar</a>'],
            ['&lt;&gt;&amp;&quot;&#039;abcdefghijklmnopqrstuvwxyz*-+.,?_()[]\/', '<>&"\'abcdefghijklmnopqrstuvwxyz*-+.,?_()[]\/'],
        ];
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
            ->with($message, [
                'exception' => $exception,
                'previous_exception_message' => $previousExceptionMessage,
            ]);
    }

    private function expectTranslate($id, array $parameters = [], $domain = null, $locale = null)
    {
        $this->translator->expects($this->once())
            ->method('trans')
            ->with($this->equalTo($id), $this->equalTo($parameters), $this->equalTo($domain), $this->equalTo($locale))
            ->will($this->returnValue($id));
    }
}
