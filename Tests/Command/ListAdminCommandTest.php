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
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ListAdminCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $application = new Application();
        $command = new ListAdminCommand();

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $admin1 = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin1->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Acme\Entity\Foo'));

        $admin2 = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin2->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Acme\Entity\Bar'));

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use ($container, $admin1, $admin2) {
                switch ($id) {
                    case 'sonata.admin.pool':
                        $pool = new Pool($container, '', '');
                        $pool->setAdminServiceIds(array('acme.admin.foo', 'acme.admin.bar'));

                        return $pool;
                        break;

                    case 'acme.admin.foo':
                        return $admin1;
                        break;

                    case 'acme.admin.bar':
                        return $admin2;
                        break;
                }

                return;
            }));

        $command->setContainer($container);

        $application->add($command);

        $command = $application->find('sonata:admin:list');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $this->assertRegExp('@Admin services:\s+acme.admin.foo\s+Acme\\\Entity\\\Foo\s+acme.admin.bar\s+Acme\\\Entity\\\Bar@', $commandTester->getDisplay());
    }
}
