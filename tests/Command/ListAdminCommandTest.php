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
use Sonata\AdminBundle\Command\ListAdminCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ListAdminCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $application = new Application();

        $container = $this->createMock(ContainerInterface::class);

        $admin1 = $this->createMock(AdminInterface::class);
        $admin1->expects($this->any())
            ->method('getClass')
            ->willReturn('Acme\Entity\Foo');

        $admin2 = $this->createMock(AdminInterface::class);
        $admin2->expects($this->any())
            ->method('getClass')
            ->willReturn('Acme\Entity\Bar');

        $container->expects($this->any())
            ->method('get')
            ->willReturnCallback(static function (string $id) use ($admin1, $admin2): AdminInterface {
                switch ($id) {
                    case 'acme.admin.foo':
                        return $admin1;

                    case 'acme.admin.bar':
                        return $admin2;
                }
            });

        $pool = new Pool($container, '', '');
        $pool->setAdminServiceIds(['acme.admin.foo', 'acme.admin.bar']);
        $command = new ListAdminCommand($pool);

        $application->add($command);

        $command = $application->find('sonata:admin:list');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('@Admin services:\s+acme.admin.foo\s+Acme\\\Entity\\\Foo\s+acme.admin.bar\s+Acme\\\Entity\\\Bar@', $commandTester->getDisplay());
    }
}
