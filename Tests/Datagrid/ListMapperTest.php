<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sonata\AdminBundle\Tests\Datagrid;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ListMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ListMapper
     */
    private $listMapper;

    /**
     * @var FieldDescriptionCollection
     */
    private $fieldDescriptionCollection;

    public function setUp()
    {
        $listBuilder = $this->getMock('Sonata\AdminBundle\Builder\ListBuilderInterface');
        $this->fieldDescriptionCollection = new FieldDescriptionCollection();
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        $listBuilder->expects($this->any())
            ->method('addField')
            ->will($this->returnCallback(function($list, $type, $fieldDescription, $admin) {
                $list->add($fieldDescription);
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

        $admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $labelTranslatorStrategy = new NoopLabelTranslatorStrategy;

        $admin->expects($this->any())
            ->method('getLabelTranslatorStrategy')
            ->will($this->returnValue($labelTranslatorStrategy));

        $this->listMapper = new ListMapper($listBuilder, $this->fieldDescriptionCollection, $admin);
    }

    public function testFluidInterface()
    {
        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->assertEquals($this->listMapper, $this->listMapper->add($fieldDescription));
        $this->assertEquals($this->listMapper, $this->listMapper->remove('fooName'));
        $this->assertEquals($this->listMapper, $this->listMapper->reorder(array()));
    }

    public function testGet()
    {
        $this->assertFalse($this->listMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->listMapper->add($fieldDescription);
        $this->assertEquals($fieldDescription, $this->listMapper->get('fooName'));
    }

    public function testAddIdentifier()
    {
        $this->assertFalse($this->listMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->listMapper->addIdentifier($fieldDescription);
        $this->assertTrue($this->listMapper->has('fooName'));
    }

    public function testAdd()
    {
        $this->listMapper->add('fooName');

        $this->assertTrue($this->listMapper->has('fooName'));

        $fieldDescription = $this->listMapper->get('fooName');

        $this->assertInstanceOf('Sonata\AdminBundle\Admin\FieldDescriptionInterface', $fieldDescription);
        $this->assertEquals('fooName', $fieldDescription->getName());
        $this->assertEquals('fooName', $fieldDescription->getOption('label'));
    }

    public function testAddViewInlineActionException()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Inline action "view" is deprecated since version 2.2.4. Use inline action "show" instead.');

        $this->assertFalse($this->listMapper->has('_action'));
        $this->listMapper->add('_action', 'actions', array('actions'=>array('view'=>array())));
    }

    public function testAddViewInlineAction()
    {
        // ignore E_USER_DEPRECATED error
        $previousErrorHandler = set_error_handler( function() {}, E_USER_DEPRECATED);

        $this->assertFalse($this->listMapper->has('_action'));
        $this->listMapper->add('_action', 'actions', array('actions'=>array('view'=>array())));

        $this->assertTrue($this->listMapper->has('_action'));

        $fieldDescription = $this->listMapper->get('_action');

        $this->assertInstanceOf('Sonata\AdminBundle\Admin\FieldDescriptionInterface', $fieldDescription);
        $this->assertEquals('_action', $fieldDescription->getName());
        $this->assertCount(1, $fieldDescription->getOption('actions'));
        $this->assertEquals(array('show'=>array()), $fieldDescription->getOption('actions'));

        set_error_handler($previousErrorHandler);
    }

    public function testAddRemove()
    {
        $this->assertFalse($this->listMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->listMapper->add($fieldDescription);
        $this->assertTrue($this->listMapper->has('fooName'));

        $this->listMapper->remove('fooName');
        $this->assertFalse($this->listMapper->has('fooName'));
    }

    public function testAddException()
    {
        try {
            $this->listMapper->add(12345);
        } catch (\RuntimeException $e) {
            $this->assertContains('Unknown or duplicate field name in list mapper. Field name should be either of FieldDescriptionInterface interface or string. Names should be unique.', $e->getMessage());

            return;
        }

        $this->fail('Failed asserting that exception of type "\RuntimeException" is thrown.');
    }

    public function testReorder()
    {
        $fieldDescription1 = $this->getFieldDescriptionMock('fooName1', 'fooLabel1');
        $fieldDescription2 = $this->getFieldDescriptionMock('fooName2', 'fooLabel2');
        $fieldDescription3 = $this->getFieldDescriptionMock('fooName3', 'fooLabel3');
        $fieldDescription4 = $this->getFieldDescriptionMock('fooName4', 'fooLabel4');

        $this->listMapper->add($fieldDescription1);
        $this->listMapper->add($fieldDescription2);
        $this->listMapper->add($fieldDescription3);
        $this->listMapper->add($fieldDescription4);

        $this->assertEquals(array(
            'fooName1'=>$fieldDescription1,
            'fooName2'=>$fieldDescription2,
            'fooName3'=>$fieldDescription3,
            'fooName4'=>$fieldDescription4,
       ), $this->fieldDescriptionCollection->getElements());

        $this->listMapper->reorder(array('fooName3', 'fooName2', 'fooName1', 'fooName4'));

        // print_r is used to compare order of items in associative arrays
        $this->assertEquals(print_r(array(
            'fooName3'=>$fieldDescription3,
            'fooName2'=>$fieldDescription2,
            'fooName1'=>$fieldDescription1,
            'fooName4'=>$fieldDescription4,
       ), true), print_r($this->fieldDescriptionCollection->getElements(), true));

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
