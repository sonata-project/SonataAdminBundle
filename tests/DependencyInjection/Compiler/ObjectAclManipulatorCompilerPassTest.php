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
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Command\GenerateObjectAclCommand;
use Sonata\AdminBundle\DependencyInjection\Compiler\ObjectAclManipulatorCompilerPass;
use Sonata\AdminBundle\Util\ObjectAclManipulator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Olivier Rey <olivier.rey@gmail.com>
 */
class ObjectAclManipulatorCompilerPassTest extends TestCase
{
    /**
     * @dataProvider containerDataProvider
     */
    public function testAvailableManager(ContainerBuilder $containerBuilder, string $serviceId): void
    {
        $objectAclManipulatorCompilerPass = new ObjectAclManipulatorCompilerPass();

        $objectAclManipulatorCompilerPass->process($containerBuilder);

        $availableManagers = $containerBuilder->getDefinition('sonata.admin.command.generate_object_acl')->getArgument(1);

        $this->assertArrayHasKey($serviceId, $availableManagers);
    }

    /**
     * @phpstan-return iterable<array{ContainerBuilder, string}>
     */
    public function containerDataProvider(): iterable
    {
        $serviceId = 'sonata.admin.manipulator.acl.object.orm';
        $container = $this->createContainer();
        $container
            ->register($serviceId)
            ->setClass(ObjectAclManipulator::class);

        yield [$container, $serviceId];

        $parameterName = 'sonata.admin.manipulator.acl.object.orm.class';
        $container = $this->createContainer();
        $container->setParameter($parameterName, ObjectAclManipulator::class);

        $container
            ->register($serviceId)
            ->setClass('%'.$parameterName.'%');

        yield [$container, $serviceId];
    }

    private function createContainer(): ContainerBuilder
    {
        $pool = new Pool(new Container());
        $container = new ContainerBuilder();
        $container
            ->register('sonata.admin.command.generate_object_acl')
            ->setClass(GenerateObjectAclCommand::class)
            ->setArguments([$pool, []]);

        return $container;
    }
}
