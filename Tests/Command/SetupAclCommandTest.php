<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Command;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Command\SetupAclCommand;
use Sonata\AdminBundle\Util\AdminAclManipulatorInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class SetupAclCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $application = new Application();
        $command = new SetupAclCommand();

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $aclManipulator = $this->getMock('Sonata\AdminBundle\Util\AdminAclManipulatorInterface');

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use ($container, $admin, $aclManipulator) {
                switch ($id) {
                    case 'sonata.admin.pool':
                        $pool = new Pool($container, '', '');
                        $pool->setAdminServiceIds(array('acme.admin.foo'));

                        return $pool;
                        break;

                    case 'sonata.admin.manipulator.acl.admin':
                        return $aclManipulator;
                        break;

                    case 'acme.admin.foo':
                        return $admin;
                        break;
                }

                return;
            }));

        $command->setContainer($container);

        $application->add($command);

        $command = $application->find('sonata:admin:setup-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $this->assertRegExp('/Starting ACL AdminBundle configuration/', $commandTester->getDisplay());
    }

    public function testExecuteWithException1()
    {
        $application = new Application();
        $command = new SetupAclCommand();

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use ($container) {
                if ($id == 'sonata.admin.pool') {
                    $pool = new Pool($container, '', '');
                    $pool->setAdminServiceIds(array('acme.admin.foo'));

                    return $pool;
                }

                throw new \Exception('Foo Exception');
            }));

        $command->setContainer($container);

        $application->add($command);

        $command = $application->find('sonata:admin:setup-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $this->assertRegExp('@Starting ACL AdminBundle configuration\s+Warning : The admin class cannot be initiated from the command line\s+Foo Exception@', $commandTester->getDisplay());
    }

    public function testExecuteWithException2()
    {
        $application = new Application();
        $command = new SetupAclCommand();

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use ($container, $admin) {
                switch ($id) {
                    case 'sonata.admin.pool':
                        $pool = new Pool($container, '', '');
                        $pool->setAdminServiceIds(array('acme.admin.foo'));

                        return $pool;
                        break;

                    case 'sonata.admin.manipulator.acl.admin':
                        return new \stdClass();
                        break;

                    case 'acme.admin.foo':
                        return $admin;
                        break;
                }

                return;
            }));

        $command->setContainer($container);

        $application->add($command);

        $command = $application->find('sonata:admin:setup-acl');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $this->assertRegExp('@Starting ACL AdminBundle configuration\s+The interface "AdminAclManipulatorInterface" is not implemented for stdClass: ignoring@', $commandTester->getDisplay());
    }
}
