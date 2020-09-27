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

namespace Sonata\AdminBundle\Tests\Maker;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Maker\AdminMaker;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Util\AutoloaderUtil;
use Symfony\Bundle\MakerBundle\Util\MakerFileLinkFormatter;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Gaurav Singh Faujdar <faujdar@gmail.com>
 */
class AdminMakerTest extends TestCase
{
    /**
     * @var string
     */
    private $projectDirectory;
    /**
     * @var array
     */
    private $modelManagers = [];
    /**
     * @var InputInterface
     */
    private $input;
    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var ConsoleStyle
     */
    private $io;
    /**
     * @var Generator
     */
    private $generator;
    /**
     * @var string
     */
    private $servicesFile;

    /**
     * @var Filesystem
     */
    private $filesystem;

    protected function setup(): void
    {
        $managerOrmProxy = $this->createStub(ModelManagerInterface::class);
        $managerOrmProxy->method('getExportFields')->with(Foo::class)
            ->willReturn(['bar', 'baz']);

        $this->modelManagers = ['sonata.admin.manager.orm' => $managerOrmProxy];
        $this->servicesFile = sprintf('%s.yml', lcg_value());
        $this->projectDirectory = sprintf('%s/sonata-admin-bundle/', sys_get_temp_dir());
        $this->filesystem = new Filesystem();
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->projectDirectory);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testExecute(): void
    {
        $maker = new AdminMaker($this->projectDirectory, $this->modelManagers);

        $in = [
            'model' => Foo::class,
            '--admin' => 'FooAdmin',
            '--controller' => 'FooAdminController',
            '--services' => $this->servicesFile,
            '--id' => 'acme_demo_admin.admin.foo',
        ];

        $definition = new InputDefinition([
            new InputArgument('model', InputArgument::REQUIRED),
            new InputOption('admin', 'a', InputOption::VALUE_REQUIRED),
            new InputOption('controller', 'c', InputOption::VALUE_REQUIRED),
            new InputOption('manager', 'm', InputOption::VALUE_REQUIRED),
            new InputOption('services', 'y', InputOption::VALUE_REQUIRED),
            new InputOption('id', 'i', InputOption::VALUE_REQUIRED),
        ]);

        $this->input = new ArrayInput($in, $definition);

        $this->output = new StreamOutput(fopen('php://memory', 'w', false));

        $this->io = new ConsoleStyle($this->input, $this->output);
        $autoloaderUtil = $this->createMock(AutoloaderUtil::class);
        $autoloaderUtil
            ->method('getPathForFutureClass')
            ->willReturnCallback(function (string $className): string {
                return sprintf('%s/%s.php', $this->projectDirectory, str_replace('\\', '/', $className));
            });

        $fileManager = new FileManager(
            $this->filesystem,
            $autoloaderUtil,
            new MakerFileLinkFormatter(null),
            $this->projectDirectory
        );
        $fileManager->setIO($this->io);

        $this->generator = new Generator($fileManager, 'Sonata\AdminBundle\Tests');
        $maker->generate($this->input, $this->io, $this->generator);
    }
}
