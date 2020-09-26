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

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Command\GenerateObjectAclCommand;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Sonata\AdminBundle\Util\ObjectAclManipulatorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class GenerateObjectAclCommandTest extends TestCase
{
    use ExpectDeprecationTrait;

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

        $registry = $this->createStub(RegistryInterface::class);
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

        $registry = $this->createStub(ManagerRegistry::class);
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
        $admin = $this->createStub(AbstractAdmin::class);
        $registry = $this->createStub(ManagerRegistry::class);
        $pool = $this->createStub(Pool::class);

        $admin
            ->method('getManagerType')
            ->willReturn('bar');

        $pool
            ->method('getAdminServiceIds')
            ->willReturn(['acme.admin.foo']);

        $pool
            ->method('getInstance')
            ->willReturn($admin);

        $aclObjectManipulators = [
            'bar' => new \stdClass(),
        ];

        $command = new GenerateObjectAclCommand($pool, $aclObjectManipulators, $registry);

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('/Admin class is using a manager type that has no manipulator implemented : ignoring/', $commandTester->getDisplay());
    }

    public function testExecuteWithManipulatorNotObjectAclManipulatorInterface(): void
    {
        $admin = $this->createStub(AbstractAdmin::class);
        $registry = $this->createStub(ManagerRegistry::class);
        $pool = $this->createStub(Pool::class);

        $admin
            ->method('getManagerType')
            ->willReturn('bar');

        $pool
            ->method('getAdminServiceIds')
            ->willReturn(['acme.admin.foo']);

        $pool
            ->method('getInstance')
            ->willReturn($admin);

        $aclObjectManipulators = [
            'sonata.admin.manipulator.acl.object.bar' => new \stdClass(),
        ];

        $command = new GenerateObjectAclCommand($pool, $aclObjectManipulators, $registry);

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('/The interface "ObjectAclManipulatorInterface" is not implemented for/', $commandTester->getDisplay());
    }

    public function testExecuteWithManipulator(): void
    {
        $admin = $this->createStub(AbstractAdmin::class);
        $registry = $this->createStub(ManagerRegistry::class);
        $pool = $this->createStub(Pool::class);

        $admin
            ->method('getManagerType')
            ->willReturn('bar');

        $pool
            ->method('getAdminServiceIds')
            ->willReturn(['acme.admin.foo']);

        $pool
            ->method('getInstance')
            ->willReturn($admin);

        $manipulator = $this->createMock(ObjectAclManipulatorInterface::class);
        $manipulator
            ->expects($this->once())
            ->method('batchConfigureAcls')
            ->with($this->isInstanceOf(StreamOutput::class), $admin, null);

        $aclObjectManipulators = [
            'sonata.admin.manipulator.acl.object.bar' => $manipulator,
        ];

        $command = new GenerateObjectAclCommand($pool, $aclObjectManipulators, $registry);

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testExecuteWithDeprecatedUserModelNotation(): void
    {
        $pool = new Pool($this->container, '', '');

        $registry = $this->createStub(ManagerRegistry::class);
        $command = new GenerateObjectAclCommand($pool, [], $registry);

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);

        $this->expectDeprecation(
            'Passing a model shortcut name ("AppBundle:User" given) as "user_model" option is deprecated'
            .' since sonata-project/admin-bundle 3.x and will throw an exception in 4.x.'
            .' Pass a fully qualified class name instead (e.g. App\Model\User).'
        );
        $commandTester->execute([
            'command' => $command->getName(),
            '--user_model' => 'AppBundle:User',
        ]);
    }

    public function testExecuteWithUserModel(): void
    {
        $admin = $this->createStub(AbstractAdmin::class);
        $registry = $this->createStub(ManagerRegistry::class);
        $pool = $this->createStub(Pool::class);

        $admin
            ->method('getManagerType')
            ->willReturn('bar');

        $pool
            ->method('getAdminServiceIds')
            ->willReturn(['acme.admin.foo']);

        $pool
            ->method('getInstance')
            ->willReturn($admin);

        $manipulator = $this->createMock(ObjectAclManipulatorInterface::class);
        $manipulator
            ->expects($this->once())
            ->method('batchConfigureAcls')
            ->with(
                $this->isInstanceOf(StreamOutput::class),
                $admin,
                $this->callback(static function (UserSecurityIdentity $userSecurityIdentity): bool {
                    return Foo::class === $userSecurityIdentity->getClass();
                })
            );

        $aclObjectManipulators = [
            'sonata.admin.manipulator.acl.object.bar' => $manipulator,
        ];

        $command = new GenerateObjectAclCommand($pool, $aclObjectManipulators, $registry);

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--user_model' => Foo::class,
            '--object_owner' => true,
        ]);
    }
}
