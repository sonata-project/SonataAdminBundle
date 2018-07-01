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

use Prophecy\Argument\Token\AnyValueToken;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Tests\Fixtures\TestExtension;
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
        $parentField = $this->prophesize(FieldDescriptionInterface::class);
        $parentField->getAdmin()->shouldBeCalled()->willReturn($parentAdmin->reveal());

        $modelManager = $this->prophesize(ModelManagerInterface::class);
        $modelManager->modelReverseTransform(Foo::class, [])->shouldBeCalled();

        $admin = $this->prophesize(AbstractAdmin::class);
        $admin->hasParentFieldDescription()->shouldBeCalled()->willReturn(false);
        $admin->getParentFieldDescription()->shouldBeCalled()->willReturn($parentField->reveal());
        $admin->hasAccess('delete')->shouldBeCalled()->willReturn(false);
        $admin->setSubject(null)->shouldBeCalled();
        $admin->defineFormBuilder(new AnyValueToken())->shouldBeCalled();
        $admin->getModelManager()->shouldBeCalled()->willReturn($modelManager);
        $admin->getClass()->shouldBeCalled()->willReturn(Foo::class);

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
        $parentSubject = new \stdClass();
        $parentSubject->foo = 1;

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
        $field->getFieldName()->shouldBeCalled()->willReturn('foo');

        $this->builder->add('foo.bar');

        try {
            $type = new AdminType();
            $type->buildForm($this->builder, [
                'sonata_field_description' => $field->reveal(),
                'delete' => false, // not needed
                'property_path' => 'foo', // actual test case
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
