<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder;

use Sonata\AdminBundle\Admin\FieldDescription;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListCollection;
    
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class ListBuilder implements ListBuilderInterface
{

    public function getBaseList(array $options = array())
    {

        return new ListCollection;
    }

    public function addField(ListCollection $list, FieldDescription $fieldDescription)
    {

        return $list->add($fieldDescription);
    }

    /**
     * The method define the correct default settings for the provided FieldDescription
     *
     * @param FieldDescription $fieldDescription
     * @return void
     */
    public function fixFieldDescription(Admin $admin, FieldDescription $fieldDescription, array $options = array())
    {
        if ($fieldDescription->getName() == '_action')
        {
          $this->buildActionFieldDescription($fieldDescription);
        }

        $fieldDescription->mergeOptions($options);
        $fieldDescription->setAdmin($admin);

        // set the default field mapping
        if (isset($admin->getClassMetaData()->fieldMappings[$fieldDescription->getName()])) {
            $fieldDescription->setFieldMapping($admin->getClassMetaData()->fieldMappings[$fieldDescription->getName()]);
        }

        // set the default association mapping
        if (isset($admin->getClassMetaData()->associationMappings[$fieldDescription->getName()])) {
            $fieldDescription->setAssociationMapping($admin->getClassMetaData()->associationMappings[$fieldDescription->getName()]);
        }

        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), get_class($admin)));
        }        

        $fieldDescription->setOption('code', $fieldDescription->getOption('code', $fieldDescription->getName()));
        $fieldDescription->setOption('label', $fieldDescription->getOption('label', $fieldDescription->getName()));

        if (!$fieldDescription->getTemplate()) {

            $fieldDescription->setTemplate(sprintf('SonataAdminBundle:CRUD:list_%s.html.twig', $fieldDescription->getType()));

            if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_ONE) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list_many_to_one.html.twig');
            }

            if ($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_ONE) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list_one_to_one.html.twig');
            }

            if ($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_MANY) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list_one_to_many.html.twig');
            }

            if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_MANY) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list_many_to_many.html.twig');
            }
        }

        if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_ONE) {
            $admin->attachAdminClass($fieldDescription);
        }

        if ($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_ONE) {
            $admin->attachAdminClass($fieldDescription);
        }

        if ($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_MANY) {
            $admin->attachAdminClass($fieldDescription);
        }

        if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_MANY) {
            $admin->attachAdminClass($fieldDescription);
        }
    }
    
    public function buildActionFieldDescription(FieldDescription $fieldDescription)
    {
        if (null === $fieldDescription->getTemplate())
        {
            $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list__action.html.twig');
        }
        
        if (null === $fieldDescription->getType())
        {
            $fieldDescription->setType('action');
        }
        
        if (null === $fieldDescription->getOption('name'))
        {
            $fieldDescription->setOption('name', 'Action');
        }
        
        if (null === $fieldDescription->getOption('code'))
        {
            $fieldDescription->setOption('code', 'Action');
        }
      
        return $fieldDescription;
    }
}