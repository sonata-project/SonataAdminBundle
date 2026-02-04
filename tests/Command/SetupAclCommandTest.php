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
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Command\SetupAclCommand;
use SensioLabs\AdminBundle\Util\AdminAclManipulatorInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Container;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class SetupAclCommandTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
        $admin = $this->createMock(AdminInterface::class);

        $this->container->set('acme.admin.foo', $admin);
    }

    public function testExecute(): void
    {
        $pool = new Pool($this->container, ['acme.admin.foo']);

        $command = new SetupAclCommand($pool, $this->createMock(AdminAclManipulatorInterface::class));

        $application = new Application();
        CommandHelper::addCommandToApplication($application, $command);

        $command = $application->find('sonata:admin:setup-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        static::assertMatchesRegularExpression('/Starting ACL AdminBundle configuration/', $commandTester->getDisplay());
    }

    public function testExecuteWithException1(): void
    {
        $this->container->set('acme.admin.foo', null);
        $pool = new Pool($this->container, ['acme.admin.foo']);

        $command = new SetupAclCommand($pool, $this->createMock(AdminAclManipulatorInterface::class));

        $application = new Application();
        CommandHelper::addCommandToApplication($application, $command);

        $command = $application->find('sonata:admin:setup-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        static::assertMatchesRegularExpression(
            '@Starting ACL AdminBundle configuration\s+Warning : The admin class cannot be initiated from the command line\s+You have requested a non-existent service "acme.admin.foo".@',
            $commandTester->getDisplay()
        );
    }
}
