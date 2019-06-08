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
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Command\GenerateObjectAclCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class GenerateObjectAclCommandTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);

        $this->container->expects($this->any())
            ->method('has')
            ->willReturnCallback(static function (string $id): bool {
                switch ($id) {
                    case 'doctrine':
                        return false;
                }
            });
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
}
