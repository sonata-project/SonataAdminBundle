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
use Prophecy\Argument;
use Sonata\AdminBundle\Action\GetShortObjectDescriptionAction;
use Sonata\AdminBundle\Action\SetObjectFieldValueAction;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @var GetShortObjectDescriptionAction
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

    protected function setUp(): void
    {
        $this->twig = new Environment(new ArrayLoader([
            'admin_template' => 'renderedTemplate',
            'field_template' => 'renderedTemplate',
        ]));
        $this->pool = $this->prophesize(Pool::class);
        $this->admin = $this->prophesize(AbstractAdmin::class);
        $this->pool->getInstance(Argument::any())->willReturn($this->admin->reveal());
        $this->admin->setRequest(Argument::type(Request::class))->shouldBeCalled();
        $this->validator = $this->prophesize(ValidatorInterface::class);
        $this->action = new SetObjectFieldValueAction(
            $this->twig,
            $this->pool->reveal(),
            $this->validator->reveal()
        );
    }

    public function testSetObjectFieldValueAction(): void
    {
        $object = new Foo();
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->prophesize(FieldDescriptionInterface::class);
        $pool = $this->prophesize(Pool::class);
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
        $this->twig->addExtension(new SonataAdminExtension(
            $pool->reveal(),
            null,
            $translator->reveal(),
            $container->reveal()
        ));
        $fieldDescription->getOption('editable')->willReturn(true);
        $fieldDescription->getAdmin()->willReturn($this->admin->reveal());
        $fieldDescription->getType()->willReturn('boolean');
        $fieldDescription->getTemplate()->willReturn('field_template');
        $fieldDescription->getValue(Argument::cetera())->willReturn('some value');

        $this->validator->validate($object)->willReturn(new ConstraintViolationList([]));

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
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'dateProp',
            'value' => '2020-12-12',
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->prophesize(FieldDescriptionInterface::class);
        $pool = $this->prophesize(Pool::class);
        $translator = $this->prophesize(TranslatorInterface::class);
        $propertyAccessor = new PropertyAccessor();
        $templateRegistry = $this->prophesize(TemplateRegistryInterface::class);
        $container = $this->prophesize(ContainerInterface::class);

        $this->admin->getObject(42)->willReturn($object);
        $this->admin->getCode()->willReturn('sonata.post.admin');
        $this->admin->hasAccess('edit', $object)->willReturn(true);
        $this->admin->getListFieldDescription('dateProp')->willReturn($fieldDescription->reveal());
        $this->admin->update($object)->shouldBeCalled();

        $this->admin->getTemplate('base_list_field')->willReturn('admin_template');
        $templateRegistry->getTemplate('base_list_field')->willReturn('admin_template');
        $container->get('sonata.post.admin.template_registry')->willReturn($templateRegistry->reveal());
        $this->pool->getPropertyAccessor()->willReturn($propertyAccessor);
        $this->twig->addExtension(new SonataAdminExtension(
            $pool->reveal(),
            null,
            $translator->reveal(),
            $container->reveal()
        ));
        $fieldDescription->getOption('editable')->willReturn(true);
        $fieldDescription->getOption('timezone')->willReturn($timezone);
        $fieldDescription->getAdmin()->willReturn($this->admin->reveal());
        $fieldDescription->getType()->willReturn('date');
        $fieldDescription->getTemplate()->willReturn('field_template');
        $fieldDescription->getValue(Argument::cetera())->willReturn('some value');

        $this->validator->validate($object)->willReturn(new ConstraintViolationList([]));

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
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'bar',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->prophesize(FieldDescriptionInterface::class);
        $modelManager = $this->prophesize(ModelManagerInterface::class);
        $translator = $this->prophesize(TranslatorInterface::class);
        $propertyAccessor = new PropertyAccessor();
        $templateRegistry = $this->prophesize(TemplateRegistryInterface::class);
        $container = $this->prophesize(ContainerInterface::class);

        $this->admin->getObject(42)->willReturn($object);
        $this->admin->getCode()->willReturn('sonata.post.admin');
        $this->admin->hasAccess('edit', $object)->willReturn(true);
        $this->admin->getListFieldDescription('bar')->willReturn($fieldDescription->reveal());
        $this->admin->getClass()->willReturn(\get_class($object));
        $this->admin->update($object)->shouldBeCalled();
        $container->get('sonata.post.admin.template_registry')->willReturn($templateRegistry->reveal());
        // NEXT_MAJOR: Remove this line
        $this->admin->getTemplate('base_list_field')->willReturn('admin_template');
        $templateRegistry->getTemplate('base_list_field')->willReturn('admin_template');
        $this->admin->getModelManager()->willReturn($modelManager->reveal());
        $this->twig->addExtension(new SonataAdminExtension(
            $this->pool->reveal(),
            null,
            $translator->reveal(),
            $container->reveal()
        ));
        $this->pool->getPropertyAccessor()->willReturn($propertyAccessor);
        $fieldDescription->getType()->willReturn('choice');
        $fieldDescription->getOption('editable')->willReturn(true);
        $fieldDescription->getOption('class')->willReturn(Bar::class);
        $fieldDescription->getTargetModel()->willReturn(Bar::class);
        $fieldDescription->getAdmin()->willReturn($this->admin->reveal());
        $fieldDescription->getTemplate()->willReturn('field_template');
        $fieldDescription->getValue(Argument::cetera())->willReturn('some value');
        $modelManager->find(\get_class($associationObject), 1)->willReturn($associationObject);

        $this->validator->validate($object)->willReturn(new ConstraintViolationList([]));

        $response = ($this->action)($request);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testSetObjectFieldValueActionWithViolations(): void
    {
        $bar = new Bar();
        $object = new Baz();
        $object->setBar($bar);
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'bar.enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

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

        $response = ($this->action)($request);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(json_encode("error1\nerror2"), $response->getContent());
    }

    public function testSetObjectFieldEditableMultipleValue(): void
    {
        $object = new StatusMultiple();
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'field' => 'status',
            'value' => [1, 2],
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

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
        $this->admin->getListFieldDescription('status')->willReturn($fieldDescription->reveal());
        $this->admin->update($object)->shouldBeCalled();
        // NEXT_MAJOR: Remove this line
        $this->admin->getTemplate('base_list_field')->willReturn('admin_template');
        $templateRegistry->getTemplate('base_list_field')->willReturn('admin_template');
        $container->get('sonata.post.admin.template_registry')->willReturn($templateRegistry->reveal());
        $this->pool->getPropertyAccessor()->willReturn($propertyAccessor);
        $this->twig->addExtension(new SonataAdminExtension(
            $pool->reveal(),
            null,
            $translator->reveal(),
            $container->reveal()
        ));
        $fieldDescription->getOption('editable')->willReturn(true);
        $fieldDescription->getOption('multiple')->willReturn(true);
        $fieldDescription->getAdmin()->willReturn($this->admin->reveal());
        $fieldDescription->getType()->willReturn('boolean');
        $fieldDescription->getTemplate()->willReturn('field_template');
        $fieldDescription->getValue(Argument::cetera())->willReturn(['some value']);

        $this->validator->validate($object)->willReturn(new ConstraintViolationList([]));

        $response = ($this->action)($request);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }
}
