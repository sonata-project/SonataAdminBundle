<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;

class TestAdmin extends Admin
{

}

class FormMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Sonata\AdminBundle\Builder\FormContractorInterface
     */
    protected $contractor;

    /**
     * @var \Symfony\Component\Form\FormBuilder
     */
    protected $builder;

    /**
     * @var \Sonata\AdminBundle\Admin\AdminInterface
     */
    protected $admin;

    /**
     * @var \Sonata\AdminBundle\Model\ModelManagerInterface
     */
    protected $modelManager;

    /**
     * @var \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    protected $fieldDescription;

    /**
     * @var \Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface
     */
    protected $labelTranslatorStrategy;

    /**
     * @var FormMapper
     */
    protected $formMapper;

    public function setUp()
    {
        $this->contractor = $this->getMock('Sonata\AdminBundle\Builder\FormContractorInterface');
        $this->builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->admin = new TestAdmin('code', 'class', 'controller');

        $this->modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $this->fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $this->labelTranslatorStrategy = $this->getMock('Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface');

        $this->formMapper = new FormMapper(
            $this->contractor,
            $this->builder,
            $this->admin
        );
    }

    public function testWithNoOptions()
    {
        $this->formMapper->with('foobar');

        $this->assertEquals(array('default' => array (
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'translation_domain' => null,
            'auto_created' => true,
            'groups' => array('foobar'),
            'tab' => true,
            'name' => 'default'
        )), $this->admin->getFormTabs());

        $this->assertEquals(array('foobar' => array(
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'translation_domain' => null,
            'fields' => array (),
            'name' => 'foobar'
        )), $this->admin->getFormGroups());
    }

    public function testWithOptions()
    {
        $this->formMapper->with('foobar', array(
            'translation_domain' => 'Foobar',
        ));

        $this->assertEquals(array('foobar' => array(
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'translation_domain' => 'Foobar',
            'fields' => array (),
            'name' => 'foobar'
        )), $this->admin->getFormGroups());

        $this->assertEquals(array('default' => array (
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'translation_domain' => 'Foobar',
            'auto_created' => true,
            'groups' => array('foobar'),
            'tab' => true,
            'name' => 'default'
        )), $this->admin->getFormTabs());
    }

    public function testWithFieldsCascadeTranslationDomain()
    {
        $formGroups = array(
            'foobar' => array(
                'collapsed' => false,
                'fields' => array(),
                'description' => false,
                'translation_domain' => 'Foobar',
                'class' => false,
                'name' => 'foobar'
            )
        );

        $this->admin->setModelManager($this->modelManager);

        $this->modelManager->expects($this->once())
            ->method('getNewFieldDescriptionInstance')
            ->with(
                'class',
                'foo',
                array(
                    'translation_domain' => 'Foobar',
                    'type' => 'bar',
                )
            )
            ->will($this->returnValue($this->fieldDescription));

        $this->contractor->expects($this->once())
            ->method('getDefaultOptions')
            ->will($this->returnValue(array()));

        $this->admin->setLabelTranslatorStrategy($this->labelTranslatorStrategy);

        $this->formMapper->with('foobar', array(
                'translation_domain' => 'Foobar'
            ))
            ->add('foo', 'bar')
        ->end();

        $this->assertEquals(array('default' => array (
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'translation_domain' => 'Foobar',
            'auto_created' => true,
            'groups' => array ('foobar'),
            'tab' => true,
            'name' => 'default'
        )), $this->admin->getFormTabs());

        $this->assertEquals(array('foobar' => array(
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'translation_domain' => 'Foobar',
            'fields' => array(
                'foo' => 'foo'
            ),
            'name' => 'foobar'
        )), $this->admin->getFormGroups());
    }

    public function testRemoveCascadeRemoveFieldFromFormGroup()
    {
        $this->formMapper->with('foo');
        $this->formMapper->remove('foo');
    }
}
