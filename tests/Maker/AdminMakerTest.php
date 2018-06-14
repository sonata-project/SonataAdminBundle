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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Gaurav Singh Faujdar <faujdar@gmail.com>
 */
class AdminMakerTest extends TestCase
{

    private $projectDirectory;
    private $modelManagers = [];

    private $input;
    private $output;
    private $io;
    private $generator;
    private $servicesFile;

    protected function setup()
    {
        $manager_orm_proxy = $this->prophesize(ModelManagerInterface::class);
        $manager_orm_proxy->getExportFields(Argument::exact('Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo'))
            ->willReturn(['bar', 'baz']);

        $this->modelManagers = ['sonata.admin.manager.orm' => $manager_orm_proxy->reveal()];
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

        $in = array(
            'model' => \Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo::class,
            '--admin' => 'FooAdmin',
            '--controller' => 'FooAdminController',
            '--services' => $this->servicesFile,
            '--id' => 'acme_demo_admin.admin.foo',
        );

        $definition = new InputDefinition(array(
            new InputArgument('model', InputArgument::REQUIRED),
            new InputOption('admin', 'a', InputOption::VALUE_REQUIRED),
            new InputOption('controller', 'c', InputOption::VALUE_REQUIRED),
            new InputOption('manager', 'm', InputOption::VALUE_REQUIRED),
            new InputOption('services', 'y', InputOption::VALUE_REQUIRED),
            new InputOption('id', 'i', InputOption::VALUE_REQUIRED),
        ));

        $this->input = new ArrayInput($in, $definition);

        $this->output = new StreamOutput(fopen('php://memory', 'w', false));

        $this->io = new ConsoleStyle($this->input, $this->output);
        $fileManager = new FileManager(new Filesystem(), '.');
        $fileManager->setIO($this->io);
        $this->generator = new Generator($fileManager, 'Sonata\AdminBundle\Tests');

        $maker->generate($this->input, $this->io, $this->generator);
    }

    private function getService($id)
    {
        return self::$kernel->getContainer()->get($id);
    }
}
