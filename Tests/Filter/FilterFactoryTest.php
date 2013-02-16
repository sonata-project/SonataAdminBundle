<?php

/*
 * This file is part of the Sonata package.
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
    /**
     * @expectedException RuntimeException
     */
    public function testEmptyType()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $filter = new FilterFactory($container, array());
        $filter->create('test', null);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testUnknownType()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $filter = new FilterFactory($container, array());
        $filter->create('test', 'mytype');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testInvalidTypeInstance()
    {
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
