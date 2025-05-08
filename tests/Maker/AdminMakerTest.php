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
use Sonata\AdminBundle\Controller\CRUDController;
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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Gaurav Singh Faujdar <faujdar@gmail.com>
 */
final class AdminMakerTest extends TestCase
{
    private string $projectDirectory;

    /**
     * @var array<string, ModelManagerInterface<object>>
     */
    private array $modelManagers = [];

    private ?ConsoleStyle $io = null;

    private ?Generator $generator = null;

    private string $servicesFile;

    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $managerOrmProxy = $this->createMock(ModelManagerInterface::class);
        $managerOrmProxy->method('getExportFields')->with(Foo::class)
            ->willReturn(['bar', 'baz']);

        $this->modelManagers = ['sonata.admin.manager.orm' => $managerOrmProxy];
        $this->servicesFile = \sprintf('%s.yml', lcg_value());
        $this->projectDirectory = \sprintf('%s/sonata-admin-bundle/', sys_get_temp_dir());
        $this->filesystem = new Filesystem();
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->projectDirectory);
    }

    public function testExecute(): void
    {
        $maker = new AdminMaker($this->projectDirectory, $this->modelManagers, CRUDController::class);

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

        $stream = fopen('php://memory', 'w', false);
        static::assertIsResource($stream);

        $input = new ArrayInput($in, $definition);
        $this->io = new ConsoleStyle($input, new StreamOutput($stream));
        // @phpstan-ignore-next-line classConstant.internalClass
        $autoloaderUtil = $this->createMock(AutoloaderUtil::class);
        $autoloaderUtil
            ->method('getPathForFutureClass')
            ->willReturnCallback(fn (string $className): string => \sprintf('%s/%s.php', $this->projectDirectory, str_replace('\\', '/', $className)));

        // @phpstan-ignore-next-line new.internalClass
        $fileManager = new FileManager(
            $this->filesystem,
            $autoloaderUtil,
            // @phpstan-ignore-next-line new.internalClass
            new MakerFileLinkFormatter(null),
            $this->projectDirectory
        );
        // @phpstan-ignore-next-line method.internalClass
        $fileManager->setIO($this->io);

        $this->generator = new Generator(
            $fileManager,
            'Sonata\AdminBundle\Tests'
        );
        $maker->generate($input, $this->io, $this->generator);
    }
}
