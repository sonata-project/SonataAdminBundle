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
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Command\SetupAclCommand;
use Sonata\AdminBundle\Util\AdminAclManipulatorInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class SetupAclCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $application = new Application();
        $command = new SetupAclCommand();

        $container = $this->createMock(ContainerInterface::class);
        $admin = $this->createMock(AdminInterface::class);
        $aclManipulator = $this->createMock(AdminAclManipulatorInterface::class);

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(static function ($id) use ($container, $admin, $aclManipulator) {
                switch ($id) {
                    case 'sonata.admin.pool':
                        $pool = new Pool($container, '', '');
                        $pool->setAdminServiceIds(['acme.admin.foo']);

                        return $pool;

                    case 'sonata.admin.manipulator.acl.admin':
                        return $aclManipulator;

                    case 'acme.admin.foo':
                        return $admin;
                }
            }));

        $command->setContainer($container);

        $application->add($command);

        $command = $application->find('sonata:admin:setup-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('/Starting ACL AdminBundle configuration/', $commandTester->getDisplay());
    }

    public function testExecuteWithException1(): void
    {
        $application = new Application();
        $command = new SetupAclCommand();

        $container = $this->createMock(ContainerInterface::class);

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(static function ($id) use ($container) {
                if ('sonata.admin.pool' === $id) {
                    $pool = new Pool($container, '', '');
                    $pool->setAdminServiceIds(['acme.admin.foo']);

                    return $pool;
                }

                throw new \Exception('Foo Exception');
            }));

        $command->setContainer($container);

        $application->add($command);

        $command = $application->find('sonata:admin:setup-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('@Starting ACL AdminBundle configuration\s+Warning : The admin class cannot be initiated from the command line\s+Foo Exception@', $commandTester->getDisplay());
    }

    public function testExecuteWithException2(): void
    {
        $application = new Application();
        $command = new SetupAclCommand();

        $container = $this->createMock(ContainerInterface::class);
        $admin = $this->createMock(AdminInterface::class);

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(static function ($id) use ($container, $admin) {
                switch ($id) {
                    case 'sonata.admin.pool':
                        $pool = new Pool($container, '', '');
                        $pool->setAdminServiceIds(['acme.admin.foo']);

                        return $pool;

                    case 'sonata.admin.manipulator.acl.admin':
                        return new \stdClass();

                    case 'acme.admin.foo':
                        return $admin;
                }
            }));

        $command->setContainer($container);

        $application->add($command);

        $command = $application->find('sonata:admin:setup-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('@Starting ACL AdminBundle configuration\s+The interface "AdminAclManipulatorInterface" is not implemented for stdClass: ignoring@', $commandTester->getDisplay());
    }
}
