<?php

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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var AdminInterface
     */
    private $admin;

    protected function setUp()
    {
        $this->request = $this->prophesize(Request::class);
        $this->pool = $this->prophesize(Pool::class);
        $this->requestStack = $this->prophesize(RequestStack::class);
        $this->admin = $this->prophesize(AdminInterface::class);

        $this->requestStack->getCurrentRequest()->willReturn($this->request);
        $this->request->get('_sonata_admin')->willReturn('admin.post');
        $this->pool->getAdminByAdminCode('admin.post')->willReturn($this->admin);

        $this->extension = new TemplateRegistryExtension(
            $this->pool->reveal(),
            $this->requestStack->reveal()
        );
    }

    public function testGetTemplate()
    {
        $this->admin->getTemplate('edit')->willReturn('@SonataAdmin/CRUD/edit.html.twig');

        $this->assertEquals(
            '@SonataAdmin/CRUD/edit.html.twig',
            $this->extension->getTemplate('edit')
        );
    }

    public function testGetPoolTemplate()
    {
        $this->pool->getTemplate('edit')->willReturn('@SonataAdmin/CRUD/edit.html.twig');

        $this->assertEquals(
            '@SonataAdmin/CRUD/edit.html.twig',
            $this->extension->getPoolTemplate('edit')
        );
    }
}
