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

namespace Command;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Command\InstallSonataAssetsCommand;
use Sonata\AdminBundle\Tests\Command\CommandHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Wojciech Błoszyk <wbloszyk@gmail.com>
 */
final class InstallSonataAssetsCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $application = new Application();

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem
            ->expects($this->exactly(1))
            ->method('exists')
            ->willReturn(true);
        $filesystem
            ->expects($this->exactly(1))
            ->method('mirror');

        $command = new InstallSonataAssetsCommand($filesystem);

        CommandHelper::addCommandToApplication($application, $command);

        $command = $application->find('sonata:admin:install-assets');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        static::assertMatchesRegularExpression('@The SonataAdmin assets source files have been successfully copied to "./assets/sonata_admin" directory.@', $commandTester->getDisplay());
    }

    public function testExecuteWithWrongDirection(): void
    {
        $application = new Application();

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem
            ->expects($this->exactly(1))
            ->method('exists')
            ->willReturn(false);
        $filesystem
            ->expects($this->exactly(0))
            ->method('mirror');

        $command = new InstallSonataAssetsCommand($filesystem);

        CommandHelper::addCommandToApplication($application, $command);

        $command = $application->find('sonata:admin:install-assets');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        static::assertMatchesRegularExpression('@Source directory not found: @', $commandTester->getDisplay());
    }

    public function testExecuteWithError(): void
    {
        $application = new Application();

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('exists')->willThrowException(new \Exception('Any expection'));

        $command = new InstallSonataAssetsCommand($filesystem);

        CommandHelper::addCommandToApplication($application, $command);

        $command = $application->find('sonata:admin:install-assets');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        static::assertMatchesRegularExpression('@An error occurred while copying files: @', $commandTester->getDisplay());
    }
}
