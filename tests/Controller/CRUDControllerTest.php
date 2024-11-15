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
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Bridge\Exporter\AdminExporter;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\Model\AuditManagerInterface;
use Sonata\AdminBundle\Model\AuditReaderInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Sonata\AdminBundle\Tests\App\Controller\CustomModelManagerExceptionMessageController;
use Sonata\AdminBundle\Tests\App\Controller\CustomModelManagerThrowableMessageController;
use Sonata\AdminBundle\Tests\Fixtures\Controller\BatchAdminController;
use Sonata\AdminBundle\Tests\Fixtures\Controller\BatchOtherController;
use Sonata\AdminBundle\Tests\Fixtures\Controller\PreCRUDController;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Entity;
use Sonata\AdminBundle\Tests\Fixtures\Util\DummyDomainObject;
use Sonata\AdminBundle\Util\AdminObjectAclManipulator;
use Sonata\Exporter\Exporter;
use Sonata\Exporter\Writer\JsonWriter;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
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
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class CRUDControllerTest extends TestCase
{
    /**
     * @var CRUDController<object>
     */
    private CRUDController $controller;

    private Request $request;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private AdminInterface $admin;

    private MutableTemplateRegistryInterface $templateRegistry;

    private Pool $pool;

    private Session $session;

    /**
     * @var AuditManagerInterface&MockObject
     */
    private AuditManagerInterface $auditManager;

    private ContainerInterface $container;

    private AdminObjectAclManipulator $adminObjectAclManipulator;

    /**
     * @var array<string, \ReflectionMethod>
     */
    private array $protectedTestedMethods = [];

    private CsrfTokenManagerInterface $csrfProvider;

    /**
     * @var TranslatorInterface&MockObject
     */
    private TranslatorInterface $translator;

    /**
     * @var LoggerInterface&MockObject
     */
    private LoggerInterface $logger;

    /**
     * @var Stub&FormFactoryInterface
     */
    private FormFactoryInterface $formFactory;

    private ParameterBag $parameterBag;

    /**
     * @var Stub&AdminFetcherInterface
     */
    private AdminFetcherInterface $adminFetcher;

    /**
     * @var MockObject&Environment
     */
    private Environment $twig;

    /**
     * @var MockObject&HttpKernelInterface
     */
    private HttpKernelInterface $httpKernel;

    /**
     * @var MockObject&ControllerResolverInterface
     */
    private ControllerResolverInterface $controllerResolver;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->request = new Request();
        $this->pool = new Pool($this->container, ['foo.admin']);
        $this->adminFetcher = $this->createStub(AdminFetcherInterface::class);
        $this->admin = $this->createMock(AdminInterface::class);
        $this->adminFetcher
            ->method('get')
            ->willReturn($this->admin);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->templateRegistry = $this->createStub(MutableTemplateRegistryInterface::class);

        $this->session = new Session(new MockArraySessionStorage());

        $this->twig = $this->createMock(Environment::class);

        $this->twig
            ->method('getRuntime')
            ->willReturn($this->createMock(FormRenderer::class));

        $exporter = new Exporter([new JsonWriter(sys_get_temp_dir().'/sonataadmin/export.json')]);

        $adminExporter = new AdminExporter($exporter);

        $this->auditManager = $this->createMock(AuditManagerInterface::class);

        $this->formFactory = $this->createStub(FormFactoryInterface::class);

        $this->controllerResolver = $this->createMock(ControllerResolverInterface::class);

        $this->httpKernel = $this->createMock(HttpKernelInterface::class);

        $this->adminObjectAclManipulator = new AdminObjectAclManipulator($this->formFactory, MaskBuilder::class);

        $this->csrfProvider = $this->createMock(CsrfTokenManagerInterface::class);

        $this->csrfProvider
            ->method('getToken')
            ->willReturnCallback(static fn (string $intention): CsrfToken => new CsrfToken($intention, \sprintf('csrf-token-123_%s', $intention)));

        $this->csrfProvider
            ->method('isTokenValid')
            ->willReturnCallback(static fn (CsrfToken $token): bool => $token->getValue() === \sprintf('csrf-token-123_%s', $token->getId()));

        $this->logger = $this->createMock(LoggerInterface::class);

        $requestStack = new RequestStack();
        $requestStack->push($this->request);

        $this->parameterBag = new ParameterBag();

        $this->request->setSession($this->session);
        $this->container->set('sonata.admin.pool', $this->pool);
        $this->container->set('request_stack', $requestStack);
        $this->container->set('foo.admin', $this->admin);
        $this->container->set('twig', $this->twig);
        $this->container->set('session', $this->session);
        $this->container->set('sonata.exporter.exporter', $exporter);
        $this->container->set('sonata.admin.admin_exporter', $adminExporter);
        $this->container->set('sonata.admin.audit.manager', $this->auditManager);
        $this->container->set('sonata.admin.object.manipulator.acl.admin', $this->adminObjectAclManipulator);
        $this->container->set('security.csrf.token_manager', $this->csrfProvider);
        $this->container->set('logger', $this->logger);
        $this->container->set('translator', $this->translator);
        $this->container->set('sonata.admin.request.fetcher', $this->adminFetcher);
        $this->container->set('parameter_bag', $this->parameterBag);
        $this->container->set('http_kernel', $this->httpKernel);
        $this->container->set('serializer', new Serializer([
            new ConstraintViolationListNormalizer(),
        ], [
            new JsonEncoder(),
        ]));
        $this->container->set('controller_resolver', $this->controllerResolver);

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
        $this->admin->method('getCode')->willReturn('foo.admin');
        $this->admin->method('hasTemplateRegistry')->willReturn(true);
        $this->admin->method('getTemplateRegistry')->willReturn($this->templateRegistry);

        $this->admin
            ->method('generateUrl')
            ->willReturnCallback(
                static function (string $name, array $parameters = []): string {
                    $result = $name;
                    if ([] !== $parameters) {
                        $result .= '?'.http_build_query($parameters);
                    }

                    return $result;
                }
            );

        $this->admin
            ->method('generateObjectUrl')
            ->willReturnCallback(
                static function (string $name, object $object, array $parameters = []): string {
                    $result = \sprintf('%s_%s', $object::class, $name);
                    if ([] !== $parameters) {
                        $result .= '?'.http_build_query($parameters);
                    }

                    return $result;
                }
            );

        $this->controller = new CRUDController();
        $this->controller->setContainer($this->container);
        $this->controller->configureAdmin($this->request);

        // Make some methods public to test them
        $testedMethods = [
            'renderJson',
            'renderWithExtraParams',
            'isXmlHttpRequest',
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

    public function testRenderJson1(): void
    {
        $data = ['example' => '123', 'foo' => 'bar'];

        $this->request->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data, 200, [], $this->request);

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertSame(json_encode($data), $response->getContent());
    }

    public function testRenderJson2(): void
    {
        $data = ['example' => '123', 'foo' => 'bar'];

        $this->request->headers->set('Content-Type', 'multipart/form-data');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data, 200, [], $this->request);

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertSame(json_encode($data), $response->getContent());
    }

    public function testRenderJsonAjax(): void
    {
        $data = ['example' => '123', 'foo' => 'bar'];

        $this->request->query->set('_xml_http_request', true);
        $this->request->headers->set('Content-Type', 'multipart/form-data');
        $response = $this->protectedTestedMethods['renderJson']->invoke($this->controller, $data, 200, [], $this->request);

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertSame(json_encode($data), $response->getContent());
    }

    public function testIsXmlHttpRequest(): void
    {
        static::assertFalse($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller, $this->request));

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        static::assertTrue($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller, $this->request));

        $this->request->headers->remove('X-Requested-With');
        static::assertFalse($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller, $this->request));

        $this->request->query->set('_xml_http_request', true);
        static::assertTrue($this->protectedTestedMethods['isXmlHttpRequest']->invoke($this->controller, $this->request));
    }

    public function testConfigureAdminWithoutTemplateRegistryThrowsException(): void
    {
        $controller = new CRUDController();
        $admin = $this->createStub(AdminInterface::class);
        $admin
            ->method('hasTemplateRegistry')
            ->willReturn(false);

        $admin
            ->method('getCode')
            ->willReturn('admin_code');

        $adminFetcher = $this->createStub(AdminFetcherInterface::class);
        $adminFetcher
            ->method('get')
            ->willReturn($admin);

        $container = new Container();
        $container->set('sonata.admin.request.fetcher', $adminFetcher);

        $controller->setContainer($container);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Unable to find the template registry related to the current admin (admin_code).'
        );

        $controller->configureAdmin($this->request);
    }

    public function testSetTwigGlobals(): void
    {
        $twig = new Environment(new ArrayLoader([]));
        $this->container->set('twig', $twig);

        $this->controller->setTwigGlobals($this->request);

        $globals = $twig->getGlobals();
        static::assertSame($this->admin, $globals['admin']);
        static::assertSame('@SonataAdmin/standard_layout.html.twig', $globals['base_template']);
    }

    public function testSetTwigGlobalsWithAjaxRequest(): void
    {
        $this->request->request->set('_xml_http_request', true);

        $twig = new Environment(new ArrayLoader([]));
        $this->container->set('twig', $twig);

        $this->controller->setTwigGlobals($this->request);

        $globals = $twig->getGlobals();
        static::assertSame($this->admin, $globals['admin']);
        static::assertSame('@SonataAdmin/ajax_layout.html.twig', $globals['base_template']);
    }

    public function testGetBaseTemplate(): void
    {
        static::assertSame(
            '@SonataAdmin/standard_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller)
        );

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        static::assertSame(
            '@SonataAdmin/ajax_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller)
        );

        $this->request->headers->remove('X-Requested-With');
        static::assertSame(
            '@SonataAdmin/standard_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller)
        );

        $this->request->request->set('_xml_http_request', true);
        static::assertSame(
            '@SonataAdmin/ajax_layout.html.twig',
            $this->protectedTestedMethods['getBaseTemplate']->invoke($this->controller)
        );
    }

    public function testRender(): void
    {
        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@FooAdmin/foo.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
            ]);

        static::assertInstanceOf(
            Response::class,
            $this->protectedTestedMethods['renderWithExtraParams']->invoke(
                $this->controller,
                '@FooAdmin/foo.html.twig',
                [],
                null
            )
        );
    }

    public function testRenderWithResponse(): void
    {
        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@FooAdmin/foo.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
            ]);

        $response = new Response();
        $response->headers->set('X-foo', 'bar');
        $responseResult = $this->protectedTestedMethods['renderWithExtraParams']->invoke(
            $this->controller,
            '@FooAdmin/foo.html.twig',
            [],
            $response
        );

        static::assertSame($response, $responseResult);
        static::assertSame('bar', $responseResult->headers->get('X-foo'));
    }

    public function testRenderCustomParams(): void
    {
        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@FooAdmin/foo.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'foo' => 'bar',
            ]);

        static::assertInstanceOf(
            Response::class,
            $this->protectedTestedMethods['renderWithExtraParams']->invoke(
                $this->controller,
                '@FooAdmin/foo.html.twig',
                ['foo' => 'bar'],
                null
            )
        );
    }

    public function testRenderAjax(): void
    {
        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@FooAdmin/foo.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/ajax_layout.html.twig',
                'foo' => 'bar',
            ]);

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        static::assertInstanceOf(
            Response::class,
            $this->protectedTestedMethods['renderWithExtraParams']->invoke(
                $this->controller,
                '@FooAdmin/foo.html.twig',
                ['foo' => 'bar'],
                null
            )
        );
    }

    public function testListActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('list'))
            ->willThrowException(new AccessDeniedException());

        $this->controller->listAction($this->request);
    }

    public function testPreList(): void
    {
        $this->admin
            ->method('hasRoute')
            ->with(static::equalTo('list'))
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('list'));

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);
        $controller->configureAdmin($this->request);

        $response = $controller->listAction($this->request);
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
            ->with(static::equalTo('list'));

        $form = $this->createMock(Form::class);

        $formView = $this->createStub(FormView::class);

        $form->expects(static::once())
            ->method('createView')
            ->willReturn($formView);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $datagrid->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/list.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'list',
                'csrf_token' => 'csrf-token-123_sonata.batch',
                'export_formats' => ['json'],
                'form' => $formView,
                'datagrid' => $datagrid,
            ]);

        static::assertInstanceOf(Response::class, $this->controller->listAction($this->request));
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testBatchActionDeleteAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('batchDelete'))
            ->willThrowException(new AccessDeniedException());

        $this->controller->batchActionDelete($this->createMock(ProxyQueryInterface::class));
    }

    public function testBatchActionDelete(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('batchDelete'));

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
        self::assertLoggerLogsModelManagerException($modelManager, 'batchDelete');

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

        $this->parameterBag->set('kernel.debug', true);

        $this->controller->batchActionDelete($this->createMock(ProxyQueryInterface::class));
    }

    public function testBatchActionDeleteWithModelManagerExceptionAndCustomError(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);

        $exception = new ModelManagerException(
            $message = 'message',
            1234,
            new \Exception($previousExceptionMessage = 'very useful message')
        );

        $modelManager->expects(static::once())
            ->method('batchDelete')
            ->willReturnCallback(static function () use ($exception): void {
                throw $exception;
            });

        $this->admin->expects(static::once())
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin->expects(static::once())
            ->method('getFilterParameters')
            ->willReturn(['foo' => 'bar']);

        $customController = new CustomModelManagerExceptionMessageController();
        $customController->setContainer($this->container);
        $customController->configureAdmin($this->request);

        $result = $customController->batchActionDelete($this->createMock(ProxyQueryInterface::class));

        static::assertInstanceOf(RedirectResponse::class, $result);
        static::assertSame(
            [CustomModelManagerExceptionMessageController::ERROR_MESSAGE],
            $this->session->getFlashBag()->get('sonata_flash_error')
        );
        static::assertSame('list?filter%5Bfoo%5D=bar', $result->getTargetUrl());
    }

    public function testBatchActionDeleteWithModelManagerThrowableCustomErrorAndHandleThrowableOnly(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);

        $exception = new ModelManagerException(
            $message = 'message',
            1234,
            new \Exception($previousExceptionMessage = 'very useful message')
        );

        $modelManager->expects(static::once())
            ->method('batchDelete')
            ->willReturnCallback(static function () use ($exception): void {
                throw $exception;
            });

        $this->admin->expects(static::once())
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin->expects(static::once())
            ->method('getFilterParameters')
            ->willReturn(['foo' => 'bar']);

        $customController = new CustomModelManagerThrowableMessageController();
        $customController->setContainer($this->container);
        $customController->configureAdmin($this->request);

        $this->translator->expects(static::never())->method('trans');

        $result = $customController->batchActionDelete($this->createMock(ProxyQueryInterface::class));

        static::assertInstanceOf(RedirectResponse::class, $result);
        static::assertSame(
            [CustomModelManagerThrowableMessageController::ERROR_MESSAGE],
            $this->session->getFlashBag()->get('sonata_flash_error')
        );
        static::assertSame('list?filter%5Bfoo%5D=bar', $result->getTargetUrl());
    }

    public function testShowActionNotFoundException(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->showAction($this->request);
    }

    public function testShowActionAccessDenied(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(new \stdClass());

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('show'))
            ->willThrowException(new AccessDeniedException());

        $this->expectException(AccessDeniedException::class);

        $this->controller->showAction($this->request);
    }

    public function testPreShow(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new Entity(123456);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('show'));

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);
        $controller->configureAdmin($this->request);

        $response = $controller->showAction($this->request);
        static::assertInstanceOf(Response::class, $response);
        static::assertSame('preShow called: 123456', $response->getContent());
    }

    public function testShowAction(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('show'));

        $show = new FieldDescriptionCollection();

        $this->admin->expects(static::once())
            ->method('getShow')
            ->willReturn($show);

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/show.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'show',
                'object' => $object,
                'elements' => $show,
            ]);

        static::assertInstanceOf(Response::class, $this->controller->showAction($this->request));
        static::assertSame([], $this->session->getFlashBag()->all());
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

        $parentAdmin = $this->createMock(AdminInterface::class);

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

        $this->controller->showAction($this->request);
    }

    /**
     * @param array<string, bool|float|int|string|null> $queryParams
     * @param array<string, bool|float|int|string|null> $requestParams
     *
     * @dataProvider provideRedirectToCases
     */
    public function testRedirectTo(
        string $expected,
        string $route,
        array $queryParams,
        array $requestParams,
        bool $hasActiveSubclass,
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

        $response = $this->protectedTestedMethods['redirectTo']->invoke($this->controller, $this->request, $object);
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

        $response = $this->protectedTestedMethods['redirectTo']->invoke($this->controller, $this->request, $object);
        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame('list', $response->getTargetUrl());
    }

    /**
     * @phpstan-return iterable<array-key, array{string, string, array<string, bool|float|int|string|null>, array<string, bool|float|int|string|null>, bool}>
     */
    public function provideRedirectToCases(): iterable
    {
        yield ['stdClass_edit', 'edit', [], [], false];
        yield ['list', 'list', ['btn_update_and_list' => true], [], false];
        yield ['list', 'list', ['btn_create_and_list' => true], [], false];
        yield ['create', 'create', ['btn_create_and_create' => true], [], false];
        yield ['create?subclass=foo', 'create', ['btn_create_and_create' => true, 'subclass' => 'foo'], [], true];
        yield ['stdClass_edit?_tab=first_tab', 'edit', ['btn_update_and_edit' => true], ['_tab' => 'first_tab'], false];
    }

    public function testDeleteActionNotFoundException(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->deleteAction($this->request);
    }

    public function testDeleteActionAccessDenied(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(new \stdClass());

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'))
            ->willThrowException(new AccessDeniedException());

        $this->expectException(AccessDeniedException::class);

        $this->controller->deleteAction($this->request);
    }

    public function testPreDelete(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new Entity(123456);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'));

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);
        $controller->configureAdmin($this->request);

        $response = $controller->deleteAction($this->request);
        static::assertInstanceOf(Response::class, $response);
        static::assertSame('preDelete called: 123456', $response->getContent());
    }

    public function testDeleteAction(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'));

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/delete.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'delete',
                'object' => $object,
                'csrf_token' => 'csrf-token-123_sonata.delete',
            ]);

        static::assertInstanceOf(Response::class, $this->controller->deleteAction($this->request));
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testDeleteActionChildNoConnectedException(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);
        $this->request->attributes->set('parent_id', 42);

        $object = new \stdClass();
        $object->parent = 'test';

        $object2 = new \stdClass();

        $admin = $this->createMock(AdminInterface::class);
        $admin->method('getIdParameter')->willReturn('parent_id');

        $admin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($object2);

        $admin->expects(static::once())
            ->method('toString')
            ->willReturn('parentObject');

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::exactly(2))
            ->method('isChild')
            ->willReturn(true);

        $this->admin->expects(static::exactly(2))
            ->method('getParent')
            ->willReturn($admin);

        $this->admin->expects(static::atLeastOnce())
            ->method('getParentAssociationMapping')
            ->willReturn('parent');

        $this->admin->expects(static::once())
            ->method('toString')
            ->willReturn('childObject');

        $this->admin->expects(static::exactly(2))
            ->method('isChild')
            ->willReturn(true);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('There is no association between "parentObject" and "childObject"');

        $this->controller->deleteAction($this->request);
    }

    public function testDeleteActionNoCsrfToken(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->container->set('security.csrf.token_manager', null);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'));

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/delete.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'delete',
                'object' => $object,
                'csrf_token' => null,
            ]);

        static::assertInstanceOf(Response::class, $this->controller->deleteAction($this->request));
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testDeleteActionAjaxSuccess1(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'));

        $this->request->setMethod(Request::METHOD_DELETE);

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction($this->request);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(json_encode(['result' => 'ok']), $response->getContent());
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testDeleteActionAjaxSuccess2(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'));

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->deleteAction($this->request);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(json_encode(['result' => 'ok']), $response->getContent());
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testDeleteActionAjaxError(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'));

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        self::assertLoggerLogsModelManagerException($this->admin, 'delete');

        $this->request->setMethod(Request::METHOD_DELETE);

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->controller->deleteAction($this->request);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(json_encode(['result' => 'error']), $response->getContent());
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testDeleteActionWithModelManagerExceptionInDebugMode(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'));

        $this->admin->expects(static::once())
            ->method('delete')
            ->willReturnCallback(static function (): void {
                throw new ModelManagerException();
            });

        $this->parameterBag->set('kernel.debug', true);

        $this->request->setMethod(Request::METHOD_DELETE);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $this->expectException(ModelManagerException::class);

        $this->controller->deleteAction($this->request);
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testDeleteActionSuccess1(string $expectedToStringValue, string $toStringValue): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('toString')
            ->with(static::equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_delete_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'));

        $this->request->setMethod(Request::METHOD_DELETE);

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction($this->request);

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

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'));

        $this->admin->expects(static::once())
            ->method('toString')
            ->with(static::equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_delete_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);

        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction($this->request);

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame(['flash_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        static::assertSame('list', $response->getTargetUrl());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testDeleteActionSuccessNoCsrfTokenProvider(string $expectedToStringValue, string $toStringValue): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->container->set('security.csrf.token_manager', null);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'));

        $this->admin->expects(static::once())
            ->method('toString')
            ->with(static::equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_delete_success', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);

        $response = $this->controller->deleteAction($this->request);

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame(['flash_delete_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        static::assertSame('list', $response->getTargetUrl());
    }

    public function testDeleteActionWrongRequestMethod(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'));

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/delete.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'delete',
                'object' => $object,
                'csrf_token' => 'csrf-token-123_sonata.delete',
            ]);

        static::assertInstanceOf(Response::class, $this->controller->deleteAction($this->request));

        static::assertSame([], $this->session->getFlashBag()->all());
        static::assertSame(Request::METHOD_GET, $this->request->getMethod());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testDeleteActionError(string $expectedToStringValue, string $toStringValue): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'));

        $this->admin->expects(static::once())
            ->method('toString')
            ->with(static::equalTo($object))
            ->willReturn($toStringValue);

        $this->expectTranslate('flash_delete_error', ['%name%' => $expectedToStringValue], 'SonataAdminBundle');

        self::assertLoggerLogsModelManagerException($this->admin, 'delete');

        $this->request->setMethod(Request::METHOD_DELETE);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $response = $this->controller->deleteAction($this->request);

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame(['flash_delete_error'], $this->session->getFlashBag()->get('sonata_flash_error'));
        static::assertSame('list', $response->getTargetUrl());
    }

    public function testDeleteActionWithModelManagerExceptionCustomError(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())->method('getObject')->willReturn($object);
        $this->admin->expects(static::once())->method('checkAccess')->with(static::equalTo('delete'));

        $this->translator->expects(static::never())->method('trans');

        $exception = new ModelManagerException(
            $message = 'message',
            1234,
            new \Exception($previousExceptionMessage = 'very useful message')
        );

        $this->admin->expects(static::once())
            ->method('delete')
            ->willReturnCallback(static function () use ($exception): void {
                throw $exception;
            });

        $this->request->setMethod(Request::METHOD_DELETE);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $customController = new CustomModelManagerExceptionMessageController();
        $customController->setContainer($this->container);
        $customController->configureAdmin($this->request);

        $response = $customController->deleteAction($this->request);

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame([CustomModelManagerExceptionMessageController::ERROR_MESSAGE], $this->session->getFlashBag()->get('sonata_flash_error'));
        static::assertSame('list', $response->getTargetUrl());
    }

    public function testDeleteActionWithModelManagerThrowableCustomError(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())->method('getObject')->willReturn($object);
        $this->admin->expects(static::once())->method('checkAccess')->with(static::equalTo('delete'));

        $this->translator->expects(static::never())->method('trans');

        $exception = new ModelManagerException(
            $message = 'message',
            1234,
            new \Exception($previousExceptionMessage = 'very useful message')
        );

        $this->admin->expects(static::once())
            ->method('delete')
            ->willReturnCallback(static function () use ($exception): void {
                throw $exception;
            });

        $this->request->setMethod(Request::METHOD_DELETE);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.delete');

        $customController = new CustomModelManagerThrowableMessageController();
        $customController->setContainer($this->container);
        $customController->configureAdmin($this->request);

        $response = $customController->deleteAction($this->request);

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame([CustomModelManagerThrowableMessageController::ERROR_MESSAGE], $this->session->getFlashBag()->get('sonata_flash_error'));
        static::assertSame('list', $response->getTargetUrl());
    }

    public function testDeleteActionInvalidCsrfToken(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('delete'));

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('_sonata_csrf_token', 'CSRF-INVALID');

        try {
            $this->controller->deleteAction($this->request);
        } catch (HttpException $e) {
            static::assertSame('The csrf token is not valid, CSRF attack?', $e->getMessage());
            static::assertSame(400, $e->getStatusCode());
        }
    }

    public function testDeleteActionChildManyToMany(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);
        $this->request->attributes->set('parent_id', 42);

        $parent = new \stdClass();

        $child = new \stdClass();
        $child->parents = [$parent];

        $parentAdmin = $this->createMock(AdminInterface::class);
        $parentAdmin->method('getIdParameter')->willReturn('parent_id');

        $childAdmin = $this->admin;

        $parentAdmin->expects(static::atLeastOnce())
            ->method('getObject')
            ->willReturn($parent);

        $childAdmin->expects(static::once())
            ->method('getObject')
            ->willReturn($child);

        $childAdmin->expects(static::exactly(2))
            ->method('isChild')
            ->willReturn(true);

        $childAdmin->expects(static::exactly(2))
            ->method('getParent')
            ->willReturn($parentAdmin);

        $childAdmin->expects(static::atLeastOnce())
            ->method('getParentAssociationMapping')
            ->willReturn('parents');

        $this->controller->deleteAction($this->request);
    }

    public function testEditActionNotFoundException(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->editAction($this->request);
    }

    public function testEditActionAccessDenied(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(new \stdClass());

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'))
            ->willThrowException(new AccessDeniedException());

        $this->expectException(AccessDeniedException::class);

        $this->controller->editAction($this->request);
    }

    public function testPreEdit(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new Entity(123456);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'));

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);
        $controller->configureAdmin($this->request);

        $response = $controller->editAction($this->request);
        static::assertInstanceOf(Response::class, $response);
        static::assertSame('preEdit called: 123456', $response->getContent());
    }

    public function testEditAction(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'));

        $form = $this->createMock(Form::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('foo_normalized');

        $formView = $this->createStub(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/edit.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'edit',
                'form' => $formView,
                'object' => $object,
                'objectId' => 'foo_normalized',
            ]);

        static::assertInstanceOf(Response::class, $this->controller->editAction($this->request));
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testEditActionSuccess(string $expectedToStringValue, string $toStringValue): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
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

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('foo_normalized');

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

        $response = $this->controller->editAction($this->request);

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

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'));

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('foo_normalized');

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

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/edit.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'edit',
                'form' => $formView,
                'object' => $object,
                'objectId' => 'foo_normalized',
            ]);

        static::assertInstanceOf(Response::class, $this->controller->editAction($this->request));

        static::assertSame(['sonata_flash_error' => ['flash_edit_error']], $this->session->getFlashBag()->all());
    }

    public function testEditActionWithModelManagerExceptionAndCustomError(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'));

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('foo_normalized');

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

        $this->translator->expects(static::never())->method('trans');

        $this->request->setMethod(Request::METHOD_POST);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $form->expects(static::once())
            ->method('getData')
            ->willReturn($object);

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/edit.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'edit',
                'form' => $formView,
                'object' => $object,
                'objectId' => 'foo_normalized',
            ]);

        $customController = new CustomModelManagerExceptionMessageController();
        $customController->setContainer($this->container);
        $customController->configureAdmin($this->request);

        $exception = new ModelManagerException(
            $message = 'message',
            1234,
            new \Exception($previousExceptionMessage = 'very useful message')
        );

        $this->admin->expects(static::once())
            ->method('update')
            ->willReturnCallback(static function () use ($exception): void {
                throw $exception;
            });

        $response = $customController->editAction($this->request);

        static::assertInstanceOf(Response::class, $response);

        static::assertSame(
            ['sonata_flash_error' => [CustomModelManagerExceptionMessageController::ERROR_MESSAGE]],
            $this->session->getFlashBag()->all()
        );
    }

    public function testEditActionWithModelManagerThrowableAndCustomError(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'));

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('foo_normalized');

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

        $this->translator->expects(static::never())->method('trans');

        $this->request->setMethod(Request::METHOD_POST);

        $formView = $this->createMock(FormView::class);

        $form
            ->method('createView')
            ->willReturn($formView);

        $form->expects(static::once())
            ->method('getData')
            ->willReturn($object);

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/edit.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'edit',
                'form' => $formView,
                'object' => $object,
                'objectId' => 'foo_normalized',
            ]);

        $customController = new CustomModelManagerThrowableMessageController();
        $customController->setContainer($this->container);
        $customController->configureAdmin($this->request);

        $exception = new ModelManagerException(
            $message = 'message',
            1234,
            new \Exception($previousExceptionMessage = 'very useful message')
        );

        $this->admin->expects(static::once())
            ->method('update')
            ->willReturnCallback(static function () use ($exception): void {
                throw $exception;
            });

        $response = $customController->editAction($this->request);

        static::assertInstanceOf(Response::class, $response);

        static::assertSame(
            ['sonata_flash_error' => [CustomModelManagerThrowableMessageController::ERROR_MESSAGE]],
            $this->session->getFlashBag()->all()
        );
    }

    public function testEditActionAjaxSuccess(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('update')
            ->willReturnArgument(0);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'));

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
        $this->request->headers->set('Accept', 'application/json');

        $response = $this->controller->editAction($this->request);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(json_encode(['result' => 'ok', 'objectId' => 'foo_normalized', 'objectName' => 'foo']), $response->getContent());
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testEditActionAjaxError(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'));

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('foo_normalized');

        $form = $this->createMock(Form::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects(static::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects(static::once())
            ->method('getName')
            ->willReturn('name');

        $form->expects(static::once())
            ->method('isValid')
            ->willReturn(false);

        $formError = $this->createMock(FormError::class);
        $formError->expects(static::once())
            ->method('getCause')
            ->willReturn(new ConstraintViolation('Form error message', '', [], 'root', 'foo', 'bar'));
        $formError->expects(static::once())
            ->method('getOrigin')
            ->willReturn($form);

        $form->expects(static::once())
            ->method('getErrors')
            ->with(true)
            ->willReturn(new FormErrorIterator($form, [$formError]));

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->headers->set('Accept', 'application/json');

        static::assertInstanceOf(JsonResponse::class, $response = $this->controller->editAction($this->request));
        $content = $response->getContent();
        static::assertNotFalse($content);

        // TODO: Remove else when dropping support for Symfony < 6.3
        if (\defined(Uuid::class.'::INVALID_TIME_BASED_VERSION_ERROR')) {
            static::assertJsonStringEqualsJsonString('{"type":"https:\/\/symfony.com\/errors\/validation","title":"Validation Failed","detail":"name: Form error message","violations":[{"propertyPath":"name","title":"Form error message","template":"","parameters":[]}]}', $content);
        } else {
            static::assertJsonStringEqualsJsonString('{"type":"https:\/\/symfony.com\/errors\/validation","title":"Validation Failed","detail":"name: Form error message","violations":[{"propertyPath":"name","title":"Form error message","parameters":[]}]}', $content);
        }
    }

    public function testEditActionAjaxErrorWithoutAcceptApplicationJson(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'));

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('foo_normalized');

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

        static::assertInstanceOf(Response::class, $response = $this->controller->editAction($this->request));
        static::assertSame(Response::HTTP_NOT_ACCEPTABLE, $response->getStatusCode());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testEditActionWithModelManagerException(string $expectedToStringValue, string $toStringValue): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'));

        $this->admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('foo_normalized');

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

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/edit.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'edit',
                'form' => $formView,
                'object' => $object,
                'objectId' => 'foo_normalized',
            ]);

        static::assertInstanceOf(Response::class, $this->controller->editAction($this->request));
        static::assertSame(['sonata_flash_error' => ['flash_edit_error']], $this->session->getFlashBag()->all());
    }

    public function testEditActionWithPreview(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('edit'));

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('foo_normalized');

        $form = $this->createMock(Form::class);

        $this->admin->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $this->admin->expects(static::once())
            ->method('supportsPreviewMode')
            ->willReturn(true);

        $this->admin->method('getShow')->willReturn(new FieldDescriptionCollection());

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

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/preview.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'edit',
                'form' => $formView,
                'object' => $object,
                'objectId' => 'foo_normalized',
            ]);

        static::assertInstanceOf(Response::class, $this->controller->editAction($this->request));
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testEditActionWithLockException(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $object = new \stdClass();
        $class = $object::class;

        $this->admin
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('checkAccess')
            ->with(static::equalTo('edit'));

        $this->admin
            ->method('getClass')
            ->willReturn($class);

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('foo_normalized');

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
            ->willThrowException(new LockException());

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

        static::assertInstanceOf(Response::class, $this->controller->editAction($this->request));
    }

    public function testCreateActionAccessDenied(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'))
            ->willThrowException(new AccessDeniedException());

        $this->expectException(AccessDeniedException::class);

        $this->controller->createAction($this->request);
    }

    public function testPreCreate(): void
    {
        $object = new Entity(123456);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'));

        $this->admin
            ->method('getClass')
            ->willReturn(Entity::class);

        $this->admin->expects(static::once())
            ->method('getNewInstance')
            ->willReturn($object);

        $controller = new PreCRUDController();
        $controller->setContainer($this->container);
        $controller->configureAdmin($this->request);

        $response = $controller->createAction($this->request);
        static::assertInstanceOf(Response::class, $response);
        static::assertSame('preCreate called: 123456', $response->getContent());
    }

    public function testCreateAction(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'));

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

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/edit.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'create',
                'form' => $formView,
                'object' => $object,
                'objectId' => null,
            ]);

        static::assertInstanceOf(Response::class, $this->controller->createAction($this->request));
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testCreateActionSuccess(string $expectedToStringValue, string $toStringValue): void
    {
        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->willReturnCallback(static function (string $name, ?object $objectIn = null) use ($object): void {
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

        $response = $this->controller->createAction($this->request);

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame(['flash_create_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        static::assertSame('stdClass_edit', $response->getTargetUrl());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testCreateActionError(string $expectedToStringValue, string $toStringValue): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'));

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

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/edit.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'create',
                'form' => $formView,
                'object' => $object,
                'objectId' => null,
            ]);

        static::assertInstanceOf(Response::class, $this->controller->createAction($this->request));
        static::assertSame(['sonata_flash_error' => ['flash_create_error']], $this->session->getFlashBag()->all());
    }

    /**
     * @dataProvider getToStringValues
     */
    public function testCreateActionWithModelManagerException(string $expectedToStringValue, string $toStringValue): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'));

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

        self::assertLoggerLogsModelManagerException($this->admin, 'create');

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/edit.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'create',
                'form' => $formView,
                'object' => $object,
                'objectId' => null,
            ]);

        static::assertInstanceOf(Response::class, $this->controller->createAction($this->request));
        static::assertSame(['sonata_flash_error' => ['flash_create_error']], $this->session->getFlashBag()->all());
    }

    public function testCreateActionWithModelManagerExceptionAndCustomError(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'));

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

        $this->translator->expects(static::never())->method('trans');

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

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/edit.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'create',
                'form' => $formView,
                'object' => $object,
                'objectId' => null,
            ]);

        $customController = new CustomModelManagerExceptionMessageController();
        $customController->setContainer($this->container);
        $customController->configureAdmin($this->request);

        $exception = new ModelManagerException(
            $message = 'message',
            1234,
            new \Exception($previousExceptionMessage = 'very useful message')
        );

        $this->admin->expects(static::once())
            ->method('create')
            ->willReturnCallback(static function () use ($exception): void {
                throw $exception;
            });

        $response = $customController->createAction($this->request);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(
            ['sonata_flash_error' => [CustomModelManagerExceptionMessageController::ERROR_MESSAGE]],
            $this->session->getFlashBag()->all()
        );
    }

    public function testCreateActionWithModelManagerThrowableAndCustomError(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'));

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

        $this->translator->expects(static::never())->method('trans');

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

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/edit.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'create',
                'form' => $formView,
                'object' => $object,
                'objectId' => null,
            ]);

        $customController = new CustomModelManagerThrowableMessageController();
        $customController->setContainer($this->container);
        $customController->configureAdmin($this->request);

        $exception = new ModelManagerException(
            $message = 'message',
            1234,
            new \Exception($previousExceptionMessage = 'very useful message')
        );

        $this->admin->expects(static::once())
            ->method('create')
            ->willReturnCallback(static function () use ($exception): void {
                throw $exception;
            });

        $response = $customController->createAction($this->request);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(
            ['sonata_flash_error' => [CustomModelManagerThrowableMessageController::ERROR_MESSAGE]],
            $this->session->getFlashBag()->all()
        );
    }

    public function testCreateActionAjaxSuccess(): void
    {
        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->willReturnCallback(static function (string $name, ?object $objectIn = null) use ($object): void {
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
        $this->request->headers->set('Accept', 'application/json');

        $response = $this->controller->createAction($this->request);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(json_encode(['result' => 'ok', 'objectId' => 'foo_normalized', 'objectName' => 'foo']), $response->getContent());
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testCreateActionAjaxError(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'));

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
            ->method('getName')
            ->willReturn('name');

        $form->expects(static::once())
            ->method('isValid')
            ->willReturn(false);

        $formError = $this->createMock(FormError::class);
        $formError->expects(static::once())
            ->method('getCause')
            ->willReturn(new ConstraintViolation('Form error message', '', [], 'root', 'foo', 'bar'));
        $formError->expects(static::once())
            ->method('getOrigin')
            ->willReturn($form);

        $form->expects(static::once())
            ->method('getErrors')
            ->with(true)
            ->willReturn(new FormErrorIterator($form, [$formError]));

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->headers->set('Accept', 'application/json');

        static::assertInstanceOf(JsonResponse::class, $response = $this->controller->createAction($this->request));

        $content = $response->getContent();
        static::assertNotFalse($content);

        // TODO: Remove else when dropping support for Symfony < 6.3
        if (\defined(Uuid::class.'::INVALID_TIME_BASED_VERSION_ERROR')) {
            static::assertJsonStringEqualsJsonString('{"type":"https:\/\/symfony.com\/errors\/validation","title":"Validation Failed","detail":"name: Form error message","violations":[{"propertyPath":"name","title":"Form error message","template":"","parameters":[]}]}', $content);
        } else {
            static::assertJsonStringEqualsJsonString('{"type":"https:\/\/symfony.com\/errors\/validation","title":"Validation Failed","detail":"name: Form error message","violations":[{"propertyPath":"name","title":"Form error message","parameters":[]}]}', $content);
        }
    }

    public function testCreateActionAjaxErrorWithoutAcceptApplicationJson(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'));

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

        static::assertInstanceOf(Response::class, $response = $this->controller->createAction($this->request));
        static::assertSame(Response::HTTP_NOT_ACCEPTABLE, $response->getStatusCode());
    }

    public function testCreateActionWithPreview(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('create'));

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

        $this->admin->method('getShow')->willReturn(new FieldDescriptionCollection());

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

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/preview.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'create',
                'form' => $formView,
                'object' => $object,
                'objectId' => null,
            ]);

        static::assertInstanceOf(Response::class, $this->controller->createAction($this->request));
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testExportActionAccessDenied(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('export'))
            ->willThrowException(new AccessDeniedException());

        $this->expectException(AccessDeniedException::class);

        $this->controller->exportAction($this->request);
    }

    public function testExportActionWrongFormat(): void
    {
        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('export'));

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
            ->with(static::equalTo('export'));

        $this->admin->expects(static::once())
            ->method('getExportFormats')
            ->willReturn(['json']);

        $this->admin->expects(static::once())
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $dataSourceIterator = $this->createMock(\Iterator::class);

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
        $this->request->query->set('id', '123');

        $this->admin
            ->method('getObject')
            ->willReturn(new \stdClass());

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('history'))
            ->willThrowException(new AccessDeniedException());

        $this->expectException(AccessDeniedException::class);
        $this->controller->historyAction($this->request);
    }

    public function testHistoryActionNotFoundException(): void
    {
        $this->request->query->set('id', '123');

        $this->admin->method('getObject')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->historyAction($this->request);
    }

    public function testHistoryActionNoReader(): void
    {
        $this->request->query->set('id', '123');

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('history'));

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('foo_normalized');

        $this->auditManager->expects(static::once())
            ->method('hasReader')
            ->with(static::equalTo('Foo'))
            ->willReturn(false);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('unable to find the audit reader for class : Foo');

        $this->controller->historyAction($this->request);
    }

    public function testHistoryAction(): void
    {
        $this->request->query->set('id', '123');

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('history'));

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('123');

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
            ->with(static::equalTo('Foo'), static::equalTo('123'))
            ->willReturn([]);

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/history.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'history',
                'revisions' => [],
                'object' => $object,
                'currentRevision' => false,
            ]);

        static::assertInstanceOf(Response::class, $this->controller->historyAction($this->request));
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testAclActionAclNotEnabled(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('ACL are not enabled for this admin');

        $this->controller->aclAction($this->request);
    }

    public function testAclActionNotFoundException(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), '21');

        $this->admin->expects(static::once())
            ->method('isAclEnabled')
            ->willReturn(true);

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->aclAction($this->request);
    }

    public function testAclActionAccessDenied(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::once())
            ->method('isAclEnabled')
            ->willReturn(true);

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('acl'), static::equalTo($object))
            ->willThrowException(new AccessDeniedException());

        $this->expectException(AccessDeniedException::class);

        $this->controller->aclAction($this->request);
    }

    public function testAclAction(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);

        $this->admin->expects(static::exactly(2))
            ->method('isAclEnabled')
            ->willReturn(true);

        $object = new DummyDomainObject();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->expects(static::once())
            ->method('checkAccess');

        $this->admin
            ->method('getSecurityInformation')
            ->willReturn([]);

        $aclUsersForm = $this->createMock(Form::class);

        $aclUsersFormView = $this->createStub(FormView::class);

        $aclUsersForm->expects(static::once())
            ->method('createView')
            ->willReturn($aclUsersFormView);

        $aclRolesForm = $this->createMock(Form::class);

        $aclRolesFormView = $this->createStub(FormView::class);

        $aclRolesForm->expects(static::once())
            ->method('createView')
            ->willReturn($aclRolesFormView);

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

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/acl.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'acl',
                'permissions' => [],
                'object' => $object,
                'users' => new \ArrayIterator(),
                'roles' => new \ArrayIterator(['ROLE_SUPER_ADMIN', 'ROLE_USER', 'ROLE_SONATA_ADMIN', 'ROLE_ADMIN']),
                'aclUsersForm' => $aclUsersFormView,
                'aclRolesForm' => $aclRolesFormView,
            ]);

        static::assertInstanceOf(Response::class, $this->controller->aclAction($this->request));
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testAclActionInvalidUpdate(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);
        $this->request->request->set(AdminObjectAclManipulator::ACL_USERS_FORM_NAME, []);

        $this->admin->expects(static::exactly(2))
            ->method('isAclEnabled')
            ->willReturn(true);

        $object = new DummyDomainObject();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->expects(static::once())
            ->method('checkAccess');

        $this->admin
            ->method('getSecurityInformation')
            ->willReturn([]);

        $aclUsersForm = $this->createMock(Form::class);

        $aclUsersForm->expects(static::once())
            ->method('isValid')
            ->willReturn(false);

        $aclUsersFormView = $this->createStub(FormView::class);

        $aclUsersForm->expects(static::once())
            ->method('createView')
            ->willReturn($aclUsersFormView);

        $aclRolesForm = $this->createMock(Form::class);

        $aclRolesFormView = $this->createStub(FormView::class);

        $aclRolesForm->expects(static::once())
            ->method('createView')
            ->willReturn($aclRolesFormView);

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

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/acl.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'acl',
                'permissions' => [],
                'object' => $object,
                'users' => new \ArrayIterator(),
                'roles' => new \ArrayIterator(['ROLE_SUPER_ADMIN', 'ROLE_USER', 'ROLE_SONATA_ADMIN', 'ROLE_ADMIN']),
                'aclUsersForm' => $aclUsersFormView,
                'aclRolesForm' => $aclRolesFormView,
            ]);

        static::assertInstanceOf(Response::class, $this->controller->aclAction($this->request));
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testAclActionSuccessfulUpdate(): void
    {
        $this->request->attributes->set($this->admin->getIdParameter(), 21);
        $this->request->request->set(AdminObjectAclManipulator::ACL_ROLES_FORM_NAME, []);

        $this->admin->expects(static::exactly(2))
            ->method('isAclEnabled')
            ->willReturn(true);

        $object = new DummyDomainObject();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->expects(static::once())
            ->method('checkAccess');

        $this->admin
            ->method('getSecurityInformation')
            ->willReturn([]);

        $aclUsersForm = $this->createMock(Form::class);

        $aclUsersForm
            ->method('createView')
            ->willReturn($this->createMock(FormView::class));

        $aclRolesForm = $this->createMock(Form::class);

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

        static::assertInstanceOf(RedirectResponse::class, $response);

        static::assertSame(['flash_acl_edit_success'], $this->session->getFlashBag()->get('sonata_flash_success'));
        static::assertSame(\sprintf('%s_acl', DummyDomainObject::class), $response->getTargetUrl());
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
            ->willThrowException(new AccessDeniedException());

        $this->expectException(AccessDeniedException::class);

        $this->controller->historyViewRevisionAction($this->request, 'fooRevision');
    }

    public function testHistoryViewRevisionActionNotFoundException(): void
    {
        $this->request->query->set('id', '123');

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(null);

        $this->admin->expects(static::once())
            ->method('getClassnameLabel')
            ->willReturn('NonexistentObject');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Unable to find NonexistentObject object with id: 123');

        $this->controller->historyViewRevisionAction($this->request, 'fooRevision');
    }

    public function testHistoryViewRevisionActionNoReader(): void
    {
        $this->request->query->set('id', '123');

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyViewRevision'));

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('foo_normalized');

        $this->auditManager->expects(static::once())
            ->method('hasReader')
            ->with(static::equalTo('Foo'))
            ->willReturn(false);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('unable to find the audit reader for class : Foo');

        $this->controller->historyViewRevisionAction($this->request, 'fooRevision');
    }

    public function testHistoryViewRevisionActionNotFoundRevision(): void
    {
        $this->request->query->set('id', '123');

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyViewRevision'));

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('123');

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
            ->with(static::equalTo('Foo'), static::equalTo('123'), static::equalTo('fooRevision'))
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(
            'unable to find the targeted object `123` from the revision `fooRevision` with classname : `Foo`'
        );

        $this->controller->historyViewRevisionAction($this->request, 'fooRevision');
    }

    public function testHistoryViewRevisionAction(): void
    {
        $this->request->query->set('id', '123');

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyViewRevision'));

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('123');

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
        $objectRevision->revision = 'fooRevision';

        $reader->expects(static::once())
            ->method('find')
            ->with(static::equalTo('Foo'), static::equalTo('123'), static::equalTo('fooRevision'))
            ->willReturn($objectRevision);

        $this->admin->expects(static::once())
            ->method('setSubject')
            ->with(static::equalTo($objectRevision));

        $fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin->expects(static::once())
            ->method('getShow')
            ->willReturn($fieldDescriptionCollection);

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/show.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'show',
                'object' => $objectRevision,
                'elements' => $fieldDescriptionCollection,
            ]);

        static::assertInstanceOf(Response::class, $this->controller->historyViewRevisionAction($this->request, 'fooRevision'));
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    public function testHistoryCompareRevisionsActionAccessDenied(): void
    {
        $this->request->query->set('id', 123);

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyCompareRevisions'))
            ->willThrowException(new AccessDeniedException());

        $this->expectException(AccessDeniedException::class);

        $this->controller->historyCompareRevisionsAction($this->request, 'fooBaseRevision', 'fooCompareRevision');
    }

    public function testHistoryCompareRevisionsActionNotFoundException(): void
    {
        $this->request->query->set('id', '123');

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyCompareRevisions'));

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn(null);

        $this->admin->expects(static::once())
            ->method('getClassnameLabel')
            ->willReturn('MyObjectWithRevisions');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Unable to find MyObjectWithRevisions object with id: 123.');

        $this->controller->historyCompareRevisionsAction($this->request, 'fooBaseRevision', 'fooCompareRevision');
    }

    public function testHistoryCompareRevisionsActionNoReader(): void
    {
        $this->request->query->set('id', '123');

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyCompareRevisions'));

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('foo_normalized');

        $this->auditManager->expects(static::once())
            ->method('hasReader')
            ->with(static::equalTo('Foo'))
            ->willReturn(false);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('unable to find the audit reader for class : Foo');

        $this->controller->historyCompareRevisionsAction($this->request, 'fooBaseRevision', 'fooCompareRevision');
    }

    public function testHistoryCompareRevisionsActionNotFoundBaseRevision(): void
    {
        $this->request->query->set('id', '123');

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyCompareRevisions'));

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('123');

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
            ->with(static::equalTo('Foo'), static::equalTo('123'), static::equalTo('fooBaseRevision'))
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(
            'unable to find the targeted object `123` from the revision `fooBaseRevision` with classname : `Foo`'
        );

        $this->controller->historyCompareRevisionsAction($this->request, 'fooBaseRevision', 'fooCompareRevision');
    }

    public function testHistoryCompareRevisionsActionNotFoundCompareRevision(): void
    {
        $this->request->query->set('id', '123');

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyCompareRevisions'));

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('123');

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
        $objectRevision->revision = 'fooBaseRevision';

        // first call should return, so the second call will throw an exception
        $reader->expects(static::exactly(2))->method('find')->willReturnMap([
            ['Foo', '123', 'fooBaseRevision', $objectRevision],
            ['Foo', '123', 'fooCompareRevision', null],
        ]);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(
            'unable to find the targeted object `123` from the revision `fooCompareRevision` with classname : `Foo`'
        );

        $this->controller->historyCompareRevisionsAction($this->request, 'fooBaseRevision', 'fooCompareRevision');
    }

    public function testHistoryCompareRevisionsActionAction(): void
    {
        $this->request->query->set('id', '123');

        $this->admin->expects(static::once())
            ->method('checkAccess')
            ->with(static::equalTo('historyCompareRevisions'));

        $object = new \stdClass();

        $this->admin->expects(static::once())
            ->method('getObject')
            ->willReturn($object);

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($object))
            ->willReturn('123');

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
        $objectRevision->revision = 'fooBaseRevision';

        $compareObjectRevision = new \stdClass();
        $compareObjectRevision->revision = 'fooCompareRevision';

        $reader->expects(static::exactly(2))->method('find')->willReturnMap([
            ['Foo', '123', 'fooBaseRevision', $objectRevision],
            ['Foo', '123', 'fooCompareRevision', $compareObjectRevision],
        ]);

        $this->admin->expects(static::once())
            ->method('setSubject')
            ->with(static::equalTo($objectRevision));

        $fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin->expects(static::once())
            ->method('getShow')
            ->willReturn($fieldDescriptionCollection);

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/show_compare.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'show',
                'object' => $objectRevision,
                'object_compare' => $compareObjectRevision,
                'elements' => $fieldDescriptionCollection,
            ]);

        static::assertInstanceOf(Response::class, $this->controller->historyCompareRevisionsAction($this->request, 'fooBaseRevision', 'fooCompareRevision'));
        static::assertSame([], $this->session->getFlashBag()->all());
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
        $this->expectExceptionMessage('A `sonata.admin.controller.crud::batchActionFoo` method must be callable or create a `controller` configuration for your batch action.');

        $batchActions = [];

        $this->admin->expects(static::once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $this->admin->expects(static::once())
            ->method('getBaseControllerName')
            ->willReturn('sonata.admin.controller.crud');

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
            static::assertSame('The csrf token is not valid, CSRF attack?', $e->getMessage());
            static::assertSame(400, $e->getStatusCode());
        }
    }

    public function testBatchActionMethodNotExist(): void
    {
        $batchActions = ['foo' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects(static::once())
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $this->expectGetController(null, false);

        $this->admin->expects(static::any())
            ->method('getBaseControllerName')
            ->willReturn('sonata.admin.controller.crud');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode(['action' => 'foo', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'A `sonata.admin.controller.crud::batchActionFoo` method must be callable or create a `controller` configuration for your batch action.'
        );

        $this->controller->batchAction($this->request);
    }

    public function testBatchActionWithoutConfirmation(): void
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects(static::exactly(2))
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $this->admin->expects(static::any())
            ->method('getBaseControllerName')
            ->willReturn($baseControllerName = 'sonata.admin.controller.crud');

        $this->expectGetController($baseControllerName.'::batchActionDelete');

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects(static::once())
            ->method('getQuery')
            ->willReturn($query);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $this->httpKernel->expects(static::once())
            ->method('handle')
            ->with(
                static::callback(static function (Request $request) use ($baseControllerName, $query) {
                    static::assertSame(
                        $baseControllerName.'::batchActionDelete',
                        $request->attributes->get('_controller')
                    );

                    static::assertSame(
                        $query,
                        $request->attributes->get('query')
                    );

                    return true;
                }),
                HttpKernelInterface::SUB_REQUEST
            )
            ->willReturn($response = new Response());

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $modelManager->expects(static::once())
            ->method('addIdentifiersToQuery')
            ->with(static::equalTo('Foo'), static::equalTo($query), static::equalTo(['123', '456']));

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode(['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        static::assertNull($this->request->get('idx'));

        $result = $this->controller->batchAction($this->request);

        static::assertNull($this->request->get('idx'), 'Ensure original request is not modified by calling `CRUDController::batchAction()`.');
        static::assertSame($response, $result);
    }

    public function testBatchActionWithoutConfirmation2(): void
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects(static::exactly(2))
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $this->admin->expects(static::any())
            ->method('getBaseControllerName')
            ->willReturn($baseControllerName = 'sonata.admin.controller.crud');

        $this->expectGetController($baseControllerName.'::batchActionDelete');

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects(static::once())
            ->method('getQuery')
            ->willReturn($query);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $this->httpKernel->expects(static::once())
            ->method('handle')
            ->with(
                static::callback(static function (Request $request) use ($baseControllerName, $query) {
                    static::assertSame(
                        $baseControllerName.'::batchActionDelete',
                        $request->attributes->get('_controller')
                    );

                    static::assertSame(
                        $query,
                        $request->attributes->get('query')
                    );

                    return true;
                }),
                HttpKernelInterface::SUB_REQUEST
            )
            ->willReturn($response = new Response());

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $modelManager->expects(static::once())
            ->method('addIdentifiersToQuery')
            ->with(static::equalTo('Foo'), static::equalTo($query), static::equalTo(['123', '456']));

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('action', 'delete');
        $this->request->request->set('idx', ['123', '456']);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction($this->request);

        static::assertSame($response, $result);
    }

    /**
     * @phpstan-return iterable<array-key, array{array<string, mixed>}>
     */
    public function provideBatchActionWithConfirmationCases(): iterable
    {
        yield 'normal data' => [['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => false]];
        yield 'without all elements' => [['action' => 'delete', 'idx' => ['123', '456']]];
        yield 'all elements' => [['action' => 'delete', 'all_elements' => true]];
        yield 'idx is null' => [['action' => 'delete', 'idx' => null, 'all_elements' => true]];
        yield 'all_elements is null' => [['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => null]];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @dataProvider provideBatchActionWithConfirmationCases
     */
    public function testBatchActionWithConfirmation(array $data): void
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'translation_domain' => 'FooBarBaz', 'ask_confirmation' => true]];

        $this->admin->expects(static::exactly(2))
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $this->admin->expects(static::any())
            ->method('getBaseControllerName')
            ->willReturn($baseControllerName = 'sonata.admin.controller.crud');

        $this->expectGetController($baseControllerName.'::batchActionDelete');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode($data, \JSON_THROW_ON_ERROR));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $form = $this->createMock(Form::class);

        $formView = $this->createStub(FormView::class);

        $form->expects(static::once())
            ->method('createView')
            ->willReturn($formView);

        $datagrid->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('@SonataAdmin/CRUD/batch_confirmation.html.twig', [
                'admin' => $this->admin,
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'action' => 'list',
                'datagrid' => $datagrid,
                'form' => $formView,
                'csrf_token' => 'csrf-token-123_sonata.batch',
                'action_label' => 'Foo Bar',
                'data' => $data,
                'batch_translation_domain' => 'FooBarBaz',
            ]);

        $datagrid->expects(static::never())
            ->method('getQuery');

        static::assertInstanceOf(Response::class, $this->controller->batchAction($this->request));
        static::assertSame([], $this->session->getFlashBag()->all());
    }

    /**
     * @dataProvider provideBatchActionNonRelevantActionCases
     *
     * @group legacy
     *
     * NEXT_MAJOR: Remove this test
     */
    public function testBatchActionNonRelevantAction(string $actionName): void
    {
        $controller = new BatchAdminController();
        $controller->setContainer($this->container);
        $controller->configureAdmin($this->request);

        $batchActions = [$actionName => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects(static::exactly(2))
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $this->expectGetController();

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

        $result = $controller->batchAction($this->request);

        static::assertNull($this->request->get('all_elements'), 'Ensure original request is not modified by calling `CRUDController::batchAction()`.');
        static::assertInstanceOf(RedirectResponse::class, $result);
        static::assertSame(['flash_batch_empty'], $this->session->getFlashBag()->get('sonata_flash_info'));
        static::assertSame('list', $result->getTargetUrl());
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public function provideBatchActionNonRelevantActionCases(): iterable
    {
        yield ['foo'];
        yield ['foo_bar'];
        yield ['foo-bar'];
        yield ['foobar'];
    }

    public function testBatchActionWithCustomConfirmationTemplate(): void
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => true, 'template' => 'custom_template.html.twig']];

        $this->admin->expects(static::exactly(2))
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $this->expectGetController();

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

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('custom_template.html.twig');

        $this->controller->batchAction($this->request);
    }

    /**
     * @group legacy
     *
     * NEXT_MAJOR: Remove this test
     */
    public function testBatchActionNonRelevantAction2(): void
    {
        $controller = new BatchAdminController();
        $controller->setContainer($this->container);
        $controller->configureAdmin($this->request);

        $batchActions = ['foo' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects(static::exactly(2))
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $this->expectGetController();

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $this->expectTranslate('flash_foo_error', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('action', 'foo');
        $this->request->request->set('idx', ['999']);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $controller->batchAction($this->request);

        static::assertInstanceOf(RedirectResponse::class, $result);
        static::assertSame(['flash_foo_error'], $this->session->getFlashBag()->get('sonata_flash_info'));
        static::assertSame('list', $result->getTargetUrl());
    }

    public function testBatchActionNoItems(): void
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => true]];

        $this->admin->expects(static::exactly(2))
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $this->expectGetController();

        $datagrid = $this->createMock(DatagridInterface::class);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $this->expectTranslate('flash_batch_empty', [], 'SonataAdminBundle');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('action', 'delete');
        $this->request->request->set('idx', []);
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction($this->request);

        static::assertInstanceOf(RedirectResponse::class, $result);
        static::assertSame(['flash_batch_empty'], $this->session->getFlashBag()->get('sonata_flash_info'));
        static::assertSame('list', $result->getTargetUrl());
    }

    /**
     * @group legacy
     *
     * NEXT_MAJOR: Remove this test
     */
    public function testBatchActionNoItemsEmptyQuery(): void
    {
        $controller = new BatchAdminController();
        $controller->setContainer($this->container);
        $controller->configureAdmin($this->request);

        $batchActions = ['bar' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects(static::exactly(2))
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $this->expectGetController();

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
        $result = $controller->batchAction($this->request);

        static::assertInstanceOf(Response::class, $result);

        $content = $result->getContent();
        static::assertNotFalse($content);
        static::assertMatchesRegularExpression('/Redirecting to list/', $content);
    }

    public function testBatchActionWithRequesData(): void
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects(static::exactly(2))
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $this->expectGetController();

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects(static::once())
            ->method('getQuery')
            ->willReturn($query);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $this->httpKernel->expects(static::once())
            ->method('handle')
            ->willReturn($response = new Response());

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $modelManager->expects(static::once())
            ->method('addIdentifiersToQuery')
            ->with(static::equalTo('Foo'), static::equalTo($query), static::equalTo(['123', '456']));

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode(['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('foo', 'bar');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction($this->request);

        static::assertSame($response, $result);
        static::assertSame('bar', $this->request->request->get('foo'));
    }

    public function testBatchActionWithRequesDataIdsAndAllItems(): void
    {
        $batchActions = ['delete' => ['label' => 'Foo Bar', 'ask_confirmation' => false]];

        $this->admin->expects(static::exactly(2))
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $this->expectGetController();

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects(static::once())
            ->method('getQuery')
            ->willReturn($query);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $this->httpKernel->expects(static::once())
            ->method('handle')
            ->willReturn($response = new Response());

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $modelManager->expects(static::never())
            ->method('addIdentifiersToQuery');

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode(['action' => 'delete', 'idx' => ['123', '456'], 'all_elements' => true]));
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction($this->request);

        static::assertSame($response, $result);
    }

    /**
     * @phpstan-return iterable<array-key, array{string, string}>
     */
    public function getToStringValues(): iterable
    {
        yield ['', ''];
        yield ['Foo', 'Foo'];
        yield ['&lt;a href=&quot;http://foo&quot;&gt;Bar&lt;/a&gt;', '<a href="http://foo">Bar</a>'];
        yield ['&lt;&gt;&amp;&quot;&#039;abcdefghijklmnopqrstuvwxyz*-+.,?_()[]\/', '<>&"\'abcdefghijklmnopqrstuvwxyz*-+.,?_()[]\/'];
    }

    public function testBatchActionActionAnotherController(): void
    {
        $batchActions = [
            'foo' => [
                'label' => 'Foo Bar',
                'controller' => $controllerName = BatchOtherController::class.'::batchAction',
                'ask_confirmation' => false,
            ],
        ];

        $this->admin->expects(static::exactly(2))
            ->method('getBatchActions')
            ->willReturn($batchActions);

        $this->expectGetController($controllerName);

        $datagrid = $this->createMock(DatagridInterface::class);

        $query = $this->createMock(ProxyQueryInterface::class);
        $datagrid->expects(static::once())
            ->method('getQuery')
            ->willReturn($query);

        $this->admin->expects(static::once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $this->httpKernel->expects(static::once())
            ->method('handle')
            ->willReturn($response = new Response());

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $this->admin
            ->method('getClass')
            ->willReturn('Foo');

        $modelManager->expects(static::once())
            ->method('addIdentifiersToQuery')
            ->with(static::equalTo('Foo'), static::equalTo($query), static::equalTo(['123', '456']));

        $this->request->setMethod(Request::METHOD_POST);
        $this->request->request->set('data', json_encode(['action' => 'foo', 'idx' => ['123', '456'], 'all_elements' => false]));
        $this->request->request->set('foo', 'bar');
        $this->request->request->set('_sonata_csrf_token', 'csrf-token-123_sonata.batch');

        $result = $this->controller->batchAction($this->request);

        static::assertSame($response, $result);
    }

    private function assertLoggerLogsModelManagerException(MockObject $subject, string $method): void
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

    /**
     * @param array<string, mixed> $parameters
     */
    private function expectTranslate(
        string $id,
        array $parameters = [],
        ?string $domain = null,
        ?string $locale = null,
    ): void {
        $this->translator->expects(static::once())
            ->method('trans')
            ->with(static::equalTo($id), static::equalTo($parameters), static::equalTo($domain), static::equalTo($locale))
            ->willReturn($id);
    }

    private function expectGetController(?string $controllerName = null, bool $exists = true): void
    {
        $this->controllerResolver->expects(static::any())
            ->method('getController')
            ->with(
                static::callback(static function (Request $request) use ($controllerName) {
                    if (null !== $controllerName) {
                        static::assertSame(
                            $controllerName,
                            $request->attributes->get('_controller')
                        );
                    }

                    return true;
                })
            )
            ->willReturn($exists ? static function (): void {} : false);
    }
}
