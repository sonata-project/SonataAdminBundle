<?php

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
     * @var string[]
     */
    private $availableModelManagers;
    /**
     * @var string
     */
    private $skeletonDirectory;
    /**
     * @var string
     */
    private $modelClass;
    /**
     * @var string
     */
    private $modelClassBasename;
    /**
     * @var string
     */
    private $adminClassBasename;
    /**
     * @var string
     */
    private $controllerClassBasename;
    /**
     * @var string
     */
    private $managerType;
    /**
     * @var ModelManagerInterface
     */
    private $modelManager;

    public function __construct($projectDirectory, array $modelManagers = [])
    {
        $this->projectDirectory = $projectDirectory;
        $this->availableModelManagers = $modelManagers;
        $this->skeletonDirectory = __DIR__.'/../Resources/skeleton';
    }

    public static function getCommandName(): string
    {
        return 'make:sonata:admin';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Generates an admin class based on the given model class')
            ->addArgument('model', InputArgument::REQUIRED, 'The fully qualified model class')
            ->addOption('admin', 'a', InputOption::VALUE_OPTIONAL, 'The admin class basename')
            ->addOption('controller', 'c', InputOption::VALUE_OPTIONAL, 'The controller class basename')
            ->addOption('manager', 'm', InputOption::VALUE_OPTIONAL, 'The model manager type')
            ->addOption('services', 's', InputOption::VALUE_OPTIONAL, 'The services YAML file', 'services.yaml')
            ->addOption('id', 'i', InputOption::VALUE_OPTIONAL, 'The admin service ID');

        $inputConfig->setArgumentAsNonInteractive('model');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        $io->section('Welcome to the Sonata Admin');
        $modelClass = $io->ask(
            'The fully qualified model class',
            $input->getArgument('model'),
            [Validators::class, 'validateClass']
        );
        $modelClassBasename = current(\array_slice(explode('\\', $modelClass), -1));

        $adminClassBasename = $io->ask(
            'The admin class basename',
            $input->getOption('admin') ?: $modelClassBasename.'Admin',
            [Validators::class, 'validateAdminClassBasename']
        );
        if (\count($this->availableModelManagers) > 1) {
            $managerTypes = array_keys($this->availableModelManagers);
            $managerType = $io->choice('The manager type', $managerTypes, $managerTypes[0]);

            $input->setOption('manager', $managerType);
        }
        if ($io->confirm('Do you want to generate a controller?', false)) {
            $controllerClassBasename = $io->ask(
                'The controller class basename',
                $input->getOption('controller') ?: $modelClassBasename.'AdminController',
                [Validators::class, 'validateControllerClassBasename']
            );
            $input->setOption('controller', $controllerClassBasename);
        }
        $input->setOption('services', false);
        if ($io->confirm('Do you want to update the services YAML configuration file?', true)) {
            $path = $this->projectDirectory.'/config/';
            $servicesFile = $io->ask(
                'The services YAML configuration file',
                is_file($path.'admin.yaml') ? 'admin.yaml' : 'services.yaml',
                [Validators::class, 'validateServicesFile']
            );
            $id = $io->ask(
                'The admin service ID',
                $this->getAdminServiceId($adminClassBasename),
                [Validators::class, 'validateServiceId']
            );
            $input->setOption('services', $servicesFile);
            $input->setOption('id', $id);
        }
        $input->setArgument('model', $modelClass);
        $input->setOption('admin', $adminClassBasename);
    }

    /**
     * Configure any library dependencies that your maker requires.
     */
    public function configureDependencies(DependencyBuilder $dependencies)
    {
    }

    /**
     * Called after normal code generation: allows you to do anything.
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $this->configure($input);

        $adminClassNameDetails = $generator->createClassNameDetails(
            $this->modelClassBasename,
            'Admin\\',
            'Admin'
        );

        $adminClassFullName = $adminClassNameDetails->getFullName();
        $this->generateAdmin($io, $generator, $adminClassNameDetails);

        $controllerClassFullName = '';
        if ($this->controllerClassBasename) {
            $controllerClassNameDetails = $generator->createClassNameDetails(
                $this->controllerClassBasename,
                'Controller\\',
                'Controller'
            );

            $this->generateController($input, $io, $generator, $controllerClassNameDetails);

            $controllerClassFullName = $controllerClassNameDetails->getFullName();
        }

        $this->generateService($input, $io, $adminClassFullName, $controllerClassFullName);
    }

    private function getAdminServiceId(string $adminClassBasename): string
    {
        return Container::underscore(sprintf(
            'admin.%s',
            str_replace('\\', '.', 'Admin' == substr($adminClassBasename, -5) ?
                substr($adminClassBasename, 0, -5) : $adminClassBasename)
        ));
    }

    private function generateService(
        InputInterface $input,
        ConsoleStyle $io,
        string $adminClassFullName,
        string $controllerClassFullName
    ) {
        if ($servicesFile = $input->getOption('services')) {
            $file = sprintf('%s/config/%s', $this->projectDirectory, $servicesFile);
            $servicesManipulator = new ServicesManipulator($file);
            $controllerName = $this->controllerClassBasename ? $controllerClassFullName : '~';

            $id = $input->getOption('id') ?:
                $this->getAdminServiceId('App', $this->adminClassBasename);

            $servicesManipulator->addResource(
                $id,
                $this->modelClass,
                $adminClassFullName,
                $controllerName,
                substr($this->managerType, \strlen('sonata.admin.manager.'))
            );

            $io->writeln(sprintf(
                '%sThe service "<info>%s</info>" has been appended to the file <info>"%s</info>".',
                PHP_EOL,
                $id,
                realpath($file)
            ));
        }
    }

    private function generateController(
        InputInterface $input,
        ConsoleStyle $io,
        Generator $generator,
        ClassNameDetails $controllerClassNameDetails
    ) {
        $controllerClassFullName = null;
        if ($controllerClassNameDetails) {
            $controllerClassFullName = $controllerClassNameDetails->getFullName();
            $generator->generateClass($controllerClassFullName,
                $this->skeletonDirectory.'/AdminController.tpl.php',
                []
            );
            $generator->writeChanges();
            $io->writeln(sprintf(
                '%sThe controller class "<info>%s</info>" has been generated under the file "<info>%s</info>".',
                PHP_EOL,
                $controllerClassNameDetails->getShortName(),
                $controllerClassFullName
            ));
        }
    }

    private function generateAdmin(ConsoleStyle $io, Generator $generator, ClassNameDetails $adminClassNameDetails)
    {
        $adminClassFullName = $adminClassNameDetails->getFullName();

        $fields = $this->modelManager->getExportFields($this->modelClass);
        $fieldString = '';
        foreach ($fields as $field) {
            $fieldString = $fieldString."\t\t\t->add('".$field."')".PHP_EOL;
        }

        $fieldString .= "\t\t\t";

        $generator->generateClass($adminClassFullName,
            $this->skeletonDirectory.'/Admin.tpl.php',
            ['fields' => $fieldString]);

        $generator->writeChanges();

        $io->writeln(sprintf(
            '%sThe admin class "<info>%s</info>" has been generated under the file "<info>%s</info>".',
            PHP_EOL,
            $adminClassNameDetails->getShortName(),
            $adminClassFullName
        ));
    }

    private function configure(InputInterface $input)
    {
        $this->modelClass = Validators::validateClass($input->getArgument('model'));
        $this->modelClassBasename = (new \ReflectionClass($this->modelClass))->getShortName();
        $this->adminClassBasename = Validators::validateAdminClassBasename(
            $input->getOption('admin') ?: $this->modelClassBasename.'Admin'
        );

        if ($this->controllerClassBasename = $input->getOption('controller')) {
            $this->controllerClassBasename = Validators::validateControllerClassBasename($this->controllerClassBasename);
        }

        if (0 === \count($this->availableModelManagers)) {
            throw new \InvalidArgumentException('There are no model managers registered.');
        }

        $this->managerType = $input->getOption('manager') ?: array_keys($this->availableModelManagers)[0];
        $this->modelManager = $this->availableModelManagers[$this->managerType] ?: current($this->availableModelManagers);
    }
}
