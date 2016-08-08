<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Twig;

use Sonata\AdminBundle\Twig\GlobalVariables;

/**
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
class GlobalVariablesTest extends \PHPUnit_Framework_TestCase
{
    private $code;
    private $action;
    private $admin;
    private $pool;

    public function setUp()
    {
        $this->code = 'sonata.page.admin.page|sonata.page.admin.snapshot';
        $this->action = 'list';
        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $this->pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock();
    }

    public function testUrl()
    {
        $this->admin->expects($this->once())
            ->method('generateUrl')
            ->with('sonata.page.admin.page|sonata.page.admin.snapshot.list', array('foo'), false)
            ->willReturn(true);

        $this->pool->expects($this->once())
            ->method('getAdminByAdminCode')
            ->with('sonata.page.admin.page')
            ->willReturn($this->admin);

        $globalVariables = new GlobalVariables($this->pool);

        $globalVariables->url($this->code, $this->action, array('foo'));
    }

    public function testObjectUrl()
    {
        $this->admin->expects($this->once())
            ->method('generateObjectUrl')
            ->with('sonata.page.admin.page|sonata.page.admin.snapshot.list', 'foo', array('bar'), false)
            ->willReturn(true);

        $this->pool->expects($this->once())
            ->method('getAdminByAdminCode')
            ->with('sonata.page.admin.page')
            ->willReturn($this->admin);

        $globalVariables = new GlobalVariables($this->pool);

        $globalVariables->objectUrl($this->code, $this->action, 'foo', array('bar'));
    }

    /**
     * @group legacy
     * NEXT_MAJOR: remove this method
     */
    public function testWithContainer()
    {
        $this->admin->expects($this->once())
            ->method('generateUrl')
            ->with('sonata.page.admin.page|sonata.page.admin.snapshot.list', array('foo'), false)
            ->willReturn(true);

        $this->pool->expects($this->once())
            ->method('getAdminByAdminCode')
            ->with('sonata.page.admin.page')
            ->willReturn($this->admin);

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())
            ->method('get')
            ->with('sonata.admin.pool')
            ->willReturn($this->pool);

        $globalVariables = new GlobalVariables($container);

        $globalVariables->url($this->code, $this->action, array('foo'));
    }

    /**
     * NEXT_MAJOR: remove this method.
     */
    public function testInvalidArgumentException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$adminPool should be an instance of Sonata\AdminBundle\Admin\Pool'
        );

        new GlobalVariables('foo');
    }
}
