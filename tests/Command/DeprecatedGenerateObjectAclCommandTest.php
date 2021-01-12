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
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 *
 * @group legacy
 *
 * NEXT_MAJOR: Remove this class
 */
final class DeprecatedGenerateObjectAclCommandTest extends TestCase
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

    public function testExecuteWithDeprecatedDoctrineService(): void
    {
        $pool = new Pool($this->container);

        $registry = $this->createStub(RegistryInterface::class);
        $this->expectDeprecation('Passing a third argument to Sonata\AdminBundle\Command\GenerateObjectAclCommand::__construct() is deprecated since sonata-project/admin-bundle 3.77.');
        $command = new GenerateObjectAclCommand($pool, [], $registry);

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertMatchesRegularExpression('/No manipulators are implemented : ignoring/', $commandTester->getDisplay());
    }

    public function testExecuteWithEmptyManipulators(): void
    {
        $pool = new Pool($this->container);

        $registry = $this->createStub(ManagerRegistry::class);
        $this->expectDeprecation('Passing a third argument to Sonata\AdminBundle\Command\GenerateObjectAclCommand::__construct() is deprecated since sonata-project/admin-bundle 3.77.');
        $command = new GenerateObjectAclCommand($pool, [], $registry);

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertMatchesRegularExpression('/No manipulators are implemented : ignoring/', $commandTester->getDisplay());
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

        $this->expectDeprecation('Passing a third argument to Sonata\AdminBundle\Command\GenerateObjectAclCommand::__construct() is deprecated since sonata-project/admin-bundle 3.77.');
        $command = new GenerateObjectAclCommand($pool, $aclObjectManipulators, $registry);

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertMatchesRegularExpression('/Admin class is using a manager type that has no manipulator implemented : ignoring/', $commandTester->getDisplay());
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

        $this->expectDeprecation('Passing a third argument to Sonata\AdminBundle\Command\GenerateObjectAclCommand::__construct() is deprecated since sonata-project/admin-bundle 3.77.');
        $command = new GenerateObjectAclCommand($pool, $aclObjectManipulators, $registry);

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertMatchesRegularExpression('/The interface "ObjectAclManipulatorInterface" is not implemented for/', $commandTester->getDisplay());
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

        $this->expectDeprecation('Passing a third argument to Sonata\AdminBundle\Command\GenerateObjectAclCommand::__construct() is deprecated since sonata-project/admin-bundle 3.77.');
        $command = new GenerateObjectAclCommand($pool, $aclObjectManipulators, $registry);

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
    }

    public function testExecuteWithDeprecatedUserModelNotation(): void
    {
        $pool = new Pool($this->container);

        $registry = $this->createStub(ManagerRegistry::class);
        $this->expectDeprecation('Passing a third argument to Sonata\AdminBundle\Command\GenerateObjectAclCommand::__construct() is deprecated since sonata-project/admin-bundle 3.77.');
        $command = new GenerateObjectAclCommand($pool, [], $registry);

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);

        $this->expectDeprecation(
            'Passing a model shortcut name ("AppBundle:User" given) as "user_model" option is deprecated'
            .' since sonata-project/admin-bundle 3.77 and will throw an exception in 4.0.'
            .' Pass a fully qualified class name instead (e.g. App\Model\User).'
        );
        $commandTester->execute([
            'command' => $command->getName(),
            '--user_model' => 'AppBundle:User',
        ]);
    }

    public function testExecuteWithDeprecatedUserModelNotationAndWithoutDoctrineService(): void
    {
        $pool = new Pool($this->container);

        $command = new GenerateObjectAclCommand($pool, []);

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);

        $this->expectDeprecation(
            'Passing a model shortcut name ("AppBundle:User" given) as "user_model" option is deprecated'
            .' since sonata-project/admin-bundle 3.77 and will throw an exception in 4.0.'
            .' Pass a fully qualified class name instead (e.g. App\Model\User).'
        );

        $commandTester->execute([
            'command' => $command->getName(),
            '--user_model' => 'AppBundle:User',
        ]);

        $this->assertMatchesRegularExpression(sprintf('/The command "%s" has a dependency on a non-existent service "doctrine"./', GenerateObjectAclCommand::getDefaultName()), $commandTester->getDisplay());
    }

    public function testExecuteWithDeprecatedUserModelNotationAndInternalSetter(): void
    {
        $pool = new Pool($this->container);

        $registry = $this->createStub(ManagerRegistry::class);
        $command = new GenerateObjectAclCommand($pool, []);
        $command->setRegistry($registry);

        $application = new Application();
        $application->add($command);

        $command = $application->find(GenerateObjectAclCommand::getDefaultName());
        $commandTester = new CommandTester($command);

        $this->expectDeprecation(
            'Passing a model shortcut name ("AppBundle:User" given) as "user_model" option is deprecated'
            .' since sonata-project/admin-bundle 3.77 and will throw an exception in 4.0.'
            .' Pass a fully qualified class name instead (e.g. App\Model\User).'
        );
        $commandTester->execute([
            'command' => $command->getName(),
            '--user_model' => 'AppBundle:User',
        ]);

        $this->assertMatchesRegularExpression('/No manipulators are implemented : ignoring/', $commandTester->getDisplay());
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

        $this->expectDeprecation('Passing a third argument to Sonata\AdminBundle\Command\GenerateObjectAclCommand::__construct() is deprecated since sonata-project/admin-bundle 3.77.');
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
