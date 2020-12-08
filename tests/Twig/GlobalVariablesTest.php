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

namespace Sonata\AdminBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Twig\GlobalVariables;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 *
 * NEXT_MAJOR: Remove this class.
 *
 * @group legacy
 */
class GlobalVariablesTest extends TestCase
{
    private $code;
    private $action;
    private $admin;
    private $pool;

    protected function setUp(): void
    {
        $this->code = 'sonata.page.admin.page|sonata.page.admin.snapshot';
        $this->action = 'list';
        $this->admin = $this->getMockForAbstractClass(AdminInterface::class);
        $this->pool = $this->getMockBuilder(Pool::class)->disableOriginalConstructor()->getMock();
    }

    public function testUrl(): void
    {
        $this->admin->expects($this->once())
            ->method('generateUrl')
            ->with('sonata.page.admin.page|sonata.page.admin.snapshot.list', ['foo'], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn('/list?foo');

        $this->pool->expects($this->once())
            ->method('getAdminByAdminCode')
            ->with('sonata.page.admin.page')
            ->willReturn($this->admin);

        $globalVariables = new GlobalVariables($this->pool);

        $globalVariables->url($this->code, $this->action, ['foo']);
    }

    public function testObjectUrl(): void
    {
        $object = new \stdClass();
        $this->admin->expects($this->once())
            ->method('generateObjectUrl')
            ->with('sonata.page.admin.page|sonata.page.admin.snapshot.list', $object, ['bar'], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn('/list?bar');

        $this->pool->expects($this->once())
            ->method('getAdminByAdminCode')
            ->with('sonata.page.admin.page')
            ->willReturn($this->admin);

        $globalVariables = new GlobalVariables($this->pool);

        $globalVariables->objectUrl($this->code, $this->action, $object, ['bar']);
    }

    public function testGetMosaicBackground(): void
    {
        $this->assertSame(
            'image.png',
            (new GlobalVariables($this->pool, 'image.png'))->getMosaicBackground()
        );
    }

    public function testGetMosaicBackgroundNull(): void
    {
        $this->assertNull((new GlobalVariables($this->pool))->getMosaicBackground());
    }
}
