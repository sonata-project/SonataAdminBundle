<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Model\ORM;

use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Admin\ORM\FieldDescription;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Doctrine\ORM\EntityManager;


class ModelManager implements ModelManagerInterface
{

    protected $entityManager;

    /**
     *
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * Returns the related model's metadata
     *
     * @abstract
     * @param string $name
     * @return void
     */
    public function getMetadata($class)
    {
        return $this->entityManager->getMetadataFactory()->getMetadataFor($class);
    }

    /**
     * Returns true is the model has some metadata
     *
     * @return boolean
     */
    public function hasMetadata($class)
    {
        return $this->entityManager->getMetadataFactory()->hasMetadataFor($class);
    }

    /**
     * Returns a new FieldDescription
     *
     * @abstract
     * @return \Sonata\AdminBundle\Admin\ORM\FieldDescription
     */
    public function getNewFieldDescriptionInstance($class, $name, array $options = array())
    {
        $metadata = $this->getMetadata($class);

        $fieldDescription = new FieldDescription;
        $fieldDescription->setName($name);
        $fieldDescription->setOptions($options);

        if (isset($metadata->associationMappings[$name])) {
            $fieldDescription->setAssociationMapping($metadata->associationMappings[$name]);
        }

        if (isset($metadata->fieldMappings[$name])) {
            $fieldDescription->setFieldMapping($metadata->fieldMappings[$name]);
        }

        return $fieldDescription;
    }

    public function create($object)
    {
        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }

    public function update($object)
    {
        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }

    public function delete($object)
    {
        $this->entityManager->remove($object);
        $this->entityManager->flush();
    }

    public function find($class, $id)
    {
        return $this->entityManager->find($class, $id);
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param string $parentAssociationMapping
     * @param string $class
     * @return \Sonata\AdminBundle\Admin\ORM\FieldDescription
     */
    public function getParentFieldDescription($parentAssociationMapping, $class)
    {
        $fieldName = $parentAssociationMapping['fieldName'];

        $metadata = $this->getMetadata($class);

        $associatingMapping = $metadata->associationMappings[$parentAssociationMapping];

        $fieldDescription = $this->getNewFieldDescriptionInstance($class, $fieldName);
        $fieldDescription->setName($parentAssociationMapping);
        $fieldDescription->setAssociationMapping($associatingMapping);

        return $fieldDescription;
    }

    /**
     * @param string $alias
     * @return \Doctrine\ORM\QueryBuilder a query instance
     */
    public function createQuery($class, $alias = 'o')
    {
        $repository = $this->getEntityManager()->getRepository($class);

        return $repository->createQueryBuilder($alias);
    }

    /**
     * @param string $class
     * @return string
     */
    public function getEntityIdentifier($class)
    {
        return $this->getMetadata($class)->identifier;
    }

    /**
     * Deletes a set of $class identified by the provided $idx array
     *
     * @param string $class
     * @param array $idx
     * @return void
     */
    public function batchDelete($class, $idx)
    {
        $queryBuilder = $this->createQuery($class, 'o');
        $objects = $queryBuilder
            ->select('o')
            ->add('where', $queryBuilder->expr()->in('o.id', $idx))
            ->getQuery()
            ->execute();

        foreach ($objects as $object) {
            $this->entityManager->remove($object);
        }

        $this->entityManager->flush();
    }

    /**
     * Returns a new model instance
     * @param string $class
     * @return
     */
    public function getModelInstance($class)
    {
        return new $class;
    }

    /**
     * Returns the parameters used in the columns header
     *
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @param \Sonata\AdminBundle\Datagrid\DatagridInterface $datagrid
     * @return string
     */
    public function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid)
    {
        $values = $datagrid->getValues();

        if ($fieldDescription->getOption('sortable') == $values['_sort_by']) {
            if ($values['_sort_order'] == 'ASC') {
                $values['_sort_order'] = 'DESC';
            } else {
                $values['_sort_order'] = 'ASC';
            }
        } else {
            $values['_sort_order']  = 'ASC';
            $values['_sort_by']     = $fieldDescription->getOption('sortable');
        }

        return $values;
    }

    /**
     * @param sring $class
     * @return array
     */
    public function getDefaultSortValues($class)
    {
        return array(
            '_sort_order' => 'ASC',
            '_sort_by'    => implode(',', $this->getEntityIdentifier($class))
        );
    }
}
