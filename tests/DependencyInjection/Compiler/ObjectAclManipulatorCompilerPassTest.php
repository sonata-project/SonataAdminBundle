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

namespace SensioLabs\AdminBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Command\GenerateObjectAclCommand;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\ObjectAclManipulatorCompilerPass;
use SensioLabs\AdminBundle\Util\ObjectAclManipulator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Olivier Rey <olivier.rey@gmail.com>
 */
final class ObjectAclManipulatorCompilerPassTest extends TestCase
{
    #[DataProvider('provideAvailableManagerCases')]
    public function testAvailableManager(ContainerBuilder $containerBuilder, string $serviceId): void
    {
        $objectAclManipulatorCompilerPass = new ObjectAclManipulatorCompilerPass();

        $objectAclManipulatorCompilerPass->process($containerBuilder);

        $availableManagers = $containerBuilder->getDefinition('sonata.admin.command.generate_object_acl')->getArgument(1);

        static::assertIsArray($availableManagers);
        static::assertArrayHasKey($serviceId, $availableManagers);
    }

    /**
     * @phpstan-return iterable<array-key, array{ContainerBuilder, string}>
     */
    public static function provideAvailableManagerCases(): iterable
    {
        $serviceId = 'sonata.admin.manipulator.acl.object.orm';
        $container = static::createContainer();
        $container
            ->register($serviceId)
            ->setClass(ObjectAclManipulator::class);

        yield [$container, $serviceId];

        $parameterName = 'sonata.admin.manipulator.acl.object.orm.class';
        $container = static::createContainer();
        $container->setParameter($parameterName, ObjectAclManipulator::class);

        $container
            ->register($serviceId)
            ->setClass('%'.$parameterName.'%');

        yield [$container, $serviceId];
    }

    private static function createContainer(): ContainerBuilder
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
