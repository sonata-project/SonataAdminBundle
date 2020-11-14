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
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\AdminBundle\Twig\Extension\TemplateRegistryExtension;
use Symfony\Component\DependencyInjection\Container;
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

    protected function setUp(): void
    {
        $this->templateRegistry = $this->createStub(TemplateRegistryInterface::class);
        $this->container = new Container();

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
        $this->expectException(ServiceNotFoundException::class);

        $this->expectExceptionMessage('You have requested a non-existent service "admin.post.template_registry"');

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
