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
use Prophecy\Argument;
use Sonata\AdminBundle\Command\GenerateAdminCommand;
use Sonata\AdminBundle\DependencyInjection\Compiler\ModelManagerCompilerPass;
use Sonata\AdminBundle\Maker\AdminMaker;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Gaurav Singh Faujdar <faujdar@gmail.com>
 */
class ModelManagerCompilerPassTest extends TestCase
{
    /**
     * @var AdminMaker
     */
    private $adminMaker;
    private $generateAdminCommand;

    public function setUp(): void
    {
        parent::setUp();
        $this->adminMaker = $this->prophesize(Definition::class);
        $this->adminMaker->replaceArgument(Argument::type('integer'), Argument::any())->shouldBeCalledTimes(1);

        $this->generateAdminCommand = $this->prophesize(Definition::class);
        $this->generateAdminCommand->replaceArgument(Argument::type('integer'), Argument::any())->shouldBeCalledTimes(1);
    }

    public function testProcess(): void
    {
        $containerBuilderMock = $this->prophesize(ContainerBuilder::class);

        $containerBuilderMock->getServiceIds()
            ->willReturn([]);

        $containerBuilderMock->getParameter(Argument::exact('kernel.bundles'))
            ->willReturn(['MakerBundle' => 'MakerBundle']);

        $containerBuilderMock->getDefinition(Argument::exact('sonata.admin.maker'))
            ->willReturn($this->adminMaker->reveal());

        $containerBuilderMock->hasDefinition(Argument::containingString('sonata.admin.manager'))
            ->willReturn(null);
        $containerBuilderMock->getDefinition(Argument::containingString('sonata.admin.manager'))
            ->willReturn(null);
        $containerBuilderMock->getParameter(Argument::containingString('kernel.project_dir'))
            ->willReturn(null);

        $containerBuilderMock->getDefinition(Argument::exact(GenerateAdminCommand::class))
            ->willReturn($this->generateAdminCommand->reveal());

        $compilerPass = new ModelManagerCompilerPass();
        $compilerPass->process($containerBuilderMock->reveal());
    }
}
