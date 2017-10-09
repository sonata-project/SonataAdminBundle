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
use Sonata\AdminBundle\Command\ListAdminCommand;
use Sonata\AdminBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ListAdminCommandTest extends PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $application = new Application();
        $command = new ListAdminCommand();

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $admin1 = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin1->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Acme\Entity\Foo'));

        $admin2 = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin2->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Acme\Entity\Bar'));

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use ($container, $admin1, $admin2) {
                switch ($id) {
                    case 'sonata.admin.pool':
                        $pool = new Pool($container, '', '');
                        $pool->setAdminServiceIds(['acme.admin.foo', 'acme.admin.bar']);

                        return $pool;

                    case 'acme.admin.foo':
                        return $admin1;

                    case 'acme.admin.bar':
                        return $admin2;
                }

                return;
            }));

        $command->setContainer($container);

        $application->add($command);

        $command = $application->find('sonata:admin:list');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('@Admin services:\s+acme.admin.foo\s+Acme\\\Entity\\\Foo\s+acme.admin.bar\s+Acme\\\Entity\\\Bar@', $commandTester->getDisplay());
    }
}
