<?php

/*
 * This file is part of the Sonata package.
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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Form\FormView;

class AdminHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $pool = $this->getMock('Sonata\AdminBundle\Admin\Pool', array(), array($container, 'title', 'logo.png'));
        $this->isInstanceOf('Sonata\AdminBundle\Admin\AdminHelper', new AdminHelper($pool));
        $pool = $this->getMock('Sonata\AdminBundle\Admin\Pool', array(), array($container, 'title', 'logo.png'));
        $this->isInstanceOf('Sonata\AdminBundle\Admin\AdminHelper', new AdminHelper($pool));
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

    public function testAppendFormFieldElement()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $pool = new Pool($container, 'title', 'logo.png');
        $helper = new AdminHelper($pool);

        $mockFormView = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();

        $mockForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $mockForm->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($mockFormView));

        $mockFormBuilder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $mockFormBuilder->expects($this->any())
            ->method('getForm')
            ->will($this->returnValue($mockForm));

        $collection = $this->getMock('Doctrine\Common\Collections\ArrayCollection');
        $collection->expects($this->once())->method('add');

        $entity = new \stdClass();
        $entity->foo = new \stdClass();
        $entity->foo->bar = $collection;

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->any())
            ->method('getNewInstance')
            ->will($this->returnValue(new \stdClass()));
        $admin->expects($this->any())
            ->method('getFormBuilder')
            ->will($this->returnValue($mockFormBuilder));
        $admin->expects($this->any())
            ->method('getSubject')
            ->will($this->returnValue($entity));

        $fooAdmin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $barAdmin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $barFormFieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fooFormFieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $admin->expects($this->any())
            ->method('getFormFieldDescription')
            ->will($this->returnValue($fooFormFieldDescription));

        $fooFormFieldDescription->expects($this->any())
            ->method('getAssociationAdmin')
            ->will($this->returnValue($fooAdmin));

        $fooAdmin->expects($this->any())
            ->method('getFormFieldDescription')
            ->will($this->returnValue($barFormFieldDescription));

        $barFormFieldDescription->expects($this->any())
            ->method('getAssociationAdmin')
            ->will($this->returnValue($barAdmin));

        $barAdmin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('\stdClass'));

        $helper->appendFormFieldElement($admin, $mockForm, 'dummy_foo_bar');
    }
}
