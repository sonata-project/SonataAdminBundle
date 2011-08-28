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

use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCommand;
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
class GenerateAdminDoctrineCommand extends GenerateDoctrineCommand
{
    /**
     * Mapping for show fields
     *
     * @var array
     */
    private $showMapping = array(
        DoctrineType::TARRAY        => null,
        DoctrineType::BIGINT        => 'integer',
        DoctrineType::BOOLEAN       => 'checkbox',
        DoctrineType::DATETIME      => 'datetime',
        DoctrineType::DATETIMETZ    => 'datetime',
        DoctrineType::DATE          => 'date',
        DoctrineType::TIME          => 'time',
        DoctrineType::DECIMAL       => 'number',
        DoctrineType::INTEGER       => 'integer',
        DoctrineType::OBJECT        => null,
        DoctrineType::SMALLINT      => 'integer',
        DoctrineType::STRING        => 'text',
        DoctrineType::TEXT          => 'textarea',
        DoctrineType::FLOAT         => 'number',
        ClassMetadataInfo::ONE_TO_MANY => 'orm_one_to_many',
        ClassMetadataInfo::MANY_TO_MANY => 'orm_many_to_many',
        ClassMetadataInfo::MANY_TO_ONE => 'orm_many_to_one',
        ClassMetadataInfo::ONE_TO_ONE => 'orm_one_to_one',
    );
    
    /**
     * Mapping for form fields
     *
     * @var array
     */
    private $formMapping = array(
        DoctrineType::TARRAY        => null,
        DoctrineType::BIGINT        => 'integer',
        DoctrineType::BOOLEAN       => 'checkbox',
        DoctrineType::DATETIME      => 'datetime',
        DoctrineType::DATETIMETZ    => 'datetime',
        DoctrineType::DATE          => 'date',
        DoctrineType::TIME          => 'time',
        DoctrineType::DECIMAL       => 'number',
        DoctrineType::INTEGER       => 'integer',
        DoctrineType::OBJECT        => null,
        DoctrineType::SMALLINT      => 'integer',
        DoctrineType::STRING        => 'text',
        DoctrineType::TEXT          => 'textarea',
        DoctrineType::FLOAT         => 'number',
        ClassMetadataInfo::ONE_TO_MANY => 'sonata_type_model',
        ClassMetadataInfo::MANY_TO_MANY => 'sonata_type_model',
        ClassMetadataInfo::MANY_TO_ONE => 'sonata_type_model',
        ClassMetadataInfo::ONE_TO_ONE => 'sonata_type_model',
    );
    
    /**
     * Mapping for list fields
     *
     * @var array
     */
    private $listMapping = array(
        DoctrineType::TARRAY        => null,
        DoctrineType::BIGINT        => 'integer',
        DoctrineType::BOOLEAN       => 'checkbox',
        DoctrineType::DATETIME      => 'datetime',
        DoctrineType::DATETIMETZ    => 'datetime',
        DoctrineType::DATE          => 'date',
        DoctrineType::TIME          => 'time',
        DoctrineType::DECIMAL       => 'number',
        DoctrineType::INTEGER       => 'integer',
        DoctrineType::OBJECT        => null,
        DoctrineType::SMALLINT      => 'integer',
        DoctrineType::STRING        => 'text',
        DoctrineType::TEXT          => 'textarea',
        DoctrineType::FLOAT         => 'number',
        ClassMetadataInfo::ONE_TO_MANY => 'orm_one_to_many',
        ClassMetadataInfo::MANY_TO_MANY => 'orm_many_to_many',
        ClassMetadataInfo::MANY_TO_ONE => 'orm_many_to_one',
        ClassMetadataInfo::ONE_TO_ONE => 'orm_one_to_one',
    );
    
    /**
     * Mapping for filter fields
     *
     * @var string
     */
    private $filterMapping = array(
        DoctrineType::TARRAY        => null,
        DoctrineType::BIGINT        => 'doctrine_orm_number',
        DoctrineType::BOOLEAN       => 'doctrine_orm_boolean',
        DoctrineType::DATETIME      => null,
        DoctrineType::DATETIMETZ    => null,
        DoctrineType::DATE          => 'doctrine_orm_date',
        DoctrineType::TIME          => 'doctrine_orm_time',
        DoctrineType::DECIMAL       => 'doctrine_orm_number',
        DoctrineType::INTEGER       => 'doctrine_orm_number',
        DoctrineType::OBJECT        => null,
        DoctrineType::SMALLINT      => 'doctrine_orm_number',
        DoctrineType::STRING        => 'doctrine_orm_string',
        DoctrineType::TEXT          => 'doctrine_orm_string',
        DoctrineType::FLOAT         => 'doctrine_orm_number',
        ClassMetadataInfo::ONE_TO_MANY => null,
        ClassMetadataInfo::MANY_TO_MANY => null,
        ClassMetadataInfo::MANY_TO_ONE => null,
        ClassMetadataInfo::ONE_TO_ONE => null,
    );
    
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
            ))
            ->setDescription('Generate an admin class for an entity using Doctrine ORM')
            ->setHelp(<<<EOT
The <info>sonata:admin:generator-doctrine</info> command generates a Admin based on a Doctrine entity.

The default command generates all admin fields for an Doctrine entity.

<info>php app/console sonata:admin:generator-doctrine AcmeBlogBundle:Post AcmeBlogBundle</info>

Using the --interactive option allows to output a dialog helper which let you choose configuration for each field of Doctrine entity.

<info>php app/console sonata:admin:generator-doctrine AcmeBlogBundle:Post AcmeBlogBundle --interactive</info>
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
        $entity = Validators::validateEntityName($input->getArgument('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);
        
        $adminBundle = $input->getArgument("bundle") ? $input->getArgument("bundle") : $bundle;
        $isInteractive = $input->getOption('interactive');
        
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
        
        foreach($fields as $name => $info)
        {
            //Show
            $showFieldType = $this->getShowMapping($info['type']);
            $showFieldDescriptionOptions = array();
            
            $fieldsShow[] = array(
                'name'      => $name,
                'type'      => str_replace("\n", '', var_export($showFieldType, true)),
                'options'   => str_replace("\n", '', var_export($showFieldDescriptionOptions, true))
            );
            
            //Form
            $formFieldType = $this->getFormMapping($info['type']);
            
            //Required field
            $required = false;
            if ($metadata->hasAssociation($name))
            {
                //Check columns join
                if (isset($info['joinColumns']))
                {
                    $required = true;
                    foreach($info['joinColumns'] as $joinColumn)
                    {
                        if ($joinColumn['nullable'])
                        {
                            $required = false;
                            break;
                        }
                    }
                }
            }
            elseif ($info['required'])
            {
                $required = true;
            }
            
            $formFieldOptions = array('required' => $required);
            $formFieldDescriptionOptions = array();
            
            $fieldsForm[] = array(
                'name'                  => $name,
                'type'                  => str_replace("\n", '', var_export($formFieldType, true)),
                'options'               => str_replace("\n", '', var_export($formFieldOptions, true)),
                'description_options'   => str_replace("\n", '', var_export($formFieldDescriptionOptions, true))
            );
            
            $listFieldType = $this->getListMapping($info['type']);
            $listFieldDescriptionOptions = $showFieldDescriptionOptions;
            
            //List
            $fieldsList[] = array(
                'name'      => $name,
                'type'      => str_replace("\n", '', var_export($listFieldType, true)),
                'options'   => str_replace("\n", '', var_export($listFieldDescriptionOptions, true))
            );
            
            $filterType = $this->getFilterMapping($info['type']);
            $filterOptions = array();
            $filterFieldType = null;
            $filterFieldOptions = array();
            
            //Filter
            if (!is_null($filterType))
            {
                $fieldsFilter[] = array(
                    'name'          => $name,
                    'type'          => str_replace("\n", '', var_export($filterType, true)),
                    'options'       => str_replace("\n", '', var_export($filterOptions, true)),
                    'field_type'    => str_replace("\n", '', var_export($filterFieldType, true)),
                    'field_options' => str_replace("\n", '', var_export($filterFieldOptions, true)),
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
        $generator->generate($adminBundle, $entity);
        
        //Admin generation
        $generator = new AdminGenerator(__DIR__.'/../Resources/skeleton');
        $generator->generate($adminBundle, $entity, $fieldsShow, $fieldsForm, $fieldsList, $fieldsFilter);
        
        //Service config generation
        $adminClass = $generator->getAdminClass();
        $bundleName = $adminBundle->getName();
        
        $configFile = $this->getContainer()->getParameter('kernel.root_dir').'/config/config.yml';
        
        $this->addConfigService($configFile, $service, $adminClass, $group, $label, $entityClass, $bundleName);
        
        $output->writeln(sprintf(
            "Admin files generated for %s entity in %s bundle", 
            $entity, 
            $adminBundle->getName()
        ));
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
    private function addConfigService($configFilePath, $service, $admin, $group, $label, $entityClass, $bundle)
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
        $servicePos = strpos($configContent, 'services');
        if (false === $servicePos)
        {
            $code = "services:\n".$code;
            $servicePos = 0;
        }
        else
        {
            $servicePos = strpos($configContent, "\n", $servicePos) + 1;
        }
        
        $startContent = substr($configContent, 0, $servicePos);
        $endContent = substr($configContent, $servicePos, strlen($configContent) - $servicePos);
        
        $fileContent = $startContent.$code.$endContent;
        
        file_put_contents($configFilePath, $fileContent);
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
    
    /**
     * Get form field type from doctrine type
     *
     * @param string $fieldType doctrine type
     * @return string
     */
    private function getFormMapping($fieldType)
    {
        return isset($this->formMapping[$fieldType]) ? $this->formMapping[$fieldType] : null;
    }
    
    /**
     * Get show field type from doctrine type
     *
     * @param string $fieldType doctrine type
     * @return string
     */
    private function getShowMapping($fieldType)
    {
        return isset($this->showMapping[$fieldType]) ? $this->showMapping[$fieldType] : null;
    }
    
    /**
     * Get list field type from doctrine type
     *
     * @param string $fieldType doctrine type
     * @return string
     */
    private function getListMapping($fieldType)
    {
        return isset($this->listMapping[$fieldType]) ? $this->listMapping[$fieldType] : null;
    }
    
    /**
     * Get filter field type from doctrine type
     *
     * @param string $fieldType doctrine type
     * @return string
     */
    private function getFilterMapping($fieldType)
    {
        return isset($this->filterMapping[$fieldType]) ? $this->filterMapping[$fieldType] : null;
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
        foreach($fields as $name)
        {
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