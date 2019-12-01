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
use Sonata\AdminBundle\Action\AppendFormFieldElementAction;
use Sonata\AdminBundle\Action\GetShortObjectDescriptionAction;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

final class AppendFormFieldElementActionTest extends TestCase
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

    /**
     * @var AdminHelper
     */
    private $helper;

    protected function setUp(): void
    {
        $this->twig = $this->prophesize(Environment::class);
        $this->pool = $this->prophesize(Pool::class);
        $this->admin = $this->prophesize(AbstractAdmin::class);
        $this->pool->getInstance(Argument::any())->willReturn($this->admin->reveal());
        $this->admin->setRequest(Argument::type(Request::class))->shouldBeCalled();
        $this->validator = $this->prophesize(ValidatorInterface::class);
        $this->helper = $this->prophesize(AdminHelper::class);
        $this->action = new AppendFormFieldElementAction(
            $this->twig->reveal(),
            $this->pool->reveal(),
            $this->helper->reveal()
        );
    }

    public function testAppendFormFieldElementAction(): void
    {
        $object = new \stdClass();
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
        $this->admin->getClass()->willReturn(\get_class($object));
        $this->admin->setSubject($object)->shouldBeCalled();
        $this->admin->getFormTheme()->willReturn($formView);
        $this->helper->appendFormFieldElement($this->admin->reveal(), $object, null)->willReturn([
            $this->prophesize(FieldDescriptionInterface::class),
            $form->reveal(),
        ]);
        $this->helper->getChildFormView($formView, null)
            ->willReturn($formView);
        $modelManager->find(\get_class($object), 42)->willReturn($object);
        $form->createView()->willReturn($formView);
        $renderer->setTheme($formView, $formView)->shouldBeCalled();
        $renderer->searchAndRenderBlock($formView, 'widget')->willReturn('block');

        $response = ($this->action)($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame($response->getContent(), 'block');
    }

    private function configureFormRenderer()
    {
        $runtime = $this->prophesize(FormRenderer::class);

        $this->twig->getRuntime(FormRenderer::class)->willReturn($runtime->reveal());

        return $runtime;
    }
}
