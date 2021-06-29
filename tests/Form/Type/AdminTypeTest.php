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
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Sonata\AdminBundle\Tests\Fixtures\TestExtension;
use Symfony\Component\Form\FormExtensionInterface;
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

        self::assertTrue($options['delete']);
        self::assertFalse($options['auto_initialize']);
        self::assertSame('link_add', $options['btn_add']);
        self::assertSame('link_list', $options['btn_list']);
        self::assertSame('link_delete', $options['btn_delete']);
        self::assertSame('SonataAdminBundle', $options['btn_catalogue']);
    }

    public function testSubmitValidData(): void
    {
        $parentAdmin = $this->createMock(AdminInterface::class);
        $parentAdmin->expects(self::once())->method('hasSubject')->willReturn(false);
        $parentField = $this->createMock(FieldDescriptionInterface::class);
        $parentField->expects(self::once())->method('setAssociationAdmin')->with(self::isInstanceOf(AdminInterface::class));
        $parentField->expects(self::once())->method('getAdmin')->willReturn($parentAdmin);

        $modelManager = $this->createStub(ModelManagerInterface::class);

        $foo = new Foo();

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects(self::exactly(2))->method('hasParentFieldDescription')->willReturn(true);
        $admin->expects(self::exactly(2))->method('getParentFieldDescription')->willReturn($parentField);
        $admin->expects(self::once())->method('hasAccess')->with('delete')->willReturn(false);
        $admin->expects(self::once())->method('defineFormBuilder');
        $admin->setModelManager($modelManager);
        $admin->expects(self::once())->method('getClass')->willReturn(Foo::class);
        $admin->expects(self::once())->method('getNewInstance')->willReturn($foo);
        $admin->expects(self::once())->method('setSubject')->with($foo);

        $field = $this->createMock(FieldDescriptionInterface::class);
        $field->expects(self::once())->method('getAssociationAdmin')->willReturn($admin);
        $field->expects(self::once())->method('getAdmin');
        $field->expects(self::once())->method('getName');
        $field->expects(self::exactly(3))->method('getOption')->withConsecutive(
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
        self::assertTrue($form->isSynchronized());
    }

    public function testDotFields(): void
    {
        $bar = new \stdClass();
        $bar->baz = 1;
        $foo = new \stdClass();
        $foo->bar = $bar;
        $parentSubject = new \stdClass();
        $parentSubject->foo = $foo;

        $parentAdmin = $this->createMock(AdminInterface::class);
        $parentAdmin->expects(self::once())->method('getSubject')->willReturn($parentSubject);
        $parentAdmin->expects(self::once())->method('hasSubject')->willReturn(true);
        $parentField = $this->createMock(FieldDescriptionInterface::class);
        $parentField->expects(self::once())->method('setAssociationAdmin')->with(self::isInstanceOf(AdminInterface::class));
        $parentField->expects(self::once())->method('getAdmin')->willReturn($parentAdmin);

        $modelManager = $this->createStub(ModelManagerInterface::class);

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects(self::exactly(2))->method('hasParentFieldDescription')->willReturn(true);
        $admin->expects(self::exactly(2))->method('getParentFieldDescription')->willReturn($parentField);
        $admin->expects(self::once())->method('setSubject')->with($bar);
        $admin->expects(self::once())->method('defineFormBuilder');
        $admin->setModelManager($modelManager);
        $admin->expects(self::once())->method('getClass')->willReturn(Foo::class);

        $field = $this->createMock(FieldDescriptionInterface::class);
        $field->expects(self::once())->method('getAssociationAdmin')->willReturn($admin);
        $field->expects(self::once())->method('getFieldName')->willReturn('bar');
        $field->expects(self::once())->method('getParentAssociationMappings')->willReturn([['fieldName' => 'foo']]);

        $this->builder->add('foo.bar');

        try {
            $this->adminType->buildForm($this->builder, [
                'sonata_field_description' => $field,
                'delete' => false, // not needed
                'property_path' => 'bar', // actual test case
            ]);
        } catch (NoSuchPropertyException $exception) {
            self::fail($exception->getMessage());
        }
    }

    public function testArrayCollection(): void
    {
        $foo = new Foo();

        $parentSubject = new \stdClass();
        $parentSubject->foo = new ArrayCollection([$foo]);

        $parentAdmin = $this->createMock(AdminInterface::class);
        $parentAdmin->expects(self::once())->method('getSubject')->willReturn($parentSubject);
        $parentAdmin->expects(self::once())->method('hasSubject')->willReturn(true);
        $parentField = $this->createMock(FieldDescriptionInterface::class);
        $parentField->expects(self::once())->method('setAssociationAdmin')->with(self::isInstanceOf(AdminInterface::class));
        $parentField->expects(self::once())->method('getAdmin')->willReturn($parentAdmin);

        $modelManager = $this->createStub(ModelManagerInterface::class);

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects(self::exactly(2))->method('hasParentFieldDescription')->willReturn(true);
        $admin->expects(self::exactly(2))->method('getParentFieldDescription')->willReturn($parentField);
        $admin->expects(self::once())->method('defineFormBuilder');
        $admin->setModelManager($modelManager);
        $admin->expects(self::once())->method('getClass')->willReturn(Foo::class);
        $admin->expects(self::once())->method('setSubject')->with($foo);

        $field = $this->createMock(FieldDescriptionInterface::class);
        $field->expects(self::once())->method('getAssociationAdmin')->willReturn($admin);
        $field->expects(self::atLeastOnce())->method('getFieldName')->willReturn('foo');
        $field->expects(self::once())->method('getParentAssociationMappings')->willReturn([]);

        $this->builder->add('foo');

        try {
            $this->adminType->buildForm($this->builder, [
                'sonata_field_description' => $field,
                'delete' => false, // not needed
                'property_path' => '[0]', // actual test case
            ]);
        } catch (NoSuchPropertyException $exception) {
            self::fail($exception->getMessage());
        }
    }

    public function testArrayCollectionNotFound(): void
    {
        $parentSubject = new class() {
            /** @var mixed[] */
            public $foo = [];
        };

        $parentAdmin = $this->createMock(AdminInterface::class);
        $parentAdmin->expects(self::once())->method('getSubject')->willReturn($parentSubject);
        $parentAdmin->expects(self::once())->method('hasSubject')->willReturn(true);
        $parentField = $this->createMock(FieldDescriptionInterface::class);
        $parentField->expects(self::once())->method('setAssociationAdmin')->with(self::isInstanceOf(AdminInterface::class));
        $parentField->expects(self::once())->method('getAdmin')->willReturn($parentAdmin);
        $parentField->expects(self::once())->method('getParentAssociationMappings')->willReturn([]);
        $parentField->expects(self::once())->method('getAssociationMapping')->willReturn(['fieldName' => 'foo', 'mappedBy' => 'bar']);

        $modelManager = $this->createStub(ModelManagerInterface::class);

        $newInstance = new class() {
            public function setBar(): void
            {
            }
        };

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects(self::exactly(2))->method('hasParentFieldDescription')->willReturn(true);
        $admin->expects(self::exactly(2))->method('getParentFieldDescription')->willReturn($parentField);
        $admin->expects(self::once())->method('defineFormBuilder');
        $admin->setModelManager($modelManager);
        $admin->expects(self::once())->method('getClass')->willReturn(Foo::class);
        $admin->expects(self::once())->method('setSubject')->with($newInstance);
        $admin->expects(self::once())->method('getNewInstance')->willReturn($newInstance);

        $field = $this->createMock(FieldDescriptionInterface::class);
        $field->expects(self::once())->method('getAssociationAdmin')->willReturn($admin);
        $field->expects(self::atLeastOnce())->method('getFieldName')->willReturn('foo');
        $field->expects(self::once())->method('getParentAssociationMappings')->willReturn([]);

        $this->builder->add('foo');

        try {
            $this->adminType->buildForm($this->builder, [
                'sonata_field_description' => $field,
                'delete' => false, // not needed
                'property_path' => '[0]', // actual test case
                'collection_by_reference' => false,
            ]);
        } catch (NoSuchPropertyException $exception) {
            self::fail($exception->getMessage());
        }
    }

    public function testArrayCollectionByReferenceNotFound(): void
    {
        $parentSubject = new class() {
            /** @var mixed[] */
            public $foo = [];

            public function addFoo(): void
            {
            }
        };

        $parentAdmin = $this->createMock(AdminInterface::class);
        $parentAdmin->expects(self::once())->method('getSubject')->willReturn($parentSubject);
        $parentAdmin->expects(self::once())->method('hasSubject')->willReturn(true);
        $parentField = $this->createMock(FieldDescriptionInterface::class);
        $parentField->expects(self::once())->method('setAssociationAdmin')->with(self::isInstanceOf(AdminInterface::class));
        $parentField->expects(self::once())->method('getAdmin')->willReturn($parentAdmin);
        $parentField->expects(self::once())->method('getParentAssociationMappings')->willReturn([]);
        $parentField->expects(self::once())->method('getAssociationMapping')->willReturn(['fieldName' => 'foo', 'mappedBy' => 'bar']);

        $modelManager = $this->createStub(ModelManagerInterface::class);

        $newInstance = new \stdClass();

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects(self::exactly(2))->method('hasParentFieldDescription')->willReturn(true);
        $admin->expects(self::exactly(2))->method('getParentFieldDescription')->willReturn($parentField);
        $admin->expects(self::once())->method('defineFormBuilder');
        $admin->setModelManager($modelManager);
        $admin->expects(self::once())->method('getClass')->willReturn(Foo::class);
        $admin->expects(self::once())->method('setSubject')->with($newInstance);
        $admin->expects(self::once())->method('getNewInstance')->willReturn($newInstance);

        $field = $this->createMock(FieldDescriptionInterface::class);
        $field->expects(self::once())->method('getAssociationAdmin')->willReturn($admin);
        $field->expects(self::atLeastOnce())->method('getFieldName')->willReturn('foo');
        $field->expects(self::once())->method('getParentAssociationMappings')->willReturn([]);

        $this->builder->add('foo');

        try {
            $this->adminType->buildForm($this->builder, [
                'sonata_field_description' => $field,
                'delete' => false, // not needed
                'property_path' => '[0]', // actual test case
                'collection_by_reference' => true,
            ]);
        } catch (NoSuchPropertyException $exception) {
            self::fail($exception->getMessage());
        }
    }

    /**
     * @return array<FormExtensionInterface>
     */
    protected function getExtensions(): array
    {
        $extensions = parent::getExtensions();

        $guesser = $this->createStub(FormTypeGuesserInterface::class);
        $extension = new TestExtension($guesser);

        $extension->addTypeExtension(new FormTypeFieldExtension([], []));
        $extensions[] = $extension;

        return $extensions;
    }
}
