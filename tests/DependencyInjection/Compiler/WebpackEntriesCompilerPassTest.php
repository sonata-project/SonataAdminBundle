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
use Sonata\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class WebpackEntriesCompilerPassTest extends TestCase
{
    private $container;

    private $definition;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerBuilder::class);
        $this->definition = $this->createMock(Definition::class);
    }

    public function testProcess(): void
    {
        $expectedEntries = [
            'app' => '%kernel.project_dir%/public/build',
            'sonata_admin' => '%kernel.project_dir%/public/bundles/sonataadmin/dist'
        ];

        $webpackConfig = [
            [
                'output_path' => '%kernel.project_dir%/public/build',
                'builds' => [
                    'app' => '%kernel.project_dir%/public/build',
                ]
            ],
            [
                'builds' => [
                    'sonata_admin' => '%kernel.project_dir%/public/bundles/sonataadmin/dist'
                ]
            ]
        ];

        $this->container
            ->expects($this->once())
            ->method('getExtensionConfig')
            ->with('webpack_encore')
            ->willReturn($webpackConfig);

        $this->definition
            ->expects($this->once())
            ->method('addMethodCall')
            ->with('addGlobal', ['webpack_encore_entries', $expectedEntries])
            ->willReturnSelf();

        $this->container
            ->expects($this->once())
            ->method('getDefinition')
            ->with('twig')
            ->willReturn($this->definition);
    }

    public function testProcessBuildNotSet(): void
    {
        $expectedEntries = [
            'sonata_admin' => '%kernel.project_dir%/public/bundles/sonataadmin/dist'
        ];

        $webpackConfig = [
            [
                'output_path' => '%kernel.project_dir%/public/build',
            ],
            [
                'builds' => [
                    'sonata_admin' => '%kernel.project_dir%/public/bundles/sonataadmin/dist'
                ]
            ]
        ];

        $this->container
            ->expects($this->once())
            ->method('getExtensionConfig')
            ->with('webpack_encore')
            ->willReturn($webpackConfig);

        $this->definition
            ->expects($this->once())
            ->method('addMethodCall')
            ->with('addGlobal', ['webpack_encore_entries', $expectedEntries])
            ->willReturnSelf();

        $this->container
            ->expects($this->once())
            ->method('getDefinition')
            ->with('twig')
            ->willReturn($this->definition);
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
            ->expects($this->nwever())
            ->method('addMethodCall');

        $this->container
            ->expects($this->never())
            ->method('getDefinition');
    }
}
