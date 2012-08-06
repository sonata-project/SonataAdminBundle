<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Simon Cosandey <simon.cosandey@simseo.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCommand;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Sonata\AdminBundle\Generator\AdminGenerator;
use Sonata\AdminBundle\Generator\ControllerGenerator;
use Sonata\AdminBundle\Manipulator\ServicesManipulator;

/**
 * Generates a admin class for a given Doctrine entity.
 *
 * @author Simon Cosandey <simon.cosandey@simseo.ch>
 */
class CreateAdminClassCommand extends GenerateDoctrineCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('entity', InputArgument::REQUIRED, 'The entity class name to initialize (shortcut notation)'),
            ))
            ->setDescription('Generates an admin class based on a Doctrine entity')
            ->setHelp(<<<EOT
The <info>sonata:admin:generate</info> command generates an admin class based on a Doctrine entity.

<info>php app/console sonata:admin:generate AcmeBlogBundle:Post</info>
EOT
            )
            ->setName('sonata:admin:generate')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entity = Validators::validateEntityName($input->getArgument('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $entityClass = $this->getContainer()->get('doctrine')->getEntityNamespace($bundle).'\\'.$entity;
        $metadata = $this->getEntityMetadata($entityClass);
        $bundle   = $this->getApplication()->getKernel()->getBundle($bundle);

        $adminGenerator = new AdminGenerator($this->getContainer()->get('filesystem'),  __DIR__.'/../Resources/skeleton/admin');
        $adminGenerator->generate($bundle, $entity, $metadata[0]);

        $output->writeln(sprintf(
            'The new %s.php class file has been created under %s.',
            $adminGenerator->getClassName(),
            $adminGenerator->getClassPath()
        ));
        
        $controllerGenerator = new ControllerGenerator($this->getContainer()->get('filesystem'),  __DIR__.'/../Resources/skeleton/controller');
        $controllerGenerator->generate($bundle, $entity, $metadata[0]);

        $output->writeln(sprintf(
            'The new %s.php class file has been created under %s.',
            $controllerGenerator->getClassName(),
            $controllerGenerator->getClassPath()
        ));
        
        $serviceManipulator = new ServicesManipulator($bundle->getPath().'/Resources/config/services.yml');
        if($serviceManipulator->addResource(
                $bundle, 
                $entity, 
                $adminGenerator->getClassName()
        ))
        {
            $output->writeln('le fichier services.yml a été mis à jour');
        }
        else
        {
            $output->writeln('erreur...');
        }
        
        
    }
}