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
use Prophecy\Argument;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\HelperController;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bridge\Twig\Command\DebugCommand;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;
use Twig\Template;
use Twig\TemplateWrapper;

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
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->pool = $this->prophesize(Pool::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->helper = $this->prophesize(AdminHelper::class);
        $this->validator = $this->prophesize(ValidatorInterface::class);
        $this->admin = $this->prophesize(AbstractAdmin::class);

        $this->pool->getInstance(Argument::any())->willReturn($this->admin->reveal());
        $this->admin->setRequest(Argument::type(Request::class))->shouldBeCalled();

        $this->controller = new HelperController(
            $this->twig->reveal(),
            $this->pool->reveal(),
            $this->helper->reveal(),
            $this->validator->reveal()
        );
    }

    public function testGetShortObjectDescriptionActionInvalidAdmin()
    {
        $this->expectException(NotFoundHttpException::class);

        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ]);

        $this->pool->getInstance('sonata.post.admin')->willReturn(null);
        $this->admin->setRequest(Argument::type(Request::class))->shouldNotBeCalled();

        $this->controller->getShortObjectDescriptionAction($request);
    }

    public function testGetShortObjectDescriptionActionObjectDoesNotExist()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid format');

        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ]);

        $this->admin->setUniqid('asdasd123')->shouldBeCalled();
        $this->admin->getObject(42)->willReturn(false);

        $this->controller->getShortObjectDescriptionAction($request);
    }

    public function testGetShortObjectDescriptionActionEmptyObjectId()
    {
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => '',
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ]);

        $this->admin->setUniqid('asdasd123')->shouldBeCalled();
        $this->admin->getObject(null)->willReturn(false);

        $response = $this->controller->getShortObjectDescriptionAction($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testGetShortObjectDescriptionActionObject()
    {
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ]);
        $object = new AdminControllerHelper_Foo();

        $this->admin->setUniqid('asdasd123')->shouldBeCalled();
        $this->admin->getObject(42)->willReturn($object);
        $this->admin->getTemplate('short_object_description')->willReturn('template');
        $this->admin->toString($object)->willReturn('bar');
        $this->twig->render('template', [
            'admin' => $this->admin->reveal(),
            'description' => 'bar',
            'object' => $object,
            'link_parameters' => [],
        ])->willReturn('renderedTemplate');

        $response = $this->controller->getShortObjectDescriptionAction($request);

        $this->assertSame('renderedTemplate', $response->getContent());
    }

    public function testSetObjectFieldValueAction()
    {
        $object = new AdminControllerHelper_Foo();
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->prophesize(FieldDescriptionInterface::class);
        $pool = $this->prophesize(Pool::class);
        $template = $this->prophesize(Template::class);
        $translator = $this->prophesize(TranslatorInterface::class);
        $propertyAccessor = new PropertyAccessor();
        $templateRegistry = $this->prophesize(TemplateRegistryInterface::class);
        $container = $this->prophesize(ContainerInterface::class);

        $this->admin->getObject(42)->willReturn($object);
        $this->admin->getCode()->willReturn('sonata.post.admin');
        $this->admin->hasAccess('edit', $object)->willReturn(true);
        $this->admin->getListFieldDescription('enabled')->willReturn($fieldDescription->reveal());
        $this->admin->update($object)->shouldBeCalled();
        // NEXT_MAJOR: Remove this line
        $this->admin->getTemplate('base_list_field')->willReturn('admin_template');
        $templateRegistry->getTemplate('base_list_field')->willReturn('admin_template');
        $container->get('sonata.post.admin.template_registry')->willReturn($templateRegistry->reveal());
        $this->pool->getPropertyAccessor()->willReturn($propertyAccessor);
        $this->twig->getExtension(SonataAdminExtension::class)->willReturn(
            new SonataAdminExtension($pool->reveal(), null, $translator->reveal(), $container->reveal())
        );
        $this->twig->load('admin_template')->willReturn(new TemplateWrapper($this->twig->reveal(), $template->reveal()));
        $this->twig->isDebug()->willReturn(false);
        $fieldDescription->getOption('editable')->willReturn(true);
        $fieldDescription->getAdmin()->willReturn($this->admin->reveal());
        $fieldDescription->getType()->willReturn('boolean');
        $fieldDescription->getTemplate()->willReturn(false);
        $fieldDescription->getValue(Argument::cetera())->willReturn('some value');
        $this->validator->validate($object)->willReturn(new ConstraintViolationList([]));

        $response = $this->controller->setObjectFieldValueAction($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSetObjectFieldValueActionOnARelationField()
    {
        $object = new AdminControllerHelper_Foo();
        $associationObject = new AdminControllerHelper_Bar();
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'bar',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->prophesize(FieldDescriptionInterface::class);
        $modelManager = $this->prophesize(ModelManagerInterface::class);
        $template = $this->prophesize(Template::class);
        $translator = $this->prophesize(TranslatorInterface::class);
        $propertyAccessor = new PropertyAccessor();
        $templateRegistry = $this->prophesize(TemplateRegistryInterface::class);
        $container = $this->prophesize(ContainerInterface::class);

        $this->admin->getObject(42)->willReturn($object);
        $this->admin->getCode()->willReturn('sonata.post.admin');
        $this->admin->hasAccess('edit', $object)->willReturn(true);
        $this->admin->getListFieldDescription('bar')->willReturn($fieldDescription->reveal());
        $this->admin->getClass()->willReturn(get_class($object));
        $this->admin->update($object)->shouldBeCalled();
        $container->get('sonata.post.admin.template_registry')->willReturn($templateRegistry->reveal());
        // NEXT_MAJOR: Remove this line
        $this->admin->getTemplate('base_list_field')->willReturn('admin_template');
        $templateRegistry->getTemplate('base_list_field')->willReturn('admin_template');
        $this->admin->getModelManager()->willReturn($modelManager->reveal());
        $this->validator->validate($object)->willReturn(new ConstraintViolationList([]));
        $this->twig->getExtension(SonataAdminExtension::class)->willReturn(
            new SonataAdminExtension($this->pool->reveal(), null, $translator->reveal(), $container->reveal())
        );
        $this->twig->load('field_template')->willReturn(new TemplateWrapper($this->twig->reveal(), $template->reveal()));
        $this->twig->isDebug()->willReturn(false);
        $this->pool->getPropertyAccessor()->willReturn($propertyAccessor);
        $fieldDescription->getType()->willReturn('choice');
        $fieldDescription->getOption('editable')->willReturn(true);
        $fieldDescription->getOption('class')->willReturn(AdminControllerHelper_Bar::class);
        $fieldDescription->getTargetEntity()->willReturn(AdminControllerHelper_Bar::class);
        $fieldDescription->getAdmin()->willReturn($this->admin->reveal());
        $fieldDescription->getTemplate()->willReturn('field_template');
        $fieldDescription->getValue(Argument::cetera())->willReturn('some value');
        $modelManager->find(get_class($associationObject), 1)->willReturn($associationObject);

        $response = $this->controller->setObjectFieldValueAction($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAppendFormFieldElementAction()
    {
        $object = new AdminControllerHelper_Foo();
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $modelManager = $this->prophesize(ModelManagerInterface::class);
        $formView = new FormView();
        $form = $this->prophesize(Form::class);

        $renderer = $this->configureFormRenderer();

        $this->admin->getObject(42)->willReturn($object);
        $this->admin->getClass()->willReturn(get_class($object));
        $this->admin->setSubject($object)->shouldBeCalled();
        $this->admin->getFormTheme()->willReturn($formView);
        $this->helper->appendFormFieldElement($this->admin->reveal(), $object, null)->willReturn([
            $this->prophesize(FieldDescriptionInterface::class),
            $form->reveal(),
        ]);
        $this->helper->getChildFormView($formView, null)
            ->willReturn($formView);
        $modelManager->find(get_class($object), 42)->willReturn($object);
        $form->createView()->willReturn($formView);
        $renderer->setTheme($formView, $formView)->shouldBeCalled();
        $renderer->searchAndRenderBlock($formView, 'widget')->willReturn('block');

        $response = $this->controller->appendFormFieldElementAction($request);

        $this->isInstanceOf(Response::class, $response);
        $this->assertSame($response->getContent(), 'block');
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

        $modelManager = $this->prophesize(ModelManagerInterface::class);
        $formView = new FormView();
        $form = $this->prophesize(Form::class);
        $formBuilder = $this->prophesize(FormBuilder::class);

        $renderer = $this->configureFormRenderer();

        $this->admin->getObject(42)->willReturn($object);
        $this->admin->getClass()->willReturn(get_class($object));
        $this->admin->setSubject($object)->shouldBeCalled();
        $this->admin->getFormTheme()->willReturn($formView);
        $this->admin->getFormBuilder()->willReturn($formBuilder->reveal());
        $this->helper->getChildFormView($formView, null)
            ->willReturn($formView);
        $modelManager->find(get_class($object), 42)->willReturn($object);
        $form->setData($object)->shouldBeCalled();
        $form->handleRequest($request)->shouldBeCalled();
        $form->createView()->willReturn($formView);
        $formBuilder->getForm()->willReturn($form->reveal());
        $renderer->setTheme($formView, $formView)->shouldBeCalled();
        $renderer->searchAndRenderBlock($formView, 'widget')->willReturn('block');

        $response = $this->controller->retrieveFormFieldElementAction($request);

        $this->isInstanceOf(Response::class, $response);
        $this->assertSame($response->getContent(), 'block');
    }

    public function testSetObjectFieldValueActionWithViolations()
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
        ], [], [], [], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->prophesize(FieldDescriptionInterface::class);
        $propertyAccessor = new PropertyAccessor();

        $this->pool->getPropertyAccessor()->willReturn($propertyAccessor);
        $this->admin->getObject(42)->willReturn($object);
        $this->admin->hasAccess('edit', $object)->willReturn(true);
        $this->admin->getListFieldDescription('bar.enabled')->willReturn($fieldDescription->reveal());
        $this->validator->validate($bar)->willReturn(new ConstraintViolationList([
            new ConstraintViolation('error1', null, [], null, 'enabled', null),
            new ConstraintViolation('error2', null, [], null, 'enabled', null),
        ]));
        $fieldDescription->getOption('editable')->willReturn(true);
        $fieldDescription->getType()->willReturn('boolean');

        $response = $this->controller->setObjectFieldValueAction($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertSame(json_encode("error1\nerror2"), $response->getContent());
    }

    public function testRetrieveAutocompleteItemsActionNotGranted()
    {
        $this->expectException(AccessDeniedException::class);

        $request = new Request([
            'admin_code' => 'foo.admin',
        ], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->admin->hasAccess('create')->willReturn(false);
        $this->admin->hasAccess('edit')->willReturn(false);

        $this->controller->retrieveAutocompleteItemsAction($request);
    }

    public function testRetrieveAutocompleteItemsActionDisabledFormelememt()
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Autocomplete list can`t be retrieved because the form element is disabled or read_only.');

        $object = new AdminControllerHelper_Foo();
        $request = new Request([
            'admin_code' => 'foo.admin',
            'field' => 'barField',
        ], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->prophesize(FieldDescriptionInterface::class);

        $this->configureFormConfig('barField', true);

        $this->admin->getNewInstance()->willReturn($object);
        $this->admin->setSubject($object)->shouldBeCalled();
        $this->admin->hasAccess('create')->willReturn(true);
        $this->admin->getFormFieldDescriptions()->willReturn(null);
        $this->admin->getFormFieldDescription('barField')->willReturn($fieldDescription->reveal());

        $fieldDescription->getTargetEntity()->willReturn(Foo::class);
        $fieldDescription->getName()->willReturn('barField');

        $this->controller->retrieveAutocompleteItemsAction($request);
    }

    public function testRetrieveAutocompleteItemsTooShortSearchString()
    {
        $object = new AdminControllerHelper_Foo();
        $request = new Request([
            'admin_code' => 'foo.admin',
            'field' => 'barField',
            'q' => 'so',
        ], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $targetAdmin = $this->prophesize(AbstractAdmin::class);
        $fieldDescription = $this->prophesize(FieldDescriptionInterface::class);

        $this->configureFormConfig('barField');

        $this->admin->getNewInstance()->willReturn($object);
        $this->admin->setSubject($object)->shouldBeCalled();
        $this->admin->hasAccess('create')->willReturn(true);
        $this->admin->getFormFieldDescription('barField')->willReturn($fieldDescription->reveal());
        $this->admin->getFormFieldDescriptions()->willReturn(null);
        $targetAdmin->checkAccess('list')->willReturn(null);
        $fieldDescription->getTargetEntity()->willReturn(Foo::class);
        $fieldDescription->getName()->willReturn('barField');
        $fieldDescription->getAssociationAdmin()->willReturn($targetAdmin->reveal());

        $response = $this->controller->retrieveAutocompleteItemsAction($request);

        $this->isInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"KO","message":"Too short search string."}', $response->getContent());
    }

    public function testRetrieveAutocompleteItems()
    {
        $entity = new Foo();
        $request = new Request([
            'admin_code' => 'foo.admin',
            'field' => 'barField',
            'q' => 'sonata',
        ], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $targetAdmin = $this->prophesize(AbstractAdmin::class);
        $datagrid = $this->prophesize(DatagridInterface::class);
        $metadata = $this->prophesize(Metadata::class);
        $pager = $this->prophesize(Pager::class);
        $fieldDescription = $this->prophesize(FieldDescriptionInterface::class);

        $this->configureFormConfig('barField');

        $this->admin->getNewInstance()->willReturn($entity);
        $this->admin->setSubject($entity)->shouldBeCalled();
        $this->admin->hasAccess('create')->willReturn(true);
        $this->admin->getFormFieldDescription('barField')->willReturn($fieldDescription->reveal());
        $this->admin->getFormFieldDescriptions()->willReturn(null);
        $this->admin->id($entity)->willReturn(123);
        $targetAdmin->checkAccess('list')->shouldBeCalled();
        $targetAdmin->setFilterPersister(null)->shouldBeCalled();
        $targetAdmin->getDatagrid()->willReturn($datagrid->reveal());
        $targetAdmin->getObjectMetadata($entity)->willReturn($metadata->reveal());
        $metadata->getTitle()->willReturn('FOO');
        $datagrid->hasFilter('foo')->willReturn(true);
        $datagrid->setValue('foo', null, 'sonata')->shouldBeCalled();
        $datagrid->setValue('_per_page', null, 10)->shouldBeCalled();
        $datagrid->setValue('_page', null, 1)->shouldBeCalled();
        $datagrid->buildPager()->willReturn(null);
        $datagrid->getPager()->willReturn($pager->reveal());
        $pager->getResults()->willReturn([$entity]);
        $pager->isLastPage()->willReturn(true);
        $fieldDescription->getTargetEntity()->willReturn(Foo::class);
        $fieldDescription->getName()->willReturn('barField');
        $fieldDescription->getAssociationAdmin()->willReturn($targetAdmin->reveal());

        $response = $this->controller->retrieveAutocompleteItemsAction($request);

        $this->isInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"OK","more":false,"items":[{"id":123,"label":"FOO"}]}', $response->getContent());
    }

    private function configureFormConfig($field, $disabled = false)
    {
        $form = $this->prophesize(Form::class);
        $formType = $this->prophesize(Form::class);
        $formConfig = $this->prophesize(FormConfigInterface::class);

        $this->admin->getForm()->willReturn($form->reveal());
        $form->get($field)->willReturn($formType->reveal());
        $formType->getConfig()->willReturn($formConfig->reveal());
        $formConfig->getAttribute('disabled')->willReturn($disabled);
        $formConfig->getAttribute('property')->willReturn('foo');
        $formConfig->getAttribute('callback')->willReturn(null);
        $formConfig->getAttribute('minimum_input_length')->willReturn(3);
        $formConfig->getAttribute('items_per_page')->willReturn(10);
        $formConfig->getAttribute('req_param_name_page_number')->willReturn('_page');
        $formConfig->getAttribute('to_string_callback')->willReturn(null);
        $formConfig->getAttribute('target_admin_access_action')->willReturn('list');
    }

    private function configureFormRenderer()
    {
        $runtime = $this->prophesize(FormRenderer::class);

        // Remove the condition when dropping sf < 3.2
        if (!method_exists(AppVariable::class, 'getToken')) {
            $extension = $this->prophesize(FormExtension::class);

            $this->twig->getExtension(FormExtension::class)->willReturn($extension->reveal());
            $extension->initRuntime($this->twig->reveal())->shouldBeCalled();
            $extension->renderer = $runtime->reveal();

            return $runtime;
        }

        // Remove the condition when dropping sf < 3.4
        if (!method_exists(DebugCommand::class, 'getLoaderPaths')) {
            $twigRuntime = $this->prophesize(TwigRenderer::class);

            $this->twig->getRuntime(TwigRenderer::class)->willReturn($twigRuntime->reveal());
            $twigRuntime->setEnvironment($this->twig->reveal())->shouldBeCalled();

            return $twigRuntime;
        }

        $this->twig->getRuntime(FormRenderer::class)->willReturn($runtime->reveal());

        return $runtime;
    }
}
