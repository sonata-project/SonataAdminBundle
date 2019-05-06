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

namespace Sonata\AdminBundle\Tests\Form\Extension\Field\Type;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormTypeFieldExtensionTest extends TestCase
{
    public function testExtendedType(): void
    {
        $extension = new FormTypeFieldExtension([], []);

        $this->assertSame(FormType::class, $extension->getExtendedType());
        $this->assertSame([FormType::class], FormTypeFieldExtension::getExtendedTypes());
    }

    public function testDefaultOptions(): void
    {
        $extension = new FormTypeFieldExtension([], []);

        $resolver = new OptionsResolver();
        $extension->configureOptions($resolver);

        $options = $resolver->resolve();

        $this->assertArrayHasKey('sonata_admin', $options);
        $this->assertArrayHasKey('sonata_field_description', $options);
        $this->assertArrayHasKey('sonata_help', $options);

        $this->assertNull($options['sonata_admin']);
        $this->assertNull($options['sonata_field_description']);
        $this->assertNull($options['sonata_help']);
    }

    public function testbuildViewWithNoSonataAdminArray(): void
    {
        $eventDispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);

        $parentFormView = new FormView();
        $parentFormView->vars['sonata_admin_enabled'] = false;

        $formView = new FormView();
        $formView->parent = $parentFormView;

        $options = [];
        $config = new FormConfigBuilder('test', 'stdClass', $eventDispatcher, $options);
        $form = new Form($config);

        $extension = new FormTypeFieldExtension([], []);
        $extension->buildView($formView, $form, []);

        $this->assertArrayHasKey('sonata_admin', $formView->vars);
        $this->assertNull($formView->vars['sonata_admin']);
    }

    //    public function testBuildForm()
    //    {
    //        $admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
    //        $admin->expects($this->once())->method('getCode')->will($this->returnValue('admin_code'));
    //
    //        $fieldDescription = $this->createMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
    //        $fieldDescription->expects($this->once())->method('getAdmin')->will($this->returnValue($admin));
    //        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('name'));
    //
    //        $formBuilder = $this->createMock('Symfony\Component\Form\FormBuilderInterface');
    //
    //        $extension = new FormTypeFieldExtension();
    //        $extension->buildForm($formBuilder, []);
    //    }

    public function testbuildViewWithWithSonataAdmin(): void
    {
        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->expects($this->exactly(2))->method('getCode')->willReturn('my.admin.reference');

        $eventDispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);

        $formView = new FormView();
        $options = [];
        $config = new FormConfigBuilder('test', 'stdClass', $eventDispatcher, $options);
        $config->setAttribute('sonata_admin', [
            'admin' => $admin,
            'name' => 'name',
        ]);
        $config->setAttribute('sonata_admin_enabled', true);

        $form = new Form($config);

        $formView->parent = new FormView();
        $formView->parent->vars['sonata_admin_enabled'] = false;

        $formView->vars['block_prefixes'] = ['form', 'field', 'text', '_s50b26aa76cb96_username'];

        $extension = new FormTypeFieldExtension([], []);
        $extension->buildView($formView, $form, [
            'sonata_help' => 'help text',
        ]);

        $this->assertArrayHasKey('block_prefixes', $formView->vars);
        $this->assertArrayHasKey('sonata_admin_enabled', $formView->vars);
        $this->assertArrayHasKey('sonata_admin', $formView->vars);

        $expected = [
            'form',
            'field',
            'text',
            'my_admin_reference_text',
            'my_admin_reference_name_text',
            'my_admin_reference_name_text_username',
        ];

        $this->assertSame($expected, $formView->vars['block_prefixes']);
        $this->assertTrue($formView->vars['sonata_admin_enabled']);
        $this->assertSame('help text', $formView->vars['sonata_help']);
    }

    public function testbuildViewWithNestedForm(): void
    {
        $eventDispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);

        $formView = new FormView();
        $formView->vars['name'] = 'format';

        $options = [];
        $config = new FormConfigBuilder('test', 'stdClass', $eventDispatcher, $options);
        $config->setAttribute('sonata_admin', ['admin' => false]);

        $form = new Form($config);

        $formView->parent = new FormView();
        $formView->parent->vars['sonata_admin_enabled'] = true;
        $formView->parent->vars['sonata_admin_code'] = 'parent_code';
        $formView->parent->vars['name'] = 'settings';

        $formView->vars['block_prefixes'] = ['form', 'field', 'text', '_s50b26aa76cb96_settings_format'];

        $extension = new FormTypeFieldExtension([], []);
        $extension->buildView($formView, $form, [
            'sonata_help' => 'help text',
        ]);

        $this->assertArrayHasKey('block_prefixes', $formView->vars);
        $this->assertArrayHasKey('sonata_admin_enabled', $formView->vars);
        $this->assertArrayHasKey('sonata_admin', $formView->vars);

        $expected = [
            'value' => null,
            'attr' => [],
            'name' => 'format',
            'block_prefixes' => [
                'form',
                'field',
                'text',
                'parent_code_text',
                'parent_code_text_settings_format',
                'parent_code_text_settings_settings_format',
            ],
            'sonata_admin_enabled' => true,
            'sonata_admin' => [
                 'admin' => false,
                 'field_description' => false,
                 'name' => false,
                 'edit' => 'standard',
                 'inline' => 'natural',
                 'block_name' => false,
                 'class' => false,
                 'options' => [],
            ],
            'sonata_help' => 'help text',
            'sonata_admin_code' => 'parent_code',
        ];

        $this->assertSame($expected, $formView->vars);
    }

    public function testbuildViewWithNestedFormWithNoParent(): void
    {
        $eventDispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);

        $formView = new FormView();
        $options = [];
        $config = new FormConfigBuilder('test', 'stdClass', $eventDispatcher, $options);
        $form = new Form($config);

        $extension = new FormTypeFieldExtension([], []);
        $extension->buildView($formView, $form, [
            'sonata_help' => 'help text',
        ]);

        $this->assertArrayNotHasKey('block_prefixes', $formView->vars);
        $this->assertArrayHasKey('sonata_admin_enabled', $formView->vars);
        $this->assertArrayHasKey('sonata_admin', $formView->vars);
    }
}
