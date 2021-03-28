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
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormConfigBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FormTypeFieldExtensionTest extends TestCase
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

        $this->assertNull($options['sonata_admin']);
        $this->assertNull($options['sonata_field_description']);
    }

    public function testbuildViewWithNoSonataAdminArray(): void
    {
        $eventDispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);

        $parentFormView = new FormView();
        $parentFormView->vars['sonata_admin_enabled'] = false;

        $formView = new FormView();
        $formView->parent = $parentFormView;

        $options = [];
        $config = new FormConfigBuilder('test', \stdClass::class, $eventDispatcher, $options);
        $form = new Form($config);

        $extension = new FormTypeFieldExtension([], []);
        $extension->buildView($formView, $form, []);

        $this->assertArrayHasKey('sonata_admin', $formView->vars);
        $this->assertNull($formView->vars['sonata_admin']);
    }

    public function testBuildFormWithFieldDescription(): void
    {
        $admin = $this->createStub(AdminInterface::class);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);
        $fieldDescription
            ->method('getAdmin')
            ->willReturn($admin);
        $fieldDescription
            ->method('getName')
            ->willReturn('name');
        $fieldDescription
            ->method('getOption')
            ->willReturnCallback(static function (string $option, $value) {
                return $value;
            });

        $formBuilder = new FormBuilder(
            'test',
            \stdClass::class,
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(FormFactoryInterface::class)
        );

        $extension = new FormTypeFieldExtension([], []);
        $extension->buildForm($formBuilder, [
            'sonata_field_description' => $fieldDescription,
        ]);

        $this->assertTrue($formBuilder->getAttribute('sonata_admin_enabled'));
        $this->assertSame([
            'name' => 'name',
            'admin' => $admin,
            'value' => null,
            'edit' => 'standard',
            'inline' => 'natural',
            'field_description' => $fieldDescription,
            'block_name' => false,
            'options' => [],
            'class' => '',
        ], $formBuilder->getAttribute('sonata_admin'));
    }

    public function testbuildViewWithWithSonataAdmin(): void
    {
        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->expects($this->exactly(2))->method('getCode')->willReturn('my.admin.reference');

        $eventDispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);

        $formView = new FormView();
        $options = [];
        $config = new FormConfigBuilder('test', \stdClass::class, $eventDispatcher, $options);
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
        $extension->buildView($formView, $form, []);

        $this->assertArrayHasKey('block_prefixes', $formView->vars);
        $this->assertArrayHasKey('sonata_admin_enabled', $formView->vars);
        $this->assertArrayHasKey('sonata_admin', $formView->vars);

        $expected = [
            'form',
            'field',
            'text',
            '_s50b26aa76cb96_username',
            'my_admin_reference_text',
            'my_admin_reference_name_text',
            'my_admin_reference_name_text_username',
        ];

        $this->assertSame($expected, $formView->vars['block_prefixes']);
        $this->assertTrue($formView->vars['sonata_admin_enabled']);
    }

    public function testbuildViewWithNestedForm(): void
    {
        $eventDispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);

        $formView = new FormView();
        $formView->vars['name'] = 'format';

        $options = [];
        $config = new FormConfigBuilder('test', \stdClass::class, $eventDispatcher, $options);
        $config->setAttribute('sonata_admin', ['admin' => false]);

        $form = new Form($config);

        $formView->parent = new FormView();
        $formView->parent->vars['sonata_admin_enabled'] = true;
        $formView->parent->vars['sonata_admin_code'] = 'parent_code';
        $formView->parent->vars['name'] = 'settings';

        $formView->vars['block_prefixes'] = ['form', 'field', 'text', '_s50b26aa76cb96_settings_format'];

        $extension = new FormTypeFieldExtension([], []);
        $extension->buildView($formView, $form, []);

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
                '_s50b26aa76cb96_settings_format',
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
            // NEXT_MAJOR: Remove this line
            'sonata_help' => null,
            'sonata_admin_code' => 'parent_code',
        ];

        $this->assertSame($expected, $formView->vars);
    }

    public function testbuildViewWithNestedFormWithNoParent(): void
    {
        $eventDispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);

        $formView = new FormView();
        $options = [];
        $config = new FormConfigBuilder('test', \stdClass::class, $eventDispatcher, $options);
        $form = new Form($config);

        $extension = new FormTypeFieldExtension([], []);
        $extension->buildView($formView, $form, []);

        $this->assertArrayNotHasKey('block_prefixes', $formView->vars);
        $this->assertArrayHasKey('sonata_admin_enabled', $formView->vars);
        $this->assertArrayHasKey('sonata_admin', $formView->vars);
    }

    public function testbuildViewCollectionField(): void
    {
        $eventDispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);

        $formView = new FormView();
        $formView->vars['name'] = 'field';
        $formView->vars['attr'] = ['hidden' => true];
        $formView->vars['block_prefixes'] = [
            'form',
            'field',
            'checkbox',
            '_parent_collection_entry_field',
        ];
        $formView->vars['unique_block_prefix'] = '_parent_collection_entry_field';
        $formView->vars['sonata_admin_enabled'] = true;
        $formView->vars['sonata_admin_code'] = 'admin.parent';

        $formView->parent = new FormView();
        $formView->parent->vars['name'] = '0';
        $formView->parent->vars['block_prefixes'] = [
            'form',
            'parent_specification',
            '_parent_collection_entry',
            'admin_parent_parent_field',
            'admin_parent_parent_field_collection_0',
            'admin_parent_parent_field_collection__parent_collection_entry',
        ];
        $formView->parent->vars['unique_block_prefix'] = '_parent_collection_entry';
        $formView->parent->vars['sonata_admin_enabled'] = true;
        $formView->parent->vars['sonata_admin_code'] = 'admin.parent';

        $formView->parent->parent = new FormView();
        $formView->parent->parent->vars['name'] = 'collection';
        $formView->parent->parent->vars['block_prefixes'] = [
            'form',
            'collection',
            'sonata_type_native_collection',
            '_parent_collection',
            'admin_parent_sonata_type_native_collection',
            'admin_parent_collection_sonata_type_native_collection',
            'admin_parent_collection_sonata_type_native_collection__parent_collection',
            'field_collection',
        ];
        $formView->parent->parent->vars['unique_block_prefix'] = '_parent_collection';
        $formView->parent->parent->vars['sonata_admin_enabled'] = false;
        $formView->parent->parent->vars['sonata_admin_code'] = 'admin.parent';

        $formView->parent->parent->parent = new FormView();
        $formView->parent->parent->parent->vars['name'] = 'parent';
        $formView->parent->parent->parent->vars['block_prefixes'] = [
            'form',
            '_parent',
        ];
        $formView->parent->parent->parent->vars['unique_block_prefix'] = '_parent';
        $formView->parent->parent->parent->vars['sonata_admin_enabled'] = false;

        $options = [];
        $config = new FormConfigBuilder('test', \stdClass::class, $eventDispatcher, $options);
        $config->setAttribute('sonata_admin', ['admin' => false]);

        $form = new Form($config);

        $extension = new FormTypeFieldExtension([], []);
        $extension->buildView($formView, $form, []);

        $expected = [
            'form',
            'field',
            'checkbox',
            '_parent_collection_entry_field',
            'admin_parent_checkbox',
            'admin_parent_checkbox_0_field',
            'admin_parent_checkbox_0__parent_collection_entry_field',
        ];

        $this->assertSame($expected, $formView->vars['block_prefixes']);
    }

    /**
     * @group legacy
     */
    public function testSonataHelp(): void
    {
        $extension = new FormTypeFieldExtension([], []);
        $optionResolver = new OptionsResolver();
        $extension->configureOptions($optionResolver);

        $defaultOptions = $optionResolver->resolve();

        $this->assertArrayHasKey('sonata_help', $defaultOptions);
        $this->assertNull($defaultOptions['sonata_help']);

        $optionsWithSonataHelp = $optionResolver->resolve(['sonata_help' => 'Sonata help message']);

        $this->assertSame('Sonata help message', $optionsWithSonataHelp['sonata_help']);
    }
}
