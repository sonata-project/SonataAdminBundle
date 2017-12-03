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

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\HelperController;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Command\DebugCommand;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminControllerHelper_Foo
{
    private $bar;

    public function getAdminTitle()
    {
        return 'foo';
    }

    public function setEnabled($value)
    {
    }

    public function setBar(AdminControllerHelper_Bar $bar)
    {
        $this->bar = $bar;
    }

    public function getBar()
    {
        return $this->bar;
    }
}

class AdminControllerHelper_Bar
{
    public function getAdminTitle()
    {
        return 'bar';
    }

    public function setEnabled($value)
    {
    }

    public function getEnabled()
    {
    }
}

class HelperControllerTest extends TestCase
{
    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var HelperController
     */
    private $controller;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $container = $this->createMock(ContainerInterface::class);
        $pool = new Pool($container, 'title', 'logo.png');
        $pool->setAdminServiceIds(['foo.admin']);

        $this->admin = $this->createMock(AbstractAdmin::class);

        $twig = new \Twig_Environment($this->createMock(\Twig_LoaderInterface::class));
        $helper = new AdminHelper($pool);
        $validator = $this->createMock(ValidatorInterface::class);
        $this->controller = new HelperController($twig, $pool, $helper, $validator);

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) {
                switch ($id) {
                    case 'foo.admin':
                        return $this->admin;
                }
            }));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testgetShortObjectDescriptionActionInvalidAdmin()
    {
        $container = $this->createMock(ContainerInterface::class);
        $twig = new \Twig_Environment($this->createMock(\Twig_LoaderInterface::class));
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ]);
        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(['sonata.post.admin']);
        $helper = new AdminHelper($pool);
        $validator = $this->createMock(ValidatorInterface::class);
        $controller = new HelperController($twig, $pool, $helper, $validator);

        $controller->getShortObjectDescriptionAction($request);
    }

    /**
     * @expectedException \RuntimeException
     * @exceptionMessage Invalid format
     */
    public function testgetShortObjectDescriptionActionObjectDoesNotExist()
    {
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('setUniqid');
        $admin->expects($this->once())->method('getObject')->will($this->returnValue(false));

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $twig = new \Twig_Environment($this->createMock(\Twig_LoaderInterface::class));
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ]);

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(['sonata.post.admin']);

        $helper = new AdminHelper($pool);

        $validator = $this->createMock(ValidatorInterface::class);
        $controller = new HelperController($twig, $pool, $helper, $validator);

        $controller->getShortObjectDescriptionAction($request);
    }

    public function testgetShortObjectDescriptionActionEmptyObjectId()
    {
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('setUniqid');
        $admin->expects($this->once())->method('getObject')->with($this->identicalTo(null))->will($this->returnValue(false));

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $twig = new \Twig_Environment($this->createMock(\Twig_LoaderInterface::class));
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => '',
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ]);

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(['sonata.post.admin']);

        $helper = new AdminHelper($pool);

        $validator = $this->createMock(ValidatorInterface::class);
        $controller = new HelperController($twig, $pool, $helper, $validator);

        $controller->getShortObjectDescriptionAction($request);
    }

    public function testgetShortObjectDescriptionActionObject()
    {
        $mockTemplate = 'AdminHelperTest:mock-short-object-description.html.twig';

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('setUniqid');
        $admin->expects($this->once())->method('getTemplate')->will($this->returnValue($mockTemplate));
        $admin->expects($this->once())->method('getObject')->will($this->returnValue(new AdminControllerHelper_Foo()));
        $admin->expects($this->once())->method('toString')->will($this->returnValue('bar'));
        $admin->expects($this->once())->method('generateObjectUrl')->will($this->returnCallback(function ($type, $object, $parameters = []) {
            if ('edit' != $type) {
                return 'invalid name';
            }

            return '/ok/url';
        }));

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $twig = $this->getMockBuilder(\Twig_Environment::class)->disableOriginalConstructor()->getMock();

        $twig->expects($this->once())->method('render')
            ->with($mockTemplate)
            ->will($this->returnCallback(function ($templateName, $templateParams) {
                return sprintf('<a href="%s" target="new">%s</a>', $templateParams['admin']->generateObjectUrl('edit', $templateParams['object']), $templateParams['description']);
            }));

        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ]);

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(['sonata.post.admin']);

        $helper = new AdminHelper($pool);

        $validator = $this->createMock(ValidatorInterface::class);

        $controller = new HelperController($twig, $pool, $helper, $validator);

        $response = $controller->getShortObjectDescriptionAction($request);

        $expected = '<a href="/ok/url" target="new">bar</a>';
        $this->assertSame($expected, $response->getContent());
    }

    public function testsetObjectFieldValueAction()
    {
        $object = new AdminControllerHelper_Foo();

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getOption')->will($this->returnValue(true));

        $admin = $this->createMock(AbstractAdmin::class);
        $admin->expects($this->once())->method('getObject')->will($this->returnValue($object));
        $admin->expects($this->once())->method('hasAccess')->will($this->returnValue(true));
        $admin->expects($this->once())->method('getListFieldDescription')->will($this->returnValue($fieldDescription));
        $fieldDescription->expects($this->exactly(2))->method('getAdmin')->will($this->returnValue($admin));

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(['sonata.post.admin']);

        $adminExtension = new SonataAdminExtension(
            $pool,
            $this->createMock(LoggerInterface::class),
            $this->createMock(TranslatorInterface::class)
        );

        // NEXT_MAJOR: Remove this check when dropping support for twig < 2
        if (method_exists(\Twig_LoaderInterface::class, 'getSourceContext')) {
            $loader = $this->createMock(\Twig_LoaderInterface::class);
        } else {
            $loader = $this->createMock([\Twig_LoaderInterface::class, \Twig_SourceContextLoaderInterface::class]);
        }
        $loader->method('getSourceContext')->will($this->returnValue(new \Twig_Source('<foo />', 'foo')));

        $twig = new \Twig_Environment($loader);
        $twig->addExtension($adminExtension);
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $helper = new AdminHelper($pool);

        $validator = $this->createMock(ValidatorInterface::class);

        $validator
            ->expects($this->once())
            ->method('validate')
            ->with($object)
            ->will($this->returnValue(new ConstraintViolationList([])))
        ;

        $controller = new HelperController($twig, $pool, $helper, $validator);

        $response = $controller->setObjectFieldValueAction($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testappendFormFieldElementAction()
    {
        $object = new AdminControllerHelper_Foo();

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->expects($this->once())->method('find')->will($this->returnValue($object));

        $mockTheme = $this->getMockBuilder(FormView::class)
            ->disableOriginalConstructor()
            ->getMock();

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('getModelManager')->will($this->returnValue($modelManager));
        $admin->expects($this->once())->method('setRequest');
        $admin->expects($this->once())->method('setSubject');
        $admin->expects($this->once())->method('getFormTheme')->will($this->returnValue($mockTheme));

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $mockRenderer = $this->getMockBuilder(TwigRendererInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockRenderer->expects($this->once())
            ->method('searchAndRenderBlock')
            ->will($this->returnValue(new Response()));

        $twig = new \Twig_Environment($this->createMock(\Twig_LoaderInterface::class));

        // Remove the condition when dropping sf < 3.2
        if (method_exists(AppVariable::class, 'getToken')) {
            $twig->addExtension(new FormExtension());
            $runtimeLoader = $this
                ->getMockBuilder(\Twig_RuntimeLoaderInterface::class)
                ->getMock();

            // Remove the condition when dropping sf < 3.4
            if (!class_exists(DebugCommand::class)) {
                $runtimeLoader->expects($this->once())
                    ->method('load')
                    ->with($this->equalTo(TwigRenderer::class))
                    ->will($this->returnValue($mockRenderer));
            } else {
                $runtimeLoader->expects($this->once())
                    ->method('load')
                    ->with($this->equalTo(FormRenderer::class))
                    ->will($this->returnValue($mockRenderer));
            }

            $twig->addRuntimeLoader($runtimeLoader);
        } else {
            $twig->addExtension(new FormExtension($mockRenderer));
        }

        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(['sonata.post.admin']);

        $validator = $this->createMock(ValidatorInterface::class);

        $mockView = $this->getMockBuilder(FormView::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockForm->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($mockView));

        $helper = $this->getMockBuilder(AdminHelper::class)
            ->setMethods(['appendFormFieldElement', 'getChildFormView'])
            ->setConstructorArgs([$pool])
            ->getMock();
        $helper->expects($this->once())->method('appendFormFieldElement')->will($this->returnValue([
            $this->createMock(FieldDescriptionInterface::class),
            $mockForm,
        ]));
        $helper->expects($this->once())->method('getChildFormView')->will($this->returnValue($mockView));

        $controller = new HelperController($twig, $pool, $helper, $validator);
        $response = $controller->appendFormFieldElementAction($request);

        $this->isInstanceOf(Response::class, $response);
    }

    public function testRetrieveFormFieldElementAction()
    {
        $object = new AdminControllerHelper_Foo();

        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->expects($this->once())->method('find')->will($this->returnValue($object));

        $mockView = $this->getMockBuilder(FormView::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockForm->expects($this->once())
            ->method('setData')
            ->with($object);

        $mockForm->expects($this->once())
            ->method('handleRequest')
            ->with($request);

        $mockForm->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($mockView));

        $formBuilder = $this->getMockBuilder(FormBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formBuilder->expects($this->once())->method('getForm')->will($this->returnValue($mockForm));

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('getModelManager')->will($this->returnValue($modelManager));
        $admin->expects($this->once())->method('getFormBuilder')->will($this->returnValue($formBuilder));

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $mockRenderer = $this->getMockBuilder(TwigRendererInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockRenderer->expects($this->once())
            ->method('searchAndRenderBlock')
            ->will($this->returnValue(new Response()));

        $twig = new \Twig_Environment($this->createMock(\Twig_LoaderInterface::class));

        // Remove the condition when dropping sf < 3.2
        if (method_exists(AppVariable::class, 'getToken')) {
            $twig->addExtension(new FormExtension());
            $runtimeLoader = $this
                ->getMockBuilder(\Twig_RuntimeLoaderInterface::class)
                ->getMock();

            // Remove the condition when dropping sf < 3.4
            if (!class_exists(DebugCommand::class)) {
                $runtimeLoader->expects($this->once())
                    ->method('load')
                    ->with($this->equalTo(TwigRenderer::class))
                    ->will($this->returnValue($mockRenderer));
            } else {
                $runtimeLoader->expects($this->once())
                    ->method('load')
                    ->with($this->equalTo(FormRenderer::class))
                    ->will($this->returnValue($mockRenderer));
            }

            $twig->addRuntimeLoader($runtimeLoader);
        } else {
            $twig->addExtension(new FormExtension($mockRenderer));
        }

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(['sonata.post.admin']);

        $validator = $this->createMock(ValidatorInterface::class);

        $helper = $this->getMockBuilder(AdminHelper::class)
            ->setMethods(['getChildFormView'])
            ->setConstructorArgs([$pool])
            ->getMock();
        $helper->expects($this->once())->method('getChildFormView')->will($this->returnValue($mockView));

        $controller = new HelperController($twig, $pool, $helper, $validator);
        $response = $controller->retrieveFormFieldElementAction($request);

        $this->isInstanceOf(Response::class, $response);
    }

    public function testSetObjectFieldValueActionWithViolations()
    {
        $bar = new AdminControllerHelper_Bar();

        $object = new AdminControllerHelper_Foo();
        $object->setBar($bar);

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getOption')->will($this->returnValue(true));

        $admin = $this->createMock(AbstractAdmin::class);
        $admin->expects($this->once())->method('getObject')->will($this->returnValue($object));
        $admin->expects($this->once())->method('hasAccess')->will($this->returnValue(true));
        $admin->expects($this->once())->method('getListFieldDescription')->will($this->returnValue($fieldDescription));

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $twig = new \Twig_Environment($this->createMock(\Twig_LoaderInterface::class));
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'bar.enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(['sonata.post.admin']);

        $helper = new AdminHelper($pool);

        $violations = new ConstraintViolationList([
            new ConstraintViolation('error1', null, [], null, 'enabled', null),
            new ConstraintViolation('error2', null, [], null, 'enabled', null),
        ]);

        $validator = $this->createMock(ValidatorInterface::class);

        $validator
            ->expects($this->once())
            ->method('validate')
            ->with($bar)
            ->will($this->returnValue($violations))
        ;

        $controller = new HelperController($twig, $pool, $helper, $validator);

        $response = $controller->setObjectFieldValueAction($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertSame(json_encode("error1\nerror2"), $response->getContent());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @exceptionMessage Invalid format
     */
    public function testRetrieveAutocompleteItemsActionNotGranted()
    {
        $this->admin->expects($this->exactly(2))
            ->method('hasAccess')
            ->will($this->returnCallback(function ($operation) {
                if ('create' == $operation || 'edit' == $operation) {
                    return false;
                }

                return;
            }));

        $request = new Request([
            'admin_code' => 'foo.admin',
        ], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->controller->retrieveAutocompleteItemsAction($request);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @exceptionMessage Autocomplete list can`t be retrieved because the form element is disabled or read_only.
     */
    public function testRetrieveAutocompleteItemsActionDisabledFormelememt()
    {
        $this->admin->expects($this->once())
            ->method('hasAccess')
            ->with('create')
            ->will($this->returnValue(true));

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);

        $fieldDescription->expects($this->once())
            ->method('getTargetEntity')
            ->will($this->returnValue(Foo::class));

        $fieldDescription->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('barField'));

        $this->admin->expects($this->once())
            ->method('getFormFieldDescriptions')
            ->will($this->returnValue(null));

        $this->admin->expects($this->once())
            ->method('getFormFieldDescription')
            ->with('barField')
            ->will($this->returnValue($fieldDescription));

        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $formType = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->once())
            ->method('get')
            ->with('barField')
            ->will($this->returnValue($formType));

        $formConfig = $this->getMockBuilder(FormConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $formType->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($formConfig));

        $formConfig->expects($this->once())
            ->method('getAttribute')
            ->with('disabled')
            ->will($this->returnValue(true));

        $request = new Request([
            'admin_code' => 'foo.admin',
            'field' => 'barField',
        ], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->controller->retrieveAutocompleteItemsAction($request);
    }

    public function testRetrieveAutocompleteItemsTooShortSearchString()
    {
        $this->admin->expects($this->once())
            ->method('hasAccess')
            ->with('create')
            ->will($this->returnValue(true));

        $targetAdmin = $this->createMock(AbstractAdmin::class);
        $targetAdmin->expects($this->once())
            ->method('checkAccess')
            ->with('list')
            ->will($this->returnValue(null));

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);

        $fieldDescription->expects($this->once())
            ->method('getTargetEntity')
            ->will($this->returnValue(Foo::class));

        $fieldDescription->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('barField'));

        $fieldDescription->expects($this->once())
            ->method('getAssociationAdmin')
            ->will($this->returnValue($targetAdmin));

        $this->admin->expects($this->once())
            ->method('getFormFieldDescriptions')
            ->will($this->returnValue(null));

        $this->admin->expects($this->once())
            ->method('getFormFieldDescription')
            ->with('barField')
            ->will($this->returnValue($fieldDescription));

        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $formType = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->once())
            ->method('get')
            ->with('barField')
            ->will($this->returnValue($formType));

        $formConfig = $this->getMockBuilder(FormConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $formType->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($formConfig));

        $formConfig->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnCallback(function ($name, $default = null) {
                switch ($name) {
                    case 'property':
                        return 'foo';
                    case 'callback':
                        return;
                    case 'minimum_input_length':
                        return 3;
                    case 'items_per_page':
                        return 10;
                    case 'req_param_name_page_number':
                        return '_page';
                    case 'to_string_callback':
                        return;
                    case 'disabled':
                        return false;
                    case 'target_admin_access_action':
                        return 'list';
                    default:
                        throw new \RuntimeException(sprintf('Unkown parameter "%s" called.', $name));
                }
            }));

        $request = new Request([
            'admin_code' => 'foo.admin',
            'field' => 'barField',
            'q' => 'so',
        ], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $response = $this->controller->retrieveAutocompleteItemsAction($request);
        $this->isInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"KO","message":"Too short search string."}', $response->getContent());
    }

    public function testRetrieveAutocompleteItems()
    {
        $entity = new Foo();
        $this->admin->expects($this->once())
            ->method('hasAccess')
            ->with('create')
            ->will($this->returnValue(true));

        $this->admin->expects($this->once())
            ->method('id')
            ->with($entity)
            ->will($this->returnValue(123));

        $targetAdmin = $this->createMock(AbstractAdmin::class);
        $targetAdmin->expects($this->once())
            ->method('checkAccess')
            ->with('list')
            ->will($this->returnValue(null));

        $targetAdmin->expects($this->once())
            ->method('setPersistFilters')
            ->with(false)
            ->will($this->returnValue(null));

        $datagrid = $this->createMock(DatagridInterface::class);
        $targetAdmin->expects($this->once())
            ->method('getDatagrid')
            ->with()
            ->will($this->returnValue($datagrid));

        $metadata = $this->createMock(Metadata::class);
        $metadata->expects($this->once())
            ->method('getTitle')
            ->with()
            ->will($this->returnValue('FOO'));

        $targetAdmin->expects($this->once())
            ->method('getObjectMetadata')
            ->with($entity)
            ->will($this->returnValue($metadata));

        $datagrid->expects($this->once())
            ->method('hasFilter')
            ->with('foo')
            ->will($this->returnValue(true));

        $datagrid->expects($this->exactly(3))
            ->method('setValue')
            ->withConsecutive(
                [$this->equalTo('foo'), $this->equalTo(null), $this->equalTo('sonata')],
                [$this->equalTo('_per_page'), $this->equalTo(null), $this->equalTo(10)],
                [$this->equalTo('_page'), $this->equalTo(null), $this->equalTo(1)]
               )
            ->will($this->returnValue(null));

        $datagrid->expects($this->once())
            ->method('buildPager')
            ->with()
            ->will($this->returnValue(null));

        $pager = $this->createMock(Pager::class);
        $datagrid->expects($this->once())
            ->method('getPager')
            ->with()
            ->will($this->returnValue($pager));

        $pager->expects($this->once())
            ->method('getResults')
            ->with()
            ->will($this->returnValue([$entity]));

        $pager->expects($this->once())
            ->method('isLastPage')
            ->with()
            ->will($this->returnValue(true));

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);

        $fieldDescription->expects($this->once())
            ->method('getTargetEntity')
            ->will($this->returnValue(Foo::class));

        $fieldDescription->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('barField'));

        $fieldDescription->expects($this->once())
            ->method('getAssociationAdmin')
            ->will($this->returnValue($targetAdmin));

        $this->admin->expects($this->once())
            ->method('getFormFieldDescriptions')
            ->will($this->returnValue(null));

        $this->admin->expects($this->once())
            ->method('getFormFieldDescription')
            ->with('barField')
            ->will($this->returnValue($fieldDescription));

        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $formType = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->once())
            ->method('get')
            ->with('barField')
            ->will($this->returnValue($formType));

        $formConfig = $this->getMockBuilder(FormConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $formType->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($formConfig));

        $formConfig->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnCallback(function ($name, $default = null) {
                switch ($name) {
                    case 'property':
                        return 'foo';
                    case 'callback':
                        return;
                    case 'minimum_input_length':
                        return 3;
                    case 'items_per_page':
                        return 10;
                    case 'req_param_name_page_number':
                        return '_page';
                    case 'to_string_callback':
                        return;
                    case 'disabled':
                        return false;
                    case 'target_admin_access_action':
                        return 'list';
                    default:
                        throw new \RuntimeException(sprintf('Unkown parameter "%s" called.', $name));
                }
            }));

        $request = new Request([
            'admin_code' => 'foo.admin',
            'field' => 'barField',
            'q' => 'sonata',
        ], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $response = $this->controller->retrieveAutocompleteItemsAction($request);
        $this->isInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"OK","more":false,"items":[{"id":123,"label":"FOO"}]}', $response->getContent());
    }
}
