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

    public function testAddNewInstanceInAssociation()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $pool = new Pool($container, 'title', 'logo.png');
        $helper = new AdminHelper($pool);

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getNewInstance')->will($this->returnValue(new \stdClass()));

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getAssociationAdmin')->will($this->returnValue($admin));
        $fieldDescription->expects($this->once())->method('getAssociationMapping')->will($this->returnValue(array('fieldName' => 'entries')));

        $grandChild = $this->getMock('sdtClass', array('addEntry'), array(), 'GrandChild');
        $grandChild->expects($this->once())->method('addEntry');

        $child = $this->getMock('sdtClass', array('getBar'), array(), 'Child');
        $child->expects($this->once())->method('getBar')->will($this->returnValue($grandChild));

        $object = $this->getMock('sdtClass', array('getFoo'), array(), 'Actual');
        $object->expects($this->once())->method('getFoo')->will($this->returnValue($child));

        $helper->addNewInstance($object, $fieldDescription, array('foo', 'bar', 'entries'));
    }
}
