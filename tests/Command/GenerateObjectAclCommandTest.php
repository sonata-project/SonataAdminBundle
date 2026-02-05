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

namespace SensioLabs\AdminBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AbstractAdmin;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Command\GenerateObjectAclCommand;
use SensioLabs\AdminBundle\Tests\Fixtures\Entity\Foo;
use SensioLabs\AdminBundle\Util\ObjectAclManipulatorInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class GenerateObjectAclCommandTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
    }

    public function testExecuteWithDeprecatedDoctrineService(): void
    {
        $pool = new Pool($this->container);

        $command = new GenerateObjectAclCommand($pool, []);

        $application = new Application();
        CommandHelper::addCommandToApplication($application, $command);

        $command = $application->find('sonata:admin:generate-object-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        static::assertMatchesRegularExpression('/No manipulators are implemented : ignoring/', $commandTester->getDisplay());
    }

    public function testExecuteWithEmptyManipulators(): void
    {
        $pool = new Pool($this->container);

        $command = new GenerateObjectAclCommand($pool, []);

        $application = new Application();
        CommandHelper::addCommandToApplication($application, $command);

        $command = $application->find('sonata:admin:generate-object-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        static::assertMatchesRegularExpression('/No manipulators are implemented : ignoring/', $commandTester->getDisplay());
    }

    public function testExecuteWithManipulatorNotFound(): void
    {
        $admin = static::createStub(AbstractAdmin::class);
        $container = new Container();
        $container->set('acme.admin.foo', $admin);
        $pool = new Pool($container, ['acme.admin.foo']);

        $admin->setManagerType('bar');

        $aclObjectManipulators = [
            'bar' => $this->createMock(ObjectAclManipulatorInterface::class),
        ];

        $command = new GenerateObjectAclCommand($pool, $aclObjectManipulators);

        $application = new Application();
        CommandHelper::addCommandToApplication($application, $command);

        $command = $application->find('sonata:admin:generate-object-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        static::assertMatchesRegularExpression('/Admin class is using a manager type that has no manipulator implemented : ignoring/', $commandTester->getDisplay());
    }

    public function testExecuteWithManipulator(): void
    {
        $admin = static::createStub(AbstractAdmin::class);
        $container = new Container();
        $container->set('acme.admin.foo', $admin);
        $pool = new Pool($container, ['acme.admin.foo']);

        $admin->setManagerType('bar');

        $manipulator = $this->createMock(ObjectAclManipulatorInterface::class);
        $manipulator->expects(static::once())->method('batchConfigureAcls')
            ->with(static::isInstanceOf(StreamOutput::class), $admin, null);

        $aclObjectManipulators = [
            'sensiolabs.admin.manipulator.acl.object.bar' => $manipulator,
        ];

        $command = new GenerateObjectAclCommand($pool, $aclObjectManipulators);

        $application = new Application();
        CommandHelper::addCommandToApplication($application, $command);

        $command = $application->find('sonata:admin:generate-object-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
    }

    public function testExecuteWithUserModel(): void
    {
        $admin = static::createStub(AbstractAdmin::class);
        $container = new Container();
        $container->set('acme.admin.foo', $admin);
        $pool = new Pool($container, ['acme.admin.foo']);

        $admin->setManagerType('bar');

        $manipulator = $this->createMock(ObjectAclManipulatorInterface::class);
        $manipulator
            ->expects(static::once())
            ->method('batchConfigureAcls')
            ->with(
                static::isInstanceOf(StreamOutput::class),
                $admin,
                static::callback(static fn (UserSecurityIdentity $userSecurityIdentity): bool => Foo::class === $userSecurityIdentity->getClass())
            );

        $aclObjectManipulators = [
            'sensiolabs.admin.manipulator.acl.object.bar' => $manipulator,
        ];

        $command = new GenerateObjectAclCommand($pool, $aclObjectManipulators);

        $application = new Application();
        CommandHelper::addCommandToApplication($application, $command);

        $command = $application->find('sonata:admin:generate-object-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--user_model' => Foo::class,
            '--object_owner' => true,
        ]);
    }
}
