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
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Twig\Environment;

final class RetrieveFormFieldElementActionTest extends TestCase
{
    /**
     * @var Stub&AdminFetcherInterface
     */
    private AdminFetcherInterface $adminFetcher;

    private RetrieveFormFieldElementAction $action;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private AdminInterface $admin;

    /**
     * @var Environment&MockObject
     */
    private Environment $twig;

    private AdminHelper $helper;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->admin = $this->createMock(AdminInterface::class);
        $this->adminFetcher = static::createStub(AdminFetcherInterface::class);
        $this->adminFetcher->method('get')->willReturn($this->admin);
        $this->helper = new AdminHelper(new PropertyAccessor());
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
            'elementId' => 'element_collection',
            'field' => 'enabled',
            'value' => 1,
            'context' => 'list',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST]);

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $formView = new FormView();
        $formViewChild = new FormView();
        $formViewChild->vars['id'] = 'element_collection';
        $formView->children['element_collection'] = $formViewChild;

        $form = static::createStub(Form::class);
        $form->method('createView')->willReturn($formView);
        $renderer = $this->configureFormRenderer();

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getForm')->willReturn($form);

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getClass')->willReturn($object::class);
        $this->admin->expects(static::once())->method('setSubject')->with($object);
        $this->admin->method('getFormTheme')->willReturn([]);
        $this->admin->method('getFormBuilder')->willReturn($formBuilder);

        $associationAdmin = $this->createMock(AdminInterface::class);
        $associationAdmin->method('getClass')->willReturn(\stdClass::class);

        $fieldDescription = static::createMock(FieldDescriptionInterface::class);
        $fieldDescription->method('getAssociationAdmin')->willReturn($associationAdmin);
        $this->admin->method('getFormFieldDescription')->willReturn($fieldDescription);
        $modelManager->method('find')->with($object::class, 42)->willReturn($object);
        $renderer->expects(static::once())->method('setTheme')->with($formViewChild);
        $renderer->method('searchAndRenderBlock')->with($formViewChild, 'widget')->willReturn('block');

        $response = ($this->action)($request);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame('block', $response->getContent());
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
