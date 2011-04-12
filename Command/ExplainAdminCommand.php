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

use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

class ExplainAdminCommand extends Command
{

    public function configure()
    {
        $this->setName('sonata:admin:explain');
        $this->setDescription('Explain an admin service');

        $this->addArgument('admin', InputArgument::REQUIRED, 'The admin service id');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $admin = $this->container->get($input->getArgument('admin'));

        $output->writeln('<comment>AdminBundle Information</comment>');
        $output->writeln(sprintf('<info>% -20s</info> : %s', 'id', $admin->getCode()));
        $output->writeln(sprintf('<info>% -20s</info> : %s', 'Model', $admin->getClass()));
        $output->writeln(sprintf('<info>% -20s</info> : %s', 'Controller', $admin->getBaseControllerName()));
        $output->writeln(sprintf('<info>% -20s</info> : %s', 'Model Manager', get_class($admin->getModelManager())));
        $output->writeln(sprintf('<info>% -20s</info> : %s', 'Form Builder', get_class($admin->getFormBuilder())));
        $output->writeln(sprintf('<info>% -20s</info> : %s', 'Datagrid Builder', get_class($admin->getDatagridBuilder())));
        $output->writeln(sprintf('<info>% -20s</info> : %s', 'List Builder', get_class($admin->getListBuilder())));

        if ($admin->isChild()) {
            $output->writeln(sprintf('<info>% -15s</info> : %s', 'Parent', $admin->getParent()->getCode()));
        }

        $output->writeln('');
        $output->writeln('<info>Routes</info>');
        foreach ($admin->getRoutes()->getElements() as $route) {
            $output->writeln(sprintf('  - %s', $route->getDefault('_sonata_name'), $route->getPattern()));
        }

        $output->writeln('');
        $output->writeln('<info>Datagrid Columns</info>');
        foreach ($admin->getListFieldDescriptions() as $name => $fieldDescription) {
            $output->writeln(sprintf('  - % -25s  % -15s % -15s', $name, $fieldDescription->getType(), $fieldDescription->getTemplate()));
        }

        $output->writeln('');
        $output->writeln('<info>Datagrid Filters</info>');
        foreach ($admin->getFilterFieldDescriptions() as $name => $fieldDescription) {
            $output->writeln(sprintf('  - % -25s  % -15s % -15s', $name, $fieldDescription->getType(), $fieldDescription->getTemplate()));
        }

        $output->writeln('');
        $output->writeln('<info>Form Fields</info>');
        foreach ($admin->getFormFieldDescriptions() as $name => $fieldDescription) {
            $output->writeln(sprintf('  - % -25s  % -15s % -15s', $name, $fieldDescription->getType(), $fieldDescription->getTemplate()));
        }

        $validatorFactory = $this->container->get('validator.mapping.class_metadata_factory');
        $metadata = $validatorFactory->getClassMetadata($admin->getClass());

        $output->writeln('');
        $output->writeln('<comment>Validation Framework</comment> - http://symfony.com/doc/2.0/book/validation.html');
        $output->writeln('<info>Properties constraints</info>');

        if (count($metadata->getConstrainedProperties()) == 0) {
            $output->writeln('    <error>no properties constraints defined !!</error>');
        }

        foreach ($metadata->getConstrainedProperties() as $name) {
            $output->writeln(sprintf('  <info>%s</info>', $name));
            $propertyMetadatas = $metadata->getMemberMetadatas($name);
            foreach ($propertyMetadatas as $propertyMetadata) {
                foreach ($propertyMetadata->getConstraints() as $constraint) {
                    $output->writeln(sprintf('    % -70s %s', get_class($constraint), implode('|', $constraint->groups)));
                }
            }
        }

        $output->writeln('');
        $output->writeln('<info>Getter constraints</info>');
        $output->writeln('  <comment>todo ;)</comment>');
//        foreach ($metadata->getters as $name => $value) {
//            $output->writeln('TODO ...');
//        }
    }
}