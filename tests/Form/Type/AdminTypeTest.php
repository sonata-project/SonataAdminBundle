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

namespace Sonata\AdminBundle\Tests\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Sonata\AdminBundle\Tests\Fixtures\TestExtension;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class AdminTypeTest extends TypeTestCase
{
    /**
     * @var AdminType
     */
    private $adminType;

    protected function setUp(): void
    {
        $this->adminType = new AdminType();

        parent::setUp();
    }

    public function testGetDefaultOptions(): void
    {
        $optionResolver = new OptionsResolver();

        $this->adminType->configureOptions($optionResolver);

        $options = $optionResolver->resolve();

        $this->assertTrue($options['delete']);
        $this->assertFalse($options['auto_initialize']);
        $this->assertSame('link_add', $options['btn_add']);
        $this->assertSame('link_list', $options['btn_list']);
        $this->assertSame('link_delete', $options['btn_delete']);
        $this->assertSame('SonataAdminBundle', $options['btn_catalogue']);
    }

    public function testSubmitValidData(): void
    {
        $parentAdmin = $this->createMock(AdminInterface::class);
        $parentAdmin->expects($this->once())->method('hasSubject')->willReturn(false);
        $parentField = $this->createMock(FieldDescriptionInterface::class);
        $parentField->expects($this->once())->method('setAssociationAdmin')->with($this->isInstanceOf(AdminInterface::class));
        $parentField->expects($this->once())->method('getAdmin')->willReturn($parentAdmin);

        $modelManager = $this->createStub(ModelManagerInterface::class);

        $foo = new Foo();

        $admin = $this->createMock(AbstractAdmin::class);
        $admin->expects($this->exactly(2))->method('hasParentFieldDescription')->willReturn(true);
        $admin->expects($this->exactly(2))->method('getParentFieldDescription')->willReturn($parentField);
        $admin->expects($this->once())->method('hasAccess')->with('delete')->willReturn(false);
        $admin->expects($this->once())->method('defineFormBuilder');
        $admin->expects($this->once())->method('getModelManager')->willReturn($modelManager);
        $admin->expects($this->once())->method('getClass')->willReturn(Foo::class);
        $admin->expects($this->once())->method('getNewInstance')->willReturn($foo);
        $admin->expects($this->once())->method('setSubject')->with($foo);

        $field = $this->createMock(FieldDescriptionInterface::class);
        $field->expects($this->once())->method('getAssociationAdmin')->willReturn($admin);
        $field->expects($this->once())->method('getAdmin');
        $field->expects($this->once())->method('getName');
        $field->expects($this->exactly(3))->method('getOption')->withConsecutive(
            ['edit', 'standard'],
            ['inline', 'natural'],
            ['block_name', false]
        );

        $formData = [];

        $form = $this->factory->create(
            AdminType::class,
            null,
            [
                'sonata_field_description' => $field,
            ]
        );
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
    }

    public function testDotFields(): void
    {
        $foo = new \stdClass();
        $foo->bar = 1;

        $parentSubject = new \stdClass();
        $parentSubject->foo = $foo;

        $parentAdmin = $this->createMock(AdminInterface::class);
        $parentAdmin->expects($this->once())->method('getSubject')->willReturn($parentSubject);
        $parentAdmin->expects($this->once())->method('hasSubject')->willReturn(true);
        $parentField = $this->createMock(FieldDescriptionInterface::class);
        $parentField->expects($this->once())->method('setAssociationAdmin')->with($this->isInstanceOf(AdminInterface::class));
        $parentField->expects($this->once())->method('getAdmin')->willReturn($parentAdmin);

        $modelManager = $this->createStub(ModelManagerInterface::class);

        $admin = $this->createMock(AbstractAdmin::class);
        $admin->expects($this->exactly(2))->method('hasParentFieldDescription')->willReturn(true);
        $admin->expects($this->exactly(2))->method('getParentFieldDescription')->willReturn($parentField);
        $admin->expects($this->once())->method('setSubject')->with(1);
        $admin->expects($this->once())->method('defineFormBuilder');
        $admin->expects($this->once())->method('getModelManager')->willReturn($modelManager);
        $admin->expects($this->once())->method('getClass')->willReturn(Foo::class);

        $field = $this->createMock(FieldDescriptionInterface::class);
        $field->expects($this->once())->method('getAssociationAdmin')->willReturn($admin);
        $field->expects($this->once())->method('getFieldName')->willReturn('bar');
        $field->expects($this->once())->method('getParentAssociationMappings')->willReturn([['fieldName' => 'foo']]);

        $this->builder->add('foo.bar');

        try {
            $this->adminType->buildForm($this->builder, [
                'sonata_field_description' => $field,
                'delete' => false, // not needed
                'property_path' => 'bar', // actual test case
            ]);
        } catch (NoSuchPropertyException $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function testArrayCollection(): void
    {
        $foo = new Foo();

        $parentSubject = new \stdClass();
        $parentSubject->foo = new ArrayCollection([$foo]);

        $parentAdmin = $this->createMock(AdminInterface::class);
        $parentAdmin->expects($this->once())->method('getSubject')->willReturn($parentSubject);
        $parentAdmin->expects($this->once())->method('hasSubject')->willReturn(true);
        $parentField = $this->createMock(FieldDescriptionInterface::class);
        $parentField->expects($this->once())->method('setAssociationAdmin')->with($this->isInstanceOf(AdminInterface::class));
        $parentField->expects($this->once())->method('getAdmin')->willReturn($parentAdmin);

        $modelManager = $this->createStub(ModelManagerInterface::class);

        $admin = $this->createMock(AbstractAdmin::class);
        $admin->expects($this->exactly(2))->method('hasParentFieldDescription')->willReturn(true);
        $admin->expects($this->exactly(2))->method('getParentFieldDescription')->willReturn($parentField);
        $admin->expects($this->once())->method('defineFormBuilder');
        $admin->expects($this->once())->method('getModelManager')->willReturn($modelManager);
        $admin->expects($this->once())->method('getClass')->willReturn(Foo::class);
        $admin->expects($this->once())->method('setSubject')->with($foo);

        $field = $this->createMock(FieldDescriptionInterface::class);
        $field->expects($this->once())->method('getAssociationAdmin')->willReturn($admin);
        $field->expects($this->atLeastOnce())->method('getFieldName')->willReturn('foo');
        $field->expects($this->once())->method('getParentAssociationMappings')->willReturn([]);

        $this->builder->add('foo');

        try {
            $this->adminType->buildForm($this->builder, [
                'sonata_field_description' => $field,
                'delete' => false, // not needed
                'property_path' => '[0]', // actual test case
            ]);
        } catch (NoSuchPropertyException $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function testArrayCollectionNotFound(): void
    {
        $parentSubject = new class() {
            public $foo = [];
        };

        $parentAdmin = $this->createMock(AdminInterface::class);
        $parentAdmin->expects($this->once())->method('getSubject')->willReturn($parentSubject);
        $parentAdmin->expects($this->once())->method('hasSubject')->willReturn(true);
        $parentField = $this->createMock(FieldDescriptionInterface::class);
        $parentField->expects($this->once())->method('setAssociationAdmin')->with($this->isInstanceOf(AdminInterface::class));
        $parentField->expects($this->once())->method('getAdmin')->willReturn($parentAdmin);
        $parentField->expects($this->once())->method('getParentAssociationMappings')->willReturn([]);
        $parentField->expects($this->once())->method('getAssociationMapping')->willReturn(['fieldName' => 'foo', 'mappedBy' => 'bar']);

        $modelManager = $this->createStub(ModelManagerInterface::class);

        $newInstance = new class() {
            public function setBar()
            {
            }
        };

        $admin = $this->createMock(AbstractAdmin::class);
        $admin->expects($this->exactly(2))->method('hasParentFieldDescription')->willReturn(true);
        $admin->expects($this->exactly(2))->method('getParentFieldDescription')->willReturn($parentField);
        $admin->expects($this->once())->method('defineFormBuilder');
        $admin->expects($this->once())->method('getModelManager')->willReturn($modelManager);
        $admin->expects($this->once())->method('getClass')->willReturn(Foo::class);
        $admin->expects($this->once())->method('setSubject')->with($newInstance);
        $admin->expects($this->once())->method('getNewInstance')->willReturn($newInstance);

        $field = $this->createMock(FieldDescriptionInterface::class);
        $field->expects($this->once())->method('getAssociationAdmin')->willReturn($admin);
        $field->expects($this->atLeastOnce())->method('getFieldName')->willReturn('foo');
        $field->expects($this->once())->method('getParentAssociationMappings')->willReturn([]);

        $this->builder->add('foo');

        try {
            $this->adminType->buildForm($this->builder, [
                'sonata_field_description' => $field,
                'delete' => false, // not needed
                'property_path' => '[0]', // actual test case
                'collection_by_reference' => false,
            ]);
        } catch (NoSuchPropertyException $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function testArrayCollectionByReferenceNotFound(): void
    {
        $parentSubject = new class() {
            public $foo = [];

            public function addFoo()
            {
            }
        };

        $parentAdmin = $this->createMock(AdminInterface::class);
        $parentAdmin->expects($this->once())->method('getSubject')->willReturn($parentSubject);
        $parentAdmin->expects($this->once())->method('hasSubject')->willReturn(true);
        $parentField = $this->createMock(FieldDescriptionInterface::class);
        $parentField->expects($this->once())->method('setAssociationAdmin')->with($this->isInstanceOf(AdminInterface::class));
        $parentField->expects($this->once())->method('getAdmin')->willReturn($parentAdmin);
        $parentField->expects($this->once())->method('getParentAssociationMappings')->willReturn([]);
        $parentField->expects($this->once())->method('getAssociationMapping')->willReturn(['fieldName' => 'foo', 'mappedBy' => 'bar']);

        $modelManager = $this->createStub(ModelManagerInterface::class);

        $newInstance = new \stdClass();

        $admin = $this->createMock(AbstractAdmin::class);
        $admin->expects($this->exactly(2))->method('hasParentFieldDescription')->willReturn(true);
        $admin->expects($this->exactly(2))->method('getParentFieldDescription')->willReturn($parentField);
        $admin->expects($this->once())->method('defineFormBuilder');
        $admin->expects($this->once())->method('getModelManager')->willReturn($modelManager);
        $admin->expects($this->once())->method('getClass')->willReturn(Foo::class);
        $admin->expects($this->once())->method('setSubject')->with($newInstance);
        $admin->expects($this->once())->method('getNewInstance')->willReturn($newInstance);

        $field = $this->createMock(FieldDescriptionInterface::class);
        $field->expects($this->once())->method('getAssociationAdmin')->willReturn($admin);
        $field->expects($this->atLeastOnce())->method('getFieldName')->willReturn('foo');
        $field->expects($this->once())->method('getParentAssociationMappings')->willReturn([]);

        $this->builder->add('foo');

        try {
            $this->adminType->buildForm($this->builder, [
                'sonata_field_description' => $field,
                'delete' => false, // not needed
                'property_path' => '[0]', // actual test case
                'collection_by_reference' => true,
            ]);
        } catch (NoSuchPropertyException $exception) {
            $this->fail($exception->getMessage());
        }
    }

    protected function getExtensions()
    {
        $extensions = parent::getExtensions();

        $guesser = $this->createStub(FormTypeGuesserInterface::class);
        $extension = new TestExtension($guesser);

        $extension->addTypeExtension(new FormTypeFieldExtension([], []));
        $extensions[] = $extension;

        return $extensions;
    }
}
