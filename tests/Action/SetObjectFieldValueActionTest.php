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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Action\SetObjectFieldValueAction;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\DataTransformerResolver;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Sonata\AdminBundle\Twig\RenderElementRuntime;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class SetObjectFieldValueActionTest extends TestCase
{
    /**
     * @var Stub&AdminFetcherInterface
     */
    private AdminFetcherInterface $adminFetcher;

    private Environment $twig;

    private SetObjectFieldValueAction $action;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private AdminInterface $admin;

    /**
     * @var ValidatorInterface&MockObject
     */
    private ValidatorInterface $validator;

    /**
     * @var ModelManagerInterface<object>&MockObject
     */
    private ModelManagerInterface $modelManager;

    private DataTransformerResolver $resolver;

    /**
     * @var MockObject&MutableTemplateRegistryInterface
     */
    private MutableTemplateRegistryInterface $templateRegistry;

    protected function setUp(): void
    {
        $this->twig = new Environment(new ArrayLoader([
            'admin_template' => 'renderedTemplate',
            'field_template' => 'renderedTemplate',
        ]));
        $this->admin = $this->createMock(AdminInterface::class);
        $this->adminFetcher = $this->createStub(AdminFetcherInterface::class);
        $this->adminFetcher->method('get')->willReturn($this->admin);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->modelManager = $this->createMock(ModelManagerInterface::class);
        $this->resolver = new DataTransformerResolver();
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->action = new SetObjectFieldValueAction(
            $this->twig,
            $this->adminFetcher,
            $this->validator,
            $this->resolver,
            $propertyAccessor,
            new RenderElementRuntime($propertyAccessor)
        );
        $this->admin->method('getModelManager')->willReturn($this->modelManager);
        $this->templateRegistry = $this->createMock(MutableTemplateRegistryInterface::class);

        $this->admin
            ->method('getTemplateRegistry')
            ->willReturn($this->templateRegistry);
    }

    public function testSetObjectFieldValueAction(): void
    {
        $object = new Foo();
        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getCode')->willReturn('sonata.post.admin');
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('hasListFieldDescription')->with('enabled')->willReturn(true);
        $this->admin->method('getListFieldDescription')->with('enabled')->willReturn($fieldDescription);
        $this->admin->expects(static::once())->method('update')->with($object);
        $this->templateRegistry->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
        $fieldDescription->method('getOption')->willReturnMap([
            ['editable', null, true],
        ]);
        $fieldDescription->method('getAdmin')->willReturn($this->admin);
        $fieldDescription->method('getType')->willReturn('boolean');
        $fieldDescription->method('getTemplate')->willReturn('field_template');
        $fieldDescription->method('getValue')->willReturn('some value');

        $this->validator->method('validate')->with($object)->willReturn(new ConstraintViolationList([]));

        $response = ($this->action)($request);

        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @phpstan-return iterable<array-key, array{\DateTimeZone|string|false|null, \DateTimeZone}>
     */
    public function provideSetObjectFieldValueActionWithDateCases(): iterable
    {
        $default = new \DateTimeZone(date_default_timezone_get());
        $custom = new \DateTimeZone('Europe/Rome');
        yield 'empty timezone' => [null, $default];
        yield 'disabled timezone' => [false, $default];
        yield 'default timezone by name' => [$default->getName(), $default];
        yield 'default timezone by object' => [$default, $default];
        yield 'custom timezone by name' => [$custom->getName(), $custom];
        yield 'custom timezone by object' => [$custom, $custom];
    }

    /**
     * @dataProvider provideSetObjectFieldValueActionWithDateCases
     */
    public function testSetObjectFieldValueActionWithDate(
        \DateTimeZone|string|false|null $timezone,
        \DateTimeZone $expectedTimezone,
    ): void {
        $object = new Bafoo();
        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'dateProp',
            'value' => '2020-12-12',
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getCode')->willReturn('sonata.post.admin');
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('hasListFieldDescription')->with('dateProp')->willReturn(true);
        $this->admin->method('getListFieldDescription')->with('dateProp')->willReturn($fieldDescription);
        $this->admin->expects(static::once())->method('update')->with($object);

        $this->templateRegistry->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
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

        static::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $defaultTimezone = new \DateTimeZone(date_default_timezone_get());
        $expectedDate = new \DateTime('2020-12-12', $expectedTimezone);
        $expectedDate->setTimezone($defaultTimezone);

        $dateProp = $object->getDateProp();
        static::assertInstanceOf(\DateTime::class, $dateProp);
        static::assertSame($expectedDate->format('Y-m-d'), $dateProp->format('Y-m-d'));
        static::assertSame($defaultTimezone->getName(), $dateProp->getTimezone()->getName());
    }

    public function testSetObjectFieldValueActionOnARelationField(): void
    {
        $object = new Baz();
        $associationObject = new Bar();
        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'bar',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getCode')->willReturn('sonata.post.admin');
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('hasListFieldDescription')->with('bar')->willReturn(true);
        $this->admin->method('getListFieldDescription')->with('bar')->willReturn($fieldDescription);
        $this->admin->method('getClass')->willReturn($object::class);
        $this->admin->expects(static::once())->method('update')->with($object);
        $this->templateRegistry->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
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
        $this->modelManager->method('find')->with($associationObject::class, 1)->willReturn($associationObject);

        $this->validator->method('validate')->with($object)->willReturn(new ConstraintViolationList([]));

        $response = ($this->action)($request);

        static::assertSame($associationObject, $object->getBar());
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testSetObjectFieldValueActionWithViolations(): void
    {
        $bar = new Bar();
        $object = new Baz();
        $object->setBar($bar);
        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'bar.enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('hasListFieldDescription')->with('bar.enabled')->willReturn(true);
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

        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        static::assertSame(json_encode("error1\nerror2"), $response->getContent());
    }

    public function testSetObjectFieldEditableMultipleValue(): void
    {
        $object = new StatusMultiple();
        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'status',
            'value' => [1, 2],
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getCode')->willReturn('sonata.post.admin');
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('hasListFieldDescription')->with('status')->willReturn(true);
        $this->admin->method('getListFieldDescription')->with('status')->willReturn($fieldDescription);
        $this->admin->expects(static::once())->method('update')->with($object);
        $this->templateRegistry->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
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

        static::assertSame([1, 2], $object->status);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testSetObjectFieldTransformed(): void
    {
        $object = new Foo();
        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 'yes',
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $dataTransformer = new CallbackTransformer(
            static fn (mixed $value): string => (string) (int) $value,
            static fn (mixed $value): bool => filter_var($value, \FILTER_VALIDATE_BOOLEAN)
        );

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getCode')->willReturn('sonata.post.admin');
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('hasListFieldDescription')->with('enabled')->willReturn(true);
        $this->admin->method('getListFieldDescription')->with('enabled')->willReturn($fieldDescription);
        $this->admin->expects(static::once())->method('update')->with($object);
        $this->templateRegistry->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
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

        static::assertTrue($object->getEnabled());
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testSetObjectFieldOverrideTransformer(): void
    {
        $object = new Foo();
        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 'yes',
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $isOverridden = false;
        $dataTransformer = new CallbackTransformer(
            static fn (mixed $value): string => (string) (int) $value,
            static function (mixed $value) use (&$isOverridden): bool {
                $isOverridden = true;

                return filter_var($value, \FILTER_VALIDATE_BOOLEAN);
            }
        );

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getCode')->willReturn('sonata.post.admin');
        $this->admin->method('hasAccess')->with('edit', $object)->willReturn(true);
        $this->admin->method('hasListFieldDescription')->with('enabled')->willReturn(true);
        $this->admin->method('getListFieldDescription')->with('enabled')->willReturn($fieldDescription);
        $this->admin->expects(static::once())->method('update')->with($object);
        $this->templateRegistry->method('getTemplate')->with('base_list_field')->willReturn('admin_template');
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

        static::assertTrue($object->getEnabled());
        static::assertTrue($isOverridden);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }
}
