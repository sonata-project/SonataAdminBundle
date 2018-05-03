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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\Container;

/**
 * @author Gaurav Singh Faudjdar <faujdar@gmail.com>
 */
class AdminMaker extends AbstractMaker
{
    /**
     * @var string
     */
    private $projectDirectory;

    /**
     * @var ModelManagerInterface[]
     */
    private $availableModelManagers;

    public function __construct($projectDirectory, array $modelManagers = [])
    {
        $this->projectDirectory = $projectDirectory;
        $this->availableModelManagers = $modelManagers;
    }

    /**
     * Return the command name for your maker (e.g. make:report).
     *
     * @return string
     */
    public static function getCommandName(): string
    {
        return 'make:sonata:admin';
    }

    /**
     * Configure the command: set description, input arguments, options, etc.
     *
     * By default, all arguments will be asked interactively. If you want
     * to avoid that, use the $inputConfig->setArgumentAsNonInteractive() method.
     *
     * @param Command            $command
     * @param InputConfiguration $inputConfig
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Generates an admin class based on the given model class')
            ->addArgument('model', InputArgument::REQUIRED, 'The fully qualified model class')
            ->addOption('admin', 'a', InputOption::VALUE_OPTIONAL, 'The admin class basename')
            ->addOption('controller', 'c', InputOption::VALUE_OPTIONAL, 'The controller class basename')
            ->addOption('manager', 'm', InputOption::VALUE_OPTIONAL, 'The model manager type')
            ->addOption('services', 'y', InputOption::VALUE_OPTIONAL, 'The services YAML file', 'services.yaml')
            ->addOption('id', 'i', InputOption::VALUE_OPTIONAL, 'The admin service ID');

        $inputConfig->setArgumentAsNonInteractive('model');
    }

    /**
     * {@inheritdoc}
     */
    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        $io->section('Welcome to the Sonata admin maker');
        $modelClass = $io->ask(
            'The fully qualified model class',
            $input->getArgument('model'),
            'Sonata\AdminBundle\Command\Validators::validateClass'
        );
        $modelClassBasename = current(array_slice(explode('\\', $modelClass), -1));

        $adminClassBasename = $io->ask(
            'The admin class basename',
            $input->getOption('admin') ?: $modelClassBasename.'Admin',
            'Sonata\AdminBundle\Command\Validators::validateAdminClassBasename'
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
                'Sonata\AdminBundle\Command\Validators::validateControllerClassBasename'
            );
            $input->setOption('controller', $controllerClassBasename);
        }
        if ($io->confirm('Do you want to update the services YAML configuration file?', true)) {
            $path = $this->projectDirectory.'/config/';
            $servicesFile = $io->ask(
                'The services YAML configuration file',
                is_file($path.'admin.yaml') ? 'admin.yaml' : 'services.yaml',
                'Sonata\AdminBundle\Command\Validators::validateServicesFile'
            );
            $id = $io->ask(
                'The admin service ID',
                $this->getAdminServiceId($adminClassBasename),
                'Sonata\AdminBundle\Command\Validators::validateServiceId'
            );
            $input->setOption('services', $servicesFile);
            $input->setOption('id', $id);
        } else {
            $input->setOption('services', false);
        }
        $input->setArgument('model', $modelClass);
        $input->setOption('admin', $adminClassBasename);
    }

    /**
     * Configure any library dependencies that your maker requires.
     *
     * @param DependencyBuilder $dependencies
     */
    public function configureDependencies(DependencyBuilder $dependencies)
    {
        // TODO: Implement configureDependencies() method.
    }

    /**
     * Called after normal code generation: allows you to do anything.
     *
     * @param InputInterface $input
     * @param ConsoleStyle   $io
     * @param Generator      $generator
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $modelClass = Validators::validateClass($input->getArgument('model'));
        $modelClassBasename = current(\array_slice(explode('\\', $modelClass), -1));
        $adminClassBasename = $input->getOption('admin') ?: $modelClassBasename.'Admin';
        $adminClassBasename = Validators::validateAdminClassBasename($adminClassBasename);
        $managerType = $input->getOption('manager') ?: array_keys($this->availableModelManagers)[0];
        $modelManager = $this->availableModelManagers[$managerType] ?: current($this->availableModelManagers);
        $skeletonDirectory = __DIR__.'/../Resources/skeleton';

        $adminClassNameDetails = $generator->createClassNameDetails(
            $modelClassBasename,
            'Admin\\',
            'Admin'
        );

        $fields = $modelManager->getExportFields($modelClass);
        $fieldString = '';
        foreach ($fields as $field) {
            $fieldString = $fieldString."\t\t\t->add('".$field."')".PHP_EOL;
        }

        $fieldString .= "\t\t\t";

        $generator->generateClass($adminClassNameDetails->getFullName(),
            $skeletonDirectory.'/Admin.tpl.php',
            ['fields' => $fieldString]);

        $generator->writeChanges();

        $io->writeln(sprintf(
                '%sThe admin class "<info>%s</info>" has been generated under the file "<info>%s</info>".',
                PHP_EOL,
                $adminClassNameDetails->getShortName(),
                $adminClassNameDetails->getFullName())
        );

        $controllerClassFullName = null;

        if ($controllerClassBasename = $input->getOption('controller')) {
            $controllerClassBasename = Validators::validateControllerClassBasename($controllerClassBasename);

            $controllerClassNameDetails = $generator->createClassNameDetails(
                $controllerClassBasename,
                'Controller\\',
                'Controller'
            );

            $controllerClassFullName = $controllerClassNameDetails->getFullName();

            $generator->generateClass($controllerClassFullName,
                $skeletonDirectory.'/AdminController.tpl.php',
                []);

            $generator->writeChanges();

            $io->writeln(sprintf(
                    '%sThe controller class "<info>%s</info>" has been generated under the file "<info>%s</info>".',
                    PHP_EOL,
                    $controllerClassNameDetails->getShortName(),
                    $controllerClassNameDetails->getFullName())
            );
        }

        if ($servicesFile = $input->getOption('services')) {
            $adminClass = $adminClassNameDetails->getFullName();
            $file = sprintf('%s/config/%s', $this->projectDirectory, $servicesFile);
            $servicesManipulator = new ServicesManipulator($file);
            $controllerName = $controllerClassBasename ? $controllerClassFullName : '~';

            $id = $input->getOption('id') ?: $this->getAdminServiceId('App', $adminClassBasename);

            $servicesManipulator->addResource($id, $modelClass, $adminClass, $controllerName, substr($managerType, strlen('sonata.admin.manager.')));
            $io->writeln(sprintf(
                '%sThe service "<info>%s</info>" has been appended to the file <info>"%s</info>".',
                PHP_EOL,
                $id,
                realpath($file)
            ));
        }
    }

    /**
     * @param string $adminClassBasename
     *
     * @return string
     */
    private function getAdminServiceId($adminClassBasename)
    {
        $suffix = 'Admin' == substr($adminClassBasename, -5) ? substr($adminClassBasename, 0, -5) : $adminClassBasename;
        $suffix = str_replace('\\', '.', $suffix);

        return Container::underscore(sprintf(
            'admin.%s',
            $suffix
        ));
    }
}
