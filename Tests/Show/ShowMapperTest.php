<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Show;

use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;

/**
 * Test for ShowMapper
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ShowMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShowMapper
     */
    private $showMapper;

    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var Sonata\AdminBundle\Builder\ShowBuilderInterface
     */
    private $showBuilder;

    /**
     * @var FieldDescriptionCollection
     */
    private $fieldDescriptionCollection;

    /**
     * @var array
     */
    private $groups;

    public function setUp()
    {
        $this->showBuilder = $this->getMock('Sonata\AdminBundle\Builder\ShowBuilderInterface');
        $this->fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        $this->admin->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue('AdminLabel'));

        $this->groups = array();

        // php 5.3 BC
        $groups = & $this->groups;

        $this->admin->expects($this->any())
            ->method('getShowGroups')
            ->will($this->returnCallback(function() use (&$groups) {
                return $groups;
            }));

        $this->admin->expects($this->any())
            ->method('setShowGroups')
            ->will($this->returnCallback(function($showGroups) use (&$groups) {
                $groups = $showGroups;
            }));

        $this->admin->expects($this->any())
            ->method('reorderShowGroup')
            ->will($this->returnCallback(function($group, $keys) use (&$groups) {
                $showGroups = $groups;
                $showGroups[$group]['fields'] = array_merge(array_flip($keys), $showGroups[$group]['fields']);
                $groups = $showGroups;
            }));

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        // php 5.3 BC
        $fieldDescription = $this->getFieldDescriptionMock();

        $modelManager->expects($this->any())
            ->method('getNewFieldDescriptionInstance')
            ->will($this->returnCallback(function($class, $name, array $options = array()) use ($fieldDescription) {
                $fieldDescriptionClone = clone $fieldDescription;
                $fieldDescriptionClone->setName($name);
                $fieldDescriptionClone->setOptions($options);

                return $fieldDescriptionClone;
            }));

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $labelTranslatorStrategy = new NoopLabelTranslatorStrategy;

        $this->admin->expects($this->any())
            ->method('getLabelTranslatorStrategy')
            ->will($this->returnValue($labelTranslatorStrategy));

        $this->showBuilder->expects($this->any())
            ->method('addField')
            ->will($this->returnCallback(function($list, $type, $fieldDescription, $admin) {
                $list->add($fieldDescription);
            }));

        $this->showMapper = new ShowMapper($this->showBuilder, $this->fieldDescriptionCollection, $this->admin);
    }

    public function testFluidInterface()
    {
        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->assertEquals($this->showMapper, $this->showMapper->add($fieldDescription));
        $this->assertEquals($this->showMapper, $this->showMapper->remove('fooName'));
        $this->assertEquals($this->showMapper, $this->showMapper->reorder(array()));
    }

    public function testGet()
    {
        $this->assertFalse($this->showMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->showMapper->add($fieldDescription);
        $this->assertEquals($fieldDescription, $this->showMapper->get('fooName'));
    }

    public function testAdd()
    {
        $this->showMapper->add('fooName');

        $this->assertTrue($this->showMapper->has('fooName'));

        $fieldDescription = $this->showMapper->get('fooName');

        $this->assertInstanceOf('Sonata\AdminBundle\Admin\FieldDescriptionInterface', $fieldDescription);
        $this->assertEquals('fooName', $fieldDescription->getName());
        $this->assertEquals('fooName', $fieldDescription->getOption('label'));
    }

    public function testAddRemove()
    {
        $this->assertFalse($this->showMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->showMapper->add($fieldDescription);
        $this->assertTrue($this->showMapper->has('fooName'));

        $this->showMapper->remove('fooName');
        $this->assertFalse($this->showMapper->has('fooName'));
    }

    public function testAddException()
    {
        try {
            $this->showMapper->add(12345);
        } catch (\RuntimeException $e) {
            $this->assertContains('invalid state', $e->getMessage());

            return;
        }

        $this->fail('Failed asserting that exception of type "\RuntimeException" is thrown.');
    }

    public function testReorder()
    {
        $this->assertEquals(array(), $this->admin->getShowGroups());

        $fieldDescription1 = $this->getFieldDescriptionMock('fooName1', 'fooLabel1');
        $fieldDescription2 = $this->getFieldDescriptionMock('fooName2', 'fooLabel2');
        $fieldDescription3 = $this->getFieldDescriptionMock('fooName3', 'fooLabel3');
        $fieldDescription4 = $this->getFieldDescriptionMock('fooName4', 'fooLabel4');

        $this->showMapper->with('Group1');
        $this->showMapper->add($fieldDescription1);
        $this->showMapper->add($fieldDescription2);
        $this->showMapper->add($fieldDescription3);
        $this->showMapper->add($fieldDescription4);

        $this->assertEquals(array(
            'Group1' =>array(
                'collapsed' => false,
                'fields' => array('fooName1'=>'fooName1', 'fooName2'=>'fooName2', 'fooName3'=>'fooName3', 'fooName4'=>'fooName4'),
                'description' => false,
                'translation_domain' => null,
       )), $this->admin->getShowGroups());

        $this->showMapper->reorder(array('fooName3', 'fooName2', 'fooName1', 'fooName4'));

        // print_r is used to compare order of items in associative arrays
        $this->assertEquals(print_r(array(
            'Group1' =>array(
                'collapsed' => false,
                'fields' => array('fooName3'=>'fooName3', 'fooName2'=>'fooName2', 'fooName1'=>'fooName1', 'fooName4'=>'fooName4'),
                'description' => false,
                'translation_domain' => null,
       )), true), print_r($this->admin->getShowGroups(), true));
    }

    private function getFieldDescriptionMock($name=null, $label=null)
    {
        $fieldDescription = $this->getMockForAbstractClass('Sonata\AdminBundle\Admin\BaseFieldDescription');

        if ($name !== null) {
            $fieldDescription->setName($name);
        }

        if ($label !== null) {
            $fieldDescription->setOption('label', $label);
        }

        return $fieldDescription;
    }
}
