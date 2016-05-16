<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CleanAdmin;
use Symfony\Component\Form\FormBuilder;

class FormMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Sonata\AdminBundle\Builder\FormContractorInterface
     */
    protected $contractor;

    /**
     * @var \Sonata\AdminBundle\Admin\AdminInterface
     */
    protected $admin;

    /**
     * @var \Sonata\AdminBundle\Model\ModelManagerInterface
     */
    protected $modelManager;

    /**
     * @var FormMapper
     */
    protected $formMapper;

    public function setUp()
    {
        $this->contractor = $this->getMock('Sonata\AdminBundle\Builder\FormContractorInterface');

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $formBuilder = new FormBuilder('test', 'stdClass', $eventDispatcher, $formFactory);

        $this->admin = new CleanAdmin('code', 'class', 'controller');

        $this->modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        // php 5.3 BC
        $fieldDescription = $this->getFieldDescriptionMock();

        $this->modelManager->expects($this->any())
            ->method('getNewFieldDescriptionInstance')
            ->will($this->returnCallback(function ($class, $name, array $options = array()) use ($fieldDescription) {
                $fieldDescriptionClone = clone $fieldDescription;
                $fieldDescriptionClone->setName($name);
                $fieldDescriptionClone->setOptions($options);

                return $fieldDescriptionClone;
            }));

        $this->admin->setModelManager($this->modelManager);

        $labelTranslatorStrategy = $this->getMock('Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface');
        $this->admin->setLabelTranslatorStrategy($labelTranslatorStrategy);

        $this->formMapper = new FormMapper(
            $this->contractor,
            $formBuilder,
            $this->admin
        );
    }

    public function testWithNoOptions()
    {
        $this->formMapper->with('foobar');

        $this->assertSame(array('default' => array(
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'translation_domain' => null,
            'name' => 'default',
            'box_class' => 'box box-primary',
            'auto_created' => true,
            'groups' => array('foobar'),
            'tab' => true,
        )), $this->admin->getFormTabs());

        $this->assertSame(array('foobar' => array(
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'translation_domain' => null,
            'name' => 'foobar',
            'box_class' => 'box box-primary',
            'fields' => array(),
        )), $this->admin->getFormGroups());
    }

    public function testWithOptions()
    {
        $this->formMapper->with('foobar', array(
            'translation_domain' => 'Foobar',
        ));

        $this->assertSame(array('foobar' => array(
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'translation_domain' => 'Foobar',
            'name' => 'foobar',
            'box_class' => 'box box-primary',
            'fields' => array(),
        )), $this->admin->getFormGroups());

        $this->assertSame(array('default' => array(
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'translation_domain' => 'Foobar',
            'name' => 'default',
            'box_class' => 'box box-primary',
            'auto_created' => true,
            'groups' => array('foobar'),
            'tab' => true,
        )), $this->admin->getFormTabs());
    }

    public function testWithFieldsCascadeTranslationDomain()
    {
        $this->contractor->expects($this->once())
            ->method('getDefaultOptions')
            ->will($this->returnValue(array()));

        $this->formMapper->with('foobar', array(
                'translation_domain' => 'Foobar',
            ))
            ->add('foo', 'bar')
        ->end();

        $fieldDescription = $this->admin->getFormFieldDescription('foo');
        $this->assertSame('foo', $fieldDescription->getName());
        $this->assertSame('bar', $fieldDescription->getType());
        $this->assertSame('Foobar', $fieldDescription->getTranslationDomain());

        $this->assertTrue($this->formMapper->has('foo'));

        $this->assertSame(array('default' => array(
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'translation_domain' => 'Foobar',
            'name' => 'default',
            'box_class' => 'box box-primary',
            'auto_created' => true,
            'groups' => array('foobar'),
            'tab' => true,
        )), $this->admin->getFormTabs());

        $this->assertSame(array('foobar' => array(
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'translation_domain' => 'Foobar',
            'name' => 'foobar',
            'box_class' => 'box box-primary',
            'fields' => array(
                'foo' => 'foo',
            ),
        )), $this->admin->getFormGroups());
    }

    public function testRemoveCascadeRemoveFieldFromFormGroup()
    {
        $this->formMapper->with('foo');
        $this->formMapper->remove('foo');
    }

    public function testIfTrueApply()
    {
        $this->contractor->expects($this->once())
            ->method('getDefaultOptions')
            ->will($this->returnValue(array()));

        $this->formMapper
            ->ifTrue(true)
            ->add('foo', 'bar')
            ->ifEnd()
        ;

        $this->assertTrue($this->formMapper->has('foo'));
    }

    public function testIfTrueNotApply()
    {
        $this->formMapper
            ->ifTrue(false)
            ->add('foo', 'bar')
            ->ifEnd()
        ;

        $this->assertFalse($this->formMapper->has('foo'));
    }

    public function testIfTrueCombination()
    {
        $this->contractor->expects($this->once())
            ->method('getDefaultOptions')
            ->will($this->returnValue(array()));

        $this->formMapper
            ->ifTrue(false)
            ->add('foo', 'bar')
            ->ifEnd()
            ->add('baz', 'foobaz')
        ;

        $this->assertFalse($this->formMapper->has('foo'));
        $this->assertTrue($this->formMapper->has('baz'));
    }

    public function testIfFalseApply()
    {
        $this->contractor->expects($this->once())
            ->method('getDefaultOptions')
            ->will($this->returnValue(array()));

        $this->formMapper
            ->ifFalse(false)
            ->add('foo', 'bar')
            ->ifEnd()
        ;

        $this->assertTrue($this->formMapper->has('foo'));
    }

    public function testIfFalseNotApply()
    {
        $this->formMapper
            ->ifFalse(true)
            ->add('foo', 'bar')
            ->ifEnd()
        ;

        $this->assertFalse($this->formMapper->has('foo'));
    }

    public function testIfFalseCombination()
    {
        $this->contractor->expects($this->once())
            ->method('getDefaultOptions')
            ->will($this->returnValue(array()));

        $this->formMapper
            ->ifFalse(true)
            ->add('foo', 'bar')
            ->ifEnd()
            ->add('baz', 'foobaz')
        ;

        $this->assertFalse($this->formMapper->has('foo'));
        $this->assertTrue($this->formMapper->has('baz'));
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Cannot nest ifTrue or ifFalse call
     */
    public function testIfTrueNested()
    {
        $this->formMapper->ifTrue(true);
        $this->formMapper->ifTrue(true);
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Cannot nest ifTrue or ifFalse call
     */
    public function testIfFalseNested()
    {
        $this->formMapper->ifFalse(false);
        $this->formMapper->ifFalse(false);
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Cannot nest ifTrue or ifFalse call
     */
    public function testIfCombinationNested()
    {
        $this->formMapper->ifTrue(true);
        $this->formMapper->ifFalse(false);
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Cannot nest ifTrue or ifFalse call
     */
    public function testIfFalseCombinationNested2()
    {
        $this->formMapper->ifFalse(false);
        $this->formMapper->ifTrue(true);
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Cannot nest ifTrue or ifFalse call
     */
    public function testIfFalseCombinationNested3()
    {
        $this->formMapper->ifFalse(true);
        $this->formMapper->ifTrue(false);
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Cannot nest ifTrue or ifFalse call
     */
    public function testIfFalseCombinationNested4()
    {
        $this->formMapper->ifTrue(false);
        $this->formMapper->ifFalse(true);
    }

    public function testAddAcceptFormBuilder()
    {
        $formBuilder = $this
            ->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $formBuilder->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $this->formMapper->add($formBuilder);
        $this->assertSame($this->formMapper->get('foo'), $formBuilder);
    }

    public function testGroupRemovingWithoutTab()
    {
        $this->formMapper->with('foobar');

        $this->formMapper->removeGroup('foobar');

        $this->assertSame(array(), $this->admin->getFormGroups());
    }

    public function testGroupRemovingWithTab()
    {
        $this->formMapper->tab('mytab')->with('foobar');

        $this->formMapper->removeGroup('foobar', 'mytab');

        $this->assertSame(array(), $this->admin->getFormGroups());
    }

    public function testGroupRemovingWithoutTabAndWithTabRemoving()
    {
        $this->formMapper->with('foobar');

        $this->formMapper->removeGroup('foobar', 'default', true);

        $this->assertSame(array(), $this->admin->getFormGroups());
        $this->assertSame(array(), $this->admin->getFormTabs());
    }

    public function testGroupRemovingWithTabAndWithTabRemoving()
    {
        $this->formMapper->tab('mytab')->with('foobar');

        $this->formMapper->removeGroup('foobar', 'mytab', true);

        $this->assertSame(array(), $this->admin->getFormGroups());
        $this->assertSame(array(), $this->admin->getFormTabs());
    }

    public function testKeys()
    {
        $this->contractor->expects($this->any())
            ->method('getDefaultOptions')
            ->will($this->returnValue(array()));

        $this->formMapper
            ->add('foo', 'bar')
            ->add('baz', 'foobaz')
        ;

        $this->assertSame(array('foo', 'baz'), $this->formMapper->keys());
    }

    private function getFieldDescriptionMock($name = null, $label = null, $translationDomain = null)
    {
        $fieldDescription = $this->getMockForAbstractClass('Sonata\AdminBundle\Admin\BaseFieldDescription');

        if ($name !== null) {
            $fieldDescription->setName($name);
        }

        if ($label !== null) {
            $fieldDescription->setOption('label', $label);
        }

        if ($translationDomain !== null) {
            $fieldDescription->setOption('translation_domain', $translationDomain);
        }

        return $fieldDescription;
    }
}
