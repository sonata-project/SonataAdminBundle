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
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\AdminBundle\Twig\Extension\TemplateRegistryExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
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
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * NEXT_MAJOR: Remove this attribute.
     *
     * @var AdminInterface
     */
    private $admin;

    protected function setUp(): void
    {
        $this->templateRegistry = $this->createStub(TemplateRegistryInterface::class);
        $this->container = $this->createStub(ContainerInterface::class);

        // NEXT_MAJOR: Remove this line
        $this->admin = $this->createStub(AdminInterface::class);

        // NEXT_MAJOR: Remove this line
        $this->admin->method('getTemplate')->with('edit')->willReturn('@SonataAdmin/CRUD/edit.html.twig');

        $this->templateRegistry->method('getTemplate')->with('edit')->willReturn('@SonataAdmin/CRUD/edit.html.twig');

        $this->extension = new TemplateRegistryExtension(
            $this->templateRegistry,
            $this->container
        );
    }

    public function getFunctionsTest(): void
    {
        $expected = [
            new TwigFunction('get_admin_template', [$this->extension, 'getAdminTemplate']),
            new TwigFunction('get_global_template', [$this->extension, 'getGlobalTemplate']),

            // NEXT MAJOR: Remove this line
            new TwigFunction('get_admin_pool_template', [$this->extension, 'getGlobalTemplate'], ['deprecated' => true]),
        ];

        $this->assertSame($expected, $this->extension->getFunctions());
    }

    public function testGetAdminTemplate(): void
    {
        $this->container->method('get')->willReturnMap([
            // NEXT_MAJOR: Remove this line
            ['admin.post', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->admin],
            ['admin.post.template_registry', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->templateRegistry],
        ]);

        $this->assertSame(
            '@SonataAdmin/CRUD/edit.html.twig',
            $this->extension->getAdminTemplate('edit', 'admin.post')
        );
    }

    public function testGetAdminTemplateFailure(): void
    {
        $this->expectException(ServiceNotFoundException::class);

        // NEXT_MAJOR: Remove this line and use the commented line below instead
        $this->expectExceptionMessage('You have requested a non-existent service "admin.post"');
        // $this->expectExceptionMessage('You have requested a non-existent service "admin.post.template_registry"');

        $this->assertSame(
            '@SonataAdmin/CRUD/edit.html.twig',
            $this->extension->getAdminTemplate('edit', 'admin.post')
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
