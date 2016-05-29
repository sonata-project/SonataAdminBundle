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

class HelperControllerTest extends \PHPUnit_Framework_TestCase
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
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $pool = new Pool($container, 'title', 'logo.png');
        $pool->setAdminServiceIds(array('foo.admin'));

        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        $twig = new \Twig_Environment($this->getMock('\Twig_LoaderInterface'));
        $helper = new AdminHelper($pool);
        $validator = $this->getMock('Symfony\Component\Validator\Validator\ValidatorInterface');
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
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @dataProvider getValidatorInterfaces
     */
    public function testgetShortObjectDescriptionActionInvalidAdmin($validatorInterface)
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $twig = new \Twig_Environment($this->getMock('\Twig_LoaderInterface'));
        $request = new Request(array(
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ));
        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(array('sonata.post.admin'));
        $helper = new AdminHelper($pool);
        $validator = $this->getMock($validatorInterface);
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
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('setUniqid');
        $admin->expects($this->once())->method('getObject')->will($this->returnValue(false));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $twig = new \Twig_Environment($this->getMock('\Twig_LoaderInterface'));
        $request = new Request(array(
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ));

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(array('sonata.post.admin'));

        $helper = new AdminHelper($pool);

        $validator = $this->getMock($validatorInterface);
        $controller = new HelperController($twig, $pool, $helper, $validator);

        $controller->getShortObjectDescriptionAction($request);
    }

    /**
     * @dataProvider getValidatorInterfaces
     */
    public function testgetShortObjectDescriptionActionEmptyObjectId($validatorInterface)
    {
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('setUniqid');
        $admin->expects($this->once())->method('getObject')->with($this->identicalTo(null))->will($this->returnValue(false));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $twig = new \Twig_Environment($this->getMock('\Twig_LoaderInterface'));
        $request = new Request(array(
            'code' => 'sonata.post.admin',
            'objectId' => '',
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ));

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(array('sonata.post.admin'));

        $helper = new AdminHelper($pool);

        $validator = $this->getMock($validatorInterface);
        $controller = new HelperController($twig, $pool, $helper, $validator);

        $controller->getShortObjectDescriptionAction($request);
    }

    /**
     * @dataProvider getValidatorInterfaces
     */
    public function testgetShortObjectDescriptionActionObject($validatorInterface)
    {
        $mockTemplate = 'AdminHelperTest:mock-short-object-description.html.twig';

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
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

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
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

        $validator = $this->getMock($validatorInterface);

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

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getOption')->will($this->returnValue(true));

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getObject')->will($this->returnValue($object));
        $admin->expects($this->once())->method('isGranted')->will($this->returnValue(true));
        $admin->expects($this->once())->method('getListFieldDescription')->will($this->returnValue($fieldDescription));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $adminExtension = $this->getMock('\Twig_ExtensionInterface', array('renderListElement', 'initRuntime', 'getTokenParsers', 'getNodeVisitors', 'getFilters', 'getTests', 'getFunctions', 'getOperators', 'getGlobals', 'getName'));
        $adminExtension->expects($this->once())->method('getName')->will($this->returnValue('sonata_admin'));
        $adminExtension->expects($this->once())->method('renderListElement')->will($this->returnValue('<foo />'));

        $twig = new \Twig_Environment($this->getMock('\Twig_LoaderInterface'));
        $twig->addExtension($adminExtension);
        $request = new Request(array(
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ), array(), array(), array(), array(), array('REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(array('sonata.post.admin'));

        $helper = new AdminHelper($pool);

        $validator = $this->getMock($validatorInterface);

        $controller = new HelperController($twig, $pool, $helper, $validator);

        $response = $controller->setObjectFieldValueAction($request);

        $this->assertSame('{"status":"OK","content":"\u003Cfoo \/\u003E"}', $response->getContent());
    }

    /**
     * @dataProvider getValidatorInterfaces
     */
    public function testappendFormFieldElementAction($validatorInterface)
    {
        $object = new AdminControllerHelper_Foo();

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->expects($this->once())->method('find')->will($this->returnValue($object));

        $mockTheme = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getModelManager')->will($this->returnValue($modelManager));
        $admin->expects($this->once())->method('setRequest');
        $admin->expects($this->once())->method('setSubject');
        $admin->expects($this->once())->method('getFormTheme')->will($this->returnValue($mockTheme));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $mockRenderer = $this->getMockBuilder('Symfony\Bridge\Twig\Form\TwigRendererInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mockRenderer->expects($this->once())
            ->method('searchAndRenderBlock')
            ->will($this->returnValue(new Response()));

        $formExtension = $this->getMock('\Twig_ExtensionInterface', array('renderListElement', 'initRuntime', 'getTokenParsers', 'getNodeVisitors', 'getFilters', 'getTests', 'getFunctions', 'getOperators', 'getGlobals', 'getName'));

        $formExtension->expects($this->once())->method('getName')->will($this->returnValue('form'));
        $formExtension->renderer = $mockRenderer;

        $twig = new \Twig_Environment($this->getMock('\Twig_LoaderInterface'));
        $twig->addExtension($formExtension);
        $request = new Request(array(
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ), array(), array(), array(), array(), array('REQUEST_METHOD' => 'POST'));

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(array('sonata.post.admin'));

        $validator = $this->getMock($validatorInterface);

        $mockView = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();

        $mockForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $mockForm->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($mockView));

        $helper = $this->getMock('Sonata\AdminBundle\Admin\AdminHelper', array('appendFormFieldElement', 'getChildFormView'), array($pool));
        $helper->expects($this->once())->method('appendFormFieldElement')->will($this->returnValue(array(
            $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface'),
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
    public function testretrieveFormFieldElementAction($validatorInterface)
    {
        $object = new AdminControllerHelper_Foo();

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->expects($this->once())->method('find')->will($this->returnValue($object));

        $mockView = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();

        $mockForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $mockForm->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($mockView));

        $formBuilder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $formBuilder->expects($this->once())->method('getForm')->will($this->returnValue($mockForm));

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getModelManager')->will($this->returnValue($modelManager));
        $admin->expects($this->once())->method('getFormBuilder')->will($this->returnValue($formBuilder));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $mockRenderer = $this->getMockBuilder('Symfony\Bridge\Twig\Form\TwigRendererInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mockRenderer->expects($this->once())
            ->method('searchAndRenderBlock')
            ->will($this->returnValue(new Response()));

        $formExtension = $this->getMock('\Twig_ExtensionInterface', array('renderListElement', 'initRuntime', 'getTokenParsers', 'getNodeVisitors', 'getFilters', 'getTests', 'getFunctions', 'getOperators', 'getGlobals', 'getName'));
        $formExtension->expects($this->once())->method('getName')->will($this->returnValue('form'));
        $formExtension->renderer = $mockRenderer;

        $twig = new \Twig_Environment($this->getMock('\Twig_LoaderInterface'));
        $twig->addExtension($formExtension);
        $request = new Request(array(
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ), array(), array(), array(), array(), array('REQUEST_METHOD' => 'POST'));

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminServiceIds(array('sonata.post.admin'));

        $validator = $this->getMock($validatorInterface);

        $helper = $this->getMock('Sonata\AdminBundle\Admin\AdminHelper', array('getChildFormView'), array($pool));
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

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getOption')->will($this->returnValue(true));

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getObject')->will($this->returnValue($object));
        $admin->expects($this->once())->method('isGranted')->will($this->returnValue(true));
        $admin->expects($this->once())->method('getListFieldDescription')->will($this->returnValue($fieldDescription));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->will($this->returnValue($admin));

        $twig = new \Twig_Environment($this->getMock('\Twig_LoaderInterface'));
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

        $validator = $this->getMock($validatorInterface);

        $validator
            ->expects($this->once())
            ->method('validate')
            ->with($bar)
            ->will($this->returnValue($violations))
        ;

        $controller = new HelperController($twig, $pool, $helper, $validator);

        $response = $controller->setObjectFieldValueAction($request);

        $this->assertSame('{"status":"KO","message":"error1\nerror2"}', $response->getContent());
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @exceptionMessage Invalid format
     */
    public function testRetrieveAutocompleteItemsActionNotGranted()
    {
        $this->admin->expects($this->exactly(2))
            ->method('isGranted')
             ->will($this->returnCallback(function ($operation) {
                 if ($operation == 'CREATE' || $operation == 'EDIT') {
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
     * @expectedException Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @exceptionMessage Invalid format
     */
    public function testRetrieveFilterAutocompleteItemsActionNotGranted()
    {
        $this->admin->expects($this->exactly(1))
            ->method('isGranted')
            ->will($this->returnCallback(function ($operation) {
                if ($operation == 'LIST') {
                    return false;
                }

                return;
            }));

        $request = new Request(array(
            'admin_code' => 'foo.admin',
            '_context' => 'filter',
        ), array(), array(), array(), array(), array('REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));

        $this->controller->retrieveAutocompleteItemsAction($request);
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @exceptionMessage Autocomplete list can`t be retrieved because the form element is disabled or read_only.
     */
    public function testRetrieveAutocompleteItemsActionDisabledFormelememt()
    {
        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with('CREATE')
            ->will($this->returnValue(true));

        $entity = new Foo();

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

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

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testRetrieveAutocompleteItemsActionNotGrantedTarget()
    {
        $this->admin->expects($this->once())
            ->method('isGranted')
            ->with('CREATE')
            ->will($this->returnValue(true));

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription->expects($this->once())
            ->method('getTargetEntity')
            ->will($this->returnValue('Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo'));

        $fieldDescription->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('barField'));

        $targetAdmin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        $fieldDescription->expects($this->once())
            ->method('getAssociationAdmin')
            ->will($this->returnValue($targetAdmin));

        $targetAdmin->expects($this->once())
            ->method('isGranted')
            ->with('LIST')
            ->will($this->returnValue(false));

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

        $formType->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($formConfig));

        $formConfig->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnCallback(function ($name) {
                switch ($name) {
                    case 'disabled':
                        return false;
                    case 'property':
                        return 'fooProperty';
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
                }
            }));

        $request = new Request(array(
            'admin_code' => 'foo.admin',
            'field' => 'barField',
        ), array(), array(), array(), array(), array('REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));

        $this->controller->retrieveAutocompleteItemsAction($request);
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
