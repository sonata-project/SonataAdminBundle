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
use Sonata\AdminBundle\Command\CreateClassCacheCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @group legacy
 *
 * NEXT_MAJOR: Remove this class.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class CreateClassCacheCommandTest extends TestCase
{
    /**
     * @var string
     */
    private $tempDirectory;

    /**
     * @var Application
     */
    private $application;

    protected function setUp(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'sonata_');
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }

        if (mkdir($tempFile)) {
            $this->tempDirectory = $tempFile;
            file_put_contents($this->tempDirectory.'/classes.map', '<?php return [\'Sonata\\AdminBundle\\Tests\\Fixtures\\Controller\\FooAdminController\', \'Sonata\\AdminBundle\\Tests\\Fixtures\\Controller\\BarAdminController\',];');
        } else {
            $this->markTestSkipped(sprintf('Temp directory "%s" creation error.', $tempFile));
        }

        $this->application = new Application();
        $command = new CreateClassCacheCommand();

        $container = $this->createMock(ContainerInterface::class);
        $kernel = $this->createMock(KernelInterface::class);

        $kernel->expects($this->any())
            ->method('getCacheDir')
            ->will($this->returnValue($this->tempDirectory));

        $kernel->expects($this->any())
            ->method('isDebug')
            ->will($this->returnValue(false));

        $container->expects($this->any())
                ->method('get')
                ->will($this->returnCallback(function ($id) use ($kernel) {
                    if ('kernel' === $id) {
                        return $kernel;
                    }
                }));

        $command->setContainer($container);

        $this->application->add($command);
    }

    protected function tearDown(): void
    {
        if ($this->tempDirectory) {
            if (file_exists($this->tempDirectory.'/classes.map')) {
                unlink($this->tempDirectory.'/classes.map');
            }

            if (file_exists($this->tempDirectory.'/classes.php')) {
                unlink($this->tempDirectory.'/classes.php');
            }

            if (file_exists($this->tempDirectory) && is_dir($this->tempDirectory)) {
                rmdir($this->tempDirectory);
            }
        }
    }

    public function testExecute(): void
    {
        $this->markTestSkipped();
        $this->assertFileExists($this->tempDirectory.'/classes.map');
        $this->assertFileNotExists($this->tempDirectory.'/classes.php');

        $command = $this->application->find('cache:create-cache-class');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('@Writing cache file ...\s+done!@', $commandTester->getDisplay());

        $this->assertFileExists($this->tempDirectory.'/classes.php');
        $this->assertFileEquals(__DIR__.'/../Fixtures/Command/classes.php', $this->tempDirectory.'/classes.php');
    }

    public function testExecuteWithException(): void
    {
        $this->assertFileExists($this->tempDirectory.'/classes.map');
        unlink($this->tempDirectory.'/classes.map');

        try {
            $command = $this->application->find('cache:create-cache-class');
            $commandTester = new CommandTester($command);
            $commandTester->execute(['command' => $command->getName()]);
        } catch (\RuntimeException $e) {
            $this->assertSame(sprintf('The file %s/classes.map does not exist', $this->tempDirectory), $e->getMessage());

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }
}
