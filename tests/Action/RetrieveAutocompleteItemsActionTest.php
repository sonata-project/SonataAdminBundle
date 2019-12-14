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
use Prophecy\Prophecy\ObjectProphecy;
use Sonata\AdminBundle\Action\GetShortObjectDescriptionAction;
use Sonata\AdminBundle\Action\RetrieveAutocompleteItemsAction;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\AdminBundle\Tests\Fixtures\Filter\FooFilter;
use Sonata\DatagridBundle\Datagrid\DatagridInterface;
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
        $this->admin = $this->prophesize(AbstractAdmin::class);
        $this->admin->setRequest(Argument::type(Request::class))->shouldBeCalled();
        $this->pool = $this->prophesize(Pool::class);
        $this->pool->getInstance(Argument::any())->willReturn($this->admin->reveal());
        $this->action = new RetrieveAutocompleteItemsAction(
            $this->pool->reveal()
        );
    }

    public function testRetrieveAutocompleteItemsActionNotGranted(): void
    {
        $this->expectException(AccessDeniedException::class);

        $request = new Request([
            'admin_code' => 'foo.admin',
        ], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->admin->hasAccess('create')->willReturn(false);
        $this->admin->hasAccess('edit')->willReturn(false);

        ($this->action)($request);
    }

    public function testRetrieveAutocompleteItemsActionDisabledFormelememt(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Autocomplete list can`t be retrieved because the form element is disabled or read_only.');

        $object = new \stdClass();
        $request = new Request([
            'admin_code' => 'foo.admin',
            'field' => 'barField',
        ], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $fieldDescription = $this->prophesize(FieldDescriptionInterface::class);

        $this->configureFormConfig('barField', true);

        $this->admin->getNewInstance()->willReturn($object);
        $this->admin->setSubject($object)->shouldBeCalled();
        $this->admin->hasAccess('create')->willReturn(true);
        $this->admin->getFormFieldDescriptions()->willReturn(null);
        $this->admin->getFormFieldDescription('barField')->willReturn($fieldDescription->reveal());

        $fieldDescription->getTargetEntity()->willReturn(Foo::class);
        $fieldDescription->getName()->willReturn('barField');

        ($this->action)($request);
    }

    public function testRetrieveAutocompleteItemsTooShortSearchString(): void
    {
        $object = new \stdClass();
        $request = new Request([
            'admin_code' => 'foo.admin',
            'field' => 'barField',
            'q' => 'so',
        ], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $targetAdmin = $this->prophesize(AbstractAdmin::class);
        $fieldDescription = $this->prophesize(FieldDescriptionInterface::class);

        $this->configureFormConfig('barField');

        $this->admin->getNewInstance()->willReturn($object);
        $this->admin->setSubject($object)->shouldBeCalled();
        $this->admin->hasAccess('create')->willReturn(true);
        $this->admin->getFormFieldDescription('barField')->willReturn($fieldDescription->reveal());
        $this->admin->getFormFieldDescriptions()->willReturn(null);
        $targetAdmin->checkAccess('list')->shouldBeCalled();
        $fieldDescription->getTargetEntity()->willReturn(Foo::class);
        $fieldDescription->getName()->willReturn('barField');
        $fieldDescription->getAssociationAdmin()->willReturn($targetAdmin->reveal());

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
        ], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->configureFormConfig('barField');

        $datagrid = $this->configureAutocompleteItemsDatagrid();
        $filter = new FooFilter();
        $filter->initialize('foo');

        $datagrid->hasFilter('foo')->willReturn(true);
        $datagrid->getFilter('foo')->willReturn($filter);
        $datagrid->setValue('foo', null, 'sonata')->shouldBeCalled();

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
        ], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->configureFormConfigComplexPropertyArray('barField');
        $datagrid = $this->configureAutocompleteItemsDatagrid();

        $filter = new FooFilter();
        $filter->initialize('entity.property');

        $datagrid->hasFilter('entity.property')->willReturn(true);
        $datagrid->getFilter('entity.property')->willReturn($filter);
        $filter2 = new FooFilter();
        $filter2->initialize('entity2.property2');

        $datagrid->hasFilter('entity2.property2')->willReturn(true);
        $datagrid->getFilter('entity2.property2')->willReturn($filter2);

        $datagrid->setValue('entity__property', null, 'sonata')->shouldBeCalled();
        $datagrid->setValue('entity2__property2', null, 'sonata')->shouldBeCalled();

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
        ], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->configureFormConfigComplexProperty('barField');
        $datagrid = $this->configureAutocompleteItemsDatagrid();

        $filter = new FooFilter();
        $filter->initialize('entity.property');

        $datagrid->hasFilter('entity.property')->willReturn(true);
        $datagrid->getFilter('entity.property')->willReturn($filter);
        $datagrid->setValue('entity__property', null, 'sonata')->shouldBeCalled();

        $response = ($this->action)($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"status":"OK","more":false,"items":[{"id":123,"label":"FOO"}]}', $response->getContent());
    }

    private function configureAutocompleteItemsDatagrid(): ObjectProphecy
    {
        $entity = new \stdClass();

        $targetAdmin = $this->prophesize(AbstractAdmin::class);
        $datagrid = $this->prophesize(DatagridInterface::class);
        $metadata = $this->prophesize(MetadataInterface::class);
        $pager = $this->prophesize(Pager::class);
        $fieldDescription = $this->prophesize(FieldDescriptionInterface::class);

        $this->admin->getNewInstance()->willReturn($entity);
        $this->admin->setSubject($entity)->shouldBeCalled();
        $this->admin->hasAccess('create')->willReturn(true);
        $this->admin->getFormFieldDescription('barField')->willReturn($fieldDescription->reveal());
        $this->admin->getFormFieldDescriptions()->willReturn(null);
        $this->admin->id($entity)->willReturn(123);
        $targetAdmin->checkAccess('list')->shouldBeCalled();
        $targetAdmin->setFilterPersister(null)->shouldBeCalled();
        $targetAdmin->getDatagrid()->willReturn($datagrid->reveal());
        $targetAdmin->getObjectMetadata($entity)->willReturn($metadata->reveal());
        $metadata->getTitle()->willReturn('FOO');

        $datagrid->setValue('_per_page', null, 10)->shouldBeCalled();
        $datagrid->setValue('_page', null, 1)->shouldBeCalled();
        $datagrid->buildPager()->willReturn(null);
        $datagrid->getPager()->willReturn($pager->reveal());
        $pager->getResults()->willReturn([$entity]);
        $pager->isLastPage()->willReturn(true);
        $fieldDescription->getTargetEntity()->willReturn(Foo::class);
        $fieldDescription->getName()->willReturn('barField');
        $fieldDescription->getAssociationAdmin()->willReturn($targetAdmin->reveal());

        return $datagrid;
    }

    private function configureFormConfig(string $field, bool $disabled = false): void
    {
        $form = $this->prophesize(Form::class);
        $formType = $this->prophesize(Form::class);
        $formConfig = $this->prophesize(FormConfigInterface::class);

        $this->admin->getForm()->willReturn($form->reveal());
        $form->get($field)->willReturn($formType->reveal());
        $formType->getConfig()->willReturn($formConfig->reveal());
        $formConfig->getAttribute('disabled')->willReturn($disabled);
        $formConfig->getAttribute('property')->willReturn('foo');
        $formConfig->getAttribute('callback')->willReturn(null);
        $formConfig->getAttribute('minimum_input_length')->willReturn(3);
        $formConfig->getAttribute('items_per_page')->willReturn(10);
        $formConfig->getAttribute('req_param_name_page_number')->willReturn('_page');
        $formConfig->getAttribute('to_string_callback')->willReturn(null);
        $formConfig->getAttribute('target_admin_access_action')->willReturn('list');
    }

    private function configureFormConfigComplexProperty(string $field): void
    {
        $form = $this->prophesize(Form::class);
        $formType = $this->prophesize(Form::class);
        $formConfig = $this->prophesize(FormConfigInterface::class);

        $this->admin->getForm()->willReturn($form->reveal());
        $form->get($field)->willReturn($formType->reveal());
        $formType->getConfig()->willReturn($formConfig->reveal());
        $formConfig->getAttribute('disabled')->willReturn(false);
        $formConfig->getAttribute('property')->willReturn('entity.property');
        $formConfig->getAttribute('callback')->willReturn(null);
        $formConfig->getAttribute('minimum_input_length')->willReturn(3);
        $formConfig->getAttribute('items_per_page')->willReturn(10);
        $formConfig->getAttribute('req_param_name_page_number')->willReturn('_page');
        $formConfig->getAttribute('to_string_callback')->willReturn(null);
        $formConfig->getAttribute('target_admin_access_action')->willReturn('list');
    }

    private function configureFormConfigComplexPropertyArray($field): void
    {
        $form = $this->prophesize(Form::class);
        $formType = $this->prophesize(Form::class);
        $formConfig = $this->prophesize(FormConfigInterface::class);

        $this->admin->getForm()->willReturn($form->reveal());
        $form->get($field)->willReturn($formType->reveal());
        $formType->getConfig()->willReturn($formConfig->reveal());
        $formConfig->getAttribute('disabled')->willReturn(false);
        $formConfig->getAttribute('property')->willReturn(['entity.property', 'entity2.property2']);
        $formConfig->getAttribute('callback')->willReturn(null);
        $formConfig->getAttribute('minimum_input_length')->willReturn(3);
        $formConfig->getAttribute('items_per_page')->willReturn(10);
        $formConfig->getAttribute('req_param_name_page_number')->willReturn('_page');
        $formConfig->getAttribute('to_string_callback')->willReturn(null);
        $formConfig->getAttribute('target_admin_access_action')->willReturn('list');
    }
}
