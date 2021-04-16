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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Exception\AdminCodeNotFoundException;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\AdminBundle\Twig\Extension\TemplateRegistryExtension;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\TwigFunction;

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
     * @var TemplateRegistryInterface&MockObject
     */
    private $templateRegistry;

    /**
     * @var ContainerInterface
     */
    private $container;

    protected function setUp(): void
    {
        $this->templateRegistry = $this->createMock(TemplateRegistryInterface::class);
        $this->container = new Container();

        $adminTemplateRegistry = $this->createMock(MutableTemplateRegistryInterface::class);
        $adminTemplateRegistry->method('getTemplate')->with('edit')->willReturn('@SonataAdmin/CRUD/edit.html.twig');
        $admin = $this->createStub(AdminInterface::class);
        $admin
            ->method('getTemplateRegistry')
            ->willReturn($adminTemplateRegistry);
        $this->container->set('admin.post', $admin);
        $pool = new Pool($this->container, ['admin.post']);

        $this->templateRegistry->method('getTemplate')->with('edit')->willReturn('@SonataAdmin/CRUD/edit.html.twig');

        $this->extension = new TemplateRegistryExtension(
            $this->templateRegistry,
            $this->container,
            $pool
        );
    }

    public function getFunctionsTest(): void
    {
        $expected = [
            new TwigFunction('get_admin_template', [$this->extension, 'getAdminTemplate']),
            new TwigFunction('get_global_template', [$this->extension, 'getGlobalTemplate']),
        ];

        $this->assertSame($expected, $this->extension->getFunctions());
    }

    public function testGetAdminTemplate(): void
    {
        $this->container->set('admin.post.template_registry', $this->templateRegistry);

        $this->assertSame(
            '@SonataAdmin/CRUD/edit.html.twig',
            $this->extension->getAdminTemplate('edit', 'admin.post')
        );
    }

    public function testGetAdminTemplateFailure(): void
    {
        $this->expectException(AdminCodeNotFoundException::class);

        $this->expectExceptionMessage('Admin service "admin.non-existing" not found in admin pool. Did you mean "admin.post" or one of those: []?');

        $this->assertSame(
            '@SonataAdmin/CRUD/edit.html.twig',
            $this->extension->getAdminTemplate('edit', 'admin.non-existing')
        );
    }

    public function testGetGlobalTemplate(): void
    {
        $this->assertSame(
            '@SonataAdmin/CRUD/edit.html.twig',
            $this->extension->getGlobalTemplate('edit')
        );
    }
}
