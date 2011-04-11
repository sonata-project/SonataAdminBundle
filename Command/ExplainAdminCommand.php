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

        $output->writeln(sprintf('<info>% -15s</info> : %s', 'id', $admin->getCode()));
        $output->writeln(sprintf('<info>% -15s</info> : %s', 'Model', $admin->getClass()));
        $output->writeln(sprintf('<info>% -15s</info> : %s', 'Controller', $admin->getBaseControllerName()));

        if ($admin->isChild()) {
            $output->writeln(sprintf('<info>% -15s</info> : %s', 'Parent', $admin->getParent()->getCode()));
        }

        $output->writeln('');
        $output->writeln('<info>Routes</info>');
        foreach ($admin->getRoutes()->getElements() as $route) {
            $output->writeln(sprintf('  - %s', $route->getDefault('_sonata_name'), $route->getPattern()));
        }

        $output->writeln('');
        $output->writeln('<info>Columns</info>');
        foreach ($admin->getListFieldDescriptions() as $name => $fieldDescription) {
            $output->writeln(sprintf('  - % -25s  % -15s % -15s', $name, $fieldDescription->getType(), $fieldDescription->getTemplate()));
        }

        $output->writeln('');
        $output->writeln('<info>Filters</info>');
        foreach ($admin->getFilterFieldDescriptions() as $name => $fieldDescription) {
            $output->writeln(sprintf('  - % -25s  % -15s % -15s', $name, $fieldDescription->getType(), $fieldDescription->getTemplate()));
        }

        $output->writeln('');
        $output->writeln('<info>Form</info>');
        foreach ($admin->getFormFieldDescriptions() as $name => $fieldDescription) {
            $output->writeln(sprintf('  - % -25s  % -15s % -15s', $name, $fieldDescription->getType(), $fieldDescription->getTemplate()));
        }

        $validatorFactory = $this->container->get('validator.mapping.class_metadata_factory');
        $metadata = $validatorFactory->getClassMetadata($admin->getClass());

        $output->writeln('');
        $output->writeln('<info>Properties constraints</info>');

        foreach($metadata->getConstrainedProperties() as $name) {

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

        foreach($metadata->getters as $name => $value) {
            $output->writeln('TODO ...');
        }
    }
}