<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;

class AdminHelperTest extends TestCase
{
    /**
     * @var AdminHelper
     */
    protected $helper;

    public function setUp(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $pool = new Pool($container, 'title', 'logo.png');
        $this->helper = new AdminHelper($pool);
    }

    public function testGetChildFormBuilder(): void
    {
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $formBuilder = new FormBuilder('test', 'stdClass', $eventDispatcher, $formFactory);

        $childFormBuilder = new FormBuilder('elementId', 'stdClass', $eventDispatcher, $formFactory);
        $formBuilder->add($childFormBuilder);

        $this->assertNull($this->helper->getChildFormBuilder($formBuilder, 'foo'));
        $this->isInstanceOf(FormBuilder::class, $this->helper->getChildFormBuilder($formBuilder, 'test_elementId'));
    }

    public function testGetChildFormView(): void
    {
        $formView = new FormView();
        $formView->vars['id'] = 'test';
        $child = new FormView($formView);
        $child->vars['id'] = 'test_elementId';

        $this->assertNull($this->helper->getChildFormView($formView, 'foo'));
        $this->isInstanceOf(FormView::class, $this->helper->getChildFormView($formView, 'test_elementId'));
    }

    public function testAddNewInstance(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('getNewInstance')->will($this->returnValue(new \stdClass()));

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getAssociationAdmin')->will($this->returnValue($admin));
        $fieldDescription->expects($this->once())->method('getAssociationMapping')->will($this->returnValue(['fieldName' => 'fooBar']));

        $object = $this->getMockBuilder('stdClass')
            ->setMethods(['addFooBar'])
            ->getMock();
        $object->expects($this->once())->method('addFooBar');

        $this->helper->addNewInstance($object, $fieldDescription);
    }

    public function testAddNewInstancePlural(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('getNewInstance')->will($this->returnValue(new \stdClass()));

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getAssociationAdmin')->will($this->returnValue($admin));
        $fieldDescription->expects($this->once())->method('getAssociationMapping')->will($this->returnValue(['fieldName' => 'fooBars']));

        $object = $this->getMockBuilder('stdClass')
            ->setMethods(['addFooBar'])
            ->getMock();
        $object->expects($this->once())->method('addFooBar');

        $this->helper->addNewInstance($object, $fieldDescription);
    }

    public function testAddNewInstanceInflector(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())->method('getNewInstance')->will($this->returnValue(new \stdClass()));

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getAssociationAdmin')->will($this->returnValue($admin));
        $fieldDescription->expects($this->once())->method('getAssociationMapping')->will($this->returnValue(['fieldName' => 'entries']));

        $object = $this->getMockBuilder('stdClass')
            ->setMethods(['addEntry'])
            ->getMock();
        $object->expects($this->once())->method('addEntry');

        $this->helper->addNewInstance($object, $fieldDescription);
    }

    public function testGetElementAccessPath(): void
    {
        $object = $this->getMockBuilder('stdClass')
            ->setMethods(['getPathToObject'])
            ->getMock();
        $subObject = $this->getMockBuilder('stdClass')
            ->setMethods(['getAnother'])
            ->getMock();
        $sub2Object = $this->getMockBuilder('stdClass')
            ->setMethods(['getMoreThings'])
            ->getMock();

        $object->expects($this->atLeastOnce())->method('getPathToObject')->will($this->returnValue([$subObject]));
        $subObject->expects($this->atLeastOnce())->method('getAnother')->will($this->returnValue($sub2Object));
        $sub2Object->expects($this->atLeastOnce())->method('getMoreThings')->will($this->returnValue('Value'));

        $path = $this->helper->getElementAccessPath('uniquePartOfId_path_to_object_0_another_more_things', $object);

        $this->assertSame('path_to_object[0].another.more_things', $path);
    }

    public function testItThrowsExceptionWhenDoesNotFindTheFullPath(): void
    {
        $path = 'uniquePartOfId_path_to_object_0_more_calls';
        $object = $this->getMockBuilder('stdClass')
            ->setMethods(['getPathToObject'])
            ->getMock();
        $subObject = $this->getMockBuilder('stdClass')
            ->setMethods(['getMore'])
            ->getMock();

        $object->expects($this->atLeastOnce())->method('getPathToObject')->will($this->returnValue([$subObject]));
        $subObject->expects($this->atLeastOnce())->method('getMore')->will($this->returnValue('Value'));

        $this->expectException(\Exception::class, 'Could not get element id from '.$path.' Failing part: calls');

        $this->helper->getElementAccessPath($path, $object);
    }

    public function testAppendFormFieldElementNested(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $object = $this->getMockBuilder('stdClass')
            ->setMethods(['getSubObject'])
            ->getMock();
        $simpleObject = $this->getMockBuilder('stdClass')
            ->setMethods(['getSubObject'])
            ->getMock();
        $subObject = $this->getMockBuilder('stdClass')
            ->setMethods(['getAnd'])
            ->getMock();
        $sub2Object = $this->getMockBuilder('stdClass')
            ->setMethods(['getMore'])
            ->getMock();
        $sub3Object = $this->getMockBuilder('stdClass')
            ->setMethods(['getFinalData'])
            ->getMock();
        $dataMapper = $this->createMock(DataMapperInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $formBuilder = new FormBuilder('test', get_class($simpleObject), $eventDispatcher, $formFactory);
        $childFormBuilder = new FormBuilder('subObject', get_class($subObject), $eventDispatcher, $formFactory);

        $object->expects($this->atLeastOnce())->method('getSubObject')->will($this->returnValue([$subObject]));
        $subObject->expects($this->atLeastOnce())->method('getAnd')->will($this->returnValue($sub2Object));
        $sub2Object->expects($this->atLeastOnce())->method('getMore')->will($this->returnValue([$sub3Object]));
        $sub3Object->expects($this->atLeastOnce())->method('getFinalData')->will($this->returnValue('value'));

        $formBuilder->setCompound(true);
        $formBuilder->setDataMapper($dataMapper);
        $formBuilder->add($childFormBuilder);

        $admin->expects($this->once())->method('getFormBuilder')->will($this->returnValue($formBuilder));
        $admin->expects($this->once())->method('getSubject')->will($this->returnValue($object));

        $this->expectException(\Exception::class, 'unknown collection class');

        $this->helper->appendFormFieldElement($admin, $simpleObject, 'uniquePartOfId_sub_object_0_and_more_0_final_data');
    }
}
