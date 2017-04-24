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

use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\HelperController;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo;
use Sonata\AdminBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

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

class HelperControllerTest extends PHPUnit_Framework_TestCase
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
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $pool = new Pool($container, 'title', 'logo.png');
        $pool->setAdminServiceIds(array('foo.admin'));

        $this->admin = $this->createMock('Sonata\AdminBundle\Admin\AbstractAdmin');

        $twig = new \Twig_Environment($this->createMock('\Twig_LoaderInterface'));
        $helper = new AdminHelper($pool);

        // NEXT_MAJOR: Remove this when dropping support for SF < 2.8
        if (interface_exists('Symfony\Component\Validator\ValidatorInterface')) {
            $validator = $this->createMock('Symfony\Component\Validator\ValidatorInterface');
        } else {
            $validator = $this->createMock('Symfony\Component\Validator\Validator\ValidatorInterface');
        }
        $this->controller = new HelperController($twig, $pool, $helper, $validator);

        // php 5.3 BC
        $admin = $this->admin;

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use ($admin) {
                switch ($id) {
                    case 'foo.admin':
                        return $admin;
                }
            }));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @dataProvider getValidatorInterfaces
     */
    public function testgetShortObjectDescriptionActionInvalidAdmin($validatorInterface)
    {
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $twig = new \Twig_Environment($this->createMock('\Twig_LoaderInterface'));
        $request = new Request(array(
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ));
        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(array('sonata.post.admin'));
        $helper = new AdminHelper($pool);
        $validator = $this->createMock($validatorInterface);
        $controller = new HelperController($twig, $pool, $helper, $validator);

        $controller->getShortObjectDescriptionAction($request);
    }

    /**
     * @expectedException \RuntimeException
     * @exceptionMessage Invalid format
     *
     * @dataProvider getValidatorInterfaces
     */
    public function testgetShortObjectDescriptionActionObjectDoesNotExist($validatorInterface)
    {
        $admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('setUniqid');
        $admin->expects($this->once())->method('getObject')->will($this->returnValue(false));

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $twig = new \Twig_Environment($this->createMock('\Twig_LoaderInterface'));
        $request = new Request(array(
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ));

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(array('sonata.post.admin'));

        $helper = new AdminHelper($pool);

        $validator = $this->createMock($validatorInterface);
        $controller = new HelperController($twig, $pool, $helper, $validator);

        $controller->getShortObjectDescriptionAction($request);
    }

    /**
     * @dataProvider getValidatorInterfaces
     */
    public function testgetShortObjectDescriptionActionEmptyObjectId($validatorInterface)
    {
        $admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('setUniqid');
        $admin->expects($this->once())->method('getObject')->with($this->identicalTo(null))->will($this->returnValue(false));

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $twig = new \Twig_Environment($this->createMock('\Twig_LoaderInterface'));
        $request = new Request(array(
            'code' => 'sonata.post.admin',
            'objectId' => '',
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ));

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(array('sonata.post.admin'));

        $helper = new AdminHelper($pool);

        $validator = $this->createMock($validatorInterface);
        $controller = new HelperController($twig, $pool, $helper, $validator);

        $controller->getShortObjectDescriptionAction($request);
    }

    /**
     * @dataProvider getValidatorInterfaces
     */
    public function testgetShortObjectDescriptionActionObject($validatorInterface)
    {
        $mockTemplate = 'AdminHelperTest:mock-short-object-description.html.twig';

        $admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('setUniqid');
        $admin->expects($this->once())->method('getTemplate')->will($this->returnValue($mockTemplate));
        $admin->expects($this->once())->method('getObject')->will($this->returnValue(new AdminControllerHelper_Foo()));
        $admin->expects($this->once())->method('toString')->will($this->returnValue('bar'));
        $admin->expects($this->once())->method('generateObjectUrl')->will($this->returnCallback(function ($type, $object, $parameters = array()) {
            if ($type != 'edit') {
                return 'invalid name';
            }

            return '/ok/url';
        }));

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $twig = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();

        $twig->expects($this->once())->method('render')
            ->with($mockTemplate)
            ->will($this->returnCallback(function ($templateName, $templateParams) {
                return sprintf('<a href="%s" target="new">%s</a>', $templateParams['admin']->generateObjectUrl('edit', $templateParams['object']), $templateParams['description']);
            }));

        $request = new Request(array(
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ));

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(array('sonata.post.admin'));

        $helper = new AdminHelper($pool);

        $validator = $this->createMock($validatorInterface);

        $controller = new HelperController($twig, $pool, $helper, $validator);

        $response = $controller->getShortObjectDescriptionAction($request);

        $expected = '<a href="/ok/url" target="new">bar</a>';
        $this->assertSame($expected, $response->getContent());
    }

    /**
     * @dataProvider getValidatorInterfaces
     */
    public function testsetObjectFieldValueAction($validatorInterface)
    {
        $object = new AdminControllerHelper_Foo();

        $fieldDescription = $this->createMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getOption')->will($this->returnValue(true));

        $admin = $this->createMock('Sonata\AdminBundle\Admin\AbstractAdmin');
        $admin->expects($this->once())->method('getObject')->will($this->returnValue($object));
        $admin->expects($this->once())->method('hasAccess')->will($this->returnValue(true));
        $admin->expects($this->once())->method('getListFieldDescription')->will($this->returnValue($fieldDescription));
        $fieldDescription->expects($this->exactly(2))->method('getAdmin')->will($this->returnValue($admin));

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(array('sonata.post.admin'));

        $adminExtension = new SonataAdminExtension(
            $pool,
            $this->createMock('Psr\Log\LoggerInterface'),
            $this->createMock('Symfony\Component\Translation\TranslatorInterface')
        );

        $loader = $this->createMock('\Twig_LoaderInterface');

        // NEXT_MAJOR: Remove this check when dropping support for twig < 2
        if (method_exists('\Twig_LoaderInterface', 'getSourceContext')) {
            $loader->method('getSourceContext')->will($this->returnValue(new \Twig_Source('<foo />', 'foo')));
        } else {
            $loader->method('getSource')->will($this->returnValue('<foo />'));
        }

        $twig = new \Twig_Environment($loader);
        $twig->addExtension($adminExtension);
        $request = new Request(array(
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ), array(), array(), array(), array(), array('REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));

        $helper = new AdminHelper($pool);

        $validator = $this->createMock($validatorInterface);

        $controller = new HelperController($twig, $pool, $helper, $validator);

        $response = $controller->setObjectFieldValueAction($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @dataProvider getValidatorInterfaces
     */
    public function testappendFormFieldElementAction($validatorInterface)
    {
        $object = new AdminControllerHelper_Foo();

        $modelManager = $this->createMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->expects($this->once())->method('find')->will($this->returnValue($object));

        $mockTheme = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();

        $admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getModelManager')->will($this->returnValue($modelManager));
        $admin->expects($this->once())->method('setRequest');
        $admin->expects($this->once())->method('setSubject');
        $admin->expects($this->once())->method('getFormTheme')->will($this->returnValue($mockTheme));

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $mockRenderer = $this->getMockBuilder('Symfony\Bridge\Twig\Form\TwigRendererInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mockRenderer->expects($this->once())
            ->method('searchAndRenderBlock')
            ->will($this->returnValue(new Response()));

        $twig = new \Twig_Environment($this->createMock('\Twig_LoaderInterface'));
        $twig->addExtension(new FormExtension($mockRenderer));

        if (method_exists('Symfony\Bridge\Twig\AppVariable', 'getToken')) {
            $runtimeLoader = $this
                ->getMockBuilder('Twig_RuntimeLoaderInterface')
                ->getMock();

            $runtimeLoader->expects($this->once())
                ->method('load')
                ->with($this->equalTo('Symfony\Bridge\Twig\Form\TwigRenderer'))
                ->will($this->returnValue($mockRenderer));

            $twig->addRuntimeLoader($runtimeLoader);
        }

        $request = new Request(array(
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ), array(), array(), array(), array(), array('REQUEST_METHOD' => 'POST'));

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(array('sonata.post.admin'));

        $validator = $this->createMock($validatorInterface);

        $mockView = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();

        $mockForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $mockForm->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($mockView));

        $helper = $this->getMockBuilder('Sonata\AdminBundle\Admin\AdminHelper')
            ->setMethods(array('appendFormFieldElement', 'getChildFormView'))
            ->setConstructorArgs(array($pool))
            ->getMock();
        $helper->expects($this->once())->method('appendFormFieldElement')->will($this->returnValue(array(
            $this->createMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface'),
            $mockForm,
        )));
        $helper->expects($this->once())->method('getChildFormView')->will($this->returnValue($mockView));

        $controller = new HelperController($twig, $pool, $helper, $validator);
        $response = $controller->appendFormFieldElementAction($request);

        $this->isInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
    }

    /**
     * @dataProvider getValidatorInterfaces
     */
    public function testRetrieveFormFieldElementAction($validatorInterface)
    {
        $object = new AdminControllerHelper_Foo();

        $request = new Request(array(
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ), array(), array(), array(), array(), array('REQUEST_METHOD' => 'POST'));

        $modelManager = $this->createMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->expects($this->once())->method('find')->will($this->returnValue($object));

        $mockView = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();

        $mockForm = $this->getMockBuilder('Symfony\Component\Form\Form')
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

        $formBuilder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $formBuilder->expects($this->once())->method('getForm')->will($this->returnValue($mockForm));

        $admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getModelManager')->will($this->returnValue($modelManager));
        $admin->expects($this->once())->method('getFormBuilder')->will($this->returnValue($formBuilder));

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $mockRenderer = $this->getMockBuilder('Symfony\Bridge\Twig\Form\TwigRendererInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mockRenderer->expects($this->once())
            ->method('searchAndRenderBlock')
            ->will($this->returnValue(new Response()));

        $twig = new \Twig_Environment($this->createMock('\Twig_LoaderInterface'));
        $twig->addExtension(new FormExtension($mockRenderer));
        if (method_exists('Symfony\Bridge\Twig\AppVariable', 'getToken')) {
            $runtimeLoader = $this
                ->getMockBuilder('Twig_RuntimeLoaderInterface')
                ->getMock();

            $runtimeLoader->expects($this->once())
                ->method('load')
                ->with($this->equalTo('Symfony\Bridge\Twig\Form\TwigRenderer'))
                ->will($this->returnValue($mockRenderer));

            $twig->addRuntimeLoader($runtimeLoader);
        }

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(array('sonata.post.admin'));

        $validator = $this->createMock($validatorInterface);

        $helper = $this->getMockBuilder('Sonata\AdminBundle\Admin\AdminHelper')
            ->setMethods(array('getChildFormView'))
            ->setConstructorArgs(array($pool))
            ->getMock();
        $helper->expects($this->once())->method('getChildFormView')->will($this->returnValue($mockView));

        $controller = new HelperController($twig, $pool, $helper, $validator);
        $response = $controller->retrieveFormFieldElementAction($request);

        $this->isInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
    }

    /**
     * @dataProvider getValidatorInterfaces
     */
    public function testSetObjectFieldValueActionWithViolations($validatorInterface)
    {
        $bar = new AdminControllerHelper_Bar();

        $object = new AdminControllerHelper_Foo();
        $object->setBar($bar);

        $fieldDescription = $this->createMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getOption')->will($this->returnValue(true));

        $admin = $this->createMock('Sonata\AdminBundle\Admin\AbstractAdmin');
        $admin->expects($this->once())->method('getObject')->will($this->returnValue($object));
        $admin->expects($this->once())->method('hasAccess')->will($this->returnValue(true));
        $admin->expects($this->once())->method('getListFieldDescription')->will($this->returnValue($fieldDescription));

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $twig = new \Twig_Environment($this->createMock('\Twig_LoaderInterface'));
        $request = new Request(array(
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'bar.enabled',
            'value' => 1,
            'context' => 'list',
        ), array(), array(), array(), array(), array('REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(array('sonata.post.admin'));

        $helper = new AdminHelper($pool);

        $violations = new ConstraintViolationList(array(
            new ConstraintViolation('error1', null, array(), null, 'enabled', null),
            new ConstraintViolation('error2', null, array(), null, 'enabled', null),
        ));

        $validator = $this->createMock($validatorInterface);

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
                if ($operation == 'create' || $operation == 'edit') {
                    return false;
                }

                return;
            }));

        $request = new Request(array(
            'admin_code' => 'foo.admin',
        ), array(), array(), array(), array(), array('REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));

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

        $fieldDescription = $this->createMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription->expects($this->once())
            ->method('getTargetEntity')
            ->will($this->returnValue('Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo'));

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

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $formType = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->once())
            ->method('get')
            ->with('barField')
            ->will($this->returnValue($formType));

        $formConfig = $this->getMockBuilder('Symfony\Component\Form\FormConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $formType->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($formConfig));

        $formConfig->expects($this->once())
            ->method('getAttribute')
            ->with('disabled')
            ->will($this->returnValue(true));

        $request = new Request(array(
            'admin_code' => 'foo.admin',
            'field' => 'barField',
        ), array(), array(), array(), array(), array('REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));

        $this->controller->retrieveAutocompleteItemsAction($request);
    }

    public function testRetrieveAutocompleteItemsTooShortSearchString()
    {
        $this->admin->expects($this->once())
            ->method('hasAccess')
            ->with('create')
            ->will($this->returnValue(true));

        $targetAdmin = $this->createMock('Sonata\AdminBundle\Admin\AbstractAdmin');
        $targetAdmin->expects($this->once())
            ->method('checkAccess')
            ->with('list')
            ->will($this->returnValue(null));

        $fieldDescription = $this->createMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription->expects($this->once())
            ->method('getTargetEntity')
            ->will($this->returnValue('Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo'));

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

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $formType = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->once())
            ->method('get')
            ->with('barField')
            ->will($this->returnValue($formType));

        $formConfig = $this->getMockBuilder('Symfony\Component\Form\FormConfigInterface')
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

        $request = new Request(array(
            'admin_code' => 'foo.admin',
            'field' => 'barField',
            'q' => 'so',
        ), array(), array(), array(), array(), array('REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));

        $response = $this->controller->retrieveAutocompleteItemsAction($request);
        $this->isInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
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

        $targetAdmin = $this->createMock('Sonata\AdminBundle\Admin\AbstractAdmin');
        $targetAdmin->expects($this->once())
            ->method('checkAccess')
            ->with('list')
            ->will($this->returnValue(null));

        $targetAdmin->expects($this->once())
            ->method('setPersistFilters')
            ->with(false)
            ->will($this->returnValue(null));

        $datagrid = $this->createMock('Sonata\AdminBundle\Datagrid\DatagridInterface');
        $targetAdmin->expects($this->once())
            ->method('getDatagrid')
            ->with()
            ->will($this->returnValue($datagrid));

        $metadata = $this->createMock('Sonata\CoreBundle\Model\Metadata');
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
                array($this->equalTo('foo'), $this->equalTo(null), $this->equalTo('sonata')),
                array($this->equalTo('_per_page'), $this->equalTo(null), $this->equalTo(10)),
                array($this->equalTo('_page'), $this->equalTo(null), $this->equalTo(1))
               )
            ->will($this->returnValue(null));

        $datagrid->expects($this->once())
            ->method('buildPager')
            ->with()
            ->will($this->returnValue(null));

        $pager = $this->createMock('Sonata\AdminBundle\Datagrid\Pager');
        $datagrid->expects($this->once())
            ->method('getPager')
            ->with()
            ->will($this->returnValue($pager));

        $pager->expects($this->once())
            ->method('getResults')
            ->with()
            ->will($this->returnValue(array($entity)));

        $pager->expects($this->once())
            ->method('isLastPage')
            ->with()
            ->will($this->returnValue(true));

        $fieldDescription = $this->createMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription->expects($this->once())
            ->method('getTargetEntity')
            ->will($this->returnValue('Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo'));

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

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $formType = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->once())
            ->method('get')
            ->with('barField')
            ->will($this->returnValue($formType));

        $formConfig = $this->getMockBuilder('Symfony\Component\Form\FormConfigInterface')
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

        $request = new Request(array(
            'admin_code' => 'foo.admin',
            'field' => 'barField',
            'q' => 'sonata',
        ), array(), array(), array(), array(), array('REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));

        $response = $this->controller->retrieveAutocompleteItemsAction($request);
        $this->isInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"OK","more":false,"items":[{"id":123,"label":"FOO"}]}', $response->getContent());
    }

    /**
     * Symfony Validator has 2 API version (2.4 and 2.5)
     * This data provider ensure tests pass on each one.
     */
    public function getValidatorInterfaces()
    {
        $data = array();

        // For Symfony <= 2.8
        if (interface_exists('Symfony\Component\Validator\ValidatorInterface')) {
            $data['2.4'] = array('Symfony\Component\Validator\ValidatorInterface');
        }

        // For Symfony >= 2.5
        if (interface_exists('Symfony\Component\Validator\Validator\ValidatorInterface')) {
            $data['2.5'] = array('Symfony\Component\Validator\Validator\ValidatorInterface');
        }

        return $data;
    }
}
