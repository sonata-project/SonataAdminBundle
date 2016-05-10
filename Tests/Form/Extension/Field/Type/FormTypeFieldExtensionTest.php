<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\Extension\Field\Type;

use Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormTypeFieldExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testExtendedType()
    {
        $extension = new FormTypeFieldExtension(array(), array());

        $this->assertSame(
            method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix') ?
            'Symfony\Component\Form\Extension\Core\Type\FormType' :
            'form',
            $extension->getExtendedType()
        );
    }

    public function testDefaultOptions()
    {
        $extension = new FormTypeFieldExtension(array(), array());

        $resolver = new OptionsResolver();
        if (!method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $extension->setDefaultOptions($resolver);
        } else {
            $extension->configureOptions($resolver);
        }

        $options = $resolver->resolve();

        $this->assertArrayHasKey('sonata_admin', $options);
        $this->assertArrayHasKey('sonata_field_description', $options);
        $this->assertArrayHasKey('sonata_help', $options);

        $this->assertNull($options['sonata_admin']);
        $this->assertNull($options['sonata_field_description']);
        $this->assertNull($options['sonata_help']);
    }

    public function testbuildViewWithNoSonataAdminArray()
    {
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $parentFormView = new FormView();
        $parentFormView->vars['sonata_admin_enabled'] = false;

        $formView = new FormView();
        $formView->parent = $parentFormView;

        $options = array();
        $config = new FormConfigBuilder('test', 'stdClass', $eventDispatcher, $options);
        $form = new Form($config);

        $extension = new FormTypeFieldExtension(array(), array());
        $extension->buildView($formView, $form, array());

        $this->assertArrayHasKey('sonata_admin', $formView->vars);
        $this->assertNull($formView->vars['sonata_admin']);
    }

//    public function testBuildForm()
//    {
//        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
//        $admin->expects($this->once())->method('getCode')->will($this->returnValue('admin_code'));
//
//        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
//        $fieldDescription->expects($this->once())->method('getAdmin')->will($this->returnValue($admin));
//        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('name'));
//
//        $formBuilder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');
//
//        $extension = new FormTypeFieldExtension();
//        $extension->buildForm($formBuilder, array());
//    }

    public function testbuildViewWithWithSonataAdmin()
    {
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->exactly(2))->method('getCode')->will($this->returnValue('my.admin.reference'));

        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $formView = new FormView();
        $options = array();
        $config = new FormConfigBuilder('test', 'stdClass', $eventDispatcher, $options);
        $config->setAttribute('sonata_admin', array(
            'admin' => $admin,
            'name' => 'name',
        ));
        $config->setAttribute('sonata_admin_enabled', true);

        $form = new Form($config);

        $formView->parent = new FormView();
        $formView->parent->vars['sonata_admin_enabled'] = false;

        $formView->vars['block_prefixes'] = array('form', 'field', 'text', '_s50b26aa76cb96_username');

        $extension = new FormTypeFieldExtension(array(), array());
        $extension->buildView($formView, $form, array(
            'sonata_help' => 'help text',
        ));

        $this->assertArrayHasKey('block_prefixes', $formView->vars);
        $this->assertArrayHasKey('sonata_admin_enabled', $formView->vars);
        $this->assertArrayHasKey('sonata_admin', $formView->vars);

        $expected = array(
            'form',
            'field',
            'text',
            'my_admin_reference_text',
            'my_admin_reference_name_text',
            'my_admin_reference_name_text_username',
        );

        $this->assertSame($expected, $formView->vars['block_prefixes']);
        $this->assertTrue($formView->vars['sonata_admin_enabled']);
        $this->assertSame('help text', $formView->vars['sonata_help']);
    }

    public function testbuildViewWithNestedForm()
    {
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $formView = new FormView();
        $formView->vars['name'] = 'format';

        $options = array();
        $config = new FormConfigBuilder('test', 'stdClass', $eventDispatcher, $options);
        $config->setAttribute('sonata_admin', array('admin' => false));

        $form = new Form($config);

        $formView->parent = new FormView();
        $formView->parent->vars['sonata_admin_enabled'] = true;
        $formView->parent->vars['sonata_admin_code'] = 'parent_code';
        $formView->parent->vars['name'] = 'settings';

        $formView->vars['block_prefixes'] = array('form', 'field', 'text', '_s50b26aa76cb96_settings_format');

        $extension = new FormTypeFieldExtension(array(), array());
        $extension->buildView($formView, $form, array(
            'sonata_help' => 'help text',
        ));

        $this->assertArrayHasKey('block_prefixes', $formView->vars);
        $this->assertArrayHasKey('sonata_admin_enabled', $formView->vars);
        $this->assertArrayHasKey('sonata_admin', $formView->vars);

        $expected = array(
            'value' => null,
            'attr' => array(),
            'name' => 'format',
            'block_prefixes' => array(
                'form',
                'field',
                'text',
                'parent_code_text',
                'parent_code_text_settings_format',
                'parent_code_text_settings_settings_format',
            ),
            'sonata_admin_enabled' => true,
            'sonata_admin' => array(
                 'admin' => false,
                 'field_description' => false,
                 'name' => false,
                 'edit' => 'standard',
                 'inline' => 'natural',
                 'block_name' => false,
                 'class' => false,
                 'options' => array(),
            ),
            'sonata_admin_code' => 'parent_code',
        );

        $this->assertSame($expected, $formView->vars);
    }

    public function testbuildViewWithNestedFormWithNoParent()
    {
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $formView = new FormView();
        $options = array();
        $config = new FormConfigBuilder('test', 'stdClass', $eventDispatcher, $options);
        $form = new Form($config);

        $extension = new FormTypeFieldExtension(array(), array());
        $extension->buildView($formView, $form, array(
            'sonata_help' => 'help text',
        ));

        $this->assertArrayNotHasKey('block_prefixes', $formView->vars);
        $this->assertArrayHasKey('sonata_admin_enabled', $formView->vars);
        $this->assertArrayHasKey('sonata_admin', $formView->vars);
    }
}
