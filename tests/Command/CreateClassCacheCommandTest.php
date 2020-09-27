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
            file_put_contents(sprintf('%s/classes.map', $this->tempDirectory), '<?php return [\'Sonata\\AdminBundle\\Tests\\Fixtures\\Controller\\FooAdminController\', \'Sonata\\AdminBundle\\Tests\\Fixtures\\Controller\\BarAdminController\',];');
        } else {
            $this->markTestSkipped(sprintf('Temp directory "%s" creation error.', $tempFile));
        }

        $this->application = new Application();
        $command = new CreateClassCacheCommand($this->tempDirectory, false);

        $this->application->add($command);
    }

    protected function tearDown(): void
    {
        if ($this->tempDirectory) {
            if (file_exists(sprintf('%s/classes.map', $this->tempDirectory))) {
                unlink(sprintf('%s/classes.map', $this->tempDirectory));
            }

            if (file_exists(sprintf('%s/classes.php', $this->tempDirectory))) {
                unlink(sprintf('%s/classes.php', $this->tempDirectory));
            }

            if (file_exists($this->tempDirectory) && is_dir($this->tempDirectory)) {
                rmdir($this->tempDirectory);
            }
        }
    }

    public function testExecute(): void
    {
        $this->markTestSkipped();
        $this->assertFileExists(sprintf('%s/classes.map', $this->tempDirectory));
        $this->assertFileNotExists(sprintf('%s/classes.php', $this->tempDirectory));

        $command = $this->application->find('cache:create-cache-class');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertMatchesRegularExpression('@Writing cache file ...\s+done!@', $commandTester->getDisplay());

        $this->assertFileExists(sprintf('%s/classes.php', $this->tempDirectory));
        $this->assertFileEquals(sprintf('%s/../Fixtures/Command/classes.php', __DIR__), sprintf('%s/classes.php', $this->tempDirectory));
    }

    public function testExecuteWithException(): void
    {
        $this->assertFileExists(sprintf('%s/classes.map', $this->tempDirectory));
        unlink(sprintf('%s/classes.map', $this->tempDirectory));

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
