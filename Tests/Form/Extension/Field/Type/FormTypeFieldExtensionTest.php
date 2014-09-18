<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\Extension\Field\Type;

use Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension;
use Symfony\Component\Form\FormConfigBuilder;
use Symfony\Component\Form\Form;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormBuilderInterface;

class FormTypeFieldExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testExtendedType()
    {
        $extension = new FormTypeFieldExtension();

        $this->assertEquals('field', $extension->getExtendedType());
    }

    public function testDefaultOptions()
    {
        $extension = new FormTypeFieldExtension();

        $resolver = new OptionsResolver();
        $extension->setDefaultOptions($resolver);

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

        $formView = new FormView();
        $options = array();
        $config = new FormConfigBuilder('test', 'stdClass', $eventDispatcher, $options);
        $form = new Form($config);

        $extension = new FormTypeFieldExtension();
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
        $admin->expects($this->once())->method('getCode')->will($this->returnValue('my.admin.reference'));

        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $formView = new FormView();
        $options = array();
        $config = new FormConfigBuilder('test', 'stdClass', $eventDispatcher, $options);
        $config->setAttribute('sonata_admin', array(
            'admin' => $admin,
            'name' => 'name'
        ));
        $config->setAttribute('sonata_admin_enabled', true);

        $form = new Form($config);

        $formView->vars['block_prefixes'] = array('form', 'field', 'text', '_s50b26aa76cb96_username');

        $extension = new FormTypeFieldExtension();
        $extension->buildView($formView, $form, array(
            'sonata_help' => 'help text'
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

        $this->assertEquals($expected, $formView->vars['block_prefixes']);
        $this->assertTrue($formView->vars['sonata_admin_enabled']);
        $this->assertEquals('help text', $formView->vars['sonata_help']);
    }
}
