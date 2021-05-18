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
use Sonata\AdminBundle\Action\RetrieveAutocompleteItemsAction;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Sonata\AdminBundle\Tests\Fixtures\Filter\FooFilter;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class RetrieveAutocompleteItemsActionTest extends TestCase
{
    /**
     * @var Stub&AdminFetcherInterface
     */
    private $adminFetcher;

    /**
     * @var RetrieveAutocompleteItemsAction
     */
    private $action;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private $admin;

    protected function setUp(): void
    {
        $this->admin = $this->createMock(AdminInterface::class);
        $this->adminFetcher = $this->createStub(AdminFetcherInterface::class);
        $this->adminFetcher->method('get')->willReturn($this->admin);
        $this->action = new RetrieveAutocompleteItemsAction($this->adminFetcher);
    }

    public function testRetrieveAutocompleteItemsActionNotGranted(): void
    {
        $request = new Request([
            '_sonata_admin' => 'foo.admin',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->admin->method('hasAccess')->willReturnMap([
            ['create', null, false],
            ['edit', null, false],
        ]);

        $this->expectException(AccessDeniedException::class);

        ($this->action)($request);
    }

    public function testRetrieveAutocompleteItemsActionDisabledFormelememt(): void
    {
        $object = new \stdClass();
        $request = new Request([
            '_sonata_admin' => 'foo.admin',
            'field' => 'barField',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $this->configureFormConfig('barField', true);

        $this->admin->method('getNewInstance')->willReturn($object);
        $this->admin->expects($this->once())->method('setSubject')->with($object);
        $this->admin->method('hasAccess')->with('create')->willReturn(true);
        $this->admin->method('getFormFieldDescriptions')->willReturn([]);
        $this->admin->method('hasFormFieldDescription')->with('barField')->willReturn(true);
        $this->admin->method('getFormFieldDescription')->with('barField')->willReturn($fieldDescription);

        $fieldDescription->method('getTargetModel')->willReturn(Foo::class);
        $fieldDescription->method('getName')->willReturn('barField');

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Autocomplete list can`t be retrieved because the form element is disabled or read_only.');

        ($this->action)($request);
    }

    public function testRetrieveAutocompleteItemsTooShortSearchString(): void
    {
        $object = new \stdClass();
        $request = new Request([
            '_sonata_admin' => 'foo.admin',
            'field' => 'barField',
            'q' => 'so',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $targetAdmin = $this->createStub(AdminInterface::class);
        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $this->configureFormConfig('barField');

        $this->admin->method('getNewInstance')->willReturn($object);
        $this->admin->expects($this->once())->method('setSubject')->with($object);
        $this->admin->method('hasAccess')->with('create')->willReturn(true);
        $this->admin->method('hasFormFieldDescription')->with('barField')->willReturn(true);
        $this->admin->method('getFormFieldDescription')->with('barField')->willReturn($fieldDescription);
        $this->admin->method('getFormFieldDescriptions')->willReturn([]);
        $fieldDescription->method('getTargetModel')->willReturn(Foo::class);
        $fieldDescription->method('getName')->willReturn('barField');
        $fieldDescription->method('getAssociationAdmin')->willReturn($targetAdmin);

        $response = ($this->action)($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"KO","message":"Too short search string."}', $response->getContent());
    }

    public function testRetrieveAutocompleteItems(): void
    {
        $request = new Request([
            '_sonata_admin' => 'foo.admin',
            'field' => 'barField',
            'q' => 'sonata',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->configureFormConfig('barField');

        $datagrid = $this->configureAutocompleteItemsDatagrid();
        $filter = new FooFilter();
        $filter->initialize('foo');

        $datagrid->method('hasFilter')->with('foo')->willReturn(true);
        $datagrid->method('getFilter')->with('foo')->willReturn($filter);
        $datagrid->expects($this->exactly(3))->method('setValue')->withConsecutive(
            ['foo', null, 'sonata'],
            [DatagridInterface::PER_PAGE, null, 10],
            [DatagridInterface::PAGE, null, 1]
        );

        $response = ($this->action)($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"OK","more":false,"items":[{"id":"123","label":"FOO"}]}', $response->getContent());
    }

    public function testRetrieveAutocompleteItemsComplexPropertyArray(): void
    {
        $request = new Request([
            '_sonata_admin' => 'foo.admin',
            'field' => 'barField',
            'q' => 'sonata',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->configureFormConfigComplexPropertyArray('barField');
        $datagrid = $this->configureAutocompleteItemsDatagrid();

        $filter = new FooFilter();
        $filter->initialize('entity.property');

        $filter2 = new FooFilter();
        $filter2->initialize('entity2.property2');

        $datagrid->method('hasFilter')->willReturnMap([
            ['entity.property', true],
            ['entity2.property2', true],
        ]);
        $datagrid->method('getFilter')->willReturnMap([
            ['entity.property', $filter],
            ['entity2.property2', $filter2],
        ]);
        $datagrid->expects($this->exactly(4))->method('setValue')->withConsecutive(
            ['entity__property', null, 'sonata'],
            ['entity2__property2', null, 'sonata'],
            [DatagridInterface::PER_PAGE, null, 10],
            [DatagridInterface::PAGE, null, 1]
        );

        $response = ($this->action)($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"OK","more":false,"items":[{"id":"123","label":"FOO"}]}', $response->getContent());
    }

    public function testRetrieveAutocompleteItemsComplexProperty(): void
    {
        $request = new Request([
            '_sonata_admin' => 'foo.admin',
            'field' => 'barField',
            'q' => 'sonata',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->configureFormConfigComplexProperty('barField');
        $datagrid = $this->configureAutocompleteItemsDatagrid();

        $filter = new FooFilter();
        $filter->initialize('entity.property');

        $datagrid->method('hasFilter')->with('entity.property')->willReturn(true);
        $datagrid->method('getFilter')->with('entity.property')->willReturn($filter);
        $datagrid->expects($this->exactly(3))->method('setValue')->withConsecutive(
            ['entity__property', null, 'sonata'],
            [DatagridInterface::PER_PAGE, null, 10],
            [DatagridInterface::PAGE, null, 1]
        );

        $response = ($this->action)($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"OK","more":false,"items":[{"id":"123","label":"FOO"}]}', $response->getContent());
    }

    private function configureAutocompleteItemsDatagrid(): MockObject
    {
        $model = new \stdClass();

        $targetAdmin = $this->createMock(AdminInterface::class);
        $datagrid = $this->createMock(DatagridInterface::class);
        $metadata = $this->createStub(MetadataInterface::class);
        $pager = $this->createStub(PagerInterface::class);
        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $this->admin->method('getNewInstance')->willReturn($model);
        $this->admin->expects($this->once())->method('setSubject')->with($model);
        $this->admin->method('hasAccess')->with('create')->willReturn(true);
        $this->admin->method('hasFormFieldDescription')->with('barField')->willReturn(true);
        $this->admin->method('getFormFieldDescription')->with('barField')->willReturn($fieldDescription);
        $this->admin->method('getFormFieldDescriptions')->willReturn([]);
        $this->admin->method('id')->with($model)->willReturn('123');
        $targetAdmin->expects($this->once())->method('checkAccess')->with('list');
        $targetAdmin->method('getDatagrid')->willReturn($datagrid);
        $targetAdmin->method('getObjectMetadata')->with($model)->willReturn($metadata);
        $metadata->method('getTitle')->willReturn('FOO');

        $datagrid->method('getPager')->willReturn($pager);
        $pager->method('getCurrentPageResults')->willReturn([$model]);
        $pager->method('isLastPage')->willReturn(true);
        $fieldDescription->method('getTargetModel')->willReturn(Foo::class);
        $fieldDescription->method('getName')->willReturn('barField');
        $fieldDescription->method('getAssociationAdmin')->willReturn($targetAdmin);

        return $datagrid;
    }

    private function configureFormConfig(string $field, bool $disabled = false): void
    {
        $form = $this->createMock(Form::class);
        $formType = $this->createStub(Form::class);
        $formConfig = $this->createStub(FormConfigInterface::class);

        $this->admin->method('getForm')->willReturn($form);
        $form->method('get')->with($field)->willReturn($formType);
        $formType->method('getConfig')->willReturn($formConfig);
        $formConfig->method('getAttribute')->willReturnMap([
            ['disabled', null, $disabled],
            ['property', null, 'foo'],
            ['callback', null, null],
            ['minimum_input_length', null, 3],
            ['items_per_page', null, 10],
            ['req_param_name_page_number', null, DatagridInterface::PAGE],
            ['to_string_callback', null, null],
            ['target_admin_access_action', null, 'list'],
            ['response_item_callback', null, null],
        ]);
    }

    private function configureFormConfigComplexProperty(string $field): void
    {
        $form = $this->createMock(Form::class);
        $formType = $this->createStub(Form::class);
        $formConfig = $this->createStub(FormConfigInterface::class);

        $this->admin->method('getForm')->willReturn($form);
        $form->method('get')->with($field)->willReturn($formType);
        $formType->method('getConfig')->willReturn($formConfig);

        $formConfig->method('getAttribute')->willReturnMap([
            ['disabled', null, false],
            ['property', null, 'entity.property'],
            ['minimum_input_length', null, 3],
            ['items_per_page', null, 10],
            ['req_param_name_page_number', null, DatagridInterface::PAGE],
            ['target_admin_access_action', null, 'list'],
            ['response_item_callback', null, null],
        ]);
    }

    private function configureFormConfigComplexPropertyArray(string $field): void
    {
        $form = $this->createMock(Form::class);
        $formType = $this->createStub(Form::class);
        $formConfig = $this->createStub(FormConfigInterface::class);

        $this->admin->method('getForm')->willReturn($form);
        $form->method('get')->with($field)->willReturn($formType);
        $formType->method('getConfig')->willReturn($formConfig);

        $formConfig->method('getAttribute')->willReturnMap([
            ['disabled', null, false],
            ['property', null, ['entity.property', 'entity2.property2']],
            ['minimum_input_length', null, 3],
            ['items_per_page', null, 10],
            ['req_param_name_page_number', null, DatagridInterface::PAGE],
            ['target_admin_access_action', null, 'list'],
            ['response_item_callback', null, null],
        ]);
    }
}
