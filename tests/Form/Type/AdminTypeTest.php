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
use Prophecy\Argument\Token\AnyValueToken;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
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
    public function testGetDefaultOptions(): void
    {
        $type = new AdminType();

        $optionResolver = new OptionsResolver();

        $type->configureOptions($optionResolver);

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
        $parentAdmin = $this->prophesize(AdminInterface::class);
        $parentAdmin->getSubject()->shouldBeCalled()->willReturn(null);
        $parentField = $this->prophesize(FieldDescriptionInterface::class);
        $parentField->getAdmin()->shouldBeCalled()->willReturn($parentAdmin->reveal());

        $modelManager = $this->prophesize(ModelManagerInterface::class);

        $foo = new Foo();

        $admin = $this->prophesize(AbstractAdmin::class);
        $admin->hasParentFieldDescription()->shouldBeCalled()->willReturn(false);
        $admin->getParentFieldDescription()->shouldBeCalled()->willReturn($parentField->reveal());
        $admin->hasAccess('delete')->shouldBeCalled()->willReturn(false);
        $admin->defineFormBuilder(new AnyValueToken())->shouldBeCalled();
        $admin->getModelManager()->shouldBeCalled()->willReturn($modelManager);
        $admin->getClass()->shouldBeCalled()->willReturn(Foo::class);
        $admin->getNewInstance()->shouldBeCalled()->willReturn($foo);
        $admin->setSubject($foo)->shouldBeCalled();

        $field = $this->prophesize(FieldDescriptionInterface::class);
        $field->getAssociationAdmin()->shouldBeCalled()->willReturn($admin->reveal());
        $field->getAdmin()->shouldBeCalled();
        $field->getName()->shouldBeCalled();
        $field->getOption('edit', 'standard')->shouldBeCalled();
        $field->getOption('inline', 'natural')->shouldBeCalled();
        $field->getOption('block_name', false)->shouldBeCalled();
        $formData = [];

        $form = $this->factory->create(
            AdminType::class,
            null,
            [
                'sonata_field_description' => $field->reveal(),
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

        $parentAdmin = $this->prophesize(AdminInterface::class);
        $parentAdmin->getSubject()->shouldBeCalled()->willReturn($parentSubject);
        $parentField = $this->prophesize(FieldDescriptionInterface::class);
        $parentField->getAdmin()->shouldBeCalled()->willReturn($parentAdmin->reveal());

        $modelManager = $this->prophesize(ModelManagerInterface::class);

        $admin = $this->prophesize(AbstractAdmin::class);
        $admin->hasParentFieldDescription()->shouldBeCalled()->willReturn(false);
        $admin->getParentFieldDescription()->shouldBeCalled()->willReturn($parentField->reveal());
        $admin->setSubject(1)->shouldBeCalled();
        $admin->defineFormBuilder(new AnyValueToken())->shouldBeCalled();
        $admin->getModelManager()->shouldBeCalled()->willReturn($modelManager);
        $admin->getClass()->shouldBeCalled()->willReturn(Foo::class);

        $field = $this->prophesize(FieldDescriptionInterface::class);
        $field->getAssociationAdmin()->shouldBeCalled()->willReturn($admin->reveal());
        $field->getFieldName()->shouldBeCalled()->willReturn('bar');
        $field->getParentAssociationMappings()->shouldBeCalled()->willReturn([['fieldName' => 'foo']]);

        $this->builder->add('foo.bar');

        $type = new AdminType();
        try {
            $type->buildForm($this->builder, [
                'sonata_field_description' => $field->reveal(),
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

        $parentAdmin = $this->prophesize(AdminInterface::class);
        $parentAdmin->getSubject()->shouldBeCalled()->willReturn($parentSubject);
        $parentField = $this->prophesize(FieldDescriptionInterface::class);
        $parentField->getAdmin()->shouldBeCalled()->willReturn($parentAdmin->reveal());

        $modelManager = $this->prophesize(ModelManagerInterface::class);

        $admin = $this->prophesize(AbstractAdmin::class);
        $admin->hasParentFieldDescription()->shouldBeCalled()->willReturn(false);
        $admin->getParentFieldDescription()->shouldBeCalled()->willReturn($parentField->reveal());
        $admin->defineFormBuilder(new AnyValueToken())->shouldBeCalled();
        $admin->getModelManager()->shouldBeCalled()->willReturn($modelManager);
        $admin->getClass()->shouldBeCalled()->willReturn(Foo::class);
        $admin->setSubject($foo)->shouldBeCalled();

        $field = $this->prophesize(FieldDescriptionInterface::class);
        $field->getAssociationAdmin()->shouldBeCalled()->willReturn($admin->reveal());
        $field->getFieldName()->shouldBeCalled()->willReturn('foo');
        $field->getParentAssociationMappings()->shouldBeCalled()->willReturn([]);

        $this->builder->add('foo');

        $type = new AdminType();
        try {
            $type->buildForm($this->builder, [
                'sonata_field_description' => $field->reveal(),
                'delete' => false, // not needed
                'property_path' => '[0]', // actual test case
            ]);
        } catch (NoSuchPropertyException $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function testArrayCollectionNotFound(): void
    {
        $parentSubject = new \stdClass();
        $parentSubject->foo = new ArrayCollection();

        $parentAdmin = $this->prophesize(AdminInterface::class);
        $parentAdmin->getSubject()->shouldBeCalled()->willReturn($parentSubject);
        $parentField = $this->prophesize(FieldDescriptionInterface::class);
        $parentField->getAdmin()->shouldBeCalled()->willReturn($parentAdmin->reveal());

        $modelManager = $this->prophesize(ModelManagerInterface::class);

        $foo = new Foo();

        $admin = $this->prophesize(AbstractAdmin::class);
        $admin->hasParentFieldDescription()->shouldBeCalled()->willReturn(false);
        $admin->getParentFieldDescription()->shouldBeCalled()->willReturn($parentField->reveal());
        $admin->defineFormBuilder(new AnyValueToken())->shouldBeCalled();
        $admin->getModelManager()->shouldBeCalled()->willReturn($modelManager);
        $admin->getClass()->shouldBeCalled()->willReturn(Foo::class);
        $admin->getNewInstance()->shouldBeCalled()->willReturn($foo);
        $admin->setSubject($foo)->shouldBeCalled();

        $field = $this->prophesize(FieldDescriptionInterface::class);
        $field->getAssociationAdmin()->shouldBeCalled()->willReturn($admin->reveal());
        $field->getFieldName()->shouldBeCalled()->willReturn('foo');
        $field->getParentAssociationMappings()->shouldBeCalled()->willReturn([]);

        $this->builder->add('foo');

        $type = new AdminType();
        try {
            $type->buildForm($this->builder, [
                'sonata_field_description' => $field->reveal(),
                'delete' => false, // not needed
                'property_path' => '[0]', // actual test case
            ]);
        } catch (NoSuchPropertyException $exception) {
            $this->fail($exception->getMessage());
        }
    }

    protected function getExtensions()
    {
        $extensions = parent::getExtensions();
        $guesser = $this->prophesize(FormTypeGuesserInterface::class)->reveal();
        $extension = new TestExtension($guesser);

        $extension->addTypeExtension(new FormTypeFieldExtension([], []));
        $extensions[] = $extension;

        return $extensions;
    }
}
