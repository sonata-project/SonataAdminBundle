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

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Twig\GlobalVariables;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
class GlobalVariablesTest extends TestCase
{
    private $code;
    private $action;
    private $admin;
    private $pool;

    public function setUp()
    {
        $this->code = 'sonata.page.admin.page|sonata.page.admin.snapshot';
        $this->action = 'list';
        $this->admin = $this->getMockForAbstractClass(AdminInterface::class);
        $this->pool = $this->getMockBuilder(Pool::class)->disableOriginalConstructor()->getMock();
    }

    public function testUrl()
    {
        $this->admin->expects($this->once())
            ->method('generateUrl')
            ->with('sonata.page.admin.page|sonata.page.admin.snapshot.list', ['foo'], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn(true);

        $this->pool->expects($this->once())
            ->method('getAdminByAdminCode')
            ->with('sonata.page.admin.page')
            ->willReturn($this->admin);

        $globalVariables = new GlobalVariables($this->pool);

        $globalVariables->url($this->code, $this->action, ['foo']);
    }

    public function testObjectUrl()
    {
        $this->admin->expects($this->once())
            ->method('generateObjectUrl')
            ->with('sonata.page.admin.page|sonata.page.admin.snapshot.list', 'foo', ['bar'], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn(true);

        $this->pool->expects($this->once())
            ->method('getAdminByAdminCode')
            ->with('sonata.page.admin.page')
            ->willReturn($this->admin);

        $globalVariables = new GlobalVariables($this->pool);

        $globalVariables->objectUrl($this->code, $this->action, 'foo', ['bar']);
    }

    /**
     * @group legacy
     * NEXT_MAJOR: remove this method
     */
    public function testWithContainer()
    {
        $this->admin->expects($this->once())
            ->method('generateUrl')
            ->with('sonata.page.admin.page|sonata.page.admin.snapshot.list', ['foo'], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn(true);

        $this->pool->expects($this->once())
            ->method('getAdminByAdminCode')
            ->with('sonata.page.admin.page')
            ->willReturn($this->admin);

        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->with('sonata.admin.pool')
            ->willReturn($this->pool);

        $globalVariables = new GlobalVariables($container);

        $globalVariables->url($this->code, $this->action, ['foo']);
    }

    /**
     * NEXT_MAJOR: remove this method.
     */
    public function testInvalidArgumentException()
    {
        $this->expectException(
            \InvalidArgumentException::class,
            '$adminPool should be an instance of Sonata\AdminBundle\Admin\Pool'
        );

        new GlobalVariables('foo');
    }

    /**
     * @dataProvider getMosaicBackgroundProvider
     */
    public function testGetMosaicBackground($expected, $image)
    {
        $globalVariables = new GlobalVariables($this->pool, $image);
        $this->assertEquals($expected, $globalVariables->getMosaicBackground());
    }

    public function getMosaicBackgroundProvider()
    {
        return [
            'background is null' => [null, null],
            'background is set' => ['image.png', 'image.png'],
        ];
    }
}
