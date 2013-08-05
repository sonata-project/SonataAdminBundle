<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Command;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Sonata\AdminBundle\Generator\AdminGenerator;
use Sonata\AdminBundle\Generator\ControllerGenerator;
use Sonata\AdminBundle\Manipulator\ServicesManipulator;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * @author Marek Stipek <mario.dweller@seznam.cz>
 * @author Simon Cosandey <simon.cosandey@simseo.ch>
 */
class GenerateAdminCommand extends ContainerAwareCommand
{
    /** @var string[] */
    private $managerTypes;

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this
            ->setName('sonata:admin:generate')
            ->setDescription('Generates an admin class based on the given entity class')
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity name')
            ->addArgument('controller', InputArgument::OPTIONAL, 'The controller class name')
            ->addOption('bundle', 'b', InputOption::VALUE_OPTIONAL, 'The bundle name')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'The manager type')
            ->addOption('services', 'y', InputOption::VALUE_OPTIONAL, 'The services YAML file', 'services.yml')
            ->addOption('id', 'i', InputOption::VALUE_OPTIONAL, 'The admin service ID')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $skeletonDirectory = __DIR__ . '/../Resources/skeleton';
        $entity = $input->getArgument('entity');
        list($bundleName, $entityClassName) = Validators::validateEntityName($entity);
        $bundle = $this->getBundle($input->getOption('bundle') ?: $bundleName);
        $modelManager = $this->getModelManager($input->getOption('type') ?: $this->getDefaultManagerType());

        if (!$entityClass = $this->getEntityClass($modelManager, $entity)) {
            $entityClass = sprintf('%s\Entity\%s', $bundle->getNamespace(), $entityClassName);
        }

        $adminGenerator = new AdminGenerator($modelManager, $skeletonDirectory);

        try {
            $adminGenerator->generate($bundle, $entityClassName . 'Admin', $entityClass);
            $output->writeln(sprintf(
                '%sThe admin class "<info>%s</info>" has been generated under the file "<info>%s</info>".',
                "\n",
                $adminGenerator->getClass(),
                realpath($adminGenerator->getFile())
            ));
        } catch (\Exception $e) {
            $this->writeError($output, $e->getMessage());
        }

        if ($controller = $input->getArgument('controller')) {
            $controllerGenerator = new ControllerGenerator($skeletonDirectory);

            try {
                $controllerGenerator->generate($bundle, $entityClassName . 'AdminController');
                $output->writeln(sprintf(
                    '%sThe controller class "<info>%s</info>" has been generated under the file "<info>%s</info>".',
                    "\n",
                    $controllerGenerator->getClass(),
                    realpath($controllerGenerator->getFile())
                ));
            } catch (\Exception $e) {
                $this->writeError($output, $e->getMessage());
            }
        }

        if ($services = $input->getOption('services')) {
            $adminClass = $adminGenerator->getClass();
            $file = sprintf('%s/Resources/config/%s', $bundle->getPath(), $services);
            $servicesManipulator = new ServicesManipulator($file);
            $controllerName = $controller
                ? sprintf('%s:%s', $bundle->getName(), substr($controller, 0, -10))
                : 'SonataAdminBundle:CRUD'
            ;

            try {
                $id = $input->getOption('id') ?: $this->getAdminServiceId($bundle->getName(), $entityClassName);
                $servicesManipulator->addResource($id, $entityClass, $adminClass, $controllerName);
                $output->writeln(sprintf(
                    '%sThe service "<info>%s</info>" has been appended to the file <info>"%s</info>".',
                    "\n",
                    $id,
                    realpath($file)
                ));
            } catch (\Exception $e) {
                $this->writeError($output, $e->getMessage());
            }
        }

        return 0;
    }

    /**
     * {@inheritDoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the Sonata admin generator');
        list($bundleName, $entity) = $this->askAndValidate(
            $output,
            'The entity name',
            $input->getArgument('entity'),
            'Sonata\AdminBundle\Command\Validators::validateEntityName'
        );
        $bundleName = $this->askAndValidate(
            $output,
            'The bundle name',
            $input->getOption('bundle') ?: $bundleName,
            'Sensio\Bundle\GeneratorBundle\Command\Validators::validateBundleName'
        );

        if (count($this->getManagerTypes()) > 1) {
            $managerType = $this->askAndValidate(
                $output,
                'The manager type',
                $input->getOption('type') ?: $this->getDefaultManagerType(),
                array($this, 'validateManagerType')
            );
            $input->setOption('type', $managerType);
        }

        $question = $dialog->getQuestion('Do you want to generate a controller', 'no', '?');

        if ($dialog->askConfirmation($output, $question, false)) {
            $controller = $this->askAndValidate(
                $output,
                'The controller class name',
                $input->getArgument('controller') ?: $entity . 'AdminController',
                'Sonata\AdminBundle\Command\Validators::validateControllerClassName'
            );
            $input->setArgument('controller', $controller);
        }

        $question = $dialog->getQuestion('Do you want to update the services YAML configuration file', 'yes', '?');

        if ($dialog->askConfirmation($output, $question)) {
            $path = $this->getBundle($bundleName)->getPath() . '/Resources/config/';
            $services = $this->askAndValidate(
                $output,
                'The services YAML configuration file',
                is_file($path . 'admin.yml') ? 'admin.yml' : 'services.yml',
                'Sonata\AdminBundle\Command\Validators::validateServicesFile'
            );
            $id = $this->askAndValidate(
                $output,
                'The admin service ID',
                $this->getAdminServiceId($bundleName, $entity),
                'Sonata\AdminBundle\Command\Validators::validateServiceId'
            );
            $input->setOption('services', $services);
            $input->setOption('id', $id);
        }

        $input->setArgument('entity', sprintf('%s:%s', $bundleName, $entity));
        $input->setOption('bundle', $bundleName);
    }

    /**
     * @param string $managerType
     * @return string
     * @throws \InvalidArgumentException
     */
    public function validateManagerType($managerType)
    {
        $managerTypes = $this->getManagerTypes();

        if (!in_array($managerType, $managerTypes)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid manager type "%s". Valid manager types are "%s".',
                $managerType,
                implode('", "', $managerTypes)
            ));
        }

        return $managerType;
    }

    /**
     * @param OutputInterface $output
     * @param string $question
     * @param mixed $default
     * @param callable $validator
     * @return mixed
     */
    private function askAndValidate(OutputInterface $output, $question, $default, $validator)
    {
        $dialog = $this->getDialogHelper();

        return $dialog->askAndValidate($output, $dialog->getQuestion($question, $default), $validator, false, $default);
    }

    /**
     * @param OutputInterface $output
     * @param string $message
     */
    private function writeError(OutputInterface $output, $message)
    {
        $output->writeln(sprintf("\n<error>%s</error>", $message));
    }

    /**
     * @param string $name
     * @return BundleInterface
     */
    private function getBundle($name)
    {
        $application = $this->getApplication();
        /* @var $application Application */

        return $application->getKernel()->getBundle($name);
    }

    /**
     * @param ModelManagerInterface $modelManager
     * @param string $entity
     * @return string|null
     */
    private function getEntityClass(ModelManagerInterface $modelManager, $entity)
    {
        if (is_callable(array($modelManager, 'getMetadata'))) {
            $metadata = $modelManager->getMetadata($entity);

            if ($metadata instanceof ClassMetadata) {
                return $metadata->name;
            };
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function getManagerTypes()
    {
        $container = $this->getContainer();

        if (!$container instanceof Container) {
            return array();
        }

        if ($this->managerTypes === null) {
            $this->managerTypes = array();

            foreach ($container->getServiceIds() as $id) {
                if (!strncmp($id, 'sonata.admin.manager.', 21)) {
                    $this->managerTypes[] = substr($id, 21);
                }
            }
        }

        return $this->managerTypes;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    private function getDefaultManagerType()
    {
        if (!$managerTypes = $this->getManagerTypes()) {
            throw new \RuntimeException('There are no registered model managers.');
        }

        return $managerTypes[0];
    }

    /**
     * @param string $managerType
     * @return ModelManagerInterface
     */
    private function getModelManager($managerType)
    {
        return $this->getContainer()->get('sonata.admin.manager.' . $managerType);
    }

    /**
     * @param string $bundleName
     * @param string $entityClassName
     * @return string
     */
    private function getAdminServiceId($bundleName, $entityClassName)
    {
        return Container::underscore(sprintf('%s.admin.%s', substr($bundleName, 0, -6), $entityClassName));
    }

    /**
     * @return DialogHelper
     */
    private function getDialogHelper()
    {
        $dialogHelper = $this->getHelper('dialog');

        if (!$dialogHelper instanceof DialogHelper) {
            $dialogHelper = new DialogHelper();
            $this->getHelperSet()->set($dialogHelper);
        }

        return $dialogHelper;
    }
}