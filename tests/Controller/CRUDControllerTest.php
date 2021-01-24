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
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilder;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Bridge\Exporter\AdminExporter;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Model\AuditManagerInterface;
use Sonata\AdminBundle\Model\AuditReaderInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Sonata\AdminBundle\Tests\Fixtures\Admin\PostAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Controller\BatchAdminController;
use Sonata\AdminBundle\Tests\Fixtures\Controller\PreCRUDController;
use Sonata\AdminBundle\Tests\Fixtures\Util\DummyDomainObject;
use Sonata\AdminBundle\Util\AdminObjectAclManipulator;
use Sonata\Exporter\Exporter;
use Sonata\Exporter\Source\SourceIteratorInterface;
use Sonata\Exporter\Writer\JsonWriter;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
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
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var MutableTemplateRegistryInterface
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
     * @var AuditManagerInterface
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
        $this->admin = $this->createMock(AdminInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->parameters = [];
        $this->template = '';

        $this->templateRegistry = $this->createStub(MutableTemplateRegistryInterface::class);

        $templatingRenderReturnCallback = $this->returnCallback(function (
            string $name,
            array $context = []
        ): string {
            $this->template = $name;

            $this->parameters = $context;

            return '';
        });

        $this->session = new Session(new MockArraySessionStorage());

        $twig = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twig
            ->method('getRuntime')
            ->willReturn($this->createMock(FormRenderer::class));

        $twig
            ->method('render')
            ->will($templatingRenderReturnCallback);

        $exporter = new Exporter([new JsonWriter(sys_get_temp_dir().'/sonataadmin/export.json')]);

        $adminExporter = new AdminExporter($exporter);

        $this->auditManager = $this->createMock(AuditManagerInterface::class);

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

        $this->parameterBag = new ParameterBag();

        $this->container->set('sonata.admin.pool', $this->pool);
        $this->container->set('request_stack', $requestStack);
        $this->container->set('foo.admin', $this->admin);
        $this->container->set('twig', $twig);
        $this->container->set('session', $this->session);
        $this->container->set('sonata.exporter.exporter', $exporter);
        $this->container->set('sonata.admin.admin_exporter', $adminExporter);
        $this->container->set('sonata.admin.audit.manager', $this->auditManager);
        $this->container->set('sonata.admin.object.manipulator.acl.admin', $this->adminObjectAclManipulator);
        $this->container->set('security.csrf.token_manager', $this->csrfProvider);
        $this->container->set('logger', $this->logger);
        $this->container->set('translator', $this->translator);
        $this->container->set('sonata.admin.breadcrumbs_builder', new BreadcrumbsBuilder([]));
        $this->container->set('parameter_bag', $this->parameterBag);

        $this->parameterBag->set(
            'security.role_hierarchy.roles',
            ['ROLE_SUPER_ADMIN' => ['ROLE_USER', 'ROLE_SONATA_ADMIN', 'ROLE_ADMIN']]
        );
        $this->parameterBag->set('kernel.debug', false);

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

        $this->admin->method('getIdParameter')->willReturn('id');
        $this->admin->method('getAccessMapping')->willReturn([]);
        $this->admin->method('getCode')->willReturn('foo.admin');
        $this->admin->method('hasTemplateRegistry')->willReturn(true);
        $this->admin->method('getTemplateRegistry')->willReturn($this->templateRegistry);

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
            $method = new \ReflectionMethod(CRUDController::class, $testedMethod);
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
            ->willReturnCallback(static function (string $uniqid) use (&$uniqueId): void {
                $uniqueId = $uniqid;
            });

        $this->request->query->set('uniqid', '123456');
        $this->controller->configureAdmin($this->request);

        $this->assertSame('123456', $uniqueId);
    }

    public function testConfigureAdminChild(): void
    {
        $uniqueId = '';

        $this->admin->expects($this->once())
            ->method('setUniqid')
            ->willReturnCallback(static function (string $uniqid) use (&$uniqueId): void {
                $uniqueId = $uniqid;
            });

        $this->admin->expects($this->once())
            ->method('isChild')
            ->willReturn(true);

        $adminParent = $this->getMockBuilder(AdminInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->admin->expects($this->once())
            ->method('getParent')
            ->willReturn($adminParent);

        $this->request->query->set('uniqid', '123456');
        $this->controller->configureAdmin($this->request);

        $this->assertSame('123456', $uniqueId);
    }

    public function testConfigureAdminWithException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            'There is no `_sonata_admin` defined for the controller `Sonata\AdminBundle\Controller\CRUDController`'
        );

        $this->request->attributes->remove('_sonata_admin');
        $this->controller->configureAdmin($this->request);
    }

    public function testConfigureAdminWithException2(): void
    {
        $this->request->attributes->set('_sonata_admin', 'nonexistent.admin');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to find the admin class related to the current controller (Sonata\AdminBundle\Controller\CRUDController)');

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

        $this->controller->listAction($this->request);
    }

    public function testPreList(): void
    {
        $this->admin
            ->method('hasRoute')
            ->with($this->equalTo('list'))
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('list'));

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->listAction($this->request);
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
            ->with($this->equalTo('list'));

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
        $this->assertInstanceOf(Response::class, $this->controller->listAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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
            ->with($this->equalTo('batchDelete'));

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

        $this->parameterBag->set('kernel.debug', true);

        $this->controller->batchActionDelete($this->createMock(ProxyQueryInterface::class));
    }

    public function testShowActionNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn(null);

        $this->controller->showAction($this->request);
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

        $this->controller->showAction($this->request);
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
            ->with($this->equalTo('show'));

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->showAction($this->request);
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
            ->with($this->equalTo('show'));

        $show = new FieldDescriptionCollection();

        $this->admin->expects($this->once())
            ->method('getShow')
            ->willReturn($show);

        $this->assertInstanceOf(Response::class, $this->controller->showAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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
            ->willReturn(null);

        $this->controller->deleteAction($this->request);
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

        $this->controller->deleteAction($this->request);
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
            ->with($this->equalTo('delete'));

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->deleteAction($this->request);
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
            ->with($this->equalTo('delete'));

        $this->assertInstanceOf(Response::class, $this->controller->deleteAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

        $this->assertSame('delete', $this->parameters['action']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertSame('csrf-token-123_sonata.delete', $this->parameters['csrf_token']);

        $this->assertSame([], $this->session->getFlashBag()->all());
        $this->assertSame('@SonataAdmin/CRUD/delete.html.twig', $this->template);
    }

    public function testDeleteActionChildNoConnectedException(): void
    {
        $object = new \stdClass();
        $object->parent = 'test';

        $object2 = new \stdClass();

        $admin = $this->createMock(PostAdmin::class);
        $admin->method('getIdParameter')->willReturn('parent_id');

        $admin->expects($this->exactly(2))
            ->method('getObject')
            ->willReturn($object2);

        $admin->expects($this->once())
            ->method('toString')
            ->willReturn('parentObject');

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('isChild')
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('getParent')
            ->willReturn($admin);

        $this->admin->expects($this->once())
            ->method('getParentAssociationMapping')
            ->willReturn('parent');

        $this->admin->expects($this->once())
            ->method('toString')
            ->willReturn('childObject');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('There is no association between "parentObject" and "childObject"');

        $this->controller->deleteAction($this->request, 1);
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
            ->with($this->equalTo('delete'));

        $this->assertInstanceOf(Response::class, $this->controller->deleteAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

        $this->assertSame('delete', $this->parameters['action']);
        $this->assertSame($object, $this->parameters['object']);
        $this->assertNull($this->parameters['csrf_token']);

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
            ->with($this->equalTo('delete'));

        $this->request->setMethod(Request::METHOD_DELETE);

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction($this->request);

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
            ->with($this->equalTo('delete'));

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('_method', Request::METHOD_DELETE);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        Request::enableHttpMethodParameterOverride();

        $response = $this->controller->deleteAction($this->request);

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
            ->with($this->equalTo('delete'));

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->assertLoggerLogsModelManagerException($this->admin, 'delete');

        $this->request->setMethod(Request::METHOD_DELETE);

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->deleteAction($this->request);

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
            ->with($this->equalTo('delete'));

        $this->admin->expects($this->once())
            ->method('delete')
            ->willReturnCallback(static function (): void {
                throw new ModelManagerException();
            });

        $this->parameterBag->set('kernel.debug', true);

        $this->request->setMethod(Request::METHOD_DELETE);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $this->controller->deleteAction($this->request);
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
            ->with($this->equalTo('delete'));

        $this->request->setMethod(Request::METHOD_DELETE);

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction($this->request);

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
            ->with($this->equalTo('delete'));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_delete_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('_method', Request::METHOD_DELETE);

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        Request::enableHttpMethodParameterOverride();

        $response = $this->controller->deleteAction($this->request);

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
            ->with($this->equalTo('delete'));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_delete_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('_method', Request::METHOD_DELETE);

        Request::enableHttpMethodParameterOverride();

        $response = $this->controller->deleteAction($this->request);

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
            ->with($this->equalTo('delete'));

        //without POST request parameter "_method" should not be used as real REST method
        $this->request->query->set('_method', Request::METHOD_DELETE);

        Request::enableHttpMethodParameterOverride();

        $this->assertInstanceOf(Response::class, $this->controller->deleteAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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
            ->with($this->equalTo('delete'));

        $this->admin->expects($this->once())
            ->method('toString')
            ->with($this->equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_delete_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->assertLoggerLogsModelManagerException($this->admin, 'delete');

        $this->request->setMethod(Request::METHOD_DELETE);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction($this->request);

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
            ->with($this->equalTo('delete'));

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('_method', Request::METHOD_DELETE);
        $this->request->request->set('_sonata_csrf_token', 'CSRF-INVALID');

        Request::enableHttpMethodParameterOverride();

        try {
            $this->controller->deleteAction($this->request);
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
            ->willReturn(null);

        $this->controller->editAction($this->request);
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

        $this->controller->editAction($this->request);
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
            ->with($this->equalTo('edit'));

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->editAction($this->request);
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
            ->with($this->equalTo('edit'));

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $this->assertInstanceOf(Response::class, $this->controller->editAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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

        $response = $this->controller->editAction($this->request);

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
            ->with($this->equalTo('edit'));

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

        $this->assertInstanceOf(Response::class, $this->controller->editAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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
            ->with($this->equalTo('edit'));

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
        $this->request->headers->set('Accept', 'application/json');

        $response = $this->controller->editAction($this->request);

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
            ->with($this->equalTo('edit'));

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

        $this->assertInstanceOf(JsonResponse::class, $response = $this->controller->editAction($this->request));
        $this->assertJsonStringEqualsJsonString('{"result":"error","errors":["Form error message"]}', $response->getContent());
    }

    public function testEditActionAjaxErrorWithoutAcceptApplicationJson(): void
    {
        $object = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('edit'));

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

        $this->assertInstanceOf(Response::class, $response = $this->controller->editAction($this->request));
        $this->assertSame(Response::HTTP_NOT_ACCEPTABLE, $response->getStatusCode());
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
            ->with($this->equalTo('edit'));

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
        $this->assertInstanceOf(Response::class, $this->controller->editAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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
            ->with($this->equalTo('edit'));

        $form = $this->createMock(Form::class);

        $this->admin->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $this->admin->expects($this->once())
            ->method('supportsPreviewMode')
            ->willReturn(true);

        $this->admin->method('getShow')->willReturn(new FieldDescriptionCollection());

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

        $this->assertInstanceOf(Response::class, $this->controller->editAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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
            ->with($this->equalTo('edit'));

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

        $this->assertInstanceOf(Response::class, $this->controller->editAction($this->request));
    }

    public function testCreateActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->controller->createAction($this->request);
    }

    public function testPreCreate(): void
    {
        $object = new \stdClass();
        $object->foo = 123456;

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'));

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($object);

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);

        $response = $controller->createAction($this->request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('preCreate called: 123456', $response->getContent());
    }

    public function testCreateAction(): void
    {
        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'));

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

        $this->assertInstanceOf(Response::class, $this->controller->createAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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
            ->willReturnCallback(static function (string $name, $objectIn = null) use ($object): void {
                if ('edit' === $name) {
                    return;
                }

                if ('create' !== $name) {
                    throw new AccessDeniedException();
                }

                if (null === $objectIn) {
                    return;
                }

                if ($objectIn !== $object) {
                    throw new AccessDeniedException();
                }
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

        $response = $this->controller->createAction($this->request);

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
            ->willReturnCallback(static function (string $name, $object = null): void {
                if ('create' !== $name) {
                    throw new AccessDeniedException();
                }
                if (null === $object) {
                    return;
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

        $this->controller->createAction($this->request);
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testCreateActionError(string $expectedToStringValue, string $toStringValue): void
    {
        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'));

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

        $this->assertInstanceOf(Response::class, $this->controller->createAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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
            ->with($this->equalTo('create'));

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

        $this->assertInstanceOf(Response::class, $this->controller->createAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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
            ->willReturnCallback(static function (string $name, $objectIn = null) use ($object): void {
                if ('create' !== $name) {
                    throw new AccessDeniedException();
                }

                if (null === $objectIn) {
                    return;
                }

                if ($objectIn !== $object) {
                    throw new AccessDeniedException();
                }
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
        $this->request->headers->set('Accept', 'application/json');

        $response = $this->controller->createAction($this->request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(json_encode(['result' => 'ok', 'objectId' => 'foo_normalized', 'objectName' => 'foo']), $response->getContent());
        $this->assertSame([], $this->session->getFlashBag()->all());
    }

    public function testCreateActionAjaxError(): void
    {
        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'));

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

        $this->assertInstanceOf(JsonResponse::class, $response = $this->controller->createAction($this->request));
        $this->assertJsonStringEqualsJsonString('{"result":"error","errors":["Form error message"]}', $response->getContent());
    }

    public function testCreateActionAjaxErrorWithoutAcceptApplicationJson(): void
    {
        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'));

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

        $this->assertInstanceOf(Response::class, $response = $this->controller->createAction($this->request));
        $this->assertSame(Response::HTTP_NOT_ACCEPTABLE, $response->getStatusCode());
    }

    public function testCreateActionWithPreview(): void
    {
        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('create'));

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

        $this->admin->method('getShow')->willReturn(new FieldDescriptionCollection());

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

        $this->assertInstanceOf(Response::class, $this->controller->createAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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
            ->with($this->equalTo('export'));

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
            ->with($this->equalTo('export'));

        $this->admin->expects($this->once())
            ->method('getExportFormats')
            ->willReturn(['json']);

        $this->admin->expects($this->once())
            ->method('getClass')
            ->willReturn(\stdClass::class);

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

        $this->controller->historyAction($this->request);
    }

    public function testHistoryActionNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn(null);

        $this->controller->historyAction($this->request);
    }

    public function testHistoryActionNoReader(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('unable to find the audit reader for class : Foo');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('history'));

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

        $this->controller->historyAction($this->request);
    }

    public function testHistoryAction(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('history'));

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

        $this->assertInstanceOf(Response::class, $this->controller->historyAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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

        $this->controller->aclAction($this->request);
    }

    public function testAclActionNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->admin->expects($this->once())
            ->method('isAclEnabled')
            ->willReturn(true);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn(null);

        $this->controller->aclAction($this->request);
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

        $this->controller->aclAction($this->request);
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
            ->expects($this->once())
            ->method('checkAccess');

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

        $aclSecurityHandler = $this->createStub(AclSecurityHandlerInterface::class);
        $aclSecurityHandler
            ->method('getObjectPermissions')
            ->willReturn([]);

        $aclSecurityHandler
            ->method('createAcl')
            ->willReturn($this->createStub(MutableAclInterface::class));

        $this->admin
            ->method('getSecurityHandler')
            ->willReturn($aclSecurityHandler);

        $this->assertInstanceOf(Response::class, $this->controller->aclAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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
            ->expects($this->once())
            ->method('checkAccess');

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

        $aclSecurityHandler = $this->createStub(AclSecurityHandlerInterface::class);
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

        $this->assertInstanceOf(Response::class, $this->controller->aclAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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
            ->expects($this->once())
            ->method('checkAccess');

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

        $aclSecurityHandler = $this->createStub(AclSecurityHandlerInterface::class);
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

        $response = $this->controller->aclAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertSame(['flash_acl_edit_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame(sprintf('%s_acl', DummyDomainObject::class), $response->getTargetUrl());
    }

    public function testHistoryViewRevisionActionAccessDenied(): void
    {
        $this->admin
            ->method('getObject')
            ->willReturn(new \stdClass());

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyViewRevision'))
            ->will($this->throwException(new AccessDeniedException()));

        $this->expectException(AccessDeniedException::class);

        $this->controller->historyViewRevisionAction($this->request, 'fooRevision');
    }

    public function testHistoryViewRevisionActionNotFoundException(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('unable to find the object with id: 123');

        $this->controller->historyViewRevisionAction($this->request, 'fooRevision');
    }

    public function testHistoryViewRevisionActionNoReader(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('unable to find the audit reader for class : Foo');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyViewRevision'));

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

        $this->controller->historyViewRevisionAction($this->request, 'fooRevision');
    }

    public function testHistoryViewRevisionActionNotFoundRevision(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(
            'unable to find the targeted object `123` from the revision `fooRevision` with classname : `Foo`'
        );

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyViewRevision'));

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
            ->with($this->equalTo('Foo'), $this->equalTo(123), $this->equalTo('fooRevision'))
            ->willReturn(null);

        $this->controller->historyViewRevisionAction($this->request, 'fooRevision');
    }

    public function testHistoryViewRevisionAction(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyViewRevision'));

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
        $objectRevision->revision = 'fooRevision';

        $reader->expects($this->once())
            ->method('find')
            ->with($this->equalTo('Foo'), $this->equalTo(123), $this->equalTo('fooRevision'))
            ->willReturn($objectRevision);

        $this->admin->expects($this->once())
            ->method('setSubject')
            ->with($this->equalTo($objectRevision));

        $fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin->expects($this->once())
            ->method('getShow')
            ->willReturn($fieldDescriptionCollection);

        $this->assertInstanceOf(Response::class, $this->controller->historyViewRevisionAction($this->request, 'fooRevision'));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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

        $this->controller->historyCompareRevisionsAction($this->request, 'fooBaseRevision', 'fooCompareRevision');
    }

    public function testHistoryCompareRevisionsActionNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('unable to find the object with id: 123');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'));

        $this->admin->expects($this->once())
            ->method('getObject')
            ->willReturn(null);

        $this->controller->historyCompareRevisionsAction($this->request, 'fooBaseRevision', 'fooCompareRevision');
    }

    public function testHistoryCompareRevisionsActionNoReader(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('unable to find the audit reader for class : Foo');

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'));

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

        $this->controller->historyCompareRevisionsAction($this->request, 'fooBaseRevision', 'fooCompareRevision');
    }

    public function testHistoryCompareRevisionsActionNotFoundBaseRevision(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(
            'unable to find the targeted object `123` from the revision `fooBaseRevision` with classname : `Foo`'
        );

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'));

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
            ->with($this->equalTo('Foo'), $this->equalTo(123), $this->equalTo('fooBaseRevision'))
            ->willReturn(null);

        $this->controller->historyCompareRevisionsAction($this->request, 'fooBaseRevision', 'fooCompareRevision');
    }

    public function testHistoryCompareRevisionsActionNotFoundCompareRevision(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(
            'unable to find the targeted object `123` from the revision `fooCompareRevision` with classname : `Foo`'
        );

        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'));

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
        $objectRevision->revision = 'fooBaseRevision';

        // first call should return, so the second call will throw an exception
        $reader->expects($this->exactly(2))->method('find')->willReturnMap([
            ['Foo', 123, 'fooBaseRevision', $objectRevision],
            ['Foo', 123, 'fooCompareRevision', null],
        ]);

        $this->controller->historyCompareRevisionsAction($this->request, 'fooBaseRevision', 'fooCompareRevision');
    }

    public function testHistoryCompareRevisionsActionAction(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo('historyCompareRevisions'));

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
        $objectRevision->revision = 'fooBaseRevision';

        $compareObjectRevision = new \stdClass();
        $compareObjectRevision->revision = 'fooCompareRevision';

        $reader->expects($this->exactly(2))->method('find')->willReturnMap([
            ['Foo', 123, 'fooBaseRevision', $objectRevision],
            ['Foo', 123, 'fooCompareRevision', $compareObjectRevision],
        ]);

        $this->admin->expects($this->once())
            ->method('setSubject')
            ->with($this->equalTo($objectRevision));

        $fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin->expects($this->once())
            ->method('getShow')
            ->willReturn($fieldDescriptionCollection);

        $this->assertInstanceOf(Response::class, $this->controller->historyCompareRevisionsAction($this->request, 'fooBaseRevision', 'fooCompareRevision'));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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

        $this->controller->batchAction($this->request);
    }

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

        $this->controller->batchAction($this->request);
    }

    public function testBatchActionActionInvalidCsrfToken(): void
    {
        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode(['action' => 'foo', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('_sonata_csrf_token', 'CSRF-INVALID');

        try {
            $this->controller->batchAction($this->request);
        } catch (HttpException $e) {
            $this->assertSame('The csrf token is not valid, CSRF attack?', $e->getMessage());
            $this->assertSame(400, $e->getStatusCode());
        }
    }

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

        $this->controller->batchAction($this->request);
    }

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
            ->with($this->equalTo('batchDelete'));

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $modelManager->expects($this->once())
            ->method('addIdentifiersToQuery')
            ->with($this->equalTo('Foo'), $this->equalTo($query), $this->equalTo(['123', '456']));

        $this->expectTranslate('flash_batch_delete_success', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode(['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $this->assertNull($this->request->get('idx'));

        $result = $this->controller->batchAction($this->request);

        $this->assertNull($this->request->get('idx'), 'Ensure original request is not modified by calling `CRUDController::batchAction()`.');
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list', $result->getTargetUrl());
    }

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
            ->with($this->equalTo('batchDelete'));

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $modelManager->expects($this->once())
            ->method('addIdentifiersToQuery')
            ->with($this->equalTo('Foo'), $this->equalTo($query), $this->equalTo(['123', '456']));

        $this->expectTranslate('flash_batch_delete_success', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('action', 'delete');
        $this->request->request->set('idx', ['123', '456']);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction($this->request);

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
     * @dataProvider provideConfirmationData
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

        $this->assertInstanceOf(Response::class, $this->controller->batchAction($this->request));

        $this->assertSame($this->admin, $this->parameters['admin']);
        $this->assertSame('@SonataAdmin/standard_layout.html.twig', $this->parameters['base_template']);

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

        $result = $controller->batchAction($this->request);

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

        $this->controller->batchAction($this->request);

        $this->assertSame('custom_template.html.twig', $this->template);
    }

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

        $this->expectTranslate('flash_batch_empty', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('action', 'foo');
        $this->request->request->set('idx', ['999']);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $controller->batchAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_empty'], $this->session->getFlashBag()->get('sonata_flash_info'));
        $this->assertSame('list', $result->getTargetUrl());
    }

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

        $result = $this->controller->batchAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_empty'], $this->session->getFlashBag()->get('sonata_flash_info'));
        $this->assertSame('list', $result->getTargetUrl());
    }

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
        $result = $controller->batchAction($this->request);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertMatchesRegularExpression('/Redirecting to list/', $result->getContent());
    }

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
            ->with($this->equalTo('batchDelete'));

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $modelManager->expects($this->once())
            ->method('addIdentifiersToQuery')
            ->with($this->equalTo('Foo'), $this->equalTo($query), $this->equalTo(['123', '456']));

        $this->expectTranslate('flash_batch_delete_success', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode(['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('foo', 'bar');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(['flash_batch_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        $this->assertSame('list', $result->getTargetUrl());
        $this->assertSame('bar', $this->request->request->get('foo'));
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
