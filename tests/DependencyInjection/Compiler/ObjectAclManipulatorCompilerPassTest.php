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
use Sonata\AdminBundle\Command\GenerateObjectAclCommand;
use Sonata\AdminBundle\DependencyInjection\Compiler\ObjectAclManipulatorCompilerPass;
use Sonata\AdminBundle\Util\ObjectAclManipulator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Olivier Rey
 */
class ObjectAclManipulatorCompilerPassTest extends TestCase
{
    public function testAvailableManagerFromServiceDefinitionClass(): void
    {
        $containerBuilder = $this->getContainerWithServiceClass();

        $objectAclManipulatorCompilerPass = new ObjectAclManipulatorCompilerPass();

        $objectAclManipulatorCompilerPass->process($containerBuilder);

        $availableManagers = $containerBuilder->getDefinition(GenerateObjectAclCommand::class)->getArgument(1);

        $this->assertArrayHasKey('sonata.admin.manipulator.acl.object.orm', $availableManagers);
    }

    public function testAvailableManagerFromServiceDefinitionWithParameterClass(): void
    {
        $containerBuilder = $this->getContainerWithParameterAsServiceClass();

        $objectAclManipulatorCompilerPass = new ObjectAclManipulatorCompilerPass();

        $objectAclManipulatorCompilerPass->process($containerBuilder);

        $availableManagers = $containerBuilder->getDefinition(GenerateObjectAclCommand::class)->getArgument(1);

        $this->assertArrayHasKey('sonata.admin.manipulator.acl.object.orm', $availableManagers);
    }

    private function getContainerWithServiceClass(): ContainerBuilder
    {
        $container = $this->getContainer();

        $container
            ->register('sonata.admin.manipulator.acl.object.orm')
            ->setClass(ObjectAclManipulator::class);

        return $container;
    }

    private function getContainerWithParameterAsServiceClass(): ContainerBuilder
    {
        $container = $this->getContainer();

        $container->setParameter('sonata.admin.manipulator.acl.object.orm.class', ObjectAclManipulator::class);

        $container
            ->register('sonata.admin.manipulator.acl.object.orm')
            ->setClass('%sonata.admin.manipulator.acl.object.orm.class%');

        return $container;
    }

    private function getContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $container
            ->register('Sonata\AdminBundle\Command\GenerateObjectAclCommand')
            ->setClass(GenerateObjectAclCommand::class)
            ->setArguments(['', []]);

        return $container;
    }
}
