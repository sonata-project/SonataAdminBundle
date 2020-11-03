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

namespace Sonata\AdminBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\DependencyInjection\Compiler\TemplateRegistryCompilerPass;
use Sonata\AdminBundle\Templating\AbstractTemplateRegistry;
use Sonata\AdminBundle\Templating\MutableTemplateRegistry;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryAwareInterface;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryAwareInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class TemplateRegistryCompilerPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
    }

    public function testTemplateRegistry(): void
    {
        $compilerPass = new TemplateRegistryCompilerPass();

        // NEXT_MAJOR: change NewTemplateRegistry to TemplateRegistr
        $this->container
            ->register('sonata.admin.custom_template_registry', NewTemplateRegistry::class)
            ->addTag('sonata.admin.template_registry', ['template_name' => 'show', 'template_path' => 'CRUD/tag_show.html.twig'])
            ->addTag('sonata.admin.template_registry', ['template_name' => 'edit', 'template_path' => 'CRUD/tag_edit.html.twig'])
            ->setArgument(0, ['list' => 'CRUD/list.html.twig', 'edit' => 'CRUD/edit.html.twig'])
            ->setPublic(true);

        $compilerPass->process($this->container);
        $this->container->compile();

        $this->assertTrue($this->container->hasDefinition('sonata.admin.custom_template_registry'));

        $templateRegistry = $this->container->get('sonata.admin.custom_template_registry');

        $this->assertSame('CRUD/list.html.twig', $templateRegistry->getTemplate('list'));
        $this->assertSame('CRUD/tag_show.html.twig', $templateRegistry->getTemplate('show'));
        $this->assertSame('CRUD/tag_edit.html.twig', $templateRegistry->getTemplate('edit'));
    }

    public function testMutableTemplateRegistry(): void
    {
        $compilerPass = new TemplateRegistryCompilerPass();

        $this->container
            ->register('sonata.admin.custom_template_registry', MutableTemplateRegistry::class)
            ->addTag('sonata.admin.template_registry', ['template_name' => 'show', 'template_path' => 'CRUD/tag_show.html.twig'])
            ->addTag('sonata.admin.template_registry', ['template_name' => 'edit', 'template_path' => 'CRUD/tag_edit.html.twig'])
            ->setArgument(0, ['list' => 'CRUD/list.html.twig', 'edit' => 'CRUD/edit.html.twig'])
            ->setPublic(true);

        $compilerPass->process($this->container);
        $this->container->compile();

        $this->assertTrue($this->container->has('sonata.admin.custom_template_registry'));

        $templateRegistry = $this->container->get('sonata.admin.custom_template_registry');

        $this->assertSame('CRUD/list.html.twig', $templateRegistry->getTemplate('list'));
        $this->assertSame('CRUD/tag_show.html.twig', $templateRegistry->getTemplate('show'));
        $this->assertSame('CRUD/tag_edit.html.twig', $templateRegistry->getTemplate('edit'));
    }

    public function testTemplateRegistryAware(): void
    {
        $compilerPass = new TemplateRegistryCompilerPass();

        $this->container
            ->register('sonata.admin.custom_template_registry_aware', TemplateRegistryAware::class)
            ->addTag('sonata.admin.template_registry', ['template_name' => 'show', 'template_path' => 'CRUD/tag_show.html.twig'])
            ->setPublic(true);

        $compilerPass->process($this->container);
        $this->container->compile();

        $this->assertTrue($this->container->has('sonata.admin.custom_template_registry_aware.template_registry'));

        $templateRegistry = $this->container->get('sonata.admin.custom_template_registry_aware.template_registry');

        $this->assertSame('CRUD/tag_show.html.twig', $templateRegistry->getTemplate('show'));
    }

    public function testTemplateRegistryAwareWithTemplateRegistry(): void
    {
        $this->expectException(\Exception::class);

        $compilerPass = new TemplateRegistryCompilerPass();

        // NEXT_MAJOR: change NewTemplateRegistry to TemplateRegistry
        $this->container
            ->register('sonata.admin.custom_template_registry', NewTemplateRegistry::class)
            ->addTag('sonata.admin.template_registry', ['template_name' => 'show', 'template_path' => 'CRUD/tag_show.html.twig'])
            ->setArgument(0, ['list' => 'CRUD/list.html.twig', 'edit' => 'CRUD/edit.html.twig'])
            ->setPublic(true);

        $this->container
            ->register('sonata.admin.custom_template_registry_aware', TemplateRegistryAware::class)
            ->addTag('sonata.admin.template_registry', ['template_name' => 'edit', 'template_path' => 'CRUD/tag_aware_edit.html.twig'])
            ->addTag('sonata.admin.template_registry', ['template_name' => 'delete', 'template_path' => 'CRUD/tag_aware_delete.html.twig'])
            ->addMethodCall('setTemplateRegistry', [new Reference('sonata.admin.custom_template_registry')])
            ->setPublic(true);

        $compilerPass->process($this->container);
        $this->container->compile();

        $this->assertTrue($this->container->has('sonata.admin.custom_template_registry'));

        $templateRegistry = $this->container->get('sonata.admin.custom_template_registry');

        $this->assertSame('CRUD/list.html.twig', $templateRegistry->getTemplate('list'));
        $this->assertSame('CRUD/tag_show.html.twig', $templateRegistry->getTemplate('show'));
    }

    public function testMutableTemplateRegistryAware(): void
    {
        $compilerPass = new TemplateRegistryCompilerPass();

        $this->container
            ->register('sonata.admin.custom_mutable_template_registry_aware', MutableTemplateRegistryAware::class)
            ->addTag('sonata.admin.template_registry', ['template_name' => 'list', 'template_path' => 'CRUD/tag_list.html.twig'])
            ->addTag('sonata.admin.template_registry', ['template_name' => 'show', 'template_path' => 'CRUD/tag_show.html.twig'])
            ->setPublic(true);

        $compilerPass->process($this->container);
        $this->container->compile();

        $this->assertTrue($this->container->has('sonata.admin.custom_mutable_template_registry_aware.template_registry'));

        $templateRegistry = $this->container->get('sonata.admin.custom_mutable_template_registry_aware.template_registry');

        $this->assertSame('CRUD/tag_list.html.twig', $templateRegistry->getTemplate('list'));
        $this->assertSame('CRUD/tag_show.html.twig', $templateRegistry->getTemplate('show'));
    }

    public function testMutableTemplateRegistryAwareWithTemplateRegistry(): void
    {
        $compilerPass = new TemplateRegistryCompilerPass();

        $this->container
            ->register('sonata.admin.custom_template_registry', MutableTemplateRegistry::class)
            ->setArgument(0, ['list' => 'CRUD/list.html.twig', 'edit' => 'CRUD/edit.html.twig'])
            ->setPublic(true);

        $this->container
            ->register('sonata.admin.custom_template_registry_aware', MutableTemplateRegistryAware::class)
            ->addTag('sonata.admin.template_registry', ['template_name' => 'show', 'template_path' => 'CRUD/tag_show.html.twig'])
            ->addTag('sonata.admin.template_registry', ['template_name' => 'edit', 'template_path' => 'CRUD/tag_edit.html.twig'])
            ->addMethodCall('setTemplateRegistry', [new Reference('sonata.admin.custom_template_registry')])
            ->setPublic(true);

        $compilerPass->process($this->container);
        $this->container->compile();

        $this->assertTrue($this->container->has('sonata.admin.custom_template_registry'));

        $templateRegistry = $this->container->get('sonata.admin.custom_template_registry');

        $this->assertSame('CRUD/list.html.twig', $templateRegistry->getTemplate('list'));
        $this->assertSame('CRUD/tag_show.html.twig', $templateRegistry->getTemplate('show'));
        $this->assertSame('CRUD/tag_edit.html.twig', $templateRegistry->getTemplate('edit'));
    }
}

// NEXT_MAJOR: remove this class
final class NewTemplateRegistry extends AbstractTemplateRegistry implements TemplateRegistryInterface
{
}

final class TemplateRegistryAware implements TemplateRegistryAwareInterface
{
    /**
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;

    public function getTemplateRegistry(): TemplateRegistryInterface
    {
        return $this->templateRegistry;
    }

    public function hasTemplateRegistry(): bool
    {
        return null !== $this->templateRegistry;
    }

    public function setTemplateRegistry(TemplateRegistryInterface $templateRegistry): void
    {
        $this->templateRegistry = $templateRegistry;
    }
}

final class MutableTemplateRegistryAware implements MutableTemplateRegistryAwareInterface
{
    /**
     * @var MutableTemplateRegistryInterface
     */
    private $templateRegistry;

    public function getTemplateRegistry(): MutableTemplateRegistryInterface
    {
        return $this->templateRegistry;
    }

    public function hasTemplateRegistry(): bool
    {
        return null !== $this->templateRegistry;
    }

    public function setTemplateRegistry(MutableTemplateRegistryInterface $templateRegistry): void
    {
        $this->templateRegistry = $templateRegistry;
    }
}
