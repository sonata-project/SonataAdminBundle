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

use Sonata\AdminBundle\Form\FormMapper;

class FormMapperTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->contractor = $this->getMock('Sonata\AdminBundle\Builder\FormContractorInterface');
        $this->builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
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
        $this->admin->expects($this->once())
            ->method('setFormGroups')
            ->with(array(
                'foobar' => array(
                    'collapsed' => false,
                    'fields' => array(),
                    'description' => false,
                    'translation_domain' => null,
                )));

        $this->formMapper->with('foobar');
    }

    public function testWithOptions()
    {
        $this->admin->expects($this->once())
            ->method('setFormGroups')
            ->with(array(
                'foobar' => array(
                    'collapsed' => false,
                    'fields' => array(),
                    'description' => false,
                    'translation_domain' => 'Foobar',
                )));

        $this->formMapper->with('foobar', array(
            'translation_domain' => 'Foobar',
        ));
    }

    public function testWithFieldsCascadeTranslationDomain()
    {
        $formGroups = array(
            'foobar' => array(
                'collapsed' => false,
                'fields' => array(),
                'description' => false,
                'translation_domain' => 'Foobar',
            )
        );

        $this->admin->expects($this->exactly(2))
            ->method('setFormGroups');

        $this->admin->expects($this->any())
            ->method('getFormGroups')
            ->will($this->returnValue($formGroups));

        $this->admin->expects($this->once())
            ->method('getModelManager')
            ->will($this->returnValue($this->modelManager));

        $this->modelManager->expects($this->once())
            ->method('getNewFieldDescriptionInstance')
            ->with(
                null, // mock admin ->getClass returns null
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

        $this->admin->expects($this->once())
            ->method('getLabelTranslatorStrategy')
            ->will($this->returnValue($this->labelTranslatorStrategy));

        $this->formMapper->with('foobar', array(
            'translation_domain' => 'Foobar'
        ))
        ->add('foo', 'bar')
        ->end();
    }
}
