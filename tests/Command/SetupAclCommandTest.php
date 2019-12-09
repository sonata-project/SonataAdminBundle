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
    /**
     * @var ContainerInterface
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $admin = $this->createMock(AdminInterface::class);

        $this->container
            ->method('get')
            ->willReturnCallback(static function (string $id) use ($admin): AdminInterface {
                switch ($id) {
                    case 'acme.admin.foo':
                        return $admin;
                }
            });
    }

    public function testExecute(): void
    {
        $pool = new Pool($this->container, '', '');
        $pool->setAdminServiceIds(['acme.admin.foo']);

        $command = new SetupAclCommand($pool, $this->createMock(AdminAclManipulatorInterface::class));

        $application = new Application();
        $application->add($command);

        $command = $application->find('sonata:admin:setup-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('/Starting ACL AdminBundle configuration/', $commandTester->getDisplay());
    }

    public function testExecuteWithException1(): void
    {
        $this->container
            ->method('get')
            ->willReturnCallback(static function (string $id) {
                throw new \Exception('Foo Exception');
            });

        $pool = new Pool($this->container, '', '');
        $pool->setAdminServiceIds(['acme.admin.foo']);

        $command = new SetupAclCommand($pool, $this->createMock(AdminAclManipulatorInterface::class));

        $application = new Application();
        $application->add($command);

        $command = $application->find('sonata:admin:setup-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('@Starting ACL AdminBundle configuration\s+Warning : The admin class cannot be initiated from the command line\s+Foo Exception@', $commandTester->getDisplay());
    }

    public function testExecuteWithException2(): void
    {
        $pool = new Pool($this->container, '', '');

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage(sprintf('Argument 2 passed to %s::__construct() must implement interface %s, instance of %s given', SetupAclCommand::class, AdminAclManipulatorInterface::class, \stdClass::class));

        new SetupAclCommand($pool, new \stdClass());
    }
}
