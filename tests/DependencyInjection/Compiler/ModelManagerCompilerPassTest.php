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
use Prophecy\Argument;
use Sonata\AdminBundle\DependencyInjection\Compiler\ModelManagerCompilerPass;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\App\Model\ModelManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * @author Gaurav Singh Faujdar <faujdar@gmail.com>
 */
final class ModelManagerCompilerPassTest extends TestCase
{
    public function testProcess(): void
    {
        $adminMaker = $this->prophesize(Definition::class);
        $adminMaker->replaceArgument(Argument::type('integer'), Argument::any())->shouldNotBeCalled();
        $adminMaker->hasTag(Argument::exact(ModelManagerCompilerPass::MANAGER_TAG))
            ->willReturn(false);
        $containerBuilderMock = $this->prophesize(ContainerBuilder::class);

        $containerBuilderMock->getServiceIds()
            ->willReturn([]);

        $containerBuilderMock->getDefinitions()
            ->willReturn([]);

        $containerBuilderMock->findTaggedServiceIds(Argument::exact(ModelManagerCompilerPass::MANAGER_TAG))
            ->willReturn([]);

        $containerBuilderMock->getParameter(Argument::exact('kernel.bundles'))
            ->willReturn(['MakerBundle' => 'MakerBundle']);

        $containerBuilderMock->getDefinition(Argument::exact('sonata.admin.maker'))
            ->willReturn($adminMaker->reveal());
        $containerBuilderMock->getParameter(Argument::containingString('kernel.project_dir'))
            ->willReturn(null);

        $compilerPass = new ModelManagerCompilerPass();
        $compilerPass->process($containerBuilderMock->reveal());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     *
     * @expectedDeprecation Not setting the "sonata.admin.manager" tag on the "sonata.admin.manager.test" service is deprecated since sonata-project/admin-bundle 3.60.
     */
    public function testProcessWithUntaggedManagerDefinition(): void
    {
        $adminMaker = $this->prophesize(Definition::class);
        $adminMaker->replaceArgument(Argument::type('integer'), Argument::any())->shouldBeCalledTimes(1);
        $adminMaker->hasTag(Argument::exact(ModelManagerCompilerPass::MANAGER_TAG))
            ->willReturn(false);
        $containerBuilderMock = $this->prophesize(ContainerBuilder::class);

        $containerBuilderMock->getServiceIds()
            ->willReturn(['sonata.admin.manager.test']);

        $definitionMock = $this->prophesize(Definition::class);

        $containerBuilderMock->getDefinitions()
            ->willReturn([
                'sonata.admin.maker' => $adminMaker->reveal(),
                'sonata.admin.manager.test' => $definitionMock->reveal(),
            ]);

        $definitionMock->getClass()
            ->willReturn(ModelManager::class);

        $definitionMock->hasTag(Argument::exact(ModelManagerCompilerPass::MANAGER_TAG))
            ->willReturn(false);

        $definitionMock->addTag(Argument::exact(ModelManagerCompilerPass::MANAGER_TAG))
            ->willReturn(null);

        $containerBuilderMock->findTaggedServiceIds(Argument::exact(ModelManagerCompilerPass::MANAGER_TAG))
            ->willReturn(['sonata.admin.manager.test' => ['other.tag']]);

        $containerBuilderMock->getParameter(Argument::exact('kernel.bundles'))
            ->willReturn(['MakerBundle' => 'MakerBundle']);

        $containerBuilderMock->findDefinition(Argument::exact('sonata.admin.manager.test'))
            ->willReturn($definitionMock->reveal());

        $containerBuilderMock->getDefinition(Argument::exact('sonata.admin.maker'))
            ->willReturn($adminMaker->reveal());

        $containerBuilderMock->getParameter(Argument::containingString('kernel.project_dir'))
            ->willReturn(null);

        $compilerPass = new ModelManagerCompilerPass();
        $compilerPass->process($containerBuilderMock->reveal());
    }

    public function testProcessWithTaggedManagerDefinition(): void
    {
        $adminMaker = $this->prophesize(Definition::class);
        $adminMaker->replaceArgument(Argument::type('integer'), Argument::any())->shouldBeCalledTimes(1);
        $adminMaker->hasTag(Argument::exact(ModelManagerCompilerPass::MANAGER_TAG))
            ->willReturn(false);
        $containerBuilderMock = $this->prophesize(ContainerBuilder::class);

        $containerBuilderMock->getServiceIds()
            ->willReturn(['sonata.admin.manager.test']);

        $definitionMock = $this->prophesize(Definition::class);

        $containerBuilderMock->getDefinitions()
            ->willReturn([
                'sonata.admin.maker' => $adminMaker->reveal(),
                'sonata.admin.manager.test' => $definitionMock->reveal(),
            ]);

        $definitionMock->getClass()
            ->willReturn(ModelManager::class);

        $definitionMock->hasTag(Argument::exact(ModelManagerCompilerPass::MANAGER_TAG))
            ->willReturn(true);

        $containerBuilderMock->findTaggedServiceIds(Argument::exact(ModelManagerCompilerPass::MANAGER_TAG))
            ->willReturn(['sonata.admin.manager.test' => [ModelManagerCompilerPass::MANAGER_TAG, 'other.tag']]);

        $containerBuilderMock->getParameter(Argument::exact('kernel.bundles'))
            ->willReturn(['MakerBundle' => 'MakerBundle']);

        $containerBuilderMock->findDefinition(Argument::exact('sonata.admin.manager.test'))
            ->willReturn($definitionMock->reveal());

        $containerBuilderMock->getDefinition(Argument::exact('sonata.admin.maker'))
            ->willReturn($adminMaker->reveal());

        $containerBuilderMock->getParameter(Argument::containingString('kernel.project_dir'))
            ->willReturn(null);

        $compilerPass = new ModelManagerCompilerPass();
        $compilerPass->process($containerBuilderMock->reveal());
    }

    public function testProcessWithInvalidTaggedManagerDefinition(): void
    {
        $adminMaker = $this->prophesize(Definition::class);
        $adminMaker->replaceArgument(Argument::type('integer'), Argument::any())->shouldNotBeCalled();
        $adminMaker->hasTag(Argument::exact(ModelManagerCompilerPass::MANAGER_TAG))
            ->willReturn(false);
        $containerBuilderMock = $this->prophesize(ContainerBuilder::class);

        $containerBuilderMock->getServiceIds()
            ->willReturn(['sonata.admin.manager.test']);

        $definitionMock = $this->prophesize(Definition::class);

        $containerBuilderMock->getDefinitions()
            ->willReturn([
                'sonata.admin.maker' => $adminMaker->reveal(),
                'sonata.admin.manager.test' => $definitionMock->reveal(),
            ]);

        $definitionMock->getClass()
            ->willReturn(\stdClass::class);

        $definitionMock->hasTag(Argument::exact(ModelManagerCompilerPass::MANAGER_TAG))
            ->willReturn(true);

        $containerBuilderMock->findTaggedServiceIds(Argument::exact(ModelManagerCompilerPass::MANAGER_TAG))
            ->willReturn(['sonata.admin.manager.test' => [ModelManagerCompilerPass::MANAGER_TAG, 'other.tag']]);

        $containerBuilderMock->getParameter(Argument::exact('kernel.bundles'))
            ->willReturn(['MakerBundle' => 'MakerBundle']);

        $containerBuilderMock->findDefinition(Argument::exact('sonata.admin.manager.test'))
            ->willReturn($definitionMock->reveal());

        $containerBuilderMock->getDefinition(Argument::exact('sonata.admin.maker'))
            ->willReturn($adminMaker->reveal());

        $containerBuilderMock->getParameter(Argument::containingString('kernel.project_dir'))
            ->willReturn(null);

        $compilerPass = new ModelManagerCompilerPass();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf('Service "sonata.admin.manager.test" must implement `%s`.', ModelManagerInterface::class));

        $compilerPass->process($containerBuilderMock->reveal());
    }
}
