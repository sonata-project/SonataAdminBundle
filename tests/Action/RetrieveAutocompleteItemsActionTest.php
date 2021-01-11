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
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Action\GetShortObjectDescriptionAction;
use Sonata\AdminBundle\Action\RetrieveAutocompleteItemsAction;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\AdminBundle\Tests\Fixtures\Filter\FooFilter;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class RetrieveAutocompleteItemsActionTest extends TestCase
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var GetShortObjectDescriptionAction
     */
    private $action;

    /**
     * @var AbstractAdmin
     */
    private $admin;

    protected function setUp(): void
    {
        $this->admin = $this->createMock(AbstractAdmin::class);
        $this->admin->expects($this->once())->method('setRequest');
        $container = new Container();
        $container->set('foo.admin', $this->admin);
        $this->pool = new Pool($container, ['foo.admin']);
        $this->action = new RetrieveAutocompleteItemsAction($this->pool);
    }

    public function testRetrieveAutocompleteItemsActionNotGranted(): void
    {
        $request = new Request([
            'admin_code' => 'foo.admin',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->admin->method('hasAccess')->willReturnMap([
            ['create', false],
            ['edit', true],
        ]);

        $this->expectException(AccessDeniedException::class);

        ($this->action)($request);
    }

    public function testRetrieveAutocompleteItemsActionDisabledFormelememt(): void
    {
        $object = new \stdClass();
        $request = new Request([
            'admin_code' => 'foo.admin',
            'field' => 'barField',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        // NEXT_MAJOR: Use `createStub` instead of using mock builder
        $fieldDescription = $this->getMockBuilder(FieldDescriptionInterface::class)
            ->addMethods(['getTargetModel'])
            ->getMockForAbstractClass();

        $this->configureFormConfig('barField', true);

        $this->admin->method('getNewInstance')->willReturn($object);
        $this->admin->expects($this->once())->method('setSubject')->with($object);
        $this->admin->method('hasAccess')->with('create')->willReturn(true);
        $this->admin->method('getFormFieldDescriptions')->willReturn(null);
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
            'admin_code' => 'foo.admin',
            'field' => 'barField',
            'q' => 'so',
        ], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET, 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $targetAdmin = $this->createStub(AbstractAdmin::class);
        // NEXT_MAJOR: Use `createStub` instead of using mock builder
        $fieldDescription = $this->getMockBuilder(FieldDescriptionInterface::class)
            ->addMethods(['getTargetModel'])
            ->getMockForAbstractClass();

        $this->configureFormConfig('barField');

        $this->admin->method('getNewInstance')->willReturn($object);
        $this->admin->expects($this->once())->method('setSubject')->with($object);
        $this->admin->method('hasAccess')->with('create')->willReturn(true);
        $this->admin->method('getFormFieldDescription')->with('barField')->willReturn($fieldDescription);
        $this->admin->method('getFormFieldDescriptions')->willReturn(null);
        $targetAdmin->method('checkAccess')->with('list')->willReturn(null);
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
            'admin_code' => 'foo.admin',
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
            ['_per_page', null, 10],
            ['_page', null, 1]
        );

        $response = ($this->action)($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"OK","more":false,"items":[{"id":123,"label":"FOO"}]}', $response->getContent());
    }

    public function testRetrieveAutocompleteItemsComplexPropertyArray(): void
    {
        $request = new Request([
            'admin_code' => 'foo.admin',
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
            ['_per_page', null, 10],
            ['_page', null, 1]
        );

        $response = ($this->action)($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"OK","more":false,"items":[{"id":123,"label":"FOO"}]}', $response->getContent());
    }

    public function testRetrieveAutocompleteItemsComplexProperty(): void
    {
        $request = new Request([
            'admin_code' => 'foo.admin',
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
            ['_per_page', null, 10],
            ['_page', null, 1]
        );

        $response = ($this->action)($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"OK","more":false,"items":[{"id":123,"label":"FOO"}]}', $response->getContent());
    }

    private function configureAutocompleteItemsDatagrid(): MockObject
    {
        $model = new \stdClass();

        $targetAdmin = $this->createMock(AbstractAdmin::class);
        $datagrid = $this->createStub(DatagridInterface::class);
        $metadata = $this->createStub(MetadataInterface::class);
        // NEXT_MAJOR: Use createMock instead.
        $pager = $this->getMockBuilder(PagerInterface::class)
            ->addMethods(['getCurrentPageResults', 'isLastPage'])
            ->getMockForAbstractClass();
        // NEXT_MAJOR: Use `createStub` instead of using mock builder
        $fieldDescription = $this->getMockBuilder(FieldDescriptionInterface::class)
            ->addMethods(['getTargetModel'])
            ->getMockForAbstractClass();

        $this->admin->method('getNewInstance')->willReturn($model);
        $this->admin->expects($this->once())->method('setSubject')->with($model);
        $this->admin->method('hasAccess')->with('create')->willReturn(true);
        $this->admin->method('getFormFieldDescription')->with('barField')->willReturn($fieldDescription);
        $this->admin->method('getFormFieldDescriptions')->willReturn(null);
        $this->admin->method('id')->with($model)->willReturn(123);
        $targetAdmin->expects($this->once())->method('checkAccess')->with('list');
        $targetAdmin->expects($this->once())->method('setFilterPersister')->with(null);
        $targetAdmin->method('getDatagrid')->willReturn($datagrid);
        $targetAdmin->method('getObjectMetadata')->with($model)->willReturn($metadata);
        $metadata->method('getTitle')->willReturn('FOO');

        $datagrid->method('buildPager')->willReturn(null);
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
        $form = $this->createStub(Form::class);
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
            ['req_param_name_page_number', null, '_page'],
            ['to_string_callback', null, null],
            ['target_admin_access_action', null, 'list'],
        ]);
    }

    private function configureFormConfigComplexProperty(string $field): void
    {
        $form = $this->createStub(Form::class);
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
            ['req_param_name_page_number', null, '_page'],
            ['target_admin_access_action', null, 'list'],
        ]);
    }

    private function configureFormConfigComplexPropertyArray(string $field): void
    {
        $form = $this->createStub(Form::class);
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
            ['req_param_name_page_number', null, '_page'],
            ['target_admin_access_action', null, 'list'],
        ]);
    }
}
