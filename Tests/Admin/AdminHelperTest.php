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
use Sonata\AdminBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;

class AdminHelperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AdminHelper
     */
    protected $helper;

    public function setUp()
    {
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $pool = new Pool($container, 'title', 'logo.png');
        $this->helper = new AdminHelper($pool);
    }

    public function testGetChildFormBuilder()
    {
        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $eventDispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $formBuilder = new FormBuilder('test', 'stdClass', $eventDispatcher, $formFactory);

        $childFormBuilder = new FormBuilder('elementId', 'stdClass', $eventDispatcher, $formFactory);
        $formBuilder->add($childFormBuilder);

        $this->assertNull($this->helper->getChildFormBuilder($formBuilder, 'foo'));
        $this->isInstanceOf('Symfony\Component\Form\FormBuilder', $this->helper->getChildFormBuilder($formBuilder, 'test_elementId'));
    }

    public function testGetChildFormView()
    {
        $formView = new FormView();
        $formView->vars['id'] = 'test';
        $child = new FormView($formView);
        $child->vars['id'] = 'test_elementId';

        $this->assertNull($this->helper->getChildFormView($formView, 'foo'));
        $this->isInstanceOf('Symfony\Component\Form\FormView', $this->helper->getChildFormView($formView, 'test_elementId'));
    }

    public function testAddNewInstance()
    {
        $admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getNewInstance')->will($this->returnValue(new \stdClass()));

        $fieldDescription = $this->createMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getAssociationAdmin')->will($this->returnValue($admin));
        $fieldDescription->expects($this->once())->method('getAssociationMapping')->will($this->returnValue(array('fieldName' => 'fooBar')));

        $object = $this->getMockBuilder('stdClass')
            ->setMethods(array('addFooBar'))
            ->getMock();
        $object->expects($this->once())->method('addFooBar');

        $this->helper->addNewInstance($object, $fieldDescription);
    }

    public function testAddNewInstancePlural()
    {
        $admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getNewInstance')->will($this->returnValue(new \stdClass()));

        $fieldDescription = $this->createMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getAssociationAdmin')->will($this->returnValue($admin));
        $fieldDescription->expects($this->once())->method('getAssociationMapping')->will($this->returnValue(array('fieldName' => 'fooBars')));

        $object = $this->getMockBuilder('stdClass')
            ->setMethods(array('addFooBar'))
            ->getMock();
        $object->expects($this->once())->method('addFooBar');

        $this->helper->addNewInstance($object, $fieldDescription);
    }

    public function testAddNewInstanceInflector()
    {
        $admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getNewInstance')->will($this->returnValue(new \stdClass()));

        $fieldDescription = $this->createMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getAssociationAdmin')->will($this->returnValue($admin));
        $fieldDescription->expects($this->once())->method('getAssociationMapping')->will($this->returnValue(array('fieldName' => 'entries')));

        $object = $this->getMockBuilder('stdClass')
            ->setMethods(array('addEntry'))
            ->getMock();
        $object->expects($this->once())->method('addEntry');

        $this->helper->addNewInstance($object, $fieldDescription);
    }

    public function testGetElementAccessPath()
    {
        $object = $this->getMockBuilder('stdClass')
            ->setMethods(array('getPathToObject'))
            ->getMock();
        $subObject = $this->getMockBuilder('stdClass')
            ->setMethods(array('getAnother'))
            ->getMock();
        $sub2Object = $this->getMockBuilder('stdClass')
            ->setMethods(array('getMoreThings'))
            ->getMock();

        $object->expects($this->atLeastOnce())->method('getPathToObject')->will($this->returnValue(array($subObject)));
        $subObject->expects($this->atLeastOnce())->method('getAnother')->will($this->returnValue($sub2Object));
        $sub2Object->expects($this->atLeastOnce())->method('getMoreThings')->will($this->returnValue('Value'));

        $path = $this->helper->getElementAccessPath('uniquePartOfId_path_to_object_0_another_more_things', $object);

        $this->assertSame('path_to_object[0].another.more_things', $path);
    }

    public function testItThrowsExceptionWhenDoesNotFindTheFullPath()
    {
        $path = 'uniquePartOfId_path_to_object_0_more_calls';
        $object = $this->getMockBuilder('stdClass')
            ->setMethods(array('getPathToObject'))
            ->getMock();
        $subObject = $this->getMockBuilder('stdClass')
            ->setMethods(array('getMore'))
            ->getMock();

        $object->expects($this->atLeastOnce())->method('getPathToObject')->will($this->returnValue(array($subObject)));
        $subObject->expects($this->atLeastOnce())->method('getMore')->will($this->returnValue('Value'));

        $this->expectException('Exception', 'Could not get element id from '.$path.' Failing part: calls');

        $this->helper->getElementAccessPath($path, $object);
    }

    public function testAppendFormFieldElementNested()
    {
        $admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $object = $this->getMockBuilder('stdClass')
            ->setMethods(array('getSubObject'))
            ->getMock();
        $simpleObject = $this->getMockBuilder('stdClass')
            ->setMethods(array('getSubObject'))
            ->getMock();
        $subObject = $this->getMockBuilder('stdClass')
            ->setMethods(array('getAnd'))
            ->getMock();
        $sub2Object = $this->getMockBuilder('stdClass')
            ->setMethods(array('getMore'))
            ->getMock();
        $sub3Object = $this->getMockBuilder('stdClass')
            ->setMethods(array('getFinalData'))
            ->getMock();
        $dataMapper = $this->createMock('Symfony\Component\Form\DataMapperInterface');
        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $eventDispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
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

        $this->expectException('Exception', 'unknown collection class');

        $this->helper->appendFormFieldElement($admin, $simpleObject, 'uniquePartOfId_sub_object_0_and_more_0_final_data');
    }
}
