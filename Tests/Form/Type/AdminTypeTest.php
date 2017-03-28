<?php

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
use Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension;
use Sonata\AdminBundle\Form\Type\AdminType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Tests\Fixtures\TestExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions()
    {
        $type = new AdminType();

        $optionResolver = new OptionsResolver();

        if (!method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $type->setDefaultOptions($optionResolver);
        } else {
            $type->configureOptions($optionResolver);
        }

        $options = $optionResolver->resolve();

        $this->assertTrue($options['delete']);
        $this->assertFalse($options['auto_initialize']);
        $this->assertSame('link_add', $options['btn_add']);
        $this->assertSame('link_list', $options['btn_list']);
        $this->assertSame('link_delete', $options['btn_delete']);
        $this->assertSame('SonataAdminBundle', $options['btn_catalogue']);
    }

    public function testSubmitValidData()
    {
        if (!method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $this->markTestSkipped('Testing ancient versions would be more complicated.');

            return;
        }
        $parentAdmin = $this->prophesize('Sonata\AdminBundle\Admin\AdminInterface');
        $parentField = $this->prophesize('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $parentField->getAdmin()->shouldBeCalled()->willReturn($parentAdmin->reveal());

        $modelManager = $this->prophesize('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->modelReverseTransform(
            'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo',
            array()
        )->shouldBeCalled();

        $admin = $this->prophesize('Sonata\AdminBundle\Admin\AbstractAdmin');
        $admin->hasParentFieldDescription()->shouldBeCalled()->willReturn(false);
        $admin->getParentFieldDescription()->shouldBeCalled()->willReturn($parentField->reveal());
        $admin->hasAccess('delete')->shouldBeCalled()->willReturn(false);
        $admin->setSubject(null)->shouldBeCalled();
        $admin->defineFormBuilder(new AnyValueToken())->shouldBeCalled();
        $admin->getModelManager()->shouldBeCalled()->willReturn($modelManager);
        $admin->getClass()->shouldBeCalled()->willReturn('Sonata\AdminBundle\Tests\Fixtures\Entity\Foo');

        $field = $this->prophesize('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $field->getAssociationAdmin()->shouldBeCalled()->willReturn($admin->reveal());
        $field->getAdmin()->shouldBeCalled();
        $field->getName()->shouldBeCalled();
        $field->getOption('edit', 'standard')->shouldBeCalled();
        $field->getOption('inline', 'natural')->shouldBeCalled();
        $field->getOption('block_name', false)->shouldBeCalled();
        $formData = array();

        $form = $this->factory->create(
            'Sonata\AdminBundle\Form\Type\AdminType',
            null,
            array(
                'sonata_field_description' => $field->reveal(),
            )
        );
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
    }

    public function testDotFields()
    {
        if (!method_exists('Symfony\Component\PropertyAccess\PropertyAccessor', 'isReadable')) {
            return $this->markTestSkipped('Testing ancient versions would be more complicated.');
        }

        $parentSubject = new \stdClass();
        $parentSubject->foo = 1;

        $parentAdmin = $this->prophesize('Sonata\AdminBundle\Admin\AdminInterface');
        $parentAdmin->getSubject()->shouldBeCalled()->willReturn($parentSubject);
        $parentField = $this->prophesize('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $parentField->getAdmin()->shouldBeCalled()->willReturn($parentAdmin->reveal());

        $modelManager = $this->prophesize('Sonata\AdminBundle\Model\ModelManagerInterface');

        $admin = $this->prophesize('Sonata\AdminBundle\Admin\AbstractAdmin');
        $admin->hasParentFieldDescription()->shouldBeCalled()->willReturn(false);
        $admin->getParentFieldDescription()->shouldBeCalled()->willReturn($parentField->reveal());
        $admin->setSubject(1)->shouldBeCalled();
        $admin->defineFormBuilder(new AnyValueToken())->shouldBeCalled();
        $admin->getModelManager()->shouldBeCalled()->willReturn($modelManager);
        $admin->getClass()->shouldBeCalled()->willReturn('Sonata\AdminBundle\Tests\Fixtures\Entity\Foo');

        $field = $this->prophesize('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $field->getAssociationAdmin()->shouldBeCalled()->willReturn($admin->reveal());

        $this->builder->add('foo.bar');

        try {
            $type = new AdminType();
            $type->buildForm($this->builder, array(
                'sonata_field_description' => $field->reveal(),
                'delete' => false, // not needed
                'property_path' => 'foo', // actual test case
            ));
        } catch (\Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException $exception) {
            $this->fail($exception->getMessage());
        }
    }

    protected function getExtensions()
    {
        $extensions = parent::getExtensions();
        $guesser = $this->prophesize('Symfony\Component\Form\FormTypeGuesserInterface')->reveal();
        $extension = new TestExtension($guesser);

        $extension->addTypeExtension(new FormTypeFieldExtension(array(), array()));
        $extensions[] = $extension;

        return $extensions;
    }
}
