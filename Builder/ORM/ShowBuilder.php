<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder\ORM;

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

class ShowBuilder implements ShowBuilderInterface
{
    public function getBaseList(array $options = array())
    {
        return new FieldDescriptionCollection;
    }

    public function addField(FieldDescriptionCollection $list, FieldDescriptionInterface $fieldDescription)
    {
        if (!$fieldDescription->getType()) {
            return false;
        }

        switch($fieldDescription->getMappingType()) {
            case ClassMetadataInfo::MANY_TO_ONE:
            case ClassMetadataInfo::MANY_TO_MANY:
            case ClassMetadataInfo::ONE_TO_MANY:
            case ClassMetadataInfo::ONE_TO_ONE:
                // todo
                return;
            default:
                $list->add($fieldDescription);
        }

    }

    /**
     * The method defines the correct default settings for the provided FieldDescription
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @param array $options
     * @return void
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription, array $options = array())
    {
        $fieldDescription->mergeOptions($options);
        $fieldDescription->setAdmin($admin);

        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
            $metadata = $admin->getModelManager()->getMetadata($admin->getClass());

            // set the default field mapping
            if (isset($metadata->fieldMappings[$fieldDescription->getName()])) {
                $fieldDescription->setFieldMapping($metadata->fieldMappings[$fieldDescription->getName()]);
            }

            // set the default association mapping
            if (isset($metadata->associationMappings[$fieldDescription->getName()])) {
                $fieldDescription->setAssociationMapping($metadata->associationMappings[$fieldDescription->getName()]);
            }
        }

        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), get_class($admin)));
        }

        $fieldDescription->setOption('code', $fieldDescription->getOption('code', $fieldDescription->getName()));
        $fieldDescription->setOption('label', $fieldDescription->getOption('label', $fieldDescription->getName()));

        if (!$fieldDescription->getTemplate()) {

            $fieldDescription->setTemplate(sprintf('SonataAdminBundle:CRUD:show_%s.html.twig', $fieldDescription->getType()));

            if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_ONE) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:show_orm_many_to_one.html.twig');
            }

            if ($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_ONE) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:show_orm_one_to_one.html.twig');
            }

            if ($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_MANY) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:show_orm_one_to_many.html.twig');
            }

            if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_MANY) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:show_orm_many_to_many.html.twig');
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
}