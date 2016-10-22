<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Filter;

use Sonata\AdminBundle\Filter\FilterFactory;

class FilterFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyType()
    {
        $this->setExpectedException('\RuntimeException', 'The type must be defined');

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $filter = new FilterFactory($container, array());
        $filter->create('test', null);
    }

    public function testUnknownType()
    {
        $this->setExpectedException('\RuntimeException', 'No attached service to type named `mytype`');

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $filter = new FilterFactory($container, array());
        $filter->create('test', 'mytype');
    }

    public function testUnknownClassType()
    {
        $this->setExpectedException('\RuntimeException', 'No attached service to type named `Sonata\AdminBundle\Form\Type\Filter\FooType`');

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $filter = new FilterFactory($container, array());
        $filter->create('test', 'Sonata\AdminBundle\Form\Type\Filter\FooType');
    }

    public function testClassType()
    {
        $this->setExpectedException('\RuntimeException', 'The service `Sonata\AdminBundle\Form\Type\Filter\DefaultType` must implement `FilterInterface`');

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $filter = new FilterFactory($container, array());
        $filter->create('test', 'Sonata\AdminBundle\Form\Type\Filter\DefaultType');
    }

    public function testInvalidTypeInstance()
    {
        $this->setExpectedException('\RuntimeException', 'The service `mytype` must implement `FilterInterface`');

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())
            ->method('get')
            ->will($this->returnValue(false));

        $filter = new FilterFactory($container, array('mytype' => 'mytype'));
        $filter->create('test', 'mytype');
    }

    public function testCreateFilter()
    {
        $filter = $this->getMock('Sonata\AdminBundle\Filter\FilterInterface');
        $filter->expects($this->once())
            ->method('initialize');

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())
            ->method('get')
            ->will($this->returnValue($filter));

        $filter = new FilterFactory($container, array('mytype' => 'mytype'));
        $filter->create('test', 'mytype');
    }
}
