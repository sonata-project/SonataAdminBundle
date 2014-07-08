<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Sonata\AdminBundle\Command\CreateClassCacheCommand;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class CreateClassCacheCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $tempDirectory;

    /**
     * @var Application
     */
    private $application;

    protected function setUp()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'sonata_');
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }

        if (mkdir($tempFile)) {
            $this->tempDirectory = $tempFile;
            file_put_contents($this->tempDirectory.'/classes.map', '<?php return array(\'Sonata\\AdminBundle\\Tests\\Fixtures\\Controller\\FooAdminController\', \'Sonata\\AdminBundle\\Tests\\Fixtures\\Controller\\BarAdminController\',);');
        } else {
            $this->markTestSkipped(sprintf('Temp directory "%s" creation error.', $tempFile));
        }

        $this->application = new Application();
        $command = new CreateClassCacheCommand();

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');

        $kernel->expects($this->any())
            ->method('getCacheDir')
            ->will($this->returnValue($this->tempDirectory));

        $kernel->expects($this->any())
            ->method('isDebug')
            ->will($this->returnValue(false));

        $container->expects($this->any())
                ->method('get')
                ->will($this->returnCallback(function($id) use ($kernel) {
                    if ($id == 'kernel') {
                        return $kernel;
                    }

                    return null;
                }));

        $command->setContainer($container);

        $this->application->add($command);
    }

    protected function tearDown()
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

    public function testExecute()
    {
        return;
        $this->assertFileExists($this->tempDirectory.'/classes.map');
        $this->assertFileNotExists($this->tempDirectory.'/classes.php');

        $command = $this->application->find('cache:create-cache-class');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $this->assertRegExp('@Writing cache file ...\s+done!@', $commandTester->getDisplay());

        $this->assertFileExists($this->tempDirectory.'/classes.php');
        $this->assertFileEquals(__DIR__.'/../Fixtures/Command/classes.php', $this->tempDirectory.'/classes.php');
    }

    public function testExecuteWithException()
    {
        $this->assertFileExists($this->tempDirectory.'/classes.map');
        unlink($this->tempDirectory.'/classes.map');

        try {
            $command = $this->application->find('cache:create-cache-class');
            $commandTester = new CommandTester($command);
            $commandTester->execute(array('command' => $command->getName()));
        } catch (\RuntimeException $e) {
            $this->assertEquals(sprintf('The file %s/classes.map does not exist', $this->tempDirectory), $e->getMessage());

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }
}
