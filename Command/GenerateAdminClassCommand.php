<?php

/*
 * This file is based on GenerateDoctrineFormCommand part of the Symfony package.
 *
 * Phil Rennie phil@philrennie.co.uk
 * Origianlly (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\DoctrineBundle\Mapping\MetadataFactory;
use Sonata\AdminBundle\Generator\AdminClassGenerator;

/**
 * Generates a sonata admin class for a given Doctrine entity.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Hugo Hamon <hugo.hamon@sensio.com>
 */
class GenerateAdminClassCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    public function configure()
    {
        $this
            ->setName('sonata:admin:generate-class')
            ->setDefinition(array(
                new InputArgument('entity', InputArgument::REQUIRED, 'The entity class name to initialize (shortcut notation)'),
            ))
            ->setDescription('Generates a sonata admin class based on a Doctrine entity')
            ->setHelp(<<<EOT
The <info>sonata:admin:generate-class</info> command generates a sonata admin class based on a Doctrine entity.

<info>php app/console sonata:admin:generate-class AcmeBlogBundle:Post</info>
EOT
            )
        ;
    }

    /**
     * @see Command
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        //$entity = Validators::validateEntityName($input->getArgument('entity'));
        //list($bundle, $entity) = $this->parseShortcutNotation($entity);
        
        list($bundle, $entity) = Validators::validateEntityName($input->getArgument('entity'));

        $entityClass = $this->getContainer()->get('doctrine')->getEntityNamespace($bundle).'\\'.$entity;
        $metadata = $this->getEntityMetadata($entityClass);
        $bundle   = $this->getApplication()->getKernel()->getBundle($bundle);

        $generator = new AdminClassGenerator($this->getContainer()->get('filesystem'),  __DIR__.'/../Resources/skeleton/admin');
        $generator->generate($bundle, $entity, $metadata[0]);

        $output->writeln(sprintf(
            'The new %s.php class file has been created under %s.',
            $generator->getClassName(),
            $generator->getClassPath()
        ));
    }
    
    protected function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);
    
        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf('The entity name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Blog/Post)', $entity));
        }
    
        return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }
    
    protected function getEntityMetadata($entity)
    {
        $factory = new MetadataFactory($this->getContainer()->get('doctrine'));
    
        return $factory->getClassMetadata($entity)->getMetadata();
    }
}
