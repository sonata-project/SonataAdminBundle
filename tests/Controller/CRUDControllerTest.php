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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilder;
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
use Sonata\AdminBundle\Tests\Fixtures\Util\DummyDomainObject;
use Sonata\AdminBundle\Util\AdminObjectAclManipulator;
use Sonata\Exporter\Exporter;
use Sonata\Exporter\Source\SourceIteratorInterface;
use Sonata\Exporter\Writer\JsonWriter;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\JsonResponse;
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
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Test for CRUDController.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 *
 * @group legacy
 */
class CRUDControllerTest extends TestCase
{
    use ExpectDeprecationTrait;

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
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var bool
     */
    private $httpMethodParameterOverride = false;

    /**
     * @var Stub&FormFactoryInterface
     */
    private $formFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->httpMethodParameterOverride = Request::getHttpMethodParameterOverride();
        $this->container = new Container();
        $this->request = new Request();
        $this->pool = new Pool($this->container, ['foo.admin']);
        $this->request->attributes->set('_sonata_admin', 'foo.admin');
        $this->admin = $this->getMockBuilder(AbstractAdmin::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->parameters = [];
        $this->template = '';

        $this->templateRegistry = $this->createStub(TemplateRegistryInterface::class);

        $templating = $this->createMock(DelegatingEngine::class);

        $templatingRenderReturnCallback = $this->returnCallback(function (
            $view,
            array $parameters = [],
            ?Response $response = null
        ) {
            $this->template = $view;

            if (null === $response) {
                $response = new Response();
            }

            $this->parameters = $parameters;

            return $response;
        });

        $templating
            ->method('render')
            ->will($templatingRenderReturnCallback);

        $this->session = new Session(new MockArraySessionStorage());

        $twig = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twig
            ->method('getRuntime')
            ->willReturn($this->createMock(FormRenderer::class));

        // NEXT_MAJOR : require sonata/exporter ^1.7 and remove conditional
        if (class_exists(Exporter::class)) {
            $exporter = new Exporter([new JsonWriter('/tmp/sonataadmin/export.json')]);
        } else {
            $exporter = $this->createMock(SonataExporter::class);

            $exporter
                ->method('getResponse')
                ->willReturn(new StreamedResponse());
        }

        $this->auditManager = $this->getMockBuilder(AuditManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formFactory = $this->createStub(FormFactoryInterface::class);

        $this->adminObjectAclManipulator = new AdminObjectAclManipulator($this->formFactory, AdminPermissionMap::class);

        $this->csrfProvider = $this->getMockBuilder(CsrfTokenManagerInterface::class)
            ->getMock();

        $this->csrfProvider
            ->method('getToken')
            ->willReturnCallback(static function (string $intention): CsrfToken {
                return new CsrfToken($intention, sprintf('csrf-token-123_%s', $intention));
            });

        $this->csrfProvider
            ->method('isTokenValid')
            ->willReturnCallback(static function (CsrfToken $token): bool {
                return $token->getValue() === sprintf('csrf-token-123_%s', $token->getId());
            });

        $this->logger = $this->createMock(LoggerInterface::class);

        $requestStack = new RequestStack();
        $requestStack->push($this->request);

        $this->kernel = $this->createMock(KernelInterface::class);

        $this->container->set('sonata.admin.pool', $this->pool);
        $this->container->set('request_stack', $requestStack);
        $this->container->set('foo.admin', $this->admin);
        $this->container->set('foo.admin.template_registry', $this->templateRegistry);
        $this->container->set('templating', $templating);
        $this->container->set('twig', $twig);
        $this->container->set('session', $this->session);
        $this->container->set('sonata.admin.exporter', $exporter);
        $this->container->set('sonata.admin.audit.manager', $this->auditManager);
        $this->container->set('sonata.admin.object.manipulator.acl.admin', $this->adminObjectAclManipulator);
        $this->container->set('security.csrf.token_manager', $this->csrfProvider);
        $this->container->set('logger', $this->logger);
        $this->container->set('kernel', $this->kernel);
        $this->container->set('translator', $this->translator);
        $this->container->set('sonata.admin.breadcrumbs_builder', new BreadcrumbsBuilder([]));

        $this->container->setParameter(
            'security.role_hierarchy.roles',
            ['ROLE_SUPER_ADMIN' => ['ROLE_USER', 'ROLE_SONATA_ADMIN', 'ROLE_ADMIN']]
        );
        $this->container->setParameter('sonata.admin.security.acl_user_manager', null);

        $this->templateRegistry->method('getTemplate')->willReturnMap([
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

        $this->admin
            ->method('getIdParameter')
            ->willReturn('id');

        $this->admin
            ->method('getAccessMapping')
            ->willReturn([]);

        $this->admin
            ->method('generateUrl')
            ->willReturnCallback(
                static function ($name, array $parameters = []) {
                    $result = $name;
                    if (!empty($parameters)) {
                        $result .= '?'.http_build_query($parameters);
                    }

                    return $result;
                }
            );

        $this->admin
            ->method('generateObjectUrl')
            ->willReturnCallback(
                static function (string $name, $object, array $parameters = []): string {
                    $result = sprintf('%s_%s', \get_class($object), $name);
                    if (!empty($parameters)) {
                        $result .= '?'.http_build_query($parameters);
                    }

                    return $result;
                }
            );

        $this->admin
            ->method('getCode')
            ->willReturn('foo.admin');

        $this->controller = new CRUDController();
        $this->controller->setContainer($this->container);

        // Make some methods public to test them
        $testedMethods = [
            'renderJson',
            'isXmlHttpRequest',
            // NEXT_MAJOR: Remove next line.
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

    protected function tearDown(): void
    {
        parent::tearDown();

        if (!$this->httpMethodParameterOverride && Request::getHttpMethodParameterOverride()) {
            $disableHttpMethodParameterOverride = \Closure::bind(static function (): void {
                self::$httpMethodParameterOverride = false;
            }, null, Request::class);
            $disableHttpMethodParameterOverride();
        }
    }

    public function testRenderJson1(): void
    {
        $data = ['example' => '123', 'foo' => 'bar'];

        $this->request->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data, 200, [], $this->request);

        $this->assertSame($response->headers->get('Content-Type'), 'application/json');
        $this->assertSame(json_encode($data), $response->getContent());
    }

    public function testRenderJson2(): void
    {
        $data = ['example' => '123', 'foo' => 'bar'];

        $this->request->headers->set('Content-Type', 'multipart/form-data');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data, 200, [], $this->request);

        $this->assertSame($response->headers->get('Content-Type'), 'application/json');
        $this->assertSame(json_encode($data), $response->getContent());
    }

    public function testRenderJsonAjax(): void
    {
        $data = ['example' => '123', 'foo' => 'bar'];

        $this->request->attributes->set('_xml_http_request', true);
        $this->request->headers->set('Content-Type', 'multipart/form-data');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data, 200, [], $this->request);

        $this->assertSame($response->headers->get('Content-Type'), 'application/json');
        $this->assertSame(json_encode($data), $response->getContent());
    }

    public function testIsXmlHttpRequest(): void
    {
        $this->assertFalse($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller, $this->request));

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $this->assertTrue($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller, $this->request));

        $this->request->headers->remove('X-Requested-With');
        $this->assertFalse($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller, $this->request));

        $this->request->attributes->set('_xml_http_request', true);
        $this->assertTrue($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller, $this->request));
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testConfigureCallsConfigureAdmin(): void
    {
        $this->admin->expects($this->once())
            ->method('setRequest');

        $this->expectDeprecation('The "Sonata\AdminBundle\Controller\CRUDController::configure()" method is deprecated since sonata-project/admin-bundle version 3.86 and will be removed in 4.0 version.');

        $this->protectedTestedMethods['configure']->invoke($this->controller);
    }

    public function testConfigureAdmin(): void
    {
        $uniqueId = '';

        $this->admin->expects($this->once())
            ->method('setUniqid')
            ->willReturnCallback(static function (int $uniqid) use (&$uniqueId): void {
                $uniqueId = $uniqid;
            });

        $this->request->query->set('uniqid', 123456);
        $this->controller->configureAdmin($this->request);

        $this->assertSame(123456, $uniqueId);
    }

    public function testConfigureAdminChild(): void
    {
        $uniqueId = '';

        $this->admin->expects($this->once())
            ->method('setUniqid')
            ->willReturnCallback(static function ($uniqid) use (&$uniqueId): void {
                $uniqueId = $uniqid;
            });

        $this->admin->expects($this->once())
            ->method('isChild')
            ->willReturn(true);

        $adminParent = $this->getMockBuilder(AbstractAdmin::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->admin->expects($this->once())
            ->method('getParent')
            ->willReturn($adminParent);

        $this->request->query->set('uniqid', 123456);
        $this->controller->configureAdmin($this->request);

        $this->assertSame(123456, $uniqueId);
    }

    public function testConfigureAdminWithException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'There is no `_sonata_admin` defined for the controller `Sonata\AdminBundle\Controller\CRUDController`'
        );

        $this->request->attributes->remove('_sonata_admin');
        $this->controller->configureAdmin($this->request);
    }

    public function testConfigureAdminWithException2(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('You have requested a non-existent service "nonexistent.admin".');

        $this->pool->setAdminServiceIds(['nonexistent.admin']);
        $this->request->attributes->set('_sonata_admin', 'nonexistent.admin');
        $this->controller->configureAdmin($this->request);
    }

    public function testGetBaseTemplate(): void
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

    public function testRender(): void
    {
        $this->parameters = [];
        $this->assertInstanceOf(
            Response::class,
            $this->controller->renderWithExtraParams('@FooAdmin/foo.html.twig', [], null)
        );
        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);
        $this->assertSame('@FooAdmin/foo.html.twig', $this->template);
    }

    public function testRenderWithResponse(): void
    {
        $this->parameters = [];
        $response = new Response();
        $response->headers->set('X-foo', 'bar');
        $responseResult = $this->controller->renderWithExtraParams('@FooAdmin/foo.html.twig', [], $response);

        $this->assertSame($response, $responseResult);
        $this->assertSame('bar', $responseResult->headers->get('X-foo'));
        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);
        $this->assertSame('@FooAdmin/foo.html.twig', $this->template);
    }

    public function testRenderCustomParams(): void
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
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);
        $this->assertSame('bar', $this->parameters['foo']);
        $this->assertSame('@FooAdmin/foo.html.twig', $this->template);
    }

    public function testRenderAjax(): void
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
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);
        $this->assertSame('bar', $this->parameters['foo']);
        $this->assertSame('@FooAdmin/foo.html.twig', $this->template);
    }

    public function testListActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('list'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->listAction();
    }

    public function testPreList(): void
    {
        $this->admin
            ->method('hasRoute')
            ->with($this->equalTo('list'))
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('list'))
            ->willReturn(true);

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->listAction();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('preList called', $response->getContent());
    }

    public function testListAction(): void
    {
        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin
            ->method('hasRoute')
            ->with($this->equalTo('list'))
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('list'))
            ->willReturn(true);

        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->once())
            ->method('createView')
            ->willReturn($this->createMock(FormView::class));

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $datagrid->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $this->parameters = [];
        $this->assertInstanceOf(Response::class, $this->controller->listAction());

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('list', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertInstanceOf(DatagridInterface::class, $this->parameters['datagrid']);
        $this->assertSame('csrf-token-123_sonata.batch', $this->parameters['csrf_token']);
        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/list.html.twig', $this->template);
    }

    public function testBatchActionDeleteAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('batchDelete'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->batchActionDelete($this->createMock(ProxyQueryInterface::class));
    }

    public function testBatchActionDelete(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('batchDelete'))
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin->expects($this->once())
            ->method('getFilterParameters')
            ->willReturn(['foo' => 'bar']);

        $this->expectTranslate('flash_batch_delete_success', [], 'SonataAdminBundle');

        $result = $this->controller->batchActionDelete($this->createMock(ProxyQueryInterface::class));

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list?filter%5Bfoo%5D=bar', $result->getTargetUrl());
    }

    public function testBatchActionDeleteWithModelManagerException(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $this->assertLoggerLogsModelManagerException($modelManager, 'batchDelete');

        $this->admin->expects($this->once())
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin->expects($this->once())
            ->method('getFilterParameters')
            ->willReturn(['foo' => 'bar']);

        $this->expectTranslate('flash_batch_delete_error', [], 'SonataAdminBundle');

        $result = $this->controller->batchActionDelete($this->createMock(ProxyQueryInterface::class));

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_delete_error'], $this->session->getFlashBag()->get('sonata_flash_error'));
        $this->assertSame('list?filter%5Bfoo%5D=bar', $result->getTargetUrl());
    }

    public function testBatchActionDeleteWithModelManagerExceptionInDebugMode(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $this->expectException(ModelManagerException::class);

        $modelManager->expects($this->once())
            ->method('batchDelete')
            ->willReturnCallback(static function (): void {
                throw new ModelManagerException();
            });

        $this->admin->expects($this->once())
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->kernel->expects($this->once())
            ->method('isDebug')
            ->willReturn(true);

        $this->controller->batchActionDelete($this->createMock(ProxyQueryInterface::class));
    }

    public function testShowActionNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn(false);

        $this->controller->showAction(null);
    }

    public function testShowActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn(new \stdClass());

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('show'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->showAction(null);
    }

    public function testPreShow(): void
    {
        $object = new \stdClass();
        $object->foo = 123456;

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('show'))
            ->willReturn(true);

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->showAction(null);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('preShow called: 123456', $response->getContent());
    }

    public function testShowAction(): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('show'))
            ->willReturn(true);

        $show = $this->createMock(FieldDescriptionCollection::class);

        $this->admin->expects($this->once())
            ->method('getShow')
            ->willReturn($show);

        $this->assertInstanceOf(Response::class, $this->controller->showAction(null));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
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
    public function testRedirectTo(
        string $expected,
        string $route,
        array $queryParams,
        array $requestParams,
        bool $hasActiveSubclass
    ): void {
        $this->admin
            ->method('hasActiveSubclass')
            ->willReturn($hasActiveSubclass);

        $object = new \stdClass();

        foreach ($queryParams as $key => $value) {
            $this->request->query->set($key, $value);
        }

        foreach ($requestParams as $key => $value) {
            $this->request->request->set($key, $value);
        }

        $this->admin
            ->method('hasRoute')
            ->with($this->equalTo($route))
            ->willReturn(true);

        $this->admin
            ->method('hasAccess')
            ->with($this->equalTo($route))
            ->willReturn(true);

        $response = $this->protectedTestedMethods['redirectTo']->invoke($this->controller, $object, $this->request);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($expected, $response->getTargetUrl());
    }

    public function testRedirectToWithObject(): void
    {
        $this->admin
            ->method('hasActiveSubclass')
            ->willReturn(false);

        $object = new \stdClass();

        $this->admin->expects($this->exactly(2))->method('hasRoute')->willReturnMap([
            ['edit', true],
            ['show', false],
        ]);

        $this->admin
            ->method('hasAccess')
            ->with($this->equalTo('edit'), $object)
            ->willReturn(false);

        $response = $this->protectedTestedMethods['redirectTo']->invoke($this->controller, $object, $this->request);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('list', $response->getTargetUrl());
    }

    public function getRedirectToTests()
    {
        return [
            ['stdClass_edit', 'edit', [], [], false],
            ['list', 'list', ['btn_update_and_list' => true], [], false],
            ['list', 'list', ['btn_create_and_list' => true], [], false],
            ['create', 'create', ['btn_create_and_create' => true], [], false],
            ['create?subclass=foo', 'create', ['btn_create_and_create' => true, 'subclass' => 'foo'], [], true],
            ['stdClass_edit?_tab=first_tab', 'edit', ['btn_update_and_edit' => true], ['_tab' => 'first_tab'], false],
        ];
    }

    public function testDeleteActionNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn(false);

        $this->controller->deleteAction(1);
    }

    public function testDeleteActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn(new \stdClass());

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->deleteAction(1);
    }

    public function testPreDelete(): void
    {
        $object = new \stdClass();
        $object->foo = 123456;

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->willReturn(true);

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->deleteAction(null);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('preDelete called: 123456', $response->getContent());
    }

    public function testDeleteAction(): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->willReturn(true);

        $this->assertInstanceOf(Response::class, $this->controller->deleteAction(1));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('delete', $this->parameters['action']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertSame('csrf-token-123_sonata.delete', $this->parameters['csrf_token']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/delete.html.twig', $this->template);
    }

    /**
     * @group legacy
     * @expectedDeprecation Accessing a child that isn't connected to a given parent is deprecated since sonata-project/admin-bundle 3.34 and won't be allowed in 4.0.
     */
    public function testDeleteActionChildDeprecation(): void
    {
        $object = new \stdClass();
        $object->parent = 'test';

        $object2 = new \stdClass();

        $admin = $this->createMock(PostAdmin::class);
        $admin->method('getIdParameter')->willReturn('parent_id');

        $admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object2);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('isChild')
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('getParent')
            ->willReturn($admin);

        $this->admin->expects($this->exactly(2))
            ->method('getParentAssociationMapping')
            ->willReturn('parent');

        $this->controller->deleteAction(1);
    }

    public function testDeleteActionNoParentMappings(): void
    {
        $object = new \stdClass();

        $admin = $this->createMock(PostAdmin::class);

        $admin->expects($this->never())
            ->method('getObject');

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('isChild')
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('getParentAssociationMapping')
            ->willReturn(false);

        $this->controller->deleteAction(1);
    }

    public function testDeleteActionNoCsrfToken(): void
    {
        $this->container->set('security.csrf.token_manager', null);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->willReturn(true);

        $this->assertInstanceOf(Response::class, $this->controller->deleteAction(1));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('delete', $this->parameters['action']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertFalse($this->parameters['csrf_token']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/delete.html.twig', $this->template);
    }

    public function testDeleteActionAjaxSuccess1(): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->willReturn(true);

        $this->request->setMethod(Request::METHOD_DELETE);

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(json_encode(['result' => 'ok']), $response->getContent());
        $this->assertSame([], $this->session->getFlashBag()->all());
    }

    public function testDeleteActionAjaxSuccess2(): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->willReturn(true);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('_method', Request::METHOD_DELETE);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        Request::enableHttpMethodParameterOverride();

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(json_encode(['result' => 'ok']), $response->getContent());
        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame(Request::METHOD_DELETE, $this->request->getMethod());
    }

    public function testDeleteActionAjaxError(): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->willReturn(true);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->assertLoggerLogsModelManagerException($this->admin, 'delete');

        $this->request->setMethod(Request::METHOD_DELETE);

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(json_encode(['result' => 'error']), $response->getContent());
        $this->assertSame([], $this->session->getFlashBag()->all());
    }

    public function testDeleteActionWithModelManagerExceptionInDebugMode(): void
    {
        $this->expectException(ModelManagerException::class);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('delete')
            ->willReturnCallback(static function (): void {
                throw new ModelManagerException();
            });

        $this->kernel->expects($this->once())
            ->method('isDebug')
            ->willReturn(true);

        $this->request->setMethod(Request::METHOD_DELETE);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $this->controller->deleteAction(1);
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testDeleteActionSuccess1(string $expectedToStringValue, string $toStringValue): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_delete_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->willReturn(true);

        $this->request->setMethod(Request::METHOD_DELETE);

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['flash_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list', $response->getTargetUrl());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testDeleteActionSuccess2(string $expectedToStringValue, string $toStringValue): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_delete_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('_method', Request::METHOD_DELETE);

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        Request::enableHttpMethodParameterOverride();

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['flash_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list', $response->getTargetUrl());
        $this->assertSame(Request::METHOD_DELETE, $this->request->getMethod());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testDeleteActionSuccessNoCsrfTokenProvider(string $expectedToStringValue, string $toStringValue): void
    {
        $this->container->set('security.csrf.token_manager', null);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_delete_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('_method', Request::METHOD_DELETE);

        Request::enableHttpMethodParameterOverride();

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['flash_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list', $response->getTargetUrl());
        $this->assertSame(Request::METHOD_DELETE, $this->request->getMethod());
    }

    public function testDeleteActionWrongRequestMethod(): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->willReturn(true);

        //without POST request parameter "_method" should not be used as real REST method
        $this->request->query->set('_method', Request::METHOD_DELETE);

        Request::enableHttpMethodParameterOverride();

        $this->assertInstanceOf(Response::class, $this->controller->deleteAction(1));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('delete', $this->parameters['action']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertSame('csrf-token-123_sonata.delete', $this->parameters['csrf_token']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/delete.html.twig', $this->template);
        $this->assertSame(Request::METHOD_GET, $this->request->getMethod());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testDeleteActionError(string $expectedToStringValue, string $toStringValue): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_delete_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->assertLoggerLogsModelManagerException($this->admin, 'delete');

        $this->request->setMethod(Request::METHOD_DELETE);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['flash_delete_error'], $this->session->getFlashBag()->get('sonata_flash_error'));
        $this->assertSame('list', $response->getTargetUrl());
    }

    public function testDeleteActionInvalidCsrfToken(): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('delete'))
            ->willReturn(true);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('_method', Request::METHOD_DELETE);
        $this->request->request->set('_sonata_csrf_token', 'CSRF-INVALID');

        Request::enableHttpMethodParameterOverride();

        try {
            $this->controller->deleteAction(1);
        } catch (HttpException $e) {
            $this->assertSame('The csrf token is not valid, CSRF attack?', $e->getMessage());
            $this->assertSame(400, $e->getStatusCode());
        }

        $this->assertSame(Request::METHOD_DELETE, $this->request->getMethod());
    }

    public function testEditActionNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn(false);

        $this->controller->editAction(null);
    }

    public function testEditActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn(new \stdClass());

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->editAction(null);
    }

    public function testPreEdit(): void
    {
        $object = new \stdClass();
        $object->foo = 123456;

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->willReturn(true);

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->editAction(null);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('preEdit called: 123456', $response->getContent());
    }

    public function testEditAction(): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->willReturn(true);

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $this->assertInstanceOf(Response::class, $this->controller->editAction(null));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
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
    public function testEditActionSuccess(string $expectedToStringValue, string $toStringValue): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('update')
            ->willReturnArgument(0);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'));

        $this->admin->expects($this->once())
            ->method('hasRoute')
            ->with($this->equalTo('edit'))
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('hasAccess')
            ->with($this->equalTo('edit'))
            ->willReturn(true);

        $form = $this->createMock(Form::class);

        $form->expects($this->once())
            ->method('getData')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_edit_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);

        $response = $this->controller->editAction(null);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['flash_edit_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('stdClass_edit', $response->getTargetUrl());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testEditActionError(string $expectedToStringValue, string $toStringValue): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->willReturn(true);

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_edit_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $this->assertInstanceOf(Response::class, $this->controller->editAction(null));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('edit', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(['sonata_flash_error' => ['flash_edit_error']], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
    }

    public function testEditActionAjaxSuccess(): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('update')
            ->willReturnArgument(0);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->willReturn(true);

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('getData')
            ->willReturn($object);

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with($this->equalTo($object))
            ->willReturn('foo_normalized');

        $this->admin->expects($this->once())
            ->method('toString')
            ->willReturn('foo');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->editAction(null);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(json_encode(['result' => 'ok', 'objectId' => 'foo_normalized', 'objectName' => 'foo']), $response->getContent());
        $this->assertSame([], $this->session->getFlashBag()->all());
    }

    public function testEditActionAjaxError(): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->willReturn(true);

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $formError = $this->createMock(FormError::class);
        $formError->expects($this->atLeastOnce())
            ->method('getMessage')
            ->willReturn('Form error message');

        $form->expects($this->once())
            ->method('getErrors')
            ->with(true)
            ->willReturn([$formError]);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->headers->set('Accept', 'application/json');

        $this->assertInstanceOf(JsonResponse::class, $response = $this->controller->editAction(null));
        $this->assertJsonStringEqualsJsonString('{"result":"error","errors":["Form error message"]}', $response->getContent());
    }

    /**
     * @legacy
     */
    public function testEditActionAjaxErrorWithoutAcceptApplicationJson(): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->willReturn(true);

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $formView = $this->createMock(FormView::class);
        $form
            ->method('createView')
            ->willReturn($formView);

        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('flash message');

        $this->expectDeprecation(sprintf(
            'None of the passed values ("%s") in the "Accept" header when requesting %s %s is supported since sonata-project/admin-bundle 3.82.'
            .' It will result in a response with the status code 406 (Not Acceptable) in 4.0. You must add "application/json".',
            implode('", "', $this->request->getAcceptableContentTypes()),
            $this->request->getMethod(),
            $this->request->getUri()
        ));
        $this->assertInstanceOf(Response::class, $response = $this->controller->editAction(null));
        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/ajax_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);
        $this->assertSame('edit', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertSame([
            'sonata_flash_error' => [0 => 'flash message'],
        ], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testEditActionWithModelManagerException(string $expectedToStringValue, string $toStringValue): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->willReturn(true);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('getData')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_edit_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);
        $this->request->setMethod(Request::METHOD_POST);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $this->assertLoggerLogsModelManagerException($this->admin, 'update');
        $this->assertInstanceOf(Response::class, $this->controller->editAction(null));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('edit', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(['sonata_flash_error' => ['flash_edit_error']], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
    }

    public function testEditActionWithPreview(): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->willReturn(true);

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $this->admin->expects($this->once())
            ->method('supportsPreviewMode')
            ->willReturn(true);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('btn_preview', 'Preview');

        $this->assertInstanceOf(Response::class, $this->controller->editAction(null));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('edit', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/preview.html.twig', $this->template);
    }

    public function testEditActionWithLockException(): void
    {
        $object = new \stdClass();
        $class = \get_class($object);

        $this->admin
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('checkAccess')
            ->with($this->equalTo('edit'))
            ->willReturn(true);

        $this->admin
            ->method('getClass')
            ->willReturn($class);

        $form = $this->createMock(Form::class);

        $form
            ->method('isValid')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('getData')
            ->willReturn($object);

        $this->admin
            ->method('getForm')
            ->willReturn($form);

        $form
            ->method('isSubmitted')
            ->willReturn(true);
        $this->request->setMethod(Request::METHOD_POST);

        $this->admin
            ->method('update')
            ->will($this->throwException(new LockException()));

        $this->admin
            ->method('toString')
            ->with($this->equalTo($object))
            ->willReturn($class);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $this->expectTranslate('flash_lock_error', [
            '%name%' => $class,
            '%link_start%' => '<a href="stdClass_edit">',
            '%link_end%' => '</a>',
        ], 'SonataAdminBundle');

        $this->assertInstanceOf(Response::class, $this->controller->editAction(null));
    }

    public function testCreateActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->createAction();
    }

    public function testPreCreate(): void
    {
        $object = new \stdClass();
        $object->foo = 123456;

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->willReturn(true);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($object);

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->createAction();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('preCreate called: 123456', $response->getContent());
    }

    public function testCreateAction(): void
    {
        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($object);

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $this->assertInstanceOf(Response::class, $this->controller->createAction());

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
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
    public function testCreateActionSuccess(string $expectedToStringValue, string $toStringValue): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->exactly(2))
            ->method('checkAccess')
            ->willReturnCallback(static function (string $name, $objectIn = null) use ($object): bool {
                if ('edit' === $name) {
                    return true;
                }

                if ('create' !== $name) {
                    return false;
                }

                if (null === $objectIn) {
                    return true;
                }

                return $objectIn === $object;
            });

        $this->admin->expects($this->once())
            ->method('hasRoute')
            ->with($this->equalTo('edit'))
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('hasAccess')
            ->with($this->equalTo('edit'))
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('create')
            ->willReturnArgument(0);

        $form = $this->createMock(Form::class);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('getData')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_create_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);

        $response = $this->controller->createAction();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['flash_create_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('stdClass_edit', $response->getTargetUrl());
    }

    public function testCreateActionAccessDenied2(): void
    {
        $this->expectException(AccessDeniedException::class);

        $object = new \stdClass();

        $this->admin
            ->method('checkAccess')
            ->willReturnCallback(static function (string $name, $object = null): bool {
                if ('create' !== $name) {
                    throw new AccessDeniedException();
                }
                if (null === $object) {
                    return true;
                }

                throw new AccessDeniedException();
            });

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($object);

        $form = $this->createMock(Form::class);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('getData')
            ->willReturn($object);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod(Request::METHOD_POST);

        $this->controller->createAction();
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testCreateActionError(string $expectedToStringValue, string $toStringValue): void
    {
        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($object);

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_create_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $this->assertInstanceOf(Response::class, $this->controller->createAction());

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
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
    public function testCreateActionWithModelManagerException(string $expectedToStringValue, string $toStringValue): void
    {
        $this->admin->expects($this->exactly(2))
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->willReturn(true);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($object);

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_create_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('getData')
            ->willReturn($object);

        $this->request->setMethod(Request::METHOD_POST);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $this->assertLoggerLogsModelManagerException($this->admin, 'create');

        $this->assertInstanceOf(Response::class, $this->controller->createAction());

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('create', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame(['sonata_flash_error' => ['flash_create_error']], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
    }

    public function testCreateActionAjaxSuccess(): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->exactly(2))
            ->method('checkAccess')
            ->willReturnCallback(static function (string $name, $objectIn = null) use ($object): bool {
                if ('create' !== $name) {
                    return false;
                }

                if (null === $objectIn) {
                    return true;
                }

                return $objectIn === $object;
            });

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('create')
            ->willReturnArgument(0);

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('getData')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects($this->once())
            ->method('getNormalizedIdentifier')
            ->with($this->equalTo($object))
            ->willReturn('foo_normalized');

        $this->admin->expects($this->once())
            ->method('toString')
            ->willReturn('foo');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->createAction();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(json_encode(['result' => 'ok', 'objectId' => 'foo_normalized', 'objectName' => 'foo']), $response->getContent());
        $this->assertSame([], $this->session->getFlashBag()->all());
    }

    public function testCreateActionAjaxError(): void
    {
        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($object);

        $form = $this->createMock(Form::class);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $formError = $this->createMock(FormError::class);
        $formError->expects($this->atLeastOnce())
            ->method('getMessage')
            ->willReturn('Form error message');

        $form->expects($this->once())
            ->method('getErrors')
            ->with(true)
            ->willReturn([$formError]);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->headers->set('Accept', 'application/json');

        $this->assertInstanceOf(JsonResponse::class, $response = $this->controller->createAction());
        $this->assertJsonStringEqualsJsonString('{"result":"error","errors":["Form error message"]}', $response->getContent());
    }

    /**
     * @legacy
     */
    public function testCreateActionAjaxErrorWithoutAcceptApplicationJson(): void
    {
        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($object);

        $form = $this->createMock(Form::class);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $formView = $this->createMock(FormView::class);
        $form
            ->method('createView')
            ->willReturn($formView);

        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('flash message');

        $this->expectDeprecation(sprintf(
            'None of the passed values ("%s") in the "Accept" header when requesting %s %s is supported since sonata-project/admin-bundle 3.82.'
            .' It will result in a response with the status code 406 (Not Acceptable) in 4.0. You must add "application/json".',
            implode('", "', $this->request->getAcceptableContentTypes()),
            $this->request->getMethod(),
            $this->request->getUri()
        ));
        $this->assertInstanceOf(Response::class, $response = $this->controller->createAction());
        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/ajax_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);
        $this->assertSame('create', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertSame([
            'sonata_flash_error' => [0 => 'flash message'],
        ], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
    }

    public function testCreateActionWithPreview(): void
    {
        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($object);

        $form = $this->createMock(Form::class);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $this->admin->expects($this->once())
            ->method('supportsPreviewMode')
            ->willReturn(true);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('btn_preview', 'Preview');

        $this->assertInstanceOf(Response::class, $this->controller->createAction());

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('create', $this->parameters['action']);
        $this->assertInstanceOf(FormView::class, $this->parameters['form']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/preview.html.twig', $this->template);
    }

    public function testExportActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('export'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->exportAction($this->request);
    }

    public function testExportActionWrongFormat(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Export in format `csv` is not allowed for class: `Foo`. Allowed formats are: `json`'
        );

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('export'))
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('getExportFormats')
            ->willReturn(['json']);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->request->query->set('format', 'csv');

        $this->controller->exportAction($this->request);
    }

    public function testExportAction(): void
    {
        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('export'))
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('getExportFormats')
            ->willReturn(['json']);

        $dataSourceIterator = $this->createMock(SourceIteratorInterface::class);

        $this->admin->expects($this->once())
            ->method('getDataSourceIterator')
            ->willReturn($dataSourceIterator);

        $this->request->query->set('format', 'json');

        $response = $this->controller->exportAction($this->request);
        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame([], $this->session->getFlashBag()->all());
    }

    public function testHistoryActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin
            ->method('getObject')
            ->willReturn(new \stdClass());

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('history'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->historyAction(null);
    }

    public function testHistoryActionNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn(false);

        $this->controller->historyAction(null);
    }

    public function testHistoryActionNoReader(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('unable to find the audit reader for class : Foo');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('history'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->willReturn(false);

        $this->controller->historyAction(null);
    }

    public function testHistoryAction(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('history'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->willReturn(true);

        $reader = $this->createMock(AuditReaderInterface::class);

        $this->auditManager->expects($this->once())
            ->method('getReader')
            ->with($this->equalTo('Foo'))
            ->willReturn($reader);

        $reader->expects($this->once())
            ->method('findRevisions')
            ->with($this->equalTo('Foo'), $this->equalTo(123))
            ->willReturn([]);

        $this->assertInstanceOf(Response::class, $this->controller->historyAction(null));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('history', $this->parameters['action']);
        $this->assertSame([], $this->parameters['revisions']);
        $this->assertSame($object, $this->parameters['object']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/history.html.twig', $this->template);
    }

    public function testAclActionAclNotEnabled(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('ACL are not enabled for this admin');

        $this->controller->aclAction(null);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testAclTriggerDeprecationWithoutConfiguringUserManager(): void
    {
        $this->admin->expects($this->once())
            ->method('isAclEnabled')
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn(false);

        $this->container->setParameter('sonata.admin.security.fos_user_autoconfigured', true);

        $this->expectDeprecation('Not configuring "acl_user_manager" and using ACL security handler is deprecated since sonata-project/admin-bundle 3.78 and will not work on 4.0. You MUST specify the service name under "sonata_admin.security.acl_user_manager" option.');

        $this->expectException(NotFoundHttpException::class);

        $this->controller->aclAction(null);
    }

    public function testAclActionNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->admin->expects($this->once())
            ->method('isAclEnabled')
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn(false);

        $this->controller->aclAction(null);
    }

    public function testAclActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects($this->once())
            ->method('isAclEnabled')
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('acl'), $this->equalTo($object))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->aclAction(null);
    }

    public function testAclAction(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->exactly(2))
            ->method('isAclEnabled')
            ->willReturn(true);

        $object = new DummyDomainObject();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('checkAccess')
            ->willReturn(true);

        $this->admin
            ->method('getSecurityInformation')
            ->willReturn([]);

        $aclUsersForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclUsersForm->expects($this->once())
            ->method('createView')
            ->willReturn($this->createMock(FormView::class));

        $aclRolesForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclRolesForm->expects($this->once())
            ->method('createView')
            ->willReturn($this->createMock(FormView::class));

        $formBuilder = $this->createStub(FormBuilderInterface::class);
        $formBuilder
            ->method('getForm')
            ->willReturnOnConsecutiveCalls(
                $aclUsersForm,
                $aclRolesForm
            );

        $this->formFactory
            ->method('createNamedBuilder')
            ->willReturn($formBuilder);

        $aclSecurityHandler = $this->createStub(AclSecurityHandler::class);
        $aclSecurityHandler
            ->method('getObjectPermissions')
            ->willReturn([]);

        $aclSecurityHandler
            ->method('createAcl')
            ->willReturn($this->createStub(MutableAclInterface::class));

        $this->admin
            ->method('getSecurityHandler')
            ->willReturn($aclSecurityHandler);

        $this->assertInstanceOf(Response::class, $this->controller->aclAction(null));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
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

    public function testAclActionInvalidUpdate(): void
    {
        $this->request->query->set('id', 123);
        $this->request->request->set(AdminObjectAclManipulator::ACL_USERS_FORM_NAME, []);

        $this->admin->expects($this->exactly(2))
            ->method('isAclEnabled')
            ->willReturn(true);

        $object = new DummyDomainObject();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('checkAccess')
            ->willReturn(true);

        $this->admin
            ->method('getSecurityInformation')
            ->willReturn([]);

        $aclUsersForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclUsersForm->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $aclUsersForm->expects($this->once())
            ->method('createView')
            ->willReturn($this->createMock(FormView::class));

        $aclRolesForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclRolesForm->expects($this->once())
            ->method('createView')
            ->willReturn($this->createMock(FormView::class));

        $formBuilder = $this->createStub(FormBuilderInterface::class);
        $formBuilder
            ->method('getForm')
            ->willReturnOnConsecutiveCalls(
                $aclUsersForm,
                $aclRolesForm
            );

        $this->formFactory
            ->method('createNamedBuilder')
            ->willReturn($formBuilder);

        $aclSecurityHandler = $this->createStub(AclSecurityHandler::class);
        $aclSecurityHandler
            ->method('getObjectPermissions')
            ->willReturn([]);

        $aclSecurityHandler
            ->method('createAcl')
            ->willReturn($this->createStub(MutableAclInterface::class));

        $this->admin
            ->method('getSecurityHandler')
            ->willReturn($aclSecurityHandler);

        $this->request->setMethod(Request::METHOD_POST);

        $this->assertInstanceOf(Response::class, $this->controller->aclAction(null));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
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

    public function testAclActionSuccessfulUpdate(): void
    {
        $this->request->query->set('id', 123);
        $this->request->request->set(AdminObjectAclManipulator::ACL_ROLES_FORM_NAME, []);

        $this->admin->expects($this->exactly(2))
            ->method('isAclEnabled')
            ->willReturn(true);

        $object = new DummyDomainObject();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('checkAccess')
            ->willReturn(true);

        $this->admin
            ->method('getSecurityInformation')
            ->willReturn([]);

        $aclUsersForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclUsersForm
            ->method('createView')
            ->willReturn($this->createMock(FormView::class));

        $aclRolesForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclRolesForm
            ->method('getData')
            ->willReturn([]);

        $aclRolesForm
            ->method('createView')
            ->willReturn($this->createMock(FormView::class));

        $aclRolesForm->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $formBuilder = $this->createStub(FormBuilderInterface::class);
        $formBuilder
            ->method('getForm')
            ->willReturnOnConsecutiveCalls(
                $aclUsersForm,
                $aclRolesForm
            );

        $this->formFactory
            ->method('createNamedBuilder')
            ->willReturn($formBuilder);

        $aclSecurityHandler = $this->createStub(AclSecurityHandler::class);
        $aclSecurityHandler
            ->method('getObjectPermissions')
            ->willReturn([]);

        $aclSecurityHandler
            ->method('createAcl')
            ->willReturn($this->createStub(MutableAclInterface::class));

        $this->admin
            ->method('getSecurityHandler')
            ->willReturn($aclSecurityHandler);

        $this->expectTranslate('flash_acl_edit_success', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);

        $response = $this->controller->aclAction(null);

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertSame(['flash_acl_edit_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame(sprintf('%s_acl', DummyDomainObject::class), $response->getTargetUrl());
    }

    public function testHistoryViewRevisionActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin
            ->method('getObject')
            ->willReturn(new \stdClass());

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyViewRevision'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->historyViewRevisionAction(null, null);
    }

    public function testHistoryViewRevisionActionNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('unable to find the object with id: 123');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn(false);

        $this->controller->historyViewRevisionAction(null, null);
    }

    public function testHistoryViewRevisionActionNoReader(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('unable to find the audit reader for class : Foo');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyViewRevision'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->willReturn(false);

        $this->controller->historyViewRevisionAction(null, null);
    }

    public function testHistoryViewRevisionActionNotFoundRevision(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(
            'unable to find the targeted object `123` from the revision `456` with classname : `Foo`'
        );

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyViewRevision'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->willReturn(true);

        $reader = $this->createMock(AuditReaderInterface::class);

        $this->auditManager->expects($this->once())
            ->method('getReader')
            ->with($this->equalTo('Foo'))
            ->willReturn($reader);

        $reader->expects($this->once())
            ->method('find')
            ->with($this->equalTo('Foo'), $this->equalTo(123), $this->equalTo(456))
            ->willReturn(null);

        $this->controller->historyViewRevisionAction(123, 456);
    }

    public function testHistoryViewRevisionAction(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyViewRevision'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->willReturn(true);

        $reader = $this->createMock(AuditReaderInterface::class);

        $this->auditManager->expects($this->once())
            ->method('getReader')
            ->with($this->equalTo('Foo'))
            ->willReturn($reader);

        $objectRevision = new \stdClass();
        $objectRevision->revision = 456;

        $reader->expects($this->once())
            ->method('find')
            ->with($this->equalTo('Foo'), $this->equalTo(123), $this->equalTo(456))
            ->willReturn($objectRevision);

        $this->admin->expects($this->once())
            ->method('setSubject')
            ->with($this->equalTo($objectRevision))
            ->willReturn(null);

        $fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin->expects($this->once())
            ->method('getShow')
            ->willReturn($fieldDescriptionCollection);

        $this->assertInstanceOf(Response::class, $this->controller->historyViewRevisionAction(123, 456));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('show', $this->parameters['action']);
        $this->assertSame($objectRevision, $this->parameters['object']);
        $this->assertSame($fieldDescriptionCollection, $this->parameters['elements']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/show.html.twig', $this->template);
    }

    public function testHistoryCompareRevisionsActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->historyCompareRevisionsAction(null, null, null);
    }

    public function testHistoryCompareRevisionsActionNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('unable to find the object with id: 123');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'))
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn(false);

        $this->controller->historyCompareRevisionsAction(null, null, null);
    }

    public function testHistoryCompareRevisionsActionNoReader(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('unable to find the audit reader for class : Foo');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->willReturn(false);

        $this->controller->historyCompareRevisionsAction(null, null, null);
    }

    public function testHistoryCompareRevisionsActionNotFoundBaseRevision(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(
            'unable to find the targeted object `123` from the revision `456` with classname : `Foo`'
        );

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->willReturn(true);

        $reader = $this->createMock(AuditReaderInterface::class);

        $this->auditManager->expects($this->once())
            ->method('getReader')
            ->with($this->equalTo('Foo'))
            ->willReturn($reader);

        // once because it will not be found and therefore the second call won't be executed
        $reader->expects($this->once())
            ->method('find')
            ->with($this->equalTo('Foo'), $this->equalTo(123), $this->equalTo(456))
            ->willReturn(null);

        $this->controller->historyCompareRevisionsAction(123, 456, 789);
    }

    public function testHistoryCompareRevisionsActionNotFoundCompareRevision(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(
            'unable to find the targeted object `123` from the revision `789` with classname : `Foo`'
        );

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->willReturn(true);

        $reader = $this->createMock(AuditReaderInterface::class);

        $this->auditManager->expects($this->once())
            ->method('getReader')
            ->with($this->equalTo('Foo'))
            ->willReturn($reader);

        $objectRevision = new \stdClass();
        $objectRevision->revision = 456;

        // first call should return, so the second call will throw an exception
        $reader->expects($this->exactly(2))->method('find')->willReturnMap([
            ['Foo', 123, 456, $objectRevision],
            ['Foo', 123, 789, null],
        ]);

        $this->controller->historyCompareRevisionsAction(123, 456, 789);
    }

    public function testHistoryCompareRevisionsActionAction(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects($this->once())
            ->method('hasReader')
            ->with($this->equalTo('Foo'))
            ->willReturn(true);

        $reader = $this->createMock(AuditReaderInterface::class);

        $this->auditManager->expects($this->once())
            ->method('getReader')
            ->with($this->equalTo('Foo'))
            ->willReturn($reader);

        $objectRevision = new \stdClass();
        $objectRevision->revision = 456;

        $compareObjectRevision = new \stdClass();
        $compareObjectRevision->revision = 789;

        $reader->expects($this->exactly(2))->method('find')->willReturnMap([
            ['Foo', 123, 456, $objectRevision],
            ['Foo', 123, 789, $compareObjectRevision],
        ]);

        $this->admin->expects($this->once())
            ->method('setSubject')
            ->with($this->equalTo($objectRevision))
            ->willReturn(null);

        $fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin->expects($this->once())
            ->method('getShow')
            ->willReturn($fieldDescriptionCollection);

        $this->assertInstanceOf(Response::class, $this->controller->historyCompareRevisionsAction(123, 456, 789));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        $this->assertSame($this->pool, $this->parameters['admin_pool']);

        $this->assertSame('show', $this->parameters['action']);
        $this->assertSame($objectRevision, $this->parameters['object']);
        $this->assertSame($compareObjectRevision, $this->parameters['object_compare']);
        $this->assertSame($fieldDescriptionCollection, $this->parameters['elements']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/show_compare.html.twig', $this->template);
    }

    public function testBatchActionWrongMethod(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Invalid request method given "GET", POST expected');

        $this->controller->batchAction();
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionActionNotDefined(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The `foo` batch action is not defined');

        $batchActions = [];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode(['action' => 'foo', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $this->controller->batchAction();
    }

    public function testBatchActionActionInvalidCsrfToken(): void
    {
        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode(['action' => 'foo', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('_sonata_csrf_token', 'CSRF-INVALID');

        try {
            $this->controller->batchAction();
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
    public function testBatchActionMethodNotExist(): void
    {
        $batchActions = ['foo' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $datagrid = $this->createMock(DatagridInterface::class);
        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode(['action' => 'foo', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'A `Sonata\AdminBundle\Controller\CRUDController::batchActionFoo` method must be callable'
        );

        $this->controller->batchAction();
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionWithoutConfirmation(): void
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('batchDelete'))
            ->willReturn(true);

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $modelManager->expects($this->once())
            ->method('addIdentifiersToQuery')
            ->with($this->equalTo('Foo'), $this->equalTo($query), $this->equalTo(['123', '456']))
            ->willReturn(true);

        $this->expectTranslate('flash_batch_delete_success', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode(['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $this->assertNull($this->request->get('idx'));

        $result = $this->controller->batchAction();

        $this->assertNull($this->request->get('idx'), 'Ensure original request is not modified by calling `CRUDController::batchAction()`.');
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list', $result->getTargetUrl());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionWithoutConfirmation2(): void
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('batchDelete'))
            ->willReturn(true);

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $modelManager->expects($this->once())
            ->method('addIdentifiersToQuery')
            ->with($this->equalTo('Foo'), $this->equalTo($query), $this->equalTo(['123', '456']))
            ->willReturn(true);

        $this->expectTranslate('flash_batch_delete_success', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('action', 'delete');
        $this->request->request->set('idx', ['123', '456']);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list', $result->getTargetUrl());
    }

    public function provideConfirmationData(): iterable
    {
        yield 'normal data' => [['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => false]];
        yield 'without all elements' => [['action' => 'delete', 'idx' => ['123', '456']]];
        yield 'all elements' => [['action' => 'delete', 'all_elements' => true]];
        yield 'idx is null' => [['action' => 'delete', 'idx' => null, 'all_elements' => true]];
        yield 'all_elements is null' => [['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => null]];
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @dataProvider provideConfirmationData
     * @group legacy
     */
    public function testBatchActionWithConfirmation(array $data): void
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'translation_domain' => 'FooBarBaz', 'ask_confirmation' => true]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode($data));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->once())
            ->method('createView')
            ->willReturn($this->createMock(FormView::class));

        $datagrid->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $this->assertInstanceOf(Response::class, $this->controller->batchAction());

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
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
     *
     * @dataProvider provideActionNames
     */
    public function testBatchActionNonRelevantAction(string $actionName): void
    {
        $controller = new BatchAdminController();
        $controller->setContainer($this->container);

        $batchActions = [$actionName => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $this->expectTranslate('flash_batch_empty', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('action', $actionName);
        $this->request->request->set('idx', ['789']);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $this->assertNull($this->request->get('all_elements'));

        $result = $controller->batchAction();

        $this->assertNull($this->request->get('all_elements'), 'Ensure original request is not modified by calling `CRUDController::batchAction()`.');
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_empty'], $this->session->getFlashBag()->get('sonata_flash_info'));
        $this->assertSame('list', $result->getTargetUrl());
    }

    public function provideActionNames(): iterable
    {
        yield ['foo'];
        yield ['foo_bar'];
        yield ['foo-bar'];
        yield ['foobar'];
    }

    public function testBatchActionWithCustomConfirmationTemplate(): void
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => true, 'template' => 'custom_template.html.twig']];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $data = ['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => false];

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode($data));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $form = $this->createMock(Form::class);

        $form->expects($this->once())
            ->method('createView')
            ->willReturn($this->createMock(FormView::class));

        $datagrid->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $this->controller->batchAction();

        $this->assertSame('custom_template.html.twig', $this->template);
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionNonRelevantAction2(): void
    {
        $controller = new BatchAdminController();
        $controller->setContainer($this->container);

        $batchActions = ['foo' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $this->expectTranslate('flash_foo_error', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('action', 'foo');
        $this->request->request->set('idx', ['999']);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $controller->batchAction();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_foo_error'], $this->session->getFlashBag()->get('sonata_flash_info'));
        $this->assertSame('list', $result->getTargetUrl());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionNoItems(): void
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => true]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $this->expectTranslate('flash_batch_empty', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('action', 'delete');
        $this->request->request->set('idx', []);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_empty'], $this->session->getFlashBag()->get('sonata_flash_info'));
        $this->assertSame('list', $result->getTargetUrl());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionNoItemsEmptyQuery(): void
    {
        $controller = new BatchAdminController();
        $controller->setContainer($this->container);

        $batchActions = ['bar' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('action', 'bar');
        $this->request->request->set('idx', []);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $this->expectTranslate('flash_batch_no_elements_processed', [], 'SonataAdminBundle');
        $result = $controller->batchAction();

        $this->assertInstanceOf(Response::class, $result);
        $this->assertMatchesRegularExpression('/Redirecting to list/', $result->getContent());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionWithRequesData(): void
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects($this->once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $this->admin->expects($this->once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('batchDelete'))
            ->willReturn(true);

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $modelManager->expects($this->once())
            ->method('addIdentifiersToQuery')
            ->with($this->equalTo('Foo'), $this->equalTo($query), $this->equalTo(['123', '456']))
            ->willReturn(true);

        $this->expectTranslate('flash_batch_delete_success', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode(['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('foo', 'bar');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list', $result->getTargetUrl());
        $this->assertSame('bar', $this->request->request->get('foo'));
    }

    /**
     * @expectedDeprecation Method Sonata\AdminBundle\Controller\CRUDController::render has been renamed to Sonata\AdminBundle\Controller\CRUDController::renderWithExtraParams.
     *
     * @doesNotPerformAssertions
     */
    public function testRenderIsDeprecated(): void
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

    private function assertLoggerLogsModelManagerException($subject, string $method): void
    {
        $exception = new ModelManagerException(
            $message = 'message',
            1234,
            new \Exception($previousExceptionMessage = 'very useful message')
        );

        $subject->expects($this->once())
            ->method($method)
            ->willReturnCallback(static function () use ($exception): void {
                throw $exception;
            });

        $this->logger->expects($this->once())
            ->method('error')
            ->with($message, [
                'exception' => $exception,
                'previous_exception_message' => $previousExceptionMessage,
            ]);
    }

    private function expectTranslate(
        string $id,
        array $parameters = [],
        ?string $domain = null,
        ?string $locale = null
    ): void {
        $this->translator->expects($this->once())
            ->method('trans')
            ->with($this->equalTo($id), $this->equalTo($parameters), $this->equalTo($domain), $this->equalTo($locale))
            ->willReturn($id);
    }
}
