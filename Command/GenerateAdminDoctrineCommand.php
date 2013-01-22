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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Bundle\DoctrineBundle\Command\DoctrineCommand;
use Symfony\Bundle\DoctrineBundle\Mapping\MetadataFactory;

use Sensio\Bundle\GeneratorBundle\Command\Validators;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\DBAL\Types\Type as DoctrineType;

use Sonata\AdminBundle\Generator\ControllerGenerator;
use Sonata\AdminBundle\Generator\AdminGenerator;


/**
 * Command that create admin files and configuration for a doctrine entity
 * 
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class GenerateAdminDoctrineCommand extends DoctrineCommand
{
    
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('entity', InputArgument::REQUIRED, 'The entity to create his Admin class'),
                new InputArgument('bundle', InputArgument::OPTIONAL, 'The bundle where admin is generated'),
                new InputOption('interactive', '', InputOption::VALUE_NONE, 'Output a interactive dialog to configure Admin'),
                new InputOption('force', '', InputOption::VALUE_NONE, 'Force write of generated files if they already exist'),
                new InputOption('controller', '', InputOption::VALUE_NONE, 'Generate controller file'),
            ))
            ->setDescription('Generate an admin class for an entity using Doctrine ORM')
            ->setHelp(<<<EOT
The <info>sonata:admin:generator-doctrine</info> command generates a Admin based on a Doctrine entity.

The default command generates all admin fields for an Doctrine entity.

<info>php app/console sonata:admin:generator-doctrine AcmeBlogBundle:Post AcmeBlogBundle</info>

Using the --interactive option allows to output a dialog helper which let you choose configuration for each field of Doctrine entity.

<info>php app/console sonata:admin:generator-doctrine AcmeBlogBundle:Post AcmeBlogBundle --interactive</info>

Using the --force option will override files and configuration if they exist.

<info>php app/console sonata:admin:generator-doctrine AcmeBlogBundle:Post AcmeBlogBundle --force</info>
EOT
            )
            ->setName('sonata:admin:generate-doctrine')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Check for bundle generator if not present throw error
        if (!class_exists('Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle')) {
            throw new \RuntimeException("SensioGeneratorBundle must be install to execute this command.");
        }
        
        //Check entity
        $entity = Validators::validateEntityName($input->getArgument('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);
        
        $adminBundle = $input->getArgument("bundle") ? $input->getArgument("bundle") : $bundle;
        $isInteractive = $input->getOption('interactive');
        $force = $input->getOption('force');
        
        $entityClass = $this->getContainer()->get('doctrine')->getEntityNamespace($bundle).'\\'.$entity;
        
        $bundle = $this->getApplication()->getKernel()->getBundle($bundle);
        $adminBundle = $this->getApplication()->getKernel()->getBundle($adminBundle);
        $metadata = $this->getEntityMetadata($entityClass);
        $metadata = $metadata[0];
        
        $fields = $this->getFieldsFromMetadata($metadata);
        
        //Field configuration
        $fieldsShow = array();
        $fieldsList = array();
        $fieldsForm = array();
        $fieldsFilter = array();
        
        $listTypeGuesser = $this->getContainer()->get('sonata.admin.guesser.orm_list_chain');
        $showTypeGuesser = $this->getContainer()->get('sonata.admin.guesser.orm_show_chain');
        $filterTypeGuesser = $this->getContainer()->get('sonata.admin.guesser.orm_datagrid_chain');
        
        $test = $this->getContainer()->get('form.factory');
        
        
        foreach($fields as $name => $info) {
            //Show
            $showTypeGuess = $showTypeGuesser->guessType($entityClass, $name);
            
            $fieldsShow[] = array(
                'name'      => $name,
                'type'      => str_replace("\n", '', var_export($showTypeGuess->getType(), true)),
                'options'   => str_replace("\n", '', var_export($showTypeGuess->getOptions(), true))
            );
            
            //Form
            $formFieldType = null;
            $formFieldOptions = array();
            $formFieldDescriptionOptions = array();
            
            $fieldsForm[] = array(
                'name'                  => $name,
                'type'                  => str_replace("\n", '', var_export($formFieldType, true)),
                'options'               => str_replace("\n", '', var_export($formFieldOptions, true)),
                'description_options'   => str_replace("\n", '', var_export($formFieldDescriptionOptions, true))
            );
            
            //List
            $listTypeGuess = $listTypeGuesser->guessType($entityClass, $name);
            
            $fieldsList[] = array(
                'name'      => $name,
                'type'      => str_replace("\n", '', var_export($listTypeGuess->getType(), true)),
                'options'   => str_replace("\n", '', var_export($listTypeGuess->getOptions(), true))
            );
            
            //Filter
            $filterTypeGuess = $filterTypeGuesser->guessType($entityClass, $name);
            $filterOptions = $filterTypeGuess->getOptions();
            
            if (isset($filterOptions['field_type']) && $filterOptions['field_type'] !== false) {
                $fieldsFilter[] = array(
                    'name'          => $name,
                    'type'          => str_replace("\n", '', var_export($filterTypeGuess->getType(), true)),
                    'options'       => str_replace("\n", '', var_export($filterTypeGuess->getOptions(), true))
                );
            }
        }
        
        $service = $adminBundle->getNamespace();
        $serviceParts = explode('\\', $service);
        $serviceParts = array_map('strtolower', $serviceParts);
        
        $group = implode('', $serviceParts);
        
        $label = strtolower($entity);
        
        array_push($serviceParts, 'admin', strtolower($entity));
        
        $service = implode('.', $serviceParts);
        
        //Controller generation
        $generator = new ControllerGenerator(__DIR__.'/../Resources/skeleton');
        $generator->generate($adminBundle, $entity, $force);
        
        //Admin generation
        $generator = new AdminGenerator(__DIR__.'/../Resources/skeleton');
        $generator->generate($adminBundle, $entity, $fieldsShow, $fieldsForm, $fieldsList, $fieldsFilter, $force);
        
        //Service config generation
        $adminClass = $generator->getAdminClass();
        $bundleName = $adminBundle->getName();
        
        $configFile = $this->getContainer()->getParameter('kernel.root_dir').'/config/config.yml';
        
        $this->addConfigService($configFile, $service, $adminClass, $group, $label, $entityClass, $bundleName, $force);
        
        $output->writeln(sprintf(
            "Admin files generated for %s entity in %s bundle", 
            $entity, 
            $adminBundle->getName()
        ));
    }
    
    /**
     * Return dialog helper for interactivity
     *
     * @return DialogHelper
     */
    protected function getDialogHelper()
    {
        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog || get_class($dialog) !== 'Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper') {
            $this->getHelperSet()->set($dialog = new DialogHelper());
        }

        return $dialog;
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
    
    /**
     * Add admin service configuration to config file
     *
     * @param string $configFilePath The file path for config
     * @param string $service Service name
     * @param string $admin Full class admin name (with namespace)
     * @param string $group Admin group
     * @param string $label Admin label
     * @param string $entityClass Full class entity name (with namespace)
     * @param string $bundle Bundle name
     * 
     * @return void
     */
    private function addConfigService($configFilePath, $service, $admin, $group, $label, $entityClass, $bundle, $force = false)
    {
        $parts       = explode('\\', $admin);
        $adminClass  = array_pop($parts);
        
        $code = sprintf(<<<EOC
    %s:
        class: %s
        tags:
            - { name: sonata.admin, manager_type: orm, group: %s, label: %s }
        arguments: [null, %s, %s:%s]

EOC
        , $service, $admin, $group, $label, $entityClass, $bundle, $adminClass);
        
        $configContent = file_get_contents($configFilePath);
        $posOldService = strpos($configContent, $service);
        if ($posOldService !== false && !$force)
        {
            throw new \RuntimeException('Service config already added.');
        }
        elseif ($posOldService !== false)
        {
            //Remove old config
            $beginPos = $posOldService - 4;
            $endPos =  strpos($configContent, "\n", $posOldService);
            
            do {
                $nextLine = $endPos + 1;
                $lineText = substr($configContent, $nextLine, strpos($configContent, "\n", $nextLine) - $nextLine);
                $countSpace = strlen($lineText) - strlen(ltrim($lineText, ' '));
                
                if ($countSpace >= 8)
                {
                    $endPos = strpos($configContent, "\n", $nextLine);
                }
            } while ($countSpace >= 8);
            
            $configContent = substr($configContent, 0, $beginPos).substr($configContent, $endPos + 1);
        }
        
        $servicePos = strpos($configContent, 'services');
        if (false === $servicePos) {
            $code = "services:\n".$code;
            $servicePos = 0;
        }
        else {
            $servicePos = strpos($configContent, "\n", $servicePos) + 1;
        }
        
        $startContent = substr($configContent, 0, $servicePos);
        $endContent = substr($configContent, $servicePos, strlen($configContent) - $servicePos);
        
        $fileContent = $startContent.$code.$endContent;
        
        file_put_contents($configFilePath, $fileContent);
    }
    
    /**
     * Returns an array of fields. Fields can be both column fields and
     * association fields.
     *
     * @param ClassMetadataInfo $metadata
     * @return array $fields
     */
    private function getFieldsFromMetadata(ClassMetadataInfo $metadata)
    {
        $fields = (array) $metadata->fieldNames;
        
        // Remove the primary key field if it's not managed manually
        if (!$metadata->isIdentifierNatural()) {
            $fields = array_diff($fields, $metadata->identifier);
        }
        
        $returnFields = array();
        foreach($fields as $name) {
            $returnFields[$name] = array(
                'type' => $metadata->fieldMappings[$name]['type'],
                'required' => !(boolean)$metadata->fieldMappings[$name]['nullable']
            );
        }

        foreach ($metadata->associationMappings as $fieldName => $relation) {
            $returnFields[$fieldName] = $relation;
        }

        return $returnFields;
    }
}