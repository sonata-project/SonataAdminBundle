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
use Sonata\AdminBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

class FilterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyType()
    {
        $this->expectException('\RuntimeException', 'The type must be defined');

        $container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');

        $filter = new FilterFactory($container, []);
        $filter->create('test', null);
    }

    public function testUnknownType()
    {
        $this->expectException('\RuntimeException', 'No attached service to type named `mytype`');

        $container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');

        $filter = new FilterFactory($container, []);
        $filter->create('test', 'mytype');
    }

    public function testUnknownClassType()
    {
        $this->expectException('\RuntimeException', 'No attached service to type named `Sonata\AdminBundle\Form\Type\Filter\FooType`');

        $container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');

        $filter = new FilterFactory($container, []);
        $filter->create('test', 'Sonata\AdminBundle\Form\Type\Filter\FooType');
    }

    public function testClassType()
    {
        $this->expectException('\RuntimeException', 'The service `Sonata\AdminBundle\Form\Type\Filter\DefaultType` must implement `FilterInterface`');

        $container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');

        $filter = new FilterFactory($container, []);
        $filter->create('test', 'Sonata\AdminBundle\Form\Type\Filter\DefaultType');
    }

    public function testInvalidTypeInstance()
    {
        $this->expectException('\RuntimeException', 'The service `mytype` must implement `FilterInterface`');

        $container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())
            ->method('get')
            ->will($this->returnValue(false));

        $filter = new FilterFactory($container, ['mytype' => 'mytype']);
        $filter->create('test', 'mytype');
    }

    public function testCreateFilter()
    {
        $filter = $this->getMockForAbstractClass('Sonata\AdminBundle\Filter\FilterInterface');
        $filter->expects($this->once())
            ->method('initialize');

        $container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())
            ->method('get')
            ->will($this->returnValue($filter));

        $filter = new FilterFactory($container, ['mytype' => 'mytype']);
        $filter->create('test', 'mytype');
    }
}
