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

namespace Sonata\AdminBundle\Tests\Command;

use Doctrine\Common\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Command\GenerateObjectAclCommand;
use Sonata\AdminBundle\Util\ObjectAclManipulatorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class GenerateObjectAclCommandTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
    }

    public function testExecuteWithoutDoctrineService(): void
    {
        $generateObjectAclCommand = new GenerateObjectAclCommand(new Pool($this->container, '', ''), []);

        $application = new Application();
        $application->add($generateObjectAclCommand);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);

        $this->assertFalse($this->container->has('doctrine'));

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('The command "%s" has a dependency on a non-existent service "doctrine".', GenerateObjectAclCommand::getDefaultName()));

        $commandTester->execute(['command' => GenerateObjectAclCommand::getDefaultName()]);
    }

    public function testExecuteWithDeprecatedDoctrineService(): void
    {
        $pool = new Pool($this->container, '', '');

        $registry = $this->prophesize(RegistryInterface::class)->reveal();
        $command = new GenerateObjectAclCommand($pool, [], $registry);

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('/No manipulators are implemented : ignoring/', $commandTester->getDisplay());
    }

    public function testExecuteWithEmptyManipulators(): void
    {
        $pool = new Pool($this->container, '', '');

        $registry = $this->prophesize(ManagerRegistry::class)->reveal();
        $command = new GenerateObjectAclCommand($pool, [], $registry);

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('/No manipulators are implemented : ignoring/', $commandTester->getDisplay());
    }

    public function testExecuteWithManipulatorNotFound(): void
    {
        $admin = $this->prophesize(AbstractAdmin::class);
        $registry = $this->prophesize(ManagerRegistry::class);
        $pool = $this->prophesize(Pool::class);

        $admin->getManagerType(Argument::any())->willReturn('bar');

        $pool->getAdminServiceIds()->willReturn(['acme.admin.foo']);

        $pool->getInstance(Argument::any())->willReturn($admin->reveal());

        $aclObjectManipulators = [
            'bar' => new \stdClass(),
        ];

        $command = new GenerateObjectAclCommand($pool->reveal(), $aclObjectManipulators, $registry->reveal());

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('/Admin class is using a manager type that has no manipulator implemented : ignoring/', $commandTester->getDisplay());
    }

    public function testExecuteWithManipulatorNotObjectAclManipulatorInterface(): void
    {
        $admin = $this->prophesize(AbstractAdmin::class);
        $registry = $this->prophesize(ManagerRegistry::class);
        $pool = $this->prophesize(Pool::class);

        $admin->getManagerType(Argument::any())->willReturn('bar');

        $pool->getAdminServiceIds()->willReturn(['acme.admin.foo']);
        $pool->getInstance(Argument::any())->willReturn($admin->reveal());

        $aclObjectManipulators = [
            'sonata.admin.manipulator.acl.object.bar' => new \stdClass(),
        ];

        $command = new GenerateObjectAclCommand($pool->reveal(), $aclObjectManipulators, $registry->reveal());

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('/The interface "ObjectAclManipulatorInterface" is not implemented for/', $commandTester->getDisplay());
    }

    public function testExecuteWithManipulator(): void
    {
        $admin = $this->prophesize(AbstractAdmin::class);
        $registry = $this->prophesize(ManagerRegistry::class);
        $pool = $this->prophesize(Pool::class);

        $admin->getManagerType(Argument::any())->willReturn('bar');
        $admin = $admin->reveal();

        $pool->getAdminServiceIds()->willReturn(['acme.admin.foo']);
        $pool->getInstance(Argument::any())->willReturn($admin);

        $manipulator = $this->prophesize(ObjectAclManipulatorInterface::class);
        $manipulator
            ->batchConfigureAcls(Argument::type(StreamOutput::class), Argument::is($admin), null)
            ->shouldBeCalledTimes(1);

        $aclObjectManipulators = [
            'sonata.admin.manipulator.acl.object.bar' => $manipulator->reveal(),
        ];

        $command = new GenerateObjectAclCommand($pool->reveal(), $aclObjectManipulators, $registry->reveal());

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
    }
}
