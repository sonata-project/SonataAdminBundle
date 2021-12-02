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
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Export\Exporter as SonataExporter;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
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

        $this->session = new Session(new MockArraySessionStorage());

        $twig = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twig
            ->method('render')
            ->willReturnCallback(function ($view, array $parameters = []) {
                $this->template = $view;
                $this->parameters = $parameters;

                return '';
            });

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

        $this->container->set('sonata.admin.pool.do-not-use', $this->pool);
        $this->container->set('request_stack', $requestStack);
        $this->container->set('foo.admin', $this->admin);
        $this->container->set('foo.admin.template_registry', $this->templateRegistry);
        $this->container->set('twig', $twig);
        $this->container->set('session', $this->session);
        $this->container->set('sonata.admin.exporter', $exporter);
        $this->container->set('sonata.admin.audit.manager.do-not-use', $this->auditManager);
        $this->container->set('sonata.admin.object.manipulator.acl.admin.do-not-use', $this->adminObjectAclManipulator);
        $this->container->set('security.csrf.token_manager', $this->csrfProvider);
        $this->container->set('logger', $this->logger);
        $this->container->set('kernel', $this->kernel);
        $this->container->set('translator', $this->translator);
        $this->container->set('sonata.admin.breadcrumbs_builder.do-not-use', new BreadcrumbsBuilder([]));

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

        static::assertSame($response->headers->get('Content-Type'), 'application/json');
        static::assertSame(json_encode($data), $response->getContent());
    }

    public function testRenderJson2(): void
    {
        $data = ['example' => '123', 'foo' => 'bar'];

        $this->request->headers->set('Content-Type', 'multipart/form-data');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data, 200, [], $this->request);

        static::assertSame($response->headers->get('Content-Type'), 'application/json');
        static::assertSame(json_encode($data), $response->getContent());
    }

    public function testRenderJsonAjax(): void
    {
        $data = ['example' => '123', 'foo' => 'bar'];

        $this->request->attributes->set('_xml_http_request', true);
        $this->request->headers->set('Content-Type', 'multipart/form-data');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data, 200, [], $this->request);

        static::assertSame($response->headers->get('Content-Type'), 'application/json');
        static::assertSame(json_encode($data), $response->getContent());
    }

    public function testIsXmlHttpRequest(): void
    {
        static::assertFalse($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller, $this->request));

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        static::assertTrue($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller, $this->request));

        $this->request->headers->remove('X-Requested-With');
        static::assertFalse($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller, $this->request));

        $this->request->attributes->set('_xml_http_request', true);
        static::assertTrue($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller, $this->request));
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testConfigureCallsConfigureAdmin(): void
    {
        $this->admin->expects(static::once())
            ->method('setRequest');

        $this->expectDeprecation('The "Sonata\AdminBundle\Controller\CRUDController::configure()" method is deprecated since sonata-project/admin-bundle version 3.86 and will be removed in 4.0 version.');

        $this->protectedTestedMethods['configure']->invoke($this->controller);
    }

    public function testConfigureAdmin(): void
    {
        $uniqueId = '';

        $this->admin->expects(static::once())
            ->method('setUniqid')
            ->willReturnCallback(static function (int $uniqid) use (&$uniqueId): void {
                $uniqueId = $uniqid;
            });

        $this->request->query->set('uniqid', 123456);
        $this->controller->configureAdmin($this->request);

        static::assertSame(123456, $uniqueId);
    }

    public function testConfigureAdminChild(): void
    {
        $uniqueId = '';

        $this->admin->expects(static::once())
            ->method('setUniqid')
            ->willReturnCallback(static function ($uniqid) use (&$uniqueId): void {
                $uniqueId = $uniqid;
            });

        $this->admin->expects(static::once())
            ->method('isChild')
            ->willReturn(true);

        $adminParent = $this->getMockBuilder(AbstractAdmin::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->admin->expects(static::once())
            ->method('getParent')
            ->willReturn($adminParent);

        $this->request->query->set('uniqid', 123456);
        $this->controller->configureAdmin($this->request);

        static::assertSame(123456, $uniqueId);
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
        static::assertSame(
            '@SonataAdmin/standard_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller, $this->request)
        );

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        static::assertSame(
            '@SonataAdmin/ajax_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller, $this->request)
        );

        $this->request->headers->remove('X-Requested-With');
        static::assertSame(
            '@SonataAdmin/standard_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller, $this->request)
        );

        $this->request->attributes->set('_xml_http_request', true);
        static::assertSame(
            '@SonataAdmin/ajax_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller, $this->request)
        );
    }

    public function testRender(): void
    {
        $this->parameters = [];
        static::assertInstanceOf(
            Response::class,
            $this->controller->renderWithExtraParams('@FooAdmin/foo.html.twig', [], null)
        );
        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);
        static::assertSame('@FooAdmin/foo.html.twig', $this->template);
    }

    public function testRenderWithResponse(): void
    {
        $this->parameters = [];
        $response = new Response();
        $response->headers->set('X-foo', 'bar');
        $responseResult = $this->controller->renderWithExtraParams('@FooAdmin/foo.html.twig', [], $response);

        static::assertSame($response, $responseResult);
        static::assertSame('bar', $responseResult->headers->get('X-foo'));
        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);
        static::assertSame('@FooAdmin/foo.html.twig', $this->template);
    }

    public function testRenderCustomParams(): void
    {
        $this->parameters = [];
        static::assertInstanceOf(
            Response::class,
            $this->controller->renderWithExtraParams(
                '@FooAdmin/foo.html.twig',
                ['foo' => 'bar'],
                null
            )
        );
        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);
        static::assertSame('bar', $this->parameters['foo']);
        static::assertSame('@FooAdmin/foo.html.twig', $this->template);
    }

    public function testRenderAjax(): void
    {
        $this->parameters = [];
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        static::assertInstanceOf(
            Response::class,
            $this->controller->renderWithExtraParams(
                '@FooAdmin/foo.html.twig',
                ['foo' => 'bar'],
                null
            )
        );
        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/ajax_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);
        static::assertSame('bar', $this->parameters['foo']);
        static::assertSame('@FooAdmin/foo.html.twig', $this->template);
    }

    public function testListActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('list'))
            ->will(static::throwException(new AccessDeniedException()));

        $this->controller->listAction();
    }

    public function testPreList(): void
    {
        $this->admin
            ->method('hasRoute')
            ->with(static::equalTo('list'))
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('list'))
            ->willReturn(true);

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->listAction();
        static::assertInstanceOf(Response::class, $response);
        static::assertSame('preList called', $response->getContent());
    }

    public function testListAction(): void
    {
        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin
            ->method('hasRoute')
            ->with(static::equalTo('list'))
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('list'))
            ->willReturn(true);

        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects(static::once())
            ->method('createView')
            ->willReturn($this->createMock(FormView::class));

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $datagrid->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $this->parameters = [];
        static::assertInstanceOf(Response::class, $this->controller->listAction());

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('list', $this->parameters['action']);
        static::assertInstanceOf(FormView::class, $this->parameters['form']);
        static::assertInstanceOf(DatagridInterface::class, $this->parameters['datagrid']);
        static::assertSame('csrf-token-123_sonata.batch', $this->parameters['csrf_token']);
        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/list.html.twig', $this->template);
    }

    public function testBatchActionDeleteAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('batchDelete'))
            ->will(static::throwException(new AccessDeniedException()));

        $this->controller->batchActionDelete($this->createMock(ProxyQueryInterface::class));
    }

    public function testBatchActionDelete(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('batchDelete'))
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin->expects(static::once())
            ->method('getFilterParameters')
            ->willReturn(['foo' => 'bar']);

        $this->expectTranslate('flash_batch_delete_success', [], 'SonataAdminBundle');

        $result = $this->controller->batchActionDelete($this->createMock(ProxyQueryInterface::class));

        static::assertInstanceOf(RedirectResponse::class, $result);
        static::assertSame(['flash_batch_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        static::assertSame('list?filter%5Bfoo%5D=bar', $result->getTargetUrl());
    }

    public function testBatchActionDeleteWithModelManagerException(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $this->assertLoggerLogsModelManagerException($modelManager, 'batchDelete');

        $this->admin->expects(static::once())
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin->expects(static::once())
            ->method('getFilterParameters')
            ->willReturn(['foo' => 'bar']);

        $this->expectTranslate('flash_batch_delete_error', [], 'SonataAdminBundle');

        $result = $this->controller->batchActionDelete($this->createMock(ProxyQueryInterface::class));

        static::assertInstanceOf(RedirectResponse::class, $result);
        static::assertSame(['flash_batch_delete_error'], $this->session->getFlashBag()->get('sonata_flash_error'));
        static::assertSame('list?filter%5Bfoo%5D=bar', $result->getTargetUrl());
    }

    public function testBatchActionDeleteWithModelManagerExceptionInDebugMode(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $this->expectException(ModelManagerException::class);

        $modelManager->expects(static::once())
            ->method('batchDelete')
            ->willReturnCallback(static function (): void {
                throw new ModelManagerException();
            });

        $this->admin->expects(static::once())
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->kernel->expects(static::once())
            ->method('isDebug')
            ->willReturn(true);

        $this->controller->batchActionDelete($this->createMock(ProxyQueryInterface::class));
    }

    public function testShowActionNotFoundException(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->showAction();
    }

    public function testShowActionAccessDenied(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn(new \stdClass());

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('show'))
            ->will(static::throwException(new AccessDeniedException()));

        $this->expectException(AccessDeniedException::class);

        $this->controller->showAction();
    }

    public function testPreShow(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();
        $object->foo = 123456;

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('show'))
            ->willReturn(true);

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->showAction();
        static::assertInstanceOf(Response::class, $response);
        static::assertSame('preShow called: 123456', $response->getContent());
    }

    public function testShowAction(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('show'))
            ->willReturn(true);

        $show = $this->createMock(FieldDescriptionCollection::class);

        $this->admin->expects(static::once())
            ->method('getShow')
            ->willReturn($show);

        static::assertInstanceOf(Response::class, $this->controller->showAction(null));

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('show', $this->parameters['action']);
        static::assertInstanceOf(FieldDescriptionCollection::class, $this->parameters['elements']);
        static::assertSame($object, $this->parameters['object']);

        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/show.html.twig', $this->template);
    }

    public function testShowActionWithParentAdminAndNonexistentObject(): void
    {
        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::never())
            ->method('checkAccess');

        $this->admin->expects(static::never())
            ->method('getShow');

        $this->admin->expects(static::once())
            ->method('isChild')
            ->willReturn(true);

        $adminIdParameter = 'id';
        $this->request->attributes->set($adminIdParameter, 42);

        // NEXT_MAJOR: Mock `AdminInterface` when the `getClassnameLabel()` method is available.
        $parentAdmin = $this->createMock(AbstractAdmin::class);

        $parentAdmin->expects(static::once())
            ->method('getObject')
            ->willReturn(null);

        $parentAdminIdParameter = 'parentId';

        $parentAdmin->expects(static::once())
            ->method('getIdParameter')
            ->willReturn($parentAdminIdParameter);

        $parentAdmin->expects(static::once())
            ->method('getClassnameLabel')
            ->willReturn('NonexistentParentObject');

        $this->request->attributes->set($parentAdminIdParameter, 21);

        $this->admin->expects(static::once())
            ->method('getParent')
            ->willReturn($parentAdmin);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Unable to find NonexistentParentObject object with id: 21.');

        $this->controller->showAction();
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
            ->with(static::equalTo($route))
            ->willReturn(true);

        $this->admin
            ->method('hasAccess')
            ->with(static::equalTo($route))
            ->willReturn(true);

        $response = $this->protectedTestedMethods['redirectTo']->invoke($this->controller, $object, $this->request);
        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame($expected, $response->getTargetUrl());
    }

    public function testRedirectToWithObject(): void
    {
        $this->admin
            ->method('hasActiveSubclass')
            ->willReturn(false);

        $object = new \stdClass();

        $this->admin->expects(static::exactly(2))->method('hasRoute')->willReturnMap([
            ['edit', true],
            ['show', false],
        ]);

        $this->admin
            ->method('hasAccess')
            ->with(static::equalTo('edit'), $object)
            ->willReturn(false);

        $response = $this->protectedTestedMethods['redirectTo']->invoke($this->controller, $object, $this->request);
        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame('list', $response->getTargetUrl());
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
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->deleteAction(1);
    }

    public function testDeleteActionAccessDenied(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn(new \stdClass());

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'))
            ->will(static::throwException(new AccessDeniedException()));

        $this->expectException(AccessDeniedException::class);

        $this->controller->deleteAction(1);
    }

    public function testPreDelete(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();
        $object->foo = 123456;

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'))
            ->willReturn(true);

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->deleteAction(null);
        static::assertInstanceOf(Response::class, $response);
        static::assertSame('preDelete called: 123456', $response->getContent());
    }

    public function testDeleteAction(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'))
            ->willReturn(true);

        static::assertInstanceOf(Response::class, $this->controller->deleteAction(21));

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('delete', $this->parameters['action']);
        static::assertSame($object, $this->parameters['object']);
        static::assertSame('csrf-token-123_sonata.delete', $this->parameters['csrf_token']);

        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/delete.html.twig', $this->template);
    }

    /**
     * @group legacy
     * @expectedDeprecation Accessing a child that isn't connected to a given parent is deprecated since sonata-project/admin-bundle 3.34 and won't be allowed in 4.0.
     */
    public function testDeleteActionChildDeprecation(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);
        $this->request->attributes->set('parent_id', 42);

        $object = new \stdClass();
        $object->parent = 'test';

        $object2 = new \stdClass();

        $admin = $this->createMock(PostAdmin::class);
        $admin->method('getIdParameter')->willReturn('parent_id');

        $admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object2);

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::exactly(2))
            ->method('isChild')
            ->willReturn(true);

        $this->admin->expects(static::exactly(2))
            ->method('getParent')
            ->willReturn($admin);

        $this->admin->expects(static::exactly(2))
            ->method('getParentAssociationMapping')
            ->willReturn('parent');

        $this->controller->deleteAction(21);
    }

    public function testDeleteActionNoParentMappings(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $admin = $this->createMock(PostAdmin::class);

        $admin->expects(static::never())
            ->method('getObject');

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::exactly(2))
            ->method('isChild')
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('getParentAssociationMapping')
            ->willReturn(false);

        $this->controller->deleteAction(21);
    }

    public function testDeleteActionNoCsrfToken(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->container->set('security.csrf.token_manager', null);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'))
            ->willReturn(true);

        static::assertInstanceOf(Response::class, $this->controller->deleteAction(1));

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('delete', $this->parameters['action']);
        static::assertSame($object, $this->parameters['object']);
        static::assertFalse($this->parameters['csrf_token']);

        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/delete.html.twig', $this->template);
    }

    public function testDeleteActionAjaxSuccess1(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'))
            ->willReturn(true);

        $this->request->setMethod(Request::METHOD_DELETE);

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(21);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(json_encode(['result' => 'ok']), $response->getContent());
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testDeleteActionAjaxSuccess2(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'))
            ->willReturn(true);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('_method', Request::METHOD_DELETE);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        Request::enableHttpMethodParameterOverride();

        $response = $this->controller->deleteAction(21);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(json_encode(['result' => 'ok']), $response->getContent());
        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame(Request::METHOD_DELETE, $this->request->getMethod());
    }

    public function testDeleteActionAjaxError(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'))
            ->willReturn(true);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->assertLoggerLogsModelManagerException($this->admin, 'delete');

        $this->request->setMethod(Request::METHOD_DELETE);

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->deleteAction(21);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(json_encode(['result' => 'error']), $response->getContent());
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testDeleteActionWithModelManagerExceptionInDebugMode(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'))
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('delete')
            ->willReturnCallback(static function (): void {
                throw new ModelManagerException();
            });

        $this->kernel->expects(static::once())
            ->method('isDebug')
            ->willReturn(true);

        $this->request->setMethod(Request::METHOD_DELETE);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $this->expectException(ModelManagerException::class);

        $this->controller->deleteAction(21);
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testDeleteActionSuccess1(string $expectedToStringValue, string $toStringValue): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('toString')
            ->with(static::equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_delete_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'))
            ->willReturn(true);

        $this->request->setMethod(Request::METHOD_DELETE);

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(21);

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame(['flash_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        static::assertSame('list', $response->getTargetUrl());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testDeleteActionSuccess2(string $expectedToStringValue, string $toStringValue): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'))
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('toString')
            ->with(static::equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_delete_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('_method', Request::METHOD_DELETE);

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        Request::enableHttpMethodParameterOverride();

        $response = $this->controller->deleteAction(21);

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame(['flash_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        static::assertSame('list', $response->getTargetUrl());
        static::assertSame(Request::METHOD_DELETE, $this->request->getMethod());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testDeleteActionSuccessNoCsrfTokenProvider(string $expectedToStringValue, string $toStringValue): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->container->set('security.csrf.token_manager', null);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'))
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('toString')
            ->with(static::equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_delete_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('_method', Request::METHOD_DELETE);

        Request::enableHttpMethodParameterOverride();

        $response = $this->controller->deleteAction(21);

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame(['flash_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        static::assertSame('list', $response->getTargetUrl());
        static::assertSame(Request::METHOD_DELETE, $this->request->getMethod());
    }

    public function testDeleteActionWrongRequestMethod(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'))
            ->willReturn(true);

        //without POST request parameter "_method" should not be used as real REST method
        $this->request->query->set('_method', Request::METHOD_DELETE);

        Request::enableHttpMethodParameterOverride();

        static::assertInstanceOf(Response::class, $this->controller->deleteAction(21));

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('delete', $this->parameters['action']);
        static::assertSame($object, $this->parameters['object']);
        static::assertSame('csrf-token-123_sonata.delete', $this->parameters['csrf_token']);

        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/delete.html.twig', $this->template);
        static::assertSame(Request::METHOD_GET, $this->request->getMethod());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testDeleteActionError(string $expectedToStringValue, string $toStringValue): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'))
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('toString')
            ->with(static::equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_delete_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->assertLoggerLogsModelManagerException($this->admin, 'delete');

        $this->request->setMethod(Request::METHOD_DELETE);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction(21);

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame(['flash_delete_error'], $this->session->getFlashBag()->get('sonata_flash_error'));
        static::assertSame('list', $response->getTargetUrl());
    }

    public function testDeleteActionInvalidCsrfToken(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'))
            ->willReturn(true);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('_method', Request::METHOD_DELETE);
        $this->request->request->set('_sonata_csrf_token', 'CSRF-INVALID');

        Request::enableHttpMethodParameterOverride();

        try {
            $this->controller->deleteAction(21);
        } catch (HttpException $e) {
            static::assertSame('The csrf token is not valid, CSRF attack?', $e->getMessage());
            static::assertSame(400, $e->getStatusCode());
        }

        static::assertSame(Request::METHOD_DELETE, $this->request->getMethod());
    }

    public function testDeleteActionChildManyToMany(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);
        $this->request->attributes->set('parent_id', 42);

        $parent = new \stdClass();

        $child = new \stdClass();
        $child->parents = [$parent];

        $parentAdmin = $this->createMock(PostAdmin::class);
        $parentAdmin->method('getIdParameter')->willReturn('parent_id');

        $childAdmin = $this->admin;

        $parentAdmin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($parent);

        $childAdmin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($child);

        $childAdmin->expects(static::exactly(2))
            ->method('isChild')
            ->willReturn(true);

        $childAdmin->expects(static::exactly(2))
            ->method('getParent')
            ->willReturn($parentAdmin);

        $childAdmin->expects(static::exactly(2))
            ->method('getParentAssociationMapping')
            ->willReturn('parents');

        $this->controller->deleteAction(21);
    }

    public function testEditActionNotFoundException(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->editAction();
    }

    public function testEditActionAccessDenied(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn(new \stdClass());

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'))
            ->will(static::throwException(new AccessDeniedException()));

        $this->expectException(AccessDeniedException::class);

        $this->controller->editAction();
    }

    public function testPreEdit(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();
        $object->foo = 123456;

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'))
            ->willReturn(true);

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->editAction();
        static::assertInstanceOf(Response::class, $response);
        static::assertSame('preEdit called: 123456', $response->getContent());
    }

    public function testEditAction(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'))
            ->willReturn(true);

        $form = $this->createMock(Form::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        static::assertInstanceOf(Response::class, $this->controller->editAction());

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('edit', $this->parameters['action']);
        static::assertInstanceOf(FormView::class, $this->parameters['form']);
        static::assertSame($object, $this->parameters['object']);
        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testEditActionSuccess(string $expectedToStringValue, string $toStringValue): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('update')
            ->willReturnArgument(0);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'));

        $this->admin->expects(static::once())
            ->method('hasRoute')
            ->with(static::equalTo('edit'))
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('hasAccess')
            ->with(static::equalTo('edit'))
            ->willReturn(true);

        $form = $this->createMock(Form::class);

        $form->expects(static::once())
            ->method('getData')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects(static::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('toString')
            ->with(static::equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_edit_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);

        $response = $this->controller->editAction();

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame(['flash_edit_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        static::assertSame('stdClass_edit', $response->getTargetUrl());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testEditActionError(string $expectedToStringValue, string $toStringValue): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'))
            ->willReturn(true);

        $form = $this->createMock(Form::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects(static::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('isValid')
            ->willReturn(false);

        $this->admin->expects(static::once())
            ->method('toString')
            ->with(static::equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_edit_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        static::assertInstanceOf(Response::class, $this->controller->editAction());

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('edit', $this->parameters['action']);
        static::assertInstanceOf(FormView::class, $this->parameters['form']);
        static::assertSame($object, $this->parameters['object']);

        static::assertSame(['sonata_flash_error' => ['flash_edit_error']], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
    }

    public function testEditActionAjaxSuccess(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('update')
            ->willReturnArgument(0);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'))
            ->willReturn(true);

        $form = $this->createMock(Form::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects(static::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('getData')
            ->willReturn($object);

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('foo_normalized');

        $this->admin->expects(static::once())
            ->method('toString')
            ->willReturn('foo');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->editAction();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(json_encode(['result' => 'ok', 'objectId' => 'foo_normalized', 'objectName' => 'foo']), $response->getContent());
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testEditActionAjaxError(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'))
            ->willReturn(true);

        $form = $this->createMock(Form::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects(static::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('isValid')
            ->willReturn(false);

        $formError = $this->createMock(FormError::class);
        $formError->expects(static::atLeastOnce())
            ->method('getMessage')
            ->willReturn('Form error message');

        $form->expects(static::once())
            ->method('getErrors')
            ->with(true)
            ->willReturn([$formError]);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->headers->set('Accept', 'application/json');

        static::assertInstanceOf(JsonResponse::class, $response = $this->controller->editAction());
        static::assertJsonStringEqualsJsonString('{"result":"error","errors":["Form error message"]}', $response->getContent());
    }

    /**
     * @legacy
     */
    public function testEditActionAjaxErrorWithoutAcceptApplicationJson(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'))
            ->willReturn(true);

        $form = $this->createMock(Form::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects(static::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('isValid')
            ->willReturn(false);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $formView = $this->createMock(FormView::class);
        $form
            ->method('createView')
            ->willReturn($formView);

        $this->translator->expects(static::once())
            ->method('trans')
            ->willReturn('flash message');

        $this->expectDeprecation(sprintf(
            'None of the passed values ("%s") in the "Accept" header when requesting %s %s is supported since sonata-project/admin-bundle 3.82.'
            .' It will result in a response with the status code 406 (Not Acceptable) in 4.0. You must add "application/json".',
            implode('", "', $this->request->getAcceptableContentTypes()),
            $this->request->getMethod(),
            $this->request->getUri()
        ));
        static::assertInstanceOf(Response::class, $response = $this->controller->editAction());
        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/ajax_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);
        static::assertSame('edit', $this->parameters['action']);
        static::assertInstanceOf(FormView::class, $this->parameters['form']);
        static::assertSame($object, $this->parameters['object']);
        static::assertSame([
            'sonata_flash_error' => [0 => 'flash message'],
        ], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testEditActionWithModelManagerException(string $expectedToStringValue, string $toStringValue): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'))
            ->willReturn(true);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $form = $this->createMock(Form::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('getData')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('toString')
            ->with(static::equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_edit_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $form->expects(static::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $this->request->setMethod(Request::METHOD_POST);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $this->assertLoggerLogsModelManagerException($this->admin, 'update');
        static::assertInstanceOf(Response::class, $this->controller->editAction());

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('edit', $this->parameters['action']);
        static::assertInstanceOf(FormView::class, $this->parameters['form']);
        static::assertSame($object, $this->parameters['object']);

        static::assertSame(['sonata_flash_error' => ['flash_edit_error']], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
    }

    public function testEditActionWithPreview(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'))
            ->willReturn(true);

        $form = $this->createMock(Form::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $this->admin->expects(static::once())
            ->method('supportsPreviewMode')
            ->willReturn(true);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $form->expects(static::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('btn_preview', 'Preview');

        static::assertInstanceOf(Response::class, $this->controller->editAction());

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('edit', $this->parameters['action']);
        static::assertInstanceOf(FormView::class, $this->parameters['form']);
        static::assertSame($object, $this->parameters['object']);

        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/preview.html.twig', $this->template);
    }

    public function testEditActionWithLockException(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();
        $class = \get_class($object);

        $this->admin
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('checkAccess')
            ->with(static::equalTo('edit'))
            ->willReturn(true);

        $this->admin
            ->method('getClass')
            ->willReturn($class);

        $form = $this->createMock(Form::class);

        $form
            ->method('isValid')
            ->willReturn(true);

        $form->expects(static::once())
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
            ->will(static::throwException(new LockException()));

        $this->admin
            ->method('toString')
            ->with(static::equalTo($object))
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

        static::assertInstanceOf(Response::class, $this->controller->editAction());
    }

    public function testCreateActionAccessDenied(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'))
            ->will(static::throwException(new AccessDeniedException()));

        $this->expectException(AccessDeniedException::class);

        $this->controller->createAction();
    }

    public function testPreCreate(): void
    {
        $object = new \stdClass();
        $object->foo = 123456;

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'))
            ->willReturn(true);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects(static::once())
            ->method('getNewInstance')
            ->willReturn($object);

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->createAction();
        static::assertInstanceOf(Response::class, $response);
        static::assertSame('preCreate called: 123456', $response->getContent());
    }

    public function testCreateAction(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects(static::once())
            ->method('getNewInstance')
            ->willReturn($object);

        $form = $this->createMock(Form::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        static::assertInstanceOf(Response::class, $this->controller->createAction());

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('create', $this->parameters['action']);
        static::assertInstanceOf(FormView::class, $this->parameters['form']);
        static::assertSame($object, $this->parameters['object']);

        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testCreateActionSuccess(string $expectedToStringValue, string $toStringValue): void
    {
        $object = new \stdClass();

        $this->admin->expects(static::exactly(2))
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

        $this->admin->expects(static::once())
            ->method('hasRoute')
            ->with(static::equalTo('edit'))
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('hasAccess')
            ->with(static::equalTo('edit'))
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('getNewInstance')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('create')
            ->willReturnArgument(0);

        $form = $this->createMock(Form::class);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects(static::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('getData')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('toString')
            ->with(static::equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_create_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);

        $response = $this->controller->createAction();

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame(['flash_create_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        static::assertSame('stdClass_edit', $response->getTargetUrl());
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

        $this->admin->expects(static::once())
            ->method('getNewInstance')
            ->willReturn($object);

        $form = $this->createMock(Form::class);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects(static::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('getData')
            ->willReturn($object);

        $form->expects(static::once())
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
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects(static::once())
            ->method('getNewInstance')
            ->willReturn($object);

        $form = $this->createMock(Form::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects(static::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('isValid')
            ->willReturn(false);

        $this->admin->expects(static::once())
            ->method('toString')
            ->with(static::equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_create_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        static::assertInstanceOf(Response::class, $this->controller->createAction());

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('create', $this->parameters['action']);
        static::assertInstanceOf(FormView::class, $this->parameters['form']);
        static::assertSame($object, $this->parameters['object']);

        static::assertSame(['sonata_flash_error' => ['flash_create_error']], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testCreateActionWithModelManagerException(string $expectedToStringValue, string $toStringValue): void
    {
        $this->admin->expects(static::exactly(2))
            ->method('checkAccess')
            ->with(static::equalTo('create'))
            ->willReturn(true);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getNewInstance')
            ->willReturn($object);

        $form = $this->createMock(Form::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('toString')
            ->with(static::equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_create_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $form->expects(static::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('getData')
            ->willReturn($object);

        $this->request->setMethod(Request::METHOD_POST);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $this->assertLoggerLogsModelManagerException($this->admin, 'create');

        static::assertInstanceOf(Response::class, $this->controller->createAction());

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('create', $this->parameters['action']);
        static::assertInstanceOf(FormView::class, $this->parameters['form']);
        static::assertSame($object, $this->parameters['object']);

        static::assertSame(['sonata_flash_error' => ['flash_create_error']], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
    }

    public function testCreateActionAjaxSuccess(): void
    {
        $object = new \stdClass();

        $this->admin->expects(static::exactly(2))
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

        $this->admin->expects(static::once())
            ->method('getNewInstance')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('create')
            ->willReturnArgument(0);

        $form = $this->createMock(Form::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects(static::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('getData')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects(static::once())
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('foo_normalized');

        $this->admin->expects(static::once())
            ->method('toString')
            ->willReturn('foo');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->createAction();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(json_encode(['result' => 'ok', 'objectId' => 'foo_normalized', 'objectName' => 'foo']), $response->getContent());
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testCreateActionAjaxError(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getNewInstance')
            ->willReturn($object);

        $form = $this->createMock(Form::class);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects(static::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('isValid')
            ->willReturn(false);

        $formError = $this->createMock(FormError::class);
        $formError->expects(static::atLeastOnce())
            ->method('getMessage')
            ->willReturn('Form error message');

        $form->expects(static::once())
            ->method('getErrors')
            ->with(true)
            ->willReturn([$formError]);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->headers->set('Accept', 'application/json');

        static::assertInstanceOf(JsonResponse::class, $response = $this->controller->createAction());
        static::assertJsonStringEqualsJsonString('{"result":"error","errors":["Form error message"]}', $response->getContent());
    }

    /**
     * @legacy
     */
    public function testCreateActionAjaxErrorWithoutAcceptApplicationJson(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getNewInstance')
            ->willReturn($object);

        $form = $this->createMock(Form::class);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects(static::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('isValid')
            ->willReturn(false);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $formView = $this->createMock(FormView::class);
        $form
            ->method('createView')
            ->willReturn($formView);

        $this->translator->expects(static::once())
            ->method('trans')
            ->willReturn('flash message');

        $this->expectDeprecation(sprintf(
            'None of the passed values ("%s") in the "Accept" header when requesting %s %s is supported since sonata-project/admin-bundle 3.82.'
            .' It will result in a response with the status code 406 (Not Acceptable) in 4.0. You must add "application/json".',
            implode('", "', $this->request->getAcceptableContentTypes()),
            $this->request->getMethod(),
            $this->request->getUri()
        ));
        static::assertInstanceOf(Response::class, $response = $this->controller->createAction());
        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/ajax_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);
        static::assertSame('create', $this->parameters['action']);
        static::assertInstanceOf(FormView::class, $this->parameters['form']);
        static::assertSame($object, $this->parameters['object']);
        static::assertSame([
            'sonata_flash_error' => [0 => 'flash message'],
        ], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/edit.html.twig', $this->template);
    }

    public function testCreateActionWithPreview(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getNewInstance')
            ->willReturn($object);

        $form = $this->createMock(Form::class);

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $this->admin->expects(static::once())
            ->method('supportsPreviewMode')
            ->willReturn(true);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $form->expects(static::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('btn_preview', 'Preview');

        static::assertInstanceOf(Response::class, $this->controller->createAction());

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('create', $this->parameters['action']);
        static::assertInstanceOf(FormView::class, $this->parameters['form']);
        static::assertSame($object, $this->parameters['object']);

        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/preview.html.twig', $this->template);
    }

    public function testExportActionAccessDenied(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('export'))
            ->will(static::throwException(new AccessDeniedException()));

        $this->expectException(AccessDeniedException::class);

        $this->controller->exportAction($this->request);
    }

    public function testExportActionWrongFormat(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('export'))
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('getExportFormats')
            ->willReturn(['json']);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->request->query->set('format', 'csv');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Export in format `csv` is not allowed for class: `Foo`. Allowed formats are: `json`'
        );

        $this->controller->exportAction($this->request);
    }

    public function testExportAction(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('export'))
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('getExportFormats')
            ->willReturn(['json']);

        $dataSourceIterator = $this->createMock(SourceIteratorInterface::class);

        $this->admin->expects(static::once())
            ->method('getDataSourceIterator')
            ->willReturn($dataSourceIterator);

        $this->request->query->set('format', 'json');

        $response = $this->controller->exportAction($this->request);
        static::assertInstanceOf(StreamedResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testHistoryActionAccessDenied(): void
    {
        $this->request->query->set('id', 123);

        $this->admin
            ->method('getObject')
            ->willReturn(new \stdClass());

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('history'))
            ->will(static::throwException(new AccessDeniedException()));

        $this->expectException(AccessDeniedException::class);
        $this->controller->historyAction();
    }

    public function testHistoryActionNotFoundException(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->historyAction();
    }

    public function testHistoryActionNoReader(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('history'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects(static::exactly(2))
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects(static::once())
            ->method('hasReader')
            ->with(static::equalTo('Foo'))
            ->willReturn(false);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('unable to find the audit reader for class : Foo');

        $this->controller->historyAction();
    }

    public function testHistoryAction(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('history'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects(static::exactly(2))
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects(static::once())
            ->method('hasReader')
            ->with(static::equalTo('Foo'))
            ->willReturn(true);

        $reader = $this->createMock(AuditReaderInterface::class);

        $this->auditManager->expects(static::once())
            ->method('getReader')
            ->with(static::equalTo('Foo'))
            ->willReturn($reader);

        $reader->expects(static::once())
            ->method('findRevisions')
            ->with(static::equalTo('Foo'), static::equalTo(123))
            ->willReturn([]);

        static::assertInstanceOf(Response::class, $this->controller->historyAction(null));

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('history', $this->parameters['action']);
        static::assertSame([], $this->parameters['revisions']);
        static::assertSame($object, $this->parameters['object']);

        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/history.html.twig', $this->template);
    }

    public function testAclActionAclNotEnabled(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('ACL are not enabled for this admin');

        $this->controller->aclAction();
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testAclTriggerDeprecationWithoutConfiguringUserManager(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::once())
            ->method('isAclEnabled')
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(null);

        $this->container->setParameter('sonata.admin.security.fos_user_autoconfigured', true);

        $this->expectDeprecation('Not configuring "acl_user_manager" and using ACL security handler is deprecated since sonata-project/admin-bundle 3.78 and will not work on 4.0. You MUST specify the service name under "sonata_admin.security.acl_user_manager" option.');

        $this->expectException(NotFoundHttpException::class);

        $this->controller->aclAction();
    }

    public function testAclActionNotFoundException(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::once())
            ->method('isAclEnabled')
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->aclAction();
    }

    public function testAclActionAccessDenied(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::once())
            ->method('isAclEnabled')
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('acl'), static::equalTo($object))
            ->will(static::throwException(new AccessDeniedException()));

        $this->expectException(AccessDeniedException::class);

        $this->controller->aclAction();
    }

    public function testAclAction(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::exactly(2))
            ->method('isAclEnabled')
            ->willReturn(true);

        $object = new DummyDomainObject();

        $this->admin->expects(static::exactly(2))
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

        $aclUsersForm->expects(static::once())
            ->method('createView')
            ->willReturn($this->createMock(FormView::class));

        $aclRolesForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclRolesForm->expects(static::once())
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

        static::assertInstanceOf(Response::class, $this->controller->aclAction(null));

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('acl', $this->parameters['action']);
        static::assertSame([], $this->parameters['permissions']);
        static::assertSame($object, $this->parameters['object']);
        static::assertInstanceOf(\ArrayIterator::class, $this->parameters['users']);
        static::assertInstanceOf(\ArrayIterator::class, $this->parameters['roles']);
        static::assertInstanceOf(FormView::class, $this->parameters['aclUsersForm']);
        static::assertInstanceOf(FormView::class, $this->parameters['aclRolesForm']);

        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/acl.html.twig', $this->template);
    }

    public function testAclActionInvalidUpdate(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);
        $this->request->request->set(AdminObjectAclManipulator::ACL_USERS_FORM_NAME, []);

        $this->admin->expects(static::exactly(2))
            ->method('isAclEnabled')
            ->willReturn(true);

        $object = new DummyDomainObject();

        $this->admin->expects(static::exactly(2))
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

        $aclUsersForm->expects(static::once())
            ->method('isValid')
            ->willReturn(false);

        $aclUsersForm->expects(static::once())
            ->method('createView')
            ->willReturn($this->createMock(FormView::class));

        $aclRolesForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aclRolesForm->expects(static::once())
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

        static::assertInstanceOf(Response::class, $this->controller->aclAction());

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('acl', $this->parameters['action']);
        static::assertSame([], $this->parameters['permissions']);
        static::assertSame($object, $this->parameters['object']);
        static::assertInstanceOf(\ArrayIterator::class, $this->parameters['users']);
        static::assertInstanceOf(\ArrayIterator::class, $this->parameters['roles']);
        static::assertInstanceOf(FormView::class, $this->parameters['aclUsersForm']);
        static::assertInstanceOf(FormView::class, $this->parameters['aclRolesForm']);

        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/acl.html.twig', $this->template);
    }

    public function testAclActionSuccessfulUpdate(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);
        $this->request->request->set(AdminObjectAclManipulator::ACL_ROLES_FORM_NAME, []);

        $this->admin->expects(static::exactly(2))
            ->method('isAclEnabled')
            ->willReturn(true);

        $object = new DummyDomainObject();

        $this->admin->expects(static::exactly(2))
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

        $aclRolesForm->expects(static::once())
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

        $response = $this->controller->aclAction();

        static::assertInstanceOf(RedirectResponse::class, $response);

        static::assertSame(['flash_acl_edit_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        static::assertSame(sprintf('%s_acl', DummyDomainObject::class), $response->getTargetUrl());
    }

    public function testHistoryViewRevisionActionAccessDenied(): void
    {
        $this->request->query->set('id', 123);

        $this->admin
            ->method('getObject')
            ->willReturn(new \stdClass());

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyViewRevision'))
            ->will(static::throwException(new AccessDeniedException()));

        $this->expectException(AccessDeniedException::class);

        $this->controller->historyViewRevisionAction();
    }

    public function testHistoryViewRevisionActionNotFoundException(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(null);

        $this->admin->expects(static::once())
            ->method('getClassnameLabel')
            ->willReturn('MyObjectWithRevisions');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Unable to find MyObjectWithRevisions object with id: 123.');

        $this->controller->historyViewRevisionAction();
    }

    public function testHistoryViewRevisionActionNoReader(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyViewRevision'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects(static::exactly(2))
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects(static::once())
            ->method('hasReader')
            ->with(static::equalTo('Foo'))
            ->willReturn(false);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('unable to find the audit reader for class : Foo');

        $this->controller->historyViewRevisionAction();
    }

    public function testHistoryViewRevisionActionNotFoundRevision(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyViewRevision'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects(static::exactly(2))
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects(static::once())
            ->method('hasReader')
            ->with(static::equalTo('Foo'))
            ->willReturn(true);

        $reader = $this->createMock(AuditReaderInterface::class);

        $this->auditManager->expects(static::once())
            ->method('getReader')
            ->with(static::equalTo('Foo'))
            ->willReturn($reader);

        $reader->expects(static::once())
            ->method('find')
            ->with(static::equalTo('Foo'), static::equalTo(123), static::equalTo(456))
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(
            'unable to find the targeted object `123` from the revision `456` with classname : `Foo`'
        );

        $this->controller->historyViewRevisionAction(123, 456);
    }

    public function testHistoryViewRevisionAction(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyViewRevision'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects(static::exactly(2))
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects(static::once())
            ->method('hasReader')
            ->with(static::equalTo('Foo'))
            ->willReturn(true);

        $reader = $this->createMock(AuditReaderInterface::class);

        $this->auditManager->expects(static::once())
            ->method('getReader')
            ->with(static::equalTo('Foo'))
            ->willReturn($reader);

        $objectRevision = new \stdClass();
        $objectRevision->revision = 456;

        $reader->expects(static::once())
            ->method('find')
            ->with(static::equalTo('Foo'), static::equalTo(123), static::equalTo(456))
            ->willReturn($objectRevision);

        $this->admin->expects(static::once())
            ->method('setSubject')
            ->with(static::equalTo($objectRevision))
            ->willReturn(null);

        $fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin->expects(static::once())
            ->method('getShow')
            ->willReturn($fieldDescriptionCollection);

        static::assertInstanceOf(Response::class, $this->controller->historyViewRevisionAction(123, 456));

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('show', $this->parameters['action']);
        static::assertSame($objectRevision, $this->parameters['object']);
        static::assertSame($fieldDescriptionCollection, $this->parameters['elements']);

        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/show.html.twig', $this->template);
    }

    public function testHistoryCompareRevisionsActionAccessDenied(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyCompareRevisions'))
            ->will(static::throwException(new AccessDeniedException()));

        $this->expectException(AccessDeniedException::class);

        $this->controller->historyCompareRevisionsAction();
    }

    public function testHistoryCompareRevisionsActionNotFoundException(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyCompareRevisions'))
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(null);

        $this->admin->expects(static::once())
            ->method('getClassnameLabel')
            ->willReturn('MyObjectWithRevisions');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Unable to find MyObjectWithRevisions object with id: 123.');

        $this->controller->historyCompareRevisionsAction();
    }

    public function testHistoryCompareRevisionsActionNoReader(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyCompareRevisions'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects(static::once())
            ->method('hasReader')
            ->with(static::equalTo('Foo'))
            ->willReturn(false);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('unable to find the audit reader for class : Foo');

        $this->controller->historyCompareRevisionsAction();
    }

    public function testHistoryCompareRevisionsActionNotFoundBaseRevision(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyCompareRevisions'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects(static::once())
            ->method('hasReader')
            ->with(static::equalTo('Foo'))
            ->willReturn(true);

        $reader = $this->createMock(AuditReaderInterface::class);

        $this->auditManager->expects(static::once())
            ->method('getReader')
            ->with(static::equalTo('Foo'))
            ->willReturn($reader);

        // once because it will not be found and therefore the second call won't be executed
        $reader->expects(static::once())
            ->method('find')
            ->with(static::equalTo('Foo'), static::equalTo(123), static::equalTo(456))
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(
            'unable to find the targeted object `123` from the revision `456` with classname : `Foo`'
        );

        $this->controller->historyCompareRevisionsAction(123, 456, 789);
    }

    public function testHistoryCompareRevisionsActionNotFoundCompareRevision(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyCompareRevisions'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects(static::once())
            ->method('hasReader')
            ->with(static::equalTo('Foo'))
            ->willReturn(true);

        $reader = $this->createMock(AuditReaderInterface::class);

        $this->auditManager->expects(static::once())
            ->method('getReader')
            ->with(static::equalTo('Foo'))
            ->willReturn($reader);

        $objectRevision = new \stdClass();
        $objectRevision->revision = 456;

        // first call should return, so the second call will throw an exception
        $reader->expects(static::exactly(2))->method('find')->willReturnMap([
            ['Foo', 123, 456, $objectRevision],
            ['Foo', 123, 789, null],
        ]);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(
            'unable to find the targeted object `123` from the revision `789` with classname : `Foo`'
        );

        $this->controller->historyCompareRevisionsAction(123, 456, 789);
    }

    public function testHistoryCompareRevisionsActionAction(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyCompareRevisions'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects(static::once())
            ->method('hasReader')
            ->with(static::equalTo('Foo'))
            ->willReturn(true);

        $reader = $this->createMock(AuditReaderInterface::class);

        $this->auditManager->expects(static::once())
            ->method('getReader')
            ->with(static::equalTo('Foo'))
            ->willReturn($reader);

        $objectRevision = new \stdClass();
        $objectRevision->revision = 456;

        $compareObjectRevision = new \stdClass();
        $compareObjectRevision->revision = 789;

        $reader->expects(static::exactly(2))->method('find')->willReturnMap([
            ['Foo', 123, 456, $objectRevision],
            ['Foo', 123, 789, $compareObjectRevision],
        ]);

        $this->admin->expects(static::once())
            ->method('setSubject')
            ->with(static::equalTo($objectRevision))
            ->willReturn(null);

        $fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin->expects(static::once())
            ->method('getShow')
            ->willReturn($fieldDescriptionCollection);

        static::assertInstanceOf(Response::class, $this->controller->historyCompareRevisionsAction(123, 456, 789));

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('show', $this->parameters['action']);
        static::assertSame($objectRevision, $this->parameters['object']);
        static::assertSame($compareObjectRevision, $this->parameters['object_compare']);
        static::assertSame($fieldDescriptionCollection, $this->parameters['elements']);

        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/show_compare.html.twig', $this->template);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testHistoryCompareRevisionsActionWithDeprecatedRouteParams()
    {
        $subjectId = 123;
        $baseRevision = 456;
        $compareRevision = 789;

        $this->request->query->set('id', $subjectId);
        $this->request->attributes->set('base_revision', $baseRevision);
        $this->request->attributes->set('compare_revision', $compareRevision);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyCompareRevisions'))
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->auditManager->expects(static::once())
            ->method('hasReader')
            ->with(static::equalTo('Foo'))
            ->willReturn(true);

        $reader = $this->createMock(AuditReaderInterface::class);

        $this->auditManager->expects(static::once())
            ->method('getReader')
            ->with(static::equalTo('Foo'))
            ->willReturn($reader);

        $objectRevision = new \stdClass();
        $objectRevision->revision = $baseRevision;

        $compareObjectRevision = new \stdClass();
        $compareObjectRevision->revision = $compareRevision;

        $reader->expects(static::exactly(2))->method('find')->willReturnMap([
            ['Foo', $subjectId, $baseRevision, $objectRevision],
            ['Foo', $subjectId, $compareRevision, $compareObjectRevision],
        ]);

        $this->admin->expects(static::once())
            ->method('setSubject')
            ->with(static::equalTo($objectRevision));

        $fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin->expects(static::once())
            ->method('getShow')
            ->willReturn($fieldDescriptionCollection);

        $this->expectDeprecation(
            'Route parameter "base_revision" for action "Sonata\AdminBundle\Controller\CRUDController::historyCompareRevisionsAction()"'.
            ' is deprecated since sonata-project/admin-bundle 3.92. Use "baseRevision" parameter instead.'
        );

        $this->expectDeprecation(
            'Route parameter "compare_revision" for action "Sonata\AdminBundle\Controller\CRUDController::historyCompareRevisionsAction()"'.
            ' is deprecated since sonata-project/admin-bundle 3.92. Use "compareRevision" parameter instead.'
        );

        static::assertInstanceOf(Response::class, $this->controller->historyCompareRevisionsAction());
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

        $this->admin->expects(static::once())
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
            static::assertSame('The csrf token is not valid, CSRF attack?', $e->getMessage());
            static::assertSame(400, $e->getStatusCode());
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

        $this->admin->expects(static::once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $datagrid = $this->createMock(DatagridInterface::class);
        $this->admin->expects(static::once())
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

        $this->admin->expects(static::once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects(static::once())
            ->method('getQuery')
            ->willReturn($query);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('batchDelete'))
            ->willReturn(true);

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $modelManager->expects(static::once())
            ->method('addIdentifiersToQuery')
            ->with(static::equalTo('Foo'), static::equalTo($query), static::equalTo(['123', '456']))
            ->willReturn(true);

        $this->expectTranslate('flash_batch_delete_success', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode(['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        static::assertNull($this->request->get('idx'));

        $result = $this->controller->batchAction();

        static::assertNull($this->request->get('idx'), 'Ensure original request is not modified by calling `CRUDController::batchAction()`.');
        static::assertInstanceOf(RedirectResponse::class, $result);
        static::assertSame(['flash_batch_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        static::assertSame('list', $result->getTargetUrl());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionWithoutConfirmation2(): void
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects(static::once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects(static::once())
            ->method('getQuery')
            ->willReturn($query);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('batchDelete'))
            ->willReturn(true);

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $modelManager->expects(static::once())
            ->method('addIdentifiersToQuery')
            ->with(static::equalTo('Foo'), static::equalTo($query), static::equalTo(['123', '456']))
            ->willReturn(true);

        $this->expectTranslate('flash_batch_delete_success', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('action', 'delete');
        $this->request->request->set('idx', ['123', '456']);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction();

        static::assertInstanceOf(RedirectResponse::class, $result);
        static::assertSame(['flash_batch_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        static::assertSame('list', $result->getTargetUrl());
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

        $this->admin->expects(static::once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode($data));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects(static::once())
            ->method('createView')
            ->willReturn($this->createMock(FormView::class));

        $datagrid->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        static::assertInstanceOf(Response::class, $this->controller->batchAction());

        static::assertSame($this->admin, $this->parameters['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);
        // NEXT_MAJOR: Remove next line.
        static::assertSame($this->pool, $this->parameters['admin_pool']);

        static::assertSame('list', $this->parameters['action']);
        static::assertSame($datagrid, $this->parameters['datagrid']);
        static::assertInstanceOf(FormView::class, $this->parameters['form']);
        static::assertSame($data, $this->parameters['data']);
        static::assertSame('csrf-token-123_sonata.batch', $this->parameters['csrf_token']);
        static::assertSame('Foo Bar', $this->parameters['action_label']);

        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame('@SonataAdmin/CRUD/batch_confirmation.html.twig', $this->template);
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

        $this->admin->expects(static::once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $this->expectTranslate('flash_batch_empty', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('action', $actionName);
        $this->request->request->set('idx', ['789']);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        static::assertNull($this->request->get('all_elements'));

        $result = $controller->batchAction();

        static::assertNull($this->request->get('all_elements'), 'Ensure original request is not modified by calling `CRUDController::batchAction()`.');
        static::assertInstanceOf(RedirectResponse::class, $result);
        static::assertSame(['flash_batch_empty'], $this->session->getFlashBag()->get('sonata_flash_info'));
        static::assertSame('list', $result->getTargetUrl());
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

        $this->admin->expects(static::once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $data = ['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => false];

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode($data));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $form = $this->createMock(Form::class);

        $form->expects(static::once())
            ->method('createView')
            ->willReturn($this->createMock(FormView::class));

        $datagrid->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $this->controller->batchAction();

        static::assertSame('custom_template.html.twig', $this->template);
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

        $this->admin->expects(static::once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $this->expectTranslate('flash_foo_error', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('action', 'foo');
        $this->request->request->set('idx', ['999']);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $controller->batchAction();

        static::assertInstanceOf(RedirectResponse::class, $result);
        static::assertSame(['flash_foo_error'], $this->session->getFlashBag()->get('sonata_flash_info'));
        static::assertSame('list', $result->getTargetUrl());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionNoItems(): void
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => true]];

        $this->admin->expects(static::once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $this->expectTranslate('flash_batch_empty', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('action', 'delete');
        $this->request->request->set('idx', []);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction();

        static::assertInstanceOf(RedirectResponse::class, $result);
        static::assertSame(['flash_batch_empty'], $this->session->getFlashBag()->get('sonata_flash_info'));
        static::assertSame('list', $result->getTargetUrl());
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

        $this->admin->expects(static::once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects(static::once())
            ->method('getQuery')
            ->willReturn($query);

        $this->admin->expects(static::once())
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

        static::assertInstanceOf(Response::class, $result);
        static::assertMatchesRegularExpression('/Redirecting to list/', $result->getContent());
    }

    /**
     * NEXT_MAJOR: Remove this legacy group.
     *
     * @group legacy
     */
    public function testBatchActionWithRequesData(): void
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects(static::once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects(static::once())
            ->method('getQuery')
            ->willReturn($query);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('batchDelete'))
            ->willReturn(true);

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $modelManager->expects(static::once())
            ->method('addIdentifiersToQuery')
            ->with(static::equalTo('Foo'), static::equalTo($query), static::equalTo(['123', '456']))
            ->willReturn(true);

        $this->expectTranslate('flash_batch_delete_success', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode(['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('foo', 'bar');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction();

        static::assertInstanceOf(RedirectResponse::class, $result);
        static::assertSame(['flash_batch_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        static::assertSame('list', $result->getTargetUrl());
        static::assertSame('bar', $this->request->request->get('foo'));
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

        $subject->expects(static::once())
            ->method($method)
            ->willReturnCallback(static function () use ($exception): void {
                throw $exception;
            });

        $this->logger->expects(static::once())
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
        $this->translator->expects(static::once())
            ->method('trans')
            ->with(static::equalTo($id), static::equalTo($parameters), static::equalTo($domain), static::equalTo($locale))
            ->willReturn($id);
    }
}
