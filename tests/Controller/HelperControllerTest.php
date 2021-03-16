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
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\HelperController;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\DataTransformerResolver;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo;
use Sonata\AdminBundle\Tests\Fixtures\Filter\FooFilter;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Template;
use Twig\TemplateWrapper;

/**
 * @group legacy
 */
class HelperControllerTest extends TestCase
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var AdminHelper
     */
    private $helper;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var AbstractAdmin
     */
    private $admin;

    /**
     * @var HelperController
     */
    private $controller;

    /**
     * @var DataTransformerResolver
     */
    private $resolver;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->pool = $this->createStub(Pool::class);
        $this->twig = $this->createStub(Environment::class);
        $this->helper = $this->createStub(AdminHelper::class);
        $this->validator = $this->createStub(ValidatorInterface::class);
        $this->admin = $this->createMock(AbstractAdmin::class);
        $this->resolver = new DataTransformerResolver();

        $this->pool->method('getInstance')->willReturn($this->admin);

        $this->controller = new HelperController(
            $this->twig,
            $this->pool,
            $this->helper,
            $this->validator,
            $this->resolver
        );
    }

    public function testGetShortObjectDescriptionActionInvalidAdmin(): void
    {
        $code = 'sonata.post.admin';

        $request = new Request([
            'code' => $code,
            'objectId' => 42,
            'uniqid' => 'asdasd123',
            ]);

        $this->pool->method('getInstance')->with($code)->willThrowException(new \InvalidArgumentException());

        $this->expectException(NotFoundHttpException::class);

        $this->controller->getShortObjectDescriptionAction($request);
    }

    public function testGetShortObjectDescriptionActionObjectDoesNotExist(): void
    {
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ]);

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));
        $this->admin->expects($this->once())->method('setUniqid')->with('asdasd123');
        $this->admin->method('getObject')->with(42)->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid format');

        $this->controller->getShortObjectDescriptionAction($request);
    }

    public function testGetShortObjectDescriptionActionEmptyObjectId(): void
    {
        $request = new Request([
            'code' => 'sonata.post.admin',
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ]);

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));
        $this->admin->expects($this->once())->method('setUniqid')->with('asdasd123');
        $this->admin->method('getObject')->with(null)->willReturn(null);

        $response = $this->controller->getShortObjectDescriptionAction($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testGetShortObjectDescriptionActionObject(): void
    {
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ]);
        $object = new AdminControllerHelper_Foo();

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));
        $this->admin->expects($this->once())->method('setUniqid')->with('asdasd123');
        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getTemplate')->with('short_object_description')->willReturn('template');
        $this->admin->method('toString')->with($object)->willReturn('bar');
        $this->twig->method('render')->with('template', [
            'admin' => $this->admin,
            'description' => 'bar',
            'object' => $object,
            'link_parameters' => [],
        ])->willReturn('renderedTemplate');

        $response = $this->controller->getShortObjectDescriptionAction($request);

        $this->assertSame('renderedTemplate', $response->getContent());
    }

    public function testSetObjectFieldValueAction(): void
    {
        $object = new AdminControllerHelper_Foo();
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);
        $pool = $this->createStub(Pool::class);
        $template = $this->createStub(Template::class);
        $template->method('render')->willReturn('some value');
        $translator = $this->createStub(TranslatorInterface::class);
        $propertyAccessor = new PropertyAccessor();
        $templateRegistry = $this->createStub(TemplateRegistryInterface::class);
        $container = new Container();
        $modelManager = $this->createStub(ModelManagerInterface::class);

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));
        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getCode')->willReturn('sonata.post.admin');
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('getListFieldDescription')->with('enabled')->willReturn($fieldDescription);
        $this->admin->expects($this->once())->method('update')->with($object);
        // NEXT_MAJOR: Remove this line
        $this->admin->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $this->admin->method('getModelManager')->willReturn($modelManager);
        $templateRegistry->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $container->set('sonata.post.admin.template_registry', $templateRegistry);
        $this->pool->method('getPropertyAccessor')->willReturn($propertyAccessor);
        $this->twig->method('getExtension')->with(SonataAdminExtension::class)->willReturn(
            new SonataAdminExtension($pool, null, $translator, $container, $propertyAccessor)
        );
        $this->twig->method('load')->with('admin_template')->willReturn(new TemplateWrapper($this->twig, $template));
        $this->twig->method('isDebug')->willReturn(false);
        $fieldDescription->method('getOption')->willReturnMap([
            ['editable', null, true],
        ]);
        $fieldDescription->method('getAdmin')->willReturn($this->admin);
        $fieldDescription->method('getType')->willReturn('boolean');
        $fieldDescription->method('getTemplate')->willReturn(false);
        $fieldDescription->method('getValue')->willReturn('some value');
        $this->validator->method('validate')->with($object)->willReturn(new ConstraintViolationList([]));

        $response = $this->controller->setObjectFieldValueAction($request);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testSetObjectFieldValueActionOnARelationField(): void
    {
        $object = new AdminControllerHelper_Foo();
        $associationObject = new AdminControllerHelper_Bar();
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'bar',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        // NEXT_MAJOR: Use `createStub` instead of using mock builder
        $fieldDescription = $this->getMockBuilder(FieldDescriptionInterface::class)
            ->addMethods(['getTargetModel'])
            ->getMockForAbstractClass();
        $modelManager = $this->createStub(ModelManagerInterface::class);
        $template = $this->createStub(Template::class);
        $template->method('render')->willReturn('some value');
        $translator = $this->createStub(TranslatorInterface::class);
        $propertyAccessor = new PropertyAccessor();
        $templateRegistry = $this->createStub(TemplateRegistryInterface::class);
        $container = new Container();

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));
        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getCode')->willReturn('sonata.post.admin');
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('getListFieldDescription')->with('bar')->willReturn($fieldDescription);
        $this->admin->method('getClass')->willReturn(\get_class($object));
        $this->admin->expects($this->once())->method('update')->with($object);
        $container->set('sonata.post.admin.template_registry', $templateRegistry);
        // NEXT_MAJOR: Remove this line
        $this->admin->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $templateRegistry->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $this->admin->method('getModelManager')->willReturn($modelManager);
        $this->validator->method('validate')->with($object)->willReturn(new ConstraintViolationList([]));
        $this->twig->method('getExtension')->with(SonataAdminExtension::class)->willReturn(
            new SonataAdminExtension($this->pool, null, $translator, $container, $propertyAccessor)
        );
        $this->twig->method('load')->with('field_template')->willReturn(new TemplateWrapper($this->twig, $template));
        $this->twig->method('isDebug')->willReturn(false);
        $this->pool->method('getPropertyAccessor')->willReturn($propertyAccessor);
        $fieldDescription->method('getOption')->willReturnMap([
            ['editable', null, true],
            ['class', null, AdminControllerHelper_Bar::class],
        ]);
        $fieldDescription->method('getType')->willReturn('choice');
        $fieldDescription->method('getTargetModel')->willReturn(AdminControllerHelper_Bar::class);
        $fieldDescription->method('getAdmin')->willReturn($this->admin);
        $fieldDescription->method('getTemplate')->willReturn('field_template');
        $fieldDescription->method('getValue')->willReturn('some value');
        $modelManager->method('find')->with(\get_class($associationObject), 1)->willReturn($associationObject);

        $response = $this->controller->setObjectFieldValueAction($request);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAppendFormFieldElementAction(): void
    {
        $object = new AdminControllerHelper_Foo();
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST]);

        $modelManager = $this->createStub(ModelManagerInterface::class);
        $formView = new FormView();
        $form = $this->createStub(Form::class);

        $renderer = $this->configureFormRenderer();

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));
        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getClass')->willReturn(\get_class($object));
        $this->admin->expects($this->once())->method('setSubject')->with($object);
        $this->admin->method('getFormTheme')->willReturn($formView);
        $this->helper->method('appendFormFieldElement')->with($this->admin, $object, null)->willReturn([
            $this->createStub(FieldDescriptionInterface::class),
            $form,
        ]);
        $this->helper->method('getChildFormView')->with($formView, null)
            ->willReturn($formView);
        $modelManager->method('find')->with(\get_class($object), 42)->willReturn($object);
        $form->method('createView')->willReturn($formView);
        $renderer->expects($this->once())->method('setTheme')->with($formView, $formView);
        $renderer->method('searchAndRenderBlock')->with($formView, 'widget')->willReturn('block');

        $response = $this->controller->appendFormFieldElementAction($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame($response->getContent(), 'block');
    }

    public function testRetrieveFormFieldElementAction(): void
    {
        $object = new AdminControllerHelper_Foo();
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $modelManager = $this->createStub(ModelManagerInterface::class);
        $formView = new FormView();
        $form = $this->createMock(Form::class);
        $formBuilder = $this->createStub(FormBuilder::class);

        $renderer = $this->configureFormRenderer();

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));
        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getClass')->willReturn(\get_class($object));
        $this->admin->expects($this->once())->method('setSubject')->with($object);
        $this->admin->method('getFormTheme')->willReturn($formView);
        $this->admin->method('getFormBuilder')->willReturn($formBuilder);
        $this->helper->method('getChildFormView')->with($formView, null)
            ->willReturn($formView);
        $modelManager->method('find')->with(\get_class($object), 42)->willReturn($object);
        $form->expects($this->once())->method('setData')->with($object);
        $form->expects($this->once())->method('handleRequest')->with($request);
        $form->method('createView')->willReturn($formView);
        $formBuilder->method('getForm')->willReturn($form);
        $renderer->expects($this->once())->method('setTheme')->with($formView, $formView);
        $renderer->method('searchAndRenderBlock')->with($formView, 'widget')->willReturn('block');

        $response = $this->controller->retrieveFormFieldElementAction($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame($response->getContent(), 'block');
    }

    public function testSetObjectFieldValueActionWithViolations(): void
    {
        $bar = new AdminControllerHelper_Bar();
        $object = new AdminControllerHelper_Foo();
        $object->setBar($bar);
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'bar.enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);
        $propertyAccessor = new PropertyAccessor();
        $modelManager = $this->createStub(ModelManagerInterface::class);

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));
        $this->pool->method('getPropertyAccessor')->willReturn($propertyAccessor);
        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('getListFieldDescription')->with('bar.enabled')->willReturn($fieldDescription);
        $this->admin->method('getModelManager')->willReturn($modelManager);
        $this->validator->method('validate')->with($bar)->willReturn(new ConstraintViolationList([
            new ConstraintViolation('error1', null, [], null, 'enabled', null),
            new ConstraintViolation('error2', null, [], null, 'enabled', null),
        ]));
        $fieldDescription->method('getOption')->willReturnMap([
            ['editable', null, true],
        ]);
        $fieldDescription->method('getType')->willReturn('boolean');

        $response = $this->controller->setObjectFieldValueAction($request);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(json_encode("error1\nerror2"), $response->getContent());
    }

    public function testRetrieveAutocompleteItemsActionNotGranted(): void
    {
        $request = new Request([
            'admin_code' => 'foo.admin',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));
        $this->admin->method('hasAccess')->willReturnMap([
            ['create', false],
            ['edit', false],
        ]);

        $this->expectException(AccessDeniedException::class);

        $this->controller->retrieveAutocompleteItemsAction($request);
    }

    public function testRetrieveAutocompleteItemsActionDisabledFormElement(): void
    {
        $object = new AdminControllerHelper_Foo();
        $request = new Request([
            'admin_code' => 'foo.admin',
            'field' => 'barField',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        // NEXT_MAJOR: Use `createStub` instead of using mock builder
        $fieldDescription = $this->getMockBuilder(FieldDescriptionInterface::class)
            ->addMethods(['getTargetModel'])
            ->getMockForAbstractClass();

        $this->configureFormConfig('barField', true);

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));
        $this->admin->method('getNewInstance')->willReturn($object);
        $this->admin->expects($this->once())->method('setSubject')->with($object);
        $this->admin->method('hasAccess')->with('create')->willReturn(true);
        $this->admin->method('getFormFieldDescriptions')->willReturn(null);
        $this->admin->method('getFormFieldDescription')->with('barField')->willReturn($fieldDescription);

        $fieldDescription->method('getTargetModel')->willReturn(Foo::class);
        $fieldDescription->method('getName')->willReturn('barField');

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Autocomplete list can`t be retrieved because the form element is disabled or read_only.');

        $this->controller->retrieveAutocompleteItemsAction($request);
    }

    public function testRetrieveAutocompleteItemsTooShortSearchString(): void
    {
        $object = new AdminControllerHelper_Foo();
        $request = new Request([
            'admin_code' => 'foo.admin',
            'field' => 'barField',
            'q' => 'so',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $targetAdmin = $this->createStub(AbstractAdmin::class);
        // NEXT_MAJOR: Use `createStub` instead of using mock builder
        $fieldDescription = $this->getMockBuilder(FieldDescriptionInterface::class)
            ->addMethods(['getTargetModel'])
            ->getMockForAbstractClass();

        $this->configureFormConfig('barField');

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));
        $this->admin->method('getNewInstance')->willReturn($object);
        $this->admin->expects($this->once())->method('setSubject')->with($object);
        $this->admin->method('hasAccess')->with('create')->willReturn(true);
        $this->admin->method('getFormFieldDescription')->with('barField')->willReturn($fieldDescription);
        $this->admin->method('getFormFieldDescriptions')->willReturn(null);
        $targetAdmin->method('checkAccess')->with('list')->willReturn(null);
        $fieldDescription->method('getTargetModel')->willReturn(Foo::class);
        $fieldDescription->method('getName')->willReturn('barField');
        $fieldDescription->method('getAssociationAdmin')->willReturn($targetAdmin);

        $response = $this->controller->retrieveAutocompleteItemsAction($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"KO","message":"Too short search string."}', $response->getContent());
    }

    public function testRetrieveAutocompleteItems(): void
    {
        $request = new Request([
            'admin_code' => 'foo.admin',
            'field' => 'barField',
            'q' => 'sonata',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->configureFormConfig('barField');

        $datagrid = $this->configureAutocompleteItemsDatagrid();
        $filter = new FooFilter();
        $filter->initialize('foo');

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));

        $datagrid->method('hasFilter')->with('foo')->willReturn(true);
        $datagrid->method('getFilter')->with('foo')->willReturn($filter);
        $datagrid->expects($this->exactly(3))->method('setValue')->withConsecutive(
            ['foo', null, 'sonata'],
            ['_per_page', null, 10],
            ['_page', null, 1]
        );

        $response = $this->controller->retrieveAutocompleteItemsAction($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"OK","more":false,"items":[{"id":123,"label":"FOO"}]}', $response->getContent());
    }

    public function testRetrieveAutocompleteItemsComplexPropertyArray(): void
    {
        $request = new Request([
            'admin_code' => 'foo.admin',
            'field' => 'barField',
            'q' => 'sonata',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->configureFormConfigComplexPropertyArray('barField');
        $datagrid = $this->configureAutocompleteItemsDatagrid();

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));

        $filter = new FooFilter();
        $filter->initialize('entity.property');

        $filter2 = new FooFilter();
        $filter2->initialize('entity2.property2');

        $datagrid->method('hasFilter')->willReturnMap([
            ['entity.property', true],
            ['entity2.property2', true],
        ]);
        $datagrid->method('getFilter')->willReturnMap([
            ['entity.property', $filter],
            ['entity2.property2', $filter2],
        ]);
        $datagrid->expects($this->exactly(4))->method('setValue')->withConsecutive(
            ['entity__property', null, 'sonata'],
            ['entity2__property2', null, 'sonata'],
            ['_per_page', null, 10],
            ['_page', null, 1]
        );

        $response = $this->controller->retrieveAutocompleteItemsAction($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"OK","more":false,"items":[{"id":123,"label":"FOO"}]}', $response->getContent());
    }

    public function testRetrieveAutocompleteItemsComplexProperty(): void
    {
        $request = new Request([
            'admin_code' => 'foo.admin',
            'field' => 'barField',
            'q' => 'sonata',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->configureFormConfigComplexProperty('barField');
        $datagrid = $this->configureAutocompleteItemsDatagrid();

        $filter = new FooFilter();
        $filter->initialize('entity.property');

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));

        $datagrid->method('hasFilter')->with('entity.property')->willReturn(true);
        $datagrid->method('getFilter')->with('entity.property')->willReturn($filter);
        $datagrid->expects($this->exactly(3))->method('setValue')->withConsecutive(
            ['entity__property', null, 'sonata'],
            ['_per_page', null, 10],
            ['_page', null, 1]
        );

        $response = $this->controller->retrieveAutocompleteItemsAction($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"OK","more":false,"items":[{"id":123,"label":"FOO"}]}', $response->getContent());
    }

    private function configureAutocompleteItemsDatagrid(): MockObject
    {
        $model = new Foo();

        $targetAdmin = $this->createStub(AbstractAdmin::class);
        $datagrid = $this->createMock(DatagridInterface::class);
        $metadata = $this->createStub(MetadataInterface::class);
        $pager = $this->createStub(Pager::class);
        // NEXT_MAJOR: Use `createStub` instead of using mock builder
        $fieldDescription = $this->getMockBuilder(FieldDescriptionInterface::class)
            ->addMethods(['getTargetModel'])
            ->getMockForAbstractClass();

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));
        $this->admin->method('getNewInstance')->willReturn($model);
        $this->admin->expects($this->once())->method('setSubject')->with($model);
        $this->admin->method('hasAccess')->with('create')->willReturn(true);
        $this->admin->method('getFormFieldDescription')->with('barField')->willReturn($fieldDescription);
        $this->admin->method('getFormFieldDescriptions')->willReturn(null);
        $this->admin->method('id')->with($model)->willReturn(123);
        $targetAdmin->expects($this->once())->method('checkAccess')->with('list');
        $targetAdmin->expects($this->once())->method('setFilterPersister')->with(null);
        $targetAdmin->method('getDatagrid')->willReturn($datagrid);
        $targetAdmin->method('getObjectMetadata')->with($model)->willReturn($metadata);
        $metadata->method('getTitle')->willReturn('FOO');

        $datagrid->method('buildPager')->willReturn(null);
        $datagrid->method('getPager')->willReturn($pager);
        $pager->method('getResults')->willReturn([$model]);
        $pager->method('isLastPage')->willReturn(true);
        $fieldDescription->method('getTargetModel')->willReturn(Foo::class);
        $fieldDescription->method('getName')->willReturn('barField');
        $fieldDescription->method('getAssociationAdmin')->willReturn($targetAdmin);

        return $datagrid;
    }

    private function configureFormConfig(string $field, bool $disabled = false): void
    {
        $form = $this->createStub(Form::class);
        $formType = $this->createStub(Form::class);
        $formConfig = $this->createStub(FormConfigInterface::class);

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));
        $this->admin->method('getForm')->willReturn($form);
        $form->method('get')->with($field)->willReturn($formType);
        $formType->method('getConfig')->willReturn($formConfig);
        $formConfig->method('getAttribute')->willReturnMap([
            ['disabled', null, $disabled],
            ['property', null, 'foo'],
            ['callback', null, null],
            ['minimum_input_length', null, 3],
            ['items_per_page', null, 10],
            ['req_param_name_page_number', null, '_page'],
            ['to_string_callback', null, null],
            ['target_admin_access_action', null, 'list'],
        ]);
    }

    private function configureFormConfigComplexProperty(string $field): void
    {
        $form = $this->createStub(Form::class);
        $formType = $this->createStub(Form::class);
        $formConfig = $this->createStub(FormConfigInterface::class);

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));
        $this->admin->method('getForm')->willReturn($form);
        $form->method('get')->with($field)->willReturn($formType);
        $formType->method('getConfig')->willReturn($formConfig);
        $formConfig->method('getAttribute')->willReturnMap([
            ['disabled', null, false],
            ['property', null, 'entity.property'],
            ['callback', null, null],
            ['minimum_input_length', null, 3],
            ['items_per_page', null, 10],
            ['req_param_name_page_number', null, '_page'],
            ['to_string_callback', null, null],
            ['target_admin_access_action', null, 'list'],
        ]);
    }

    private function configureFormConfigComplexPropertyArray(string $field): void
    {
        $form = $this->createStub(Form::class);
        $formType = $this->createStub(Form::class);
        $formConfig = $this->createStub(FormConfigInterface::class);

        $this->admin->expects($this->once())->method('setRequest')->with($this->isInstanceOf(Request::class));
        $this->admin->method('getForm')->willReturn($form);
        $form->method('get')->with($field)->willReturn($formType);
        $formType->method('getConfig')->willReturn($formConfig);
        $formConfig->method('getAttribute')->willReturnMap([
            ['disabled', null, false],
            ['property', null, ['entity.property', 'entity2.property2']],
            ['callback', null, null],
            ['minimum_input_length', null, 3],
            ['items_per_page', null, 10],
            ['req_param_name_page_number', null, '_page'],
            ['to_string_callback', null, null],
            ['target_admin_access_action', null, 'list'],
        ]);
    }

    private function configureFormRenderer()
    {
        $runtime = $this->createMock(FormRenderer::class);

        $this->twig->method('getRuntime')->with(FormRenderer::class)->willReturn($runtime);

        return $runtime;
    }
}
