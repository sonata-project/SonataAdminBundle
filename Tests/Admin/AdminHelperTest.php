<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Admin;

use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;

class AdminHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetChildFormBuilder()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $pool = new Pool($container, 'title', 'logo.png');
        $helper = new AdminHelper($pool);

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $formBuilder = new FormBuilder('test', 'stdClass', $eventDispatcher, $formFactory);

        $childFormBuilder = new FormBuilder('elementId', 'stdClass', $eventDispatcher, $formFactory);
        $formBuilder->add($childFormBuilder);

        $this->assertNull($helper->getChildFormBuilder($formBuilder, 'foo'));
        $this->isInstanceOf('Symfony\Component\Form\FormBuilder', $helper->getChildFormBuilder($formBuilder, 'test_elementId'));
    }

    public function testGetChildFormView()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $pool = new Pool($container, 'title', 'logo.png');
        $helper = new AdminHelper($pool);

        $formView = new FormView();
        $formView->vars['id'] = 'test';
        $child = new FormView($formView);
        $child->vars['id'] = 'test_elementId';

        $this->assertNull($helper->getChildFormView($formView, 'foo'));
        $this->isInstanceOf('Symfony\Component\Form\FormView', $helper->getChildFormView($formView, 'test_elementId'));
    }

    public function testAddNewInstance()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $pool = new Pool($container, 'title', 'logo.png');
        $helper = new AdminHelper($pool);

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getNewInstance')->will($this->returnValue(new \stdClass()));

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getAssociationAdmin')->will($this->returnValue($admin));
        $fieldDescription->expects($this->once())->method('getAssociationMapping')->will($this->returnValue(array('fieldName' => 'fooBar')));

        $object = $this->getMock('sdtClass', array('addFooBar'));
        $object->expects($this->once())->method('addFooBar');

        $helper->addNewInstance($object, $fieldDescription);
    }

    public function testAddNewInstancePlural()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $pool = new Pool($container, 'title', 'logo.png');
        $helper = new AdminHelper($pool);

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getNewInstance')->will($this->returnValue(new \stdClass()));

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getAssociationAdmin')->will($this->returnValue($admin));
        $fieldDescription->expects($this->once())->method('getAssociationMapping')->will($this->returnValue(array('fieldName' => 'fooBars')));

        $object = $this->getMock('sdtClass', array('addFooBar'));
        $object->expects($this->once())->method('addFooBar');

        $helper->addNewInstance($object, $fieldDescription);
    }

    public function testAddNewInstanceInflector()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $pool = new Pool($container, 'title', 'logo.png');
        $helper = new AdminHelper($pool);

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getNewInstance')->will($this->returnValue(new \stdClass()));

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getAssociationAdmin')->will($this->returnValue($admin));
        $fieldDescription->expects($this->once())->method('getAssociationMapping')->will($this->returnValue(array('fieldName' => 'entries')));

        $object = $this->getMock('sdtClass', array('addEntry'));
        $object->expects($this->once())->method('addEntry');

        $helper->addNewInstance($object, $fieldDescription);
    }

    public function testGetElementAccessPath()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $pool = new Pool($container, 'title', 'logo.png');
        $helper = new AdminHelper($pool);

        $object = $this->getMock('stdClass', array('getPathToObject'));
        $subObject = $this->getMock('stdClass', array('getAnd'));
        $sub2Object = $this->getMock('stdClass', array('getMore'));

        $object->expects($this->atLeastOnce())->method('getPathToObject')->will($this->returnValue(array($subObject)));
        $subObject->expects($this->atLeastOnce())->method('getAnd')->will($this->returnValue($sub2Object));
        $sub2Object->expects($this->atLeastOnce())->method('getMore')->will($this->returnValue('Value'));

        $path = $helper->getElementAccessPath('uniquePartOfId_path_to_object_0_and_more', $object);

        $this->assertSame('path_to_object[0].and.more', $path);
    }

    /**
     * tests only so far that actual value/object is retrieved.
     *
     * @expectedException        Exception
     * @expectedExceptionCode    0
     * @expectedExceptionMessage unknown collection class
     */
    public function testAppendFormFieldElementNested()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $pool = new Pool($container, 'title', 'logo.png');
        $helper = new AdminHelper($pool);
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $object = $this->getMock('stdClass', array('getSubObject'));
        $simpleObject = $this->getMock('stdClass', array('getSubObject'));
        $subObject = $this->getMock('stdClass', array('getAnd'));
        $sub2Object = $this->getMock('stdClass', array('getMore'));
        $sub3Object = $this->getMock('stdClass', array('getFinalData'));
        $dataMapper = $this->getMock('Symfony\Component\Form\DataMapperInterface');
        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $formBuilder = new FormBuilder('test', get_class($simpleObject), $eventDispatcher, $formFactory);
        $childFormBuilder = new FormBuilder('subObject', get_class($subObject), $eventDispatcher, $formFactory);

        $object->expects($this->atLeastOnce())->method('getSubObject')->will($this->returnValue(array($subObject)));
        $subObject->expects($this->atLeastOnce())->method('getAnd')->will($this->returnValue($sub2Object));
        $sub2Object->expects($this->atLeastOnce())->method('getMore')->will($this->returnValue(array($sub3Object)));
        $sub3Object->expects($this->atLeastOnce())->method('getFinalData')->will($this->returnValue('value'));

        $formBuilder->setCompound(true);
        $formBuilder->setDataMapper($dataMapper);
        $formBuilder->add($childFormBuilder);

        $admin->expects($this->once())->method('getFormBuilder')->will($this->returnValue($formBuilder));
        $admin->expects($this->once())->method('getSubject')->will($this->returnValue($object));

        $helper->appendFormFieldElement($admin, $simpleObject, 'uniquePartOfId_sub_object_0_and_more_0_final_data');
    }
}
