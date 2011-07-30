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
use Sonata\AdminBundle\Builder\ListBuilderInterface;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

class ListBuilder implements ListBuilderInterface
{
    public function getBaseList(array $options = array())
    {
        return new FieldDescriptionCollection;
    }

    public function addField(FieldDescriptionCollection $list, $type = null, FieldDescriptionInterface $fieldDescription, AdminInterface $admin)
    {
        if ($type == null) {
            throw new \RunTimeException('type guesser on ListBuilder is not yet implemented');
        }

        $fieldDescription->setType($type);

        $this->fixFieldDescription($admin, $fieldDescription);
        $admin->addListFieldDescription($fieldDescription->getName(), $fieldDescription);

        return $list->add($fieldDescription);
    }

    /**
     * The method defines the correct default settings for the provided FieldDescription
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        if ($fieldDescription->getName() == '_action') {
            $this->buildActionFieldDescription($fieldDescription);
        }

        $fieldDescription->setAdmin($admin);

        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
            $metadata = $admin->getModelManager()->getMetadata($admin->getClass());

            // set the default field mapping
            if (isset($metadata->fieldMappings[$fieldDescription->getName()])) {
                $fieldDescription->setFieldMapping($metadata->fieldMappings[$fieldDescription->getName()]);
                if ($fieldDescription->getOption('sortable') !== false) {
                    $fieldDescription->setOption('sortable', $fieldDescription->getOption('sortable', $fieldDescription->getName()));
                }
            }

            // set the default association mapping
            if (isset($metadata->associationMappings[$fieldDescription->getName()])) {
                $fieldDescription->setAssociationMapping($metadata->associationMappings[$fieldDescription->getName()]);
            }

            $fieldDescription->setOption('_sort_order', $fieldDescription->getOption('_sort_order', 'ASC'));
        }

        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), get_class($admin)));
        }

        $fieldDescription->setOption('code', $fieldDescription->getOption('code', $fieldDescription->getName()));
        $fieldDescription->setOption('label', $fieldDescription->getOption('label', $fieldDescription->getName()));

        if (!$fieldDescription->getTemplate()) {

            $fieldDescription->setTemplate(sprintf('SonataAdminBundle:CRUD:list_%s.html.twig', $fieldDescription->getType()));

            if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_ONE) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list_orm_many_to_one.html.twig');
            }

            if ($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_ONE) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list_orm_one_to_one.html.twig');
            }

            if ($fieldDescription->getType() == ClassMetadataInfo::ONE_TO_MANY) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list_orm_one_to_many.html.twig');
            }

            if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_MANY) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list_orm_many_to_many.html.twig');
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

    /**
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    public function buildActionFieldDescription(FieldDescriptionInterface $fieldDescription)
    {
        if (null === $fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list__action.html.twig');
        }

        if (null === $fieldDescription->getType()) {
            $fieldDescription->setType('action');
        }

        if (null === $fieldDescription->getOption('name')) {
            $fieldDescription->setOption('name', 'Action');
        }

        if (null === $fieldDescription->getOption('code')) {
            $fieldDescription->setOption('code', 'Action');
        }

        if (null !== $fieldDescription->getOption('actions')) {
            $actions = $fieldDescription->getOption('actions');
            foreach ($actions as $k => $action) {
                if (!isset($action['template'])) {
                    $actions[$k]['template'] = sprintf('SonataAdminBundle:CRUD:list__action_%s.html.twig', $k);
                }
            }

            $fieldDescription->setOption('actions', $actions);
        }

        return $fieldDescription;
    }
}