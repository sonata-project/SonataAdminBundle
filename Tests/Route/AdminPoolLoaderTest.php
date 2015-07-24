<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Route;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class AdminPoolLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $pool = $this->getMock('Sonata\AdminBundle\Admin\Pool', array(), array($container, 'title', 'logoTitle'));

        $adminPoolLoader = new AdminPoolLoader($pool, array('foo_admin', 'bar_admin'), $container);

        $this->assertTrue($adminPoolLoader->supports('foo', 'sonata_admin'));
        $this->assertFalse($adminPoolLoader->supports('foo', 'bar'));
    }

    public function testLoad()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $pool = $this->getMock('Sonata\AdminBundle\Admin\Pool', array(), array($container, 'title', 'logoTitle'));

        $adminPoolLoader = new AdminPoolLoader($pool, array('foo_admin', 'bar_admin'), $container);

        $routeCollection1 = new RouteCollection('base.Code.Route.foo', 'baseRouteNameFoo', 'baseRoutePatternFoo', 'baseControllerNameFoo');
        $routeCollection2 = new RouteCollection('base.Code.Route.bar', 'baseRouteNameBar', 'baseRoutePatternBar', 'baseControllerNameBar');

        $routeCollection1->add('foo');
        $routeCollection2->add('bar');
        $routeCollection2->add('baz');

        $admin1 = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin1->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue($routeCollection1));

        $admin2 = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin2->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue($routeCollection2));

        $pool->expects($this->any())
            ->method('getInstance')
            ->will($this->returnCallback(function ($id) use ($admin1, $admin2) {
                switch ($id) {
                    case 'foo_admin':
                        return $admin1;
                    case 'bar_admin':
                        return $admin2;
                }

                return;
            }));

        $collection = $adminPoolLoader->load('foo', 'sonata_admin');

        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $collection);
        $this->assertInstanceOf('Symfony\Component\Routing\Route', $collection->get('baseRouteNameFoo_foo'));
        $this->assertInstanceOf('Symfony\Component\Routing\Route', $collection->get('baseRouteNameBar_bar'));
        $this->assertInstanceOf('Symfony\Component\Routing\Route', $collection->get('baseRouteNameBar_bar'));
    }
}
