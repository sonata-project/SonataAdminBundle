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
use Sonata\AdminBundle\DependencyInjection\Compiler\ModelManagerCompilerPass;
use Sonata\AdminBundle\Maker\AdminMaker;
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
        $adminMaker = new Definition(AdminMaker::class);
        $adminMaker->setArguments([
            '',
            [],
        ]);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setDefinition('sonata.admin.maker', $adminMaker);
        $containerBuilder->setParameter('kernel.bundles', ['MakerBundle' => 'MakerBundle']);

        $compilerPass = new ModelManagerCompilerPass();
        $compilerPass->process($containerBuilder);

        $this->assertCount(0, $adminMaker->getArgument(1));
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
        $adminMaker = new Definition(AdminMaker::class);
        $adminMaker->setArguments([
            '',
            [],
        ]);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setDefinition('sonata.admin.maker', $adminMaker);
        $containerBuilder->setParameter('kernel.bundles', ['MakerBundle' => 'MakerBundle']);

        $managerDefinition = new Definition(ModelManager::class);
        $containerBuilder->setDefinition('sonata.admin.manager.test', $managerDefinition);

        $compilerPass = new ModelManagerCompilerPass();
        $compilerPass->process($containerBuilder);

        $this->assertCount(1, $adminMaker->getArgument(1));
    }

    public function testProcessWithTaggedManagerDefinition(): void
    {
        $adminMaker = new Definition(AdminMaker::class);
        $adminMaker->setArguments([
            '',
            [],
        ]);
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.bundles', ['MakerBundle' => 'MakerBundle']);
        $containerBuilder->setDefinition('sonata.admin.maker', $adminMaker);

        $managerDefinition = new Definition(ModelManager::class);
        $managerDefinition->addTag(ModelManagerCompilerPass::MANAGER_TAG);

        $containerBuilder->setDefinition('sonata.admin.manager.test', $managerDefinition);

        $compilerPass = new ModelManagerCompilerPass();
        $compilerPass->process($containerBuilder);

        $this->assertCount(1, $adminMaker->getArgument(1));
    }

    public function testProcessWithInvalidTaggedManagerDefinition(): void
    {
        $adminMaker = new Definition(AdminMaker::class);
        $adminMaker->setArguments([
            '',
            [],
        ]);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.bundles', ['MakerBundle' => 'MakerBundle']);
        $containerBuilder->setDefinition('sonata.admin.maker', $adminMaker);

        $managerDefinition = new Definition(\stdClass::class);
        $managerDefinition->addTag(ModelManagerCompilerPass::MANAGER_TAG);

        $containerBuilder->setDefinition('sonata.admin.manager.test', $managerDefinition);

        $compilerPass = new ModelManagerCompilerPass();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf('Service "sonata.admin.manager.test" must implement `%s`.', ModelManagerInterface::class));

        $compilerPass->process($containerBuilder);
    }
}
