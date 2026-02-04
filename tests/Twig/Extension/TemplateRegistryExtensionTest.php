<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Twig\Extension;

use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Exception\AdminCodeNotFoundException;
use SensioLabs\AdminBundle\Templating\MutableTemplateRegistryInterface;
use SensioLabs\AdminBundle\Templating\TemplateRegistryInterface;
use SensioLabs\AdminBundle\Twig\Extension\TemplateRegistryExtension;
use SensioLabs\AdminBundle\Twig\TemplateRegistryRuntime;
use Symfony\Component\DependencyInjection\Container;

/**
 * NEXT_MAJOR: Remove this test.
 */
#[IgnoreDeprecations]
final class TemplateRegistryExtensionTest extends TestCase
{
    private TemplateRegistryExtension $extension;

    protected function setUp(): void
    {
        $templateRegistry = $this->createMock(TemplateRegistryInterface::class);
        $templateRegistry->method('getTemplate')->with('edit')->willReturn('@SensioLabsAdmin/CRUD/edit.html.twig');

        $adminTemplateRegistry = $this->createMock(MutableTemplateRegistryInterface::class);
        $adminTemplateRegistry->method('getTemplate')->with('edit')->willReturn('@SensioLabsAdmin/CRUD/edit.html.twig');

        $admin = static::createStub(AdminInterface::class);
        $admin
            ->method('getTemplateRegistry')
            ->willReturn($adminTemplateRegistry);

        $container = new Container();
        $container->set('admin.post', $admin);
        $pool = new Pool($container, ['admin.post']);

        $this->extension = new TemplateRegistryExtension(new TemplateRegistryRuntime(
            $templateRegistry,
            $pool
        ));
    }

    public function testGetFunctions(): void
    {
        static::assertCount(2, $this->extension->getFunctions());
    }

    public function testGetAdminTemplate(): void
    {
        static::assertSame(
            '@SensioLabsAdmin/CRUD/edit.html.twig',
            $this->extension->getAdminTemplate('edit', 'admin.post')
        );
    }

    public function testGetAdminTemplateFailure(): void
    {
        $this->expectException(AdminCodeNotFoundException::class);

        $this->expectExceptionMessage('Admin service "admin.non-existing" not found in admin pool. Did you mean "admin.post" or one of those: []?');

        static::assertSame(
            '@SensioLabsAdmin/CRUD/edit.html.twig',
            $this->extension->getAdminTemplate('edit', 'admin.non-existing')
        );
    }

    public function testGetGlobalTemplate(): void
    {
        static::assertSame(
            '@SensioLabsAdmin/CRUD/edit.html.twig',
            $this->extension->getGlobalTemplate('edit')
        );
    }
}
