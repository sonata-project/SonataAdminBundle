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

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Command\GenerateObjectAclCommand;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Sonata\AdminBundle\Util\ObjectAclManipulatorInterface;
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

        $command = new GenerateObjectAclCommand($pool, []);

        $application = new Application();
        $application->add($command);

        $command = $application->find('sonata:admin:generate-object-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        self::assertMatchesRegularExpression('/No manipulators are implemented : ignoring/', $commandTester->getDisplay());
    }

    public function testExecuteWithEmptyManipulators(): void
    {
        $pool = new Pool($this->container);

        $command = new GenerateObjectAclCommand($pool, []);

        $application = new Application();
        $application->add($command);

        $command = $application->find('sonata:admin:generate-object-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        self::assertMatchesRegularExpression('/No manipulators are implemented : ignoring/', $commandTester->getDisplay());
    }

    public function testExecuteWithManipulatorNotFound(): void
    {
        $admin = $this->createStub(AbstractAdmin::class);
        $container = new Container();
        $container->set('acme.admin.foo', $admin);
        $pool = new Pool($container, ['acme.admin.foo']);

        $admin->setManagerType('bar');

        $aclObjectManipulators = [
            'bar' => $this->createMock(ObjectAclManipulatorInterface::class),
        ];

        $command = new GenerateObjectAclCommand($pool, $aclObjectManipulators);

        $application = new Application();
        $application->add($command);

        $command = $application->find('sonata:admin:generate-object-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        self::assertMatchesRegularExpression('/Admin class is using a manager type that has no manipulator implemented : ignoring/', $commandTester->getDisplay());
    }

    /**
     * @psalm-suppress InvalidArgument
     */
    public function testExecuteWithManipulatorNotObjectAclManipulatorInterface(): void
    {
        $admin = $this->createStub(AbstractAdmin::class);
        $container = new Container();
        $container->set('acme.admin.foo', $admin);
        $pool = new Pool($container, ['acme.admin.foo']);

        $admin->setManagerType('bar');

        $aclObjectManipulators = [
            'sonata.admin.manipulator.acl.object.bar' => new \stdClass(),
        ];

        // @phpstan-ignore-next-line
        $command = new GenerateObjectAclCommand($pool, $aclObjectManipulators);

        $application = new Application();
        $application->add($command);

        $command = $application->find('sonata:admin:generate-object-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        self::assertMatchesRegularExpression('/The interface "ObjectAclManipulatorInterface" is not implemented for/', $commandTester->getDisplay());
    }

    public function testExecuteWithManipulator(): void
    {
        $admin = $this->createStub(AbstractAdmin::class);
        $container = new Container();
        $container->set('acme.admin.foo', $admin);
        $pool = new Pool($container, ['acme.admin.foo']);

        $admin->setManagerType('bar');

        $manipulator = $this->createMock(ObjectAclManipulatorInterface::class);
        $manipulator->expects(self::once())->method('batchConfigureAcls')
            ->with(self::isInstanceOf(StreamOutput::class), $admin, null);

        $aclObjectManipulators = [
            'sonata.admin.manipulator.acl.object.bar' => $manipulator,
        ];

        $command = new GenerateObjectAclCommand($pool, $aclObjectManipulators);

        $application = new Application();
        $application->add($command);

        $command = $application->find('sonata:admin:generate-object-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
    }

    public function testExecuteWithUserModel(): void
    {
        $admin = $this->createStub(AbstractAdmin::class);
        $container = new Container();
        $container->set('acme.admin.foo', $admin);
        $pool = new Pool($container, ['acme.admin.foo']);

        $admin->setManagerType('bar');

        $manipulator = $this->createMock(ObjectAclManipulatorInterface::class);
        $manipulator
            ->expects(self::once())
            ->method('batchConfigureAcls')
            ->with(
                self::isInstanceOf(StreamOutput::class),
                $admin,
                self::callback(static function (UserSecurityIdentity $userSecurityIdentity): bool {
                    return Foo::class === $userSecurityIdentity->getClass();
                })
            );

        $aclObjectManipulators = [
            'sonata.admin.manipulator.acl.object.bar' => $manipulator,
        ];

        $command = new GenerateObjectAclCommand($pool, $aclObjectManipulators);

        $application = new Application();
        $application->add($command);

        $command = $application->find('sonata:admin:generate-object-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--user_model' => Foo::class,
            '--object_owner' => true,
        ]);
    }
}
