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

namespace Sonata\AdminBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\DependencyInjection\Compiler\WebpackEntriesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @group foo
 */
class WebpackEntriesCompilerPassTest extends TestCase
{
    private $container;

    private $definition;

    private $compilerPass;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerBuilder::class);
        $this->definition = $this->createMock(Definition::class);

        $this->compilerPass = new WebpackEntriesCompilerPass();
    }

    public function testProcess(): void
    {
        $expectedEntries = [
            'app' => '%kernel.project_dir%/public/build',
            'sonata_admin' => '%kernel.project_dir%/public/bundles/sonataadmin/dist',
        ];

        $webpackConfig = [
            [
                'output_path' => '%kernel.project_dir%/public/build',
                'builds' => [
                    'app' => '%kernel.project_dir%/public/build',
                ],
            ],
            [
                'builds' => [
                    'sonata_admin' => '%kernel.project_dir%/public/bundles/sonataadmin/dist',
                ],
            ],
        ];

        $this->container
            ->expects($this->once())
            ->method('getExtensionConfig')
            ->with('webpack_encore')
            ->willReturn($webpackConfig);

        $this->definition
            ->expects($this->once())
            ->method('addMethodCall')
            ->with('addGlobal', ['sonata_admin_webpack_entries', $expectedEntries])
            ->willReturnSelf();

        $this->container
            ->expects($this->once())
            ->method('getDefinition')
            ->with('twig')
            ->willReturn($this->definition);

        $this->compilerPass->process($this->container);
    }

    public function testProcessBuildNotSet(): void
    {
        $expectedEntries = [
            'sonata_admin' => '%kernel.project_dir%/public/bundles/sonataadmin/dist',
        ];

        $webpackConfig = [
            [
                'output_path' => '%kernel.project_dir%/public/build',
            ],
            [
                'builds' => [
                    'sonata_admin' => '%kernel.project_dir%/public/bundles/sonataadmin/dist',
                ],
            ],
        ];

        $this->container
            ->expects($this->once())
            ->method('getExtensionConfig')
            ->with('webpack_encore')
            ->willReturn($webpackConfig);

        $this->definition
            ->expects($this->once())
            ->method('addMethodCall')
            ->with('addGlobal', ['sonata_admin_webpack_entries', $expectedEntries])
            ->willReturnSelf();

        $this->container
            ->expects($this->once())
            ->method('getDefinition')
            ->with('twig')
            ->willReturn($this->definition);

        $this->compilerPass->process($this->container);
    }

    public function testProcessEmptyEntries(): void
    {
        $webpackConfig = [];

        $this->container
            ->expects($this->once())
            ->method('getExtensionConfig')
            ->with('webpack_encore')
            ->willReturn($webpackConfig);

        $this->definition
            ->expects($this->never())
            ->method('addMethodCall');

        $this->container
            ->expects($this->never())
            ->method('getDefinition');

        $this->compilerPass->process($this->container);
    }
}
