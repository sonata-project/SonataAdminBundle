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
use Sonata\AdminBundle\Action\RetrieveFormFieldElementAction;
use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class RetrieveFormFieldElementActionTest extends TestCase
{
    /**
     * @var Stub&AdminFetcherInterface
     */
    private $adminFetcher;

    /**
     * @var RetrieveFormFieldElementAction
     */
    private $action;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private $admin;

    /**
     * @var Environment&MockObject
     */
    private $twig;

    /**
     * @var AdminHelper&MockObject
     */
    private $helper;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->admin = $this->createMock(AdminInterface::class);
        $this->adminFetcher = $this->createStub(AdminFetcherInterface::class);
        $this->adminFetcher->method('get')->willReturn($this->admin);
        $this->helper = $this->createMock(AdminHelper::class);
        $this->action = new RetrieveFormFieldElementAction(
            $this->twig,
            $this->adminFetcher,
            $this->helper
        );
    }

    public function testRetrieveFormFieldElementAction(): void
    {
        $object = new \stdClass();
        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'objectId' => 42,
            'elementId' => 'element_42',
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST]);

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $formView = new FormView();
        $form = $this->createMock(Form::class);
        $formBuilder = $this->createStub(FormBuilder::class);

        $renderer = $this->configureFormRenderer();

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getClass')->willReturn(\get_class($object));
        $this->admin->expects(self::once())->method('setSubject')->with($object);
        $this->admin->method('getFormTheme')->willReturn([]);
        $this->admin->method('getFormBuilder')->willReturn($formBuilder);
        $this->helper->method('getChildFormView')->with($formView, 'element_42')->willReturn($formView);
        $modelManager->method('find')->with(\get_class($object), 42)->willReturn($object);
        $form->expects(self::once())->method('setData')->with($object);
        $form->expects(self::once())->method('handleRequest')->with($request);
        $form->method('createView')->willReturn($formView);
        $formBuilder->method('getForm')->willReturn($form);
        $renderer->expects(self::once())->method('setTheme')->with($formView, []);
        $renderer->method('searchAndRenderBlock')->with($formView, 'widget')->willReturn('block');

        $response = ($this->action)($request);

        self::assertInstanceOf(Response::class, $response);
        self::assertSame($response->getContent(), 'block');
    }

    /**
     * @return MockObject&FormRenderer
     */
    private function configureFormRenderer(): MockObject
    {
        $runtime = $this->createMock(FormRenderer::class);

        $this->twig->method('getRuntime')->with(FormRenderer::class)->willReturn($runtime);

        return $runtime;
    }
}
