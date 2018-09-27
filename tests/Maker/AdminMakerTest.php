<?php

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
use Prophecy\Argument;
use Sonata\AdminBundle\Maker\AdminMaker;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
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

    protected function setup()
    {
        if (5 == PHP_MAJOR_VERSION || !class_exists('Symfony\Component\Console\CommandLoader\CommandLoaderInterface')) {
            $this->markTestSkipped('Test only available for PHP 7 and SF 3.4');
        }

        $managerOrmProxy = $this->prophesize(ModelManagerInterface::class);
        $managerOrmProxy->getExportFields(Argument::exact('Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo'))
            ->willReturn(['bar', 'baz']);

        $this->modelManagers = ['sonata.admin.manager.orm' => $managerOrmProxy->reveal()];
        $this->servicesFile = sprintf('%s.yml', lcg_value());
        $this->projectDirectory = sys_get_temp_dir();
    }

    protected function tearDown()
    {
        @unlink($this->projectDirectory.'/config/'.$this->servicesFile);
    }

    public function testExecute()
    {
        $maker = new AdminMaker($this->projectDirectory, $this->modelManagers);

        $in = [
            'model' => \Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo::class,
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

        $this->output = new StreamOutput(fopen('php://memory', 'wb', false));

        $this->io = new ConsoleStyle($this->input, $this->output);
        $fileManager = new FileManager(new Filesystem(), '.');
        $fileManager->setIO($this->io);
        $this->generator = new Generator($fileManager, 'Sonata\AdminBundle\Tests');

        $maker->generate($this->input, $this->io, $this->generator);
    }
}
