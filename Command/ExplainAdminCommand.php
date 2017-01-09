<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ExplainAdminCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sonata:admin:explain');
        $this->setDescription('Explain an admin service');

        $this->addArgument('admin', InputArgument::REQUIRED, 'The admin service id');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $admin = $this->getContainer()->get($input->getArgument('admin'));

        if (!$admin instanceof \Sonata\AdminBundle\Admin\AdminInterface) {
            throw new \RuntimeException(sprintf('Service "%s" is not an admin class', $input->getArgument('admin')));
        }

        $output->writeln('<comment>AdminBundle Information</comment>');
        $output->writeln(sprintf('<info>% -20s</info> : %s', 'id', $admin->getCode()));
        $output->writeln(sprintf('<info>% -20s</info> : %s', 'Admin', get_class($admin)));
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
            $output->writeln(sprintf('  - % -25s %s', $route->getDefault('_sonata_name'), $route->getPath()));
        }

        $output->writeln('');
        $output->writeln('<info>Datagrid Columns</info>');
        foreach ($admin->getListFieldDescriptions() as $name => $fieldDescription) {
            $output->writeln(sprintf(
                '  - % -25s  % -15s % -15s',
                $name,
                $fieldDescription->getType(),
                $fieldDescription->getTemplate()
            ));
        }

        $output->writeln('');
        $output->writeln('<info>Datagrid Filters</info>');
        foreach ($admin->getFilterFieldDescriptions() as $name => $fieldDescription) {
            $output->writeln(sprintf(
                '  - % -25s  % -15s % -15s',
                $name,
                $fieldDescription->getType(),
                $fieldDescription->getTemplate()
            ));
        }

        $output->writeln('');
        $output->writeln('<info>Form theme(s)</info>');
        foreach ($admin->getFormTheme() as $template) {
            $output->writeln(sprintf('  - %s', $template));
        }

        $output->writeln('');
        $output->writeln('<info>Form Fields</info>');
        foreach ($admin->getFormFieldDescriptions() as $name => $fieldDescription) {
            $output->writeln(sprintf(
                '  - % -25s  % -15s % -15s',
                $name,
                $fieldDescription->getType(),
                $fieldDescription->getTemplate()
            ));
        }

        $metadata = false;

        if ($this->getContainer()->has('validator.validator_factory')) {
            $factory = $this->getContainer()->get('validator.validator_factory');

            if (method_exists($factory, 'getMetadataFor')) {
                $metadata = $factory->getMetadataFor($admin->getClass());
            }
        }

        // NEXT_MAJOR: remove method check in next major release
        if (!$metadata) {
            $metadata = $this->getContainer()->get('validator')->getMetadataFor($admin->getClass());
        }

        $output->writeln('');
        $output->writeln('<comment>Validation Framework</comment> - http://symfony.com/doc/3.0/book/validation.html');
        $output->writeln('<info>Properties constraints</info>');

        if (count($metadata->properties) == 0) {
            $output->writeln('    <error>no property constraints defined !!</error>');
        } else {
            foreach ($metadata->properties as $name => $property) {
                $output->writeln(sprintf('  - %s', $name));

                foreach ($property->getConstraints() as $constraint) {
                    $output->writeln(sprintf(
                        '    % -70s %s',
                        get_class($constraint),
                        implode('|', $constraint->groups)
                    ));
                }
            }
        }

        $output->writeln('');
        $output->writeln('<info>Getters constraints</info>');

        if (count($metadata->getters) == 0) {
            $output->writeln('    <error>no getter constraints defined !!</error>');
        } else {
            foreach ($metadata->getters as $name => $property) {
                $output->writeln(sprintf('  - %s', $name));

                foreach ($property->getConstraints() as $constraint) {
                    $output->writeln(sprintf(
                        '    % -70s %s',
                        get_class($constraint),
                        implode('|', $constraint->groups)
                    ));
                }
            }
        }

        $output->writeln('');
        $output->writeln('<info>done!</info>');
    }
}
