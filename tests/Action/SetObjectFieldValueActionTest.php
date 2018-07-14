<?php

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
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bridge\Twig\Command\DebugCommand;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormRendererEngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Twig\Template;

class Foo
{
    public function setEnabled($value)
    {
    }
}

class Bar
{
    public function setEnabled($value)
    {
    }
}

class Baz
{
    private $bar;

    public function setBar(Bar $bar)
    {
        $this->bar = $bar;
    }

    public function getBar()
    {
        return $this->bar;
    }
}

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

    protected function setUp()
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

    public function testSetObjectFieldValueAction()
    {
        $object = new Foo();
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
        $this->twig->addExtension(new SonataAdminExtension(
            $pool->reveal(),
            null,
            $translator->reveal(),
            $container->reveal()
        ));
        $fieldDescription->getOption('editable')->willReturn(true);
        $fieldDescription->getAdmin()->willReturn($this->admin->reveal());
        $fieldDescription->getType()->willReturn('boolean');
        $fieldDescription->getTemplate()->willReturn(false);
        $fieldDescription->getValue(Argument::cetera())->willReturn('some value');

        $this->validator->validate($object)->willReturn(new ConstraintViolationList([]));
        $action = $this->action;
        $response = $action($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSetObjectFieldValueActionOnARelationField()
    {
        $object = new Baz();
        $associationObject = new Bar();
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
        $fieldDescription->getTargetEntity()->willReturn(Bar::class);
        $fieldDescription->getAdmin()->willReturn($this->admin->reveal());
        $fieldDescription->getTemplate()->willReturn('field_template');
        $fieldDescription->getValue(Argument::cetera())->willReturn('some value');
        $modelManager->find(get_class($associationObject), 1)->willReturn($associationObject);

        $this->validator->validate($object)->willReturn(new ConstraintViolationList([]));
        $action = $this->action;
        $response = $action($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSetObjectFieldValueActionWithViolations()
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

        $action = $this->action;
        $response = $action($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertSame(json_encode("error1\nerror2"), $response->getContent());
    }

    private function configureFormRenderer()
    {
        $runtime = new FormRenderer($this->createMock(
            FormRendererEngineInterface::class,
            CsrfTokenManagerInterface::class
        ));

        // Remove the condition when dropping sf < 3.2
        if (!method_exists(AppVariable::class, 'getToken')) {
            $extension = new FormExtension();

            $this->twig->addExtension($extension);
            $extension->renderer = $runtime;

            return $runtime;
        }

        // Remove the condition when dropping sf < 3.4
        if (!method_exists(DebugCommand::class, 'getLoaderPaths')) {
            $twigRuntime = $this->prophesize(TwigRenderer::class);

            $this->twig->addRuntimeLoader(new FactoryRuntimeLoader(
                FormRenderer::class,
                function () use ($runtime) {
                    return $runtime;
                }
            ));
            $this->twig->getRuntime(TwigRenderer::class)->willReturn($twigRuntime->reveal());
            $twigRuntime->setEnvironment($this->twig)->shouldBeCalled();

            return $twigRuntime;
        }

        $this->twig->addRuntimeLoader(new FactoryRuntimeLoader(
            FormRenderer::class,
            function () use ($runtime) {
                return $runtime;
            }
        ));

        return $runtime;
    }
}
