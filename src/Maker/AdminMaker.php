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

namespace Sonata\AdminBundle\Maker;

use Sonata\AdminBundle\Command\Validators;
use Sonata\AdminBundle\Manipulator\ServicesManipulator;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\Container;

/**
 * @author Gaurav Singh Faujdar <faujdar@gmail.com>
 */
final class AdminMaker extends AbstractMaker
{
    /**
     * @var string
     */
    private $projectDirectory;

    /**
     * @var array<string, ModelManagerInterface<object>>
     */
    private $availableModelManagers;

    /**
     * @var string
     */
    private $skeletonDirectory;

    /**
     * @var string
     * @phpstan-var class-string
     *
     * @psalm-suppress PropertyNotSetInConstructor
     *
     * @see AdminMaker::configure
     */
    private $modelClass;

    /**
     * @var string
     *
     * @psalm-suppress PropertyNotSetInConstructor
     *
     * @see AdminMaker::configure
     */
    private $modelClassBasename;

    /**
     * @var string
     *
     * @psalm-suppress PropertyNotSetInConstructor
     *
     * @see AdminMaker::configure
     */
    private $adminClassBasename;

    /**
     * @var string|null
     */
    private $controllerClassBasename;

    /**
     * @var string
     *
     * @psalm-suppress PropertyNotSetInConstructor
     *
     * @see AdminMaker::configure
     */
    private $managerType;

    /**
     * @var ModelManagerInterface<object>
     *
     * @psalm-suppress PropertyNotSetInConstructor
     *
     * @see AdminMaker::configure
     */
    private $modelManager;

    /**
     * @var string
     */
    private $defaultController;

    /**
     * @param array<string, ModelManagerInterface<object>> $modelManagers
     */
    public function __construct(string $projectDirectory, array $modelManagers, string $defaultController)
    {
        $this->projectDirectory = $projectDirectory;
        $this->availableModelManagers = $modelManagers;
        $this->skeletonDirectory = sprintf('%s/../Resources/skeleton', __DIR__);
        $this->defaultController = $defaultController;
    }

    public static function getCommandName(): string
    {
        return 'make:sonata:admin';
    }

    public static function getCommandDescription(): string
    {
        return 'Generates an admin class based on the given model class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('model', InputArgument::REQUIRED, 'The fully qualified model class')
            ->addOption('admin', 'a', InputOption::VALUE_OPTIONAL, 'The admin class basename')
            ->addOption('controller', 'c', InputOption::VALUE_OPTIONAL, 'The controller class basename')
            ->addOption('manager', 'm', InputOption::VALUE_OPTIONAL, 'The model manager type')
            ->addOption('services', 's', InputOption::VALUE_OPTIONAL, 'The services YAML file')
            ->addOption('id', 'i', InputOption::VALUE_OPTIONAL, 'The admin service ID');

        $inputConfig->setArgumentAsNonInteractive('model');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $io->section('Welcome to the Sonata Admin');
        $this->modelClass = $io->ask(
            'The fully qualified model class',
            $input->getArgument('model'),
            [Validators::class, 'validateClass']
        );
        $this->modelClassBasename = \array_slice(explode('\\', $this->modelClass), -1)[0];

        $this->adminClassBasename = $io->ask(
            'The admin class basename',
            $input->getOption('admin') ?? sprintf('%sAdmin', $this->modelClassBasename),
            [Validators::class, 'validateAdminClassBasename']
        );
        if (\count($this->availableModelManagers) > 1) {
            $managerTypes = array_keys($this->availableModelManagers);
            $this->managerType = $io->choice('The manager type', $managerTypes, $managerTypes[0]);

            $input->setOption('manager', $this->managerType);
        }
        if ($io->confirm('Do you want to generate a controller?', false)) {
            $this->controllerClassBasename = $io->ask(
                'The controller class basename',
                $input->getOption('controller') ?? sprintf('%sAdminController', $this->modelClassBasename),
                [Validators::class, 'validateControllerClassBasename']
            );
            $input->setOption('controller', $this->controllerClassBasename);
        }
        if ($io->confirm('Do you want to update the services YAML configuration file?', true)) {
            $path = sprintf('%s/config/', $this->projectDirectory);
            $servicesFile = $io->ask(
                'The services YAML configuration file',
                is_file($path.'admin.yaml') ? 'admin.yaml' : 'services.yaml',
                [Validators::class, 'validateServicesFile']
            );
            $id = $io->ask(
                'The admin service ID',
                $this->getAdminServiceId($this->adminClassBasename),
                [Validators::class, 'validateServiceId']
            );
            $input->setOption('services', $servicesFile);
            $input->setOption('id', $id);
        }
        $input->setArgument('model', $this->modelClass);
        $input->setOption('admin', $this->adminClassBasename);
    }

    /**
     * Configure any library dependencies that your maker requires.
     */
    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    /**
     * Called after normal code generation: allows you to do anything.
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $this->configure($input);

        $adminClassNameDetails = $generator->createClassNameDetails(
            $this->adminClassBasename,
            'Admin\\',
            'Admin'
        );

        /** @phpstan-var class-string $adminClassFullName */
        $adminClassFullName = $adminClassNameDetails->getFullName();
        $this->generateAdmin($io, $generator, $adminClassNameDetails);

        $controllerClassFullName = '';
        if (null !== $this->controllerClassBasename) {
            $controllerClassNameDetails = $generator->createClassNameDetails(
                $this->controllerClassBasename,
                'Controller\\',
                'Controller'
            );

            $this->generateController($io, $generator, $controllerClassNameDetails);

            $controllerClassFullName = $controllerClassNameDetails->getFullName();
        }

        $this->generateService($input, $io, $adminClassFullName, $controllerClassFullName);
    }

    private function getAdminServiceId(string $adminClassBasename): string
    {
        return Container::underscore(sprintf(
            'admin.%s',
            str_replace('\\', '.', 'Admin' === substr($adminClassBasename, -5) ?
                substr($adminClassBasename, 0, -5) : $adminClassBasename)
        ));
    }

    /**
     * @phpstan-param class-string $adminClassFullName
     */
    private function generateService(
        InputInterface $input,
        ConsoleStyle $io,
        string $adminClassFullName,
        string $controllerClassFullName
    ): void {
        $servicesFile = $input->getOption('services');
        if (null !== $servicesFile) {
            $file = sprintf('%s/config/%s', $this->projectDirectory, $servicesFile);
            $servicesManipulator = new ServicesManipulator($file);
            $controllerName = null !== $this->controllerClassBasename ? $controllerClassFullName : '~';

            $id = $input->getOption('id') ?? $this->getAdminServiceId($this->adminClassBasename);

            $servicesManipulator->addResource(
                $id,
                $this->modelClass,
                $adminClassFullName,
                $controllerName,
                substr($this->managerType, \strlen('sonata.admin.manager.'))
            );

            $io->writeln(sprintf(
                '%sThe service "<info>%s</info>" has been appended to the file <info>"%s</info>".',
                \PHP_EOL,
                $id,
                realpath($file)
            ));
        }
    }

    private function generateController(
        ConsoleStyle $io,
        Generator $generator,
        ClassNameDetails $controllerClassNameDetails
    ): void {
        $controllerClassFullName = $controllerClassNameDetails->getFullName();
        $generator->generateClass(
            $controllerClassFullName,
            sprintf('%s/AdminController.tpl.php', $this->skeletonDirectory),
            [
                'default_controller' => $this->defaultController,
                'default_controller_short_name' => Str::getShortClassName($this->defaultController),
            ]
        );
        $generator->writeChanges();
        $io->writeln(sprintf(
            '%sThe controller class "<info>%s</info>" has been generated under the file "<info>%s</info>".',
            \PHP_EOL,
            $controllerClassNameDetails->getShortName(),
            $controllerClassFullName
        ));
    }

    private function generateAdmin(
        ConsoleStyle $io,
        Generator $generator,
        ClassNameDetails $adminClassNameDetails
    ): void {
        $adminClassFullName = $adminClassNameDetails->getFullName();

        $fields = $this->modelManager->getExportFields($this->modelClass);
        $fieldString = '';
        foreach ($fields as $field) {
            $fieldString = $fieldString.sprintf('%12s', '')."->add('".$field."')".\PHP_EOL;
        }

        $fieldString .= sprintf('%12s', '');

        $generator->generateClass(
            $adminClassFullName,
            sprintf('%s/Admin.tpl.php', $this->skeletonDirectory),
            ['fields' => $fieldString]
        );

        $generator->writeChanges();

        $io->writeln(sprintf(
            '%sThe admin class "<info>%s</info>" has been generated under the file "<info>%s</info>".',
            \PHP_EOL,
            $adminClassNameDetails->getShortName(),
            $adminClassFullName
        ));
    }

    private function configure(InputInterface $input): void
    {
        $this->modelClass = Validators::validateClass($input->getArgument('model'));
        $this->modelClassBasename = (new \ReflectionClass($this->modelClass))->getShortName();
        $this->adminClassBasename = Validators::validateAdminClassBasename(
            $input->getOption('admin') ?? sprintf('%sAdmin', $this->modelClassBasename)
        );

        $this->controllerClassBasename = $input->getOption('controller');
        if (null !== $this->controllerClassBasename) {
            $this->controllerClassBasename = Validators::validateControllerClassBasename($this->controllerClassBasename);
        }

        if (0 === \count($this->availableModelManagers)) {
            throw new \InvalidArgumentException('There are no model managers registered.');
        }

        $this->managerType = $input->getOption('manager') ?? array_keys($this->availableModelManagers)[0];
        $this->modelManager = $this->availableModelManagers[$this->managerType] ?? current($this->availableModelManagers);
    }
}
