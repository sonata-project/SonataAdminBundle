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

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Twig\Extension\TemplateRegistryExtension;

/**
 * Class TemplateRegistryExtensionTest.
 */
class TemplateRegistryExtensionTest extends TestCase
{
    /**
     * @var TemplateRegistryExtension
     */
    private $extension;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var AdminInterface
     */
    private $admin;

    protected function setUp(): void
    {
        $this->pool = $this->prophesize(Pool::class);
        $this->admin = $this->prophesize(AdminInterface::class);

        $this->pool->getAdminByAdminCode('admin.post')->willReturn($this->admin);

        $this->extension = new TemplateRegistryExtension($this->pool->reveal());
    }

    public function testGetTemplate(): void
    {
        $this->admin->getTemplate('edit')->willReturn('@SonataAdmin/CRUD/edit.html.twig');

        $this->assertEquals(
            '@SonataAdmin/CRUD/edit.html.twig',
            $this->extension->getAdminTemplate('edit', 'admin.post')
        );
    }

    public function testGetPoolTemplate(): void
    {
        $this->pool->getTemplate('edit')->willReturn('@SonataAdmin/CRUD/edit.html.twig');

        $this->assertEquals(
            '@SonataAdmin/CRUD/edit.html.twig',
            $this->extension->getPoolTemplate('edit')
        );
    }
}
