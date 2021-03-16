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

namespace Sonata\AdminBundle\Tests\Action;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Action\SetObjectFieldValueAction;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\DataTransformerResolver;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class SetObjectFieldValueActionTest extends TestCase
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
     * @var SetObjectFieldValueAction
     */
    private $action;

    /**
     * @var AbstractAdmin
     */
    private $admin;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ModelManagerInterface
     */
    private $modelManager;

    /**
     * @var DataTransformerResolver
     */
    private $resolver;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @var string
     */
    private $adminCode;

    protected function setUp(): void
    {
        $this->twig = new Environment(new ArrayLoader([
            'admin_template' => 'renderedTemplate',
            'field_template' => 'renderedTemplate',
        ]));
        $this->adminCode = 'sonata.post.admin';
        $this->admin = $this->createMock(AbstractAdmin::class);
        $container = new Container();
        $container->set($this->adminCode, $this->admin);
        $this->pool = new Pool($container, [$this->adminCode]);
        $this->admin->expects($this->once())->method('setRequest');
        $this->validator = $this->createStub(ValidatorInterface::class);
        $this->modelManager = $this->createStub(ModelManagerInterface::class);
        $this->resolver = new DataTransformerResolver();
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->action = new SetObjectFieldValueAction(
            $this->twig,
            $this->pool,
            $this->validator,
            $this->resolver,
            $this->propertyAccessor
        );
        $this->admin->method('getModelManager')->willReturn($this->modelManager);
    }

    public function testSetObjectFieldValueAction(): void
    {
        $object = new Foo();
        $request = new Request([
            'code' => $this->adminCode,
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);
        $translator = $this->createStub(TranslatorInterface::class);
        $templateRegistry = $this->createStub(TemplateRegistryInterface::class);
        $container = new Container();

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getCode')->willReturn($this->adminCode);
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('getListFieldDescription')->with('enabled')->willReturn($fieldDescription);
        $this->admin->expects($this->once())->method('update')->with($object);
        // NEXT_MAJOR: Remove this line
        $this->admin->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $templateRegistry->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $container->set('sonata.post.admin.template_registry', $templateRegistry);
        $this->twig->addExtension(new SonataAdminExtension(
            new Pool(new Container()),
            null,
            $translator,
            $container,
            $this->propertyAccessor,
            null
        ));
        $fieldDescription->method('getOption')->willReturnMap([
            ['editable', null, true],
        ]);
        $fieldDescription->method('getAdmin')->willReturn($this->admin);
        $fieldDescription->method('getType')->willReturn('boolean');
        $fieldDescription->method('getTemplate')->willReturn('field_template');
        $fieldDescription->method('getValue')->willReturn('some value');

        $this->validator->method('validate')->with($object)->willReturn(new ConstraintViolationList([]));

        $response = ($this->action)($request);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function getTimeZones(): iterable
    {
        $default = new \DateTimeZone(date_default_timezone_get());
        $custom = new \DateTimeZone('Europe/Rome');

        return [
            'empty timezone' => [null, $default],
            'disabled timezone' => [false, $default],
            'default timezone by name' => [$default->getName(), $default],
            'default timezone by object' => [$default, $default],
            'custom timezone by name' => [$custom->getName(), $custom],
            'custom timezone by object' => [$custom, $custom],
        ];
    }

    /**
     * @dataProvider getTimeZones
     */
    public function testSetObjectFieldValueActionWithDate($timezone, \DateTimeZone $expectedTimezone): void
    {
        $object = new Bafoo();
        $request = new Request([
            'code' => $this->adminCode,
            'objectId' => 42,
            'field' => 'dateProp',
            'value' => '2020-12-12',
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);
        $translator = $this->createStub(TranslatorInterface::class);
        $templateRegistry = $this->createStub(TemplateRegistryInterface::class);
        $container = new Container();

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getCode')->willReturn($this->adminCode);
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('getListFieldDescription')->with('dateProp')->willReturn($fieldDescription);
        $this->admin->expects($this->once())->method('update')->with($object);

        $this->admin->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $templateRegistry->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $container->set('sonata.post.admin.template_registry', $templateRegistry);
        $this->twig->addExtension(new SonataAdminExtension(
            new Pool(new Container()),
            null,
            $translator,
            $container,
            $this->propertyAccessor,
            null
        ));
        $fieldDescription->method('getOption')->willReturnMap([
            ['timezone', null, $timezone],
            ['data_transformer', null, null],
            ['editable', null, true],
        ]);
        $fieldDescription->method('getAdmin')->willReturn($this->admin);
        $fieldDescription->method('getType')->willReturn('date');
        $fieldDescription->method('getTemplate')->willReturn('field_template');
        $fieldDescription->method('getValue')->willReturn('some value');
        $this->validator->method('validate')->with($object)->willReturn(new ConstraintViolationList([]));

        $response = ($this->action)($request);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $defaultTimezone = new \DateTimeZone(date_default_timezone_get());
        $expectedDate = new \DateTime($request->query->get('value'), $expectedTimezone);
        $expectedDate->setTimezone($defaultTimezone);

        $this->assertInstanceOf(\DateTime::class, $object->getDateProp());
        $this->assertSame($expectedDate->format('Y-m-d'), $object->getDateProp()->format('Y-m-d'));
        $this->assertSame($defaultTimezone->getName(), $object->getDateProp()->getTimezone()->getName());
    }

    public function testSetObjectFieldValueActionOnARelationField(): void
    {
        $object = new Baz();
        $associationObject = new Bar();
        $request = new Request([
            'code' => $this->adminCode,
            'objectId' => 42,
            'field' => 'bar',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        // NEXT_MAJOR: Use `createStub` instead of using mock builder
        $fieldDescription = $this->getMockBuilder(FieldDescriptionInterface::class)
            ->addMethods(['getTargetModel'])
            ->getMockForAbstractClass();
        $translator = $this->createStub(TranslatorInterface::class);
        $templateRegistry = $this->createStub(TemplateRegistryInterface::class);
        $container = new Container();

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getCode')->willReturn($this->adminCode);
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('getListFieldDescription')->with('bar')->willReturn($fieldDescription);
        $this->admin->method('getClass')->willReturn(\get_class($object));
        $this->admin->expects($this->once())->method('update')->with($object);
        $container->set('sonata.post.admin.template_registry', $templateRegistry);
        // NEXT_MAJOR: Remove this line
        $this->admin->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $templateRegistry->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $this->twig->addExtension(new SonataAdminExtension(
            $this->pool,
            null,
            $translator,
            $container,
            $this->propertyAccessor,
            null
        ));
        $fieldDescription->method('getType')->willReturn('choice');
        $fieldDescription->method('getOption')->willReturnMap([
            ['class', null, Bar::class],
            ['data_transformer', null, null],
            ['editable', null, true],
        ]);
        $fieldDescription->method('getTargetModel')->willReturn(Bar::class);
        $fieldDescription->method('getAdmin')->willReturn($this->admin);
        $fieldDescription->method('getTemplate')->willReturn('field_template');
        $fieldDescription->method('getValue')->willReturn('some value');
        $this->modelManager->method('find')->with(\get_class($associationObject), 1)->willReturn($associationObject);

        $this->validator->method('validate')->with($object)->willReturn(new ConstraintViolationList([]));

        $response = ($this->action)($request);

        $this->assertSame($associationObject, $object->getBar());
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testSetObjectFieldValueActionWithViolations(): void
    {
        $bar = new Bar();
        $object = new Baz();
        $object->setBar($bar);
        $request = new Request([
            'code' => $this->adminCode,
            'objectId' => 42,
            'field' => 'bar.enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('getListFieldDescription')->with('bar.enabled')->willReturn($fieldDescription);
        $this->validator->method('validate')->with($bar)->willReturn(new ConstraintViolationList([
            new ConstraintViolation('error1', null, [], null, 'enabled', null),
            new ConstraintViolation('error2', null, [], null, 'enabled', null),
        ]));
        $fieldDescription->method('getOption')->willReturnMap([
            ['data_transformer', null, null],
            ['editable', null, true],
        ]);
        $fieldDescription->method('getType')->willReturn('boolean');

        $response = ($this->action)($request);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(json_encode("error1\nerror2"), $response->getContent());
    }

    public function testSetObjectFieldEditableMultipleValue(): void
    {
        $object = new StatusMultiple();
        $request = new Request([
            'code' => $this->adminCode,
            'objectId' => 42,
            'field' => 'status',
            'value' => [1, 2],
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);
        $translator = $this->createStub(TranslatorInterface::class);
        $templateRegistry = $this->createStub(TemplateRegistryInterface::class);
        $container = new Container();

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getCode')->willReturn($this->adminCode);
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('getListFieldDescription')->with('status')->willReturn($fieldDescription);
        $this->admin->expects($this->once())->method('update')->with($object);
        // NEXT_MAJOR: Remove this line
        $this->admin->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $templateRegistry->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $container->set('sonata.post.admin.template_registry', $templateRegistry);
        $this->twig->addExtension(new SonataAdminExtension(
            new Pool(new Container()),
            null,
            $translator,
            $container,
            $this->propertyAccessor,
            null
        ));
        $fieldDescription->method('getOption')->willReturnMap([
            ['data_transformer', null, null],
            ['editable', null, true],
            ['multiple', null, true],
        ]);
        $fieldDescription->method('getAdmin')->willReturn($this->admin);
        $fieldDescription->method('getType')->willReturn(null);
        $fieldDescription->method('getTemplate')->willReturn('field_template');
        $fieldDescription->method('getValue')->willReturn(['some value']);

        $this->validator->method('validate')->with($object)->willReturn(new ConstraintViolationList([]));

        $response = ($this->action)($request);

        $this->assertSame([1, 2], $object->status);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testSetObjectFieldTransformed(): void
    {
        $object = new Foo();
        $request = new Request([
            'code' => $this->adminCode,
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 'yes',
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $dataTransformer = new CallbackTransformer(static function ($value): string {
            return (string) (int) $value;
        }, static function ($value): bool {
            return filter_var($value, \FILTER_VALIDATE_BOOLEAN);
        });

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);
        $translator = $this->createStub(TranslatorInterface::class);
        $templateRegistry = $this->createStub(TemplateRegistryInterface::class);
        $container = new Container();

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getCode')->willReturn($this->adminCode);
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('getListFieldDescription')->with('enabled')->willReturn($fieldDescription);
        $this->admin->expects($this->once())->method('update')->with($object);
        // NEXT_MAJOR: Remove this line
        $this->admin->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $templateRegistry->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $container->set('sonata.post.admin.template_registry', $templateRegistry);
        $this->twig->addExtension(new SonataAdminExtension(
            new Pool(new Container()),
            null,
            $translator,
            $container,
            $this->propertyAccessor,
            null
        ));
        $fieldDescription->method('getOption')->willReturnMap([
            ['data_transformer', null, $dataTransformer],
            ['editable', null, true],
        ]);
        $fieldDescription->method('getAdmin')->willReturn($this->admin);
        $fieldDescription->method('getType')->willReturn(null);
        $fieldDescription->method('getTemplate')->willReturn('field_template');
        $fieldDescription->method('getValue')->willReturn('some value');

        $this->validator->method('validate')->with($object)->willReturn(new ConstraintViolationList([]));

        $response = ($this->action)($request);

        $this->assertTrue($object->getEnabled());
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testSetObjectFieldOverrideTransformer(): void
    {
        $object = new Foo();
        $request = new Request([
            'code' => $this->adminCode,
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 'yes',
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $isOverridden = false;
        $dataTransformer = new CallbackTransformer(static function ($value): string {
            return (string) (int) $value;
        }, static function ($value) use (&$isOverridden): bool {
            $isOverridden = true;

            return filter_var($value, \FILTER_VALIDATE_BOOLEAN);
        });

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);
        $translator = $this->createStub(TranslatorInterface::class);
        $templateRegistry = $this->createStub(TemplateRegistryInterface::class);
        $container = new Container();

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getCode')->willReturn($this->adminCode);
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('getListFieldDescription')->with('enabled')->willReturn($fieldDescription);
        $this->admin->expects($this->once())->method('update')->with($object);
        // NEXT_MAJOR: Remove this line
        $this->admin->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $templateRegistry->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $container->set('sonata.post.admin.template_registry', $templateRegistry);
        $this->twig->addExtension(new SonataAdminExtension(
            new Pool(new Container()),
            null,
            $translator,
            $container,
            $this->propertyAccessor,
            null
        ));
        $fieldDescription->method('getOption')->willReturnMap([
            ['data_transformer', null, $dataTransformer],
            ['editable', null, true],
        ]);
        $fieldDescription->method('getAdmin')->willReturn($this->admin);
        $fieldDescription->method('getType')->willReturn('boolean');
        $fieldDescription->method('getTemplate')->willReturn('field_template');
        $fieldDescription->method('getValue')->willReturn('some value');

        $this->validator->method('validate')->with($object)->willReturn(new ConstraintViolationList([]));

        $response = ($this->action)($request);

        $this->assertTrue($object->getEnabled());
        $this->assertTrue($isOverridden);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }
}
