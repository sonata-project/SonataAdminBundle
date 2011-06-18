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
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Exception\PropertyAccessDeniedException;

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
     * @return \Doctrine\ORM\Mapping\ClassMetadataInfo
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

    public function findOne($class, $id)
    {
        return $this->entityManager->getRepository($class)->find($id);
    }

    public function find($class, array $criteria = array())
    {
        return $this->entityManager->getRepository($class)->findBy($criteria);
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

    public function executeQuery($query)
    {
        if ($query instanceof QueryBuilder) {
          return $query->getQuery()->execute();
        }

        return $query->execute();
    }

    /**
     * @param string $class
     * @return string
     */
    public function getModelIdentifier($class)
    {
        return $this->getMetadata($class)->identifier;
    }

    public function getIdentifierValues($entity)
    {
        if (!$this->getEntityManager()->getUnitOfWork()->isInIdentityMap($entity)) {
            throw new \RuntimeException('Entities passed to the choice field must be managed');
        }

        $data = $this->getEntityManager()->getUnitOfWork()->getOriginalEntityData($entity);
        $values = array();
        foreach ($this->getEntityManager()->getUnitOfWork()->getEntityIdentifier($entity) as $identifier => $oldvalue) {
            if(isset($data[$identifier]))
                $values[$identifier] = $data[$identifier];
        }
        return $values;
    }

    public function getIdentifierFieldNames($class)
    {
        return $this->getMetadata($class)->getIdentifierFieldNames();
    }

    public function getNormalizedIdentifier($entity)
    {
        // the entities is not managed
        if (!$this->getEntityManager()->getUnitOfWork()->isInIdentityMap($entity)) {
            return null;
        }

        $values = $this->getIdentifierValues($entity);

        return implode('-', $values);
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
            '_sort_by'    => implode(',', $this->getModelIdentifier($class))
        );
    }

    /**
     * @param string $class
     * @param object $instance
     * @return void
     */
    function modelTransform($class, $instance)
    {
        return $instance;
    }

    /**
     * @param string $class
     * @param array $array
     * @return object
     */
    function modelReverseTransform($class, array $array = array())
    {
        $instance = $this->getModelInstance($class);
        $metadata = $this->getMetadata($class);

        $reflClass = $metadata->reflClass;
        foreach ($array as $name => $value) {

            $reflection_property = false;
            // property or association ?
            if (array_key_exists($name, $metadata->fieldMappings)) {

                $property = $metadata->fieldMappings[$name]['fieldName'];
                $reflection_property = $metadata->reflFields[$name];

            } else if (array_key_exists($name, $metadata->associationMappings)) {
                $property = $metadata->associationMappings[$name]['fieldName'];
            } else {
                $property = $name;
            }

            $setter = 'set'.$this->camelize($name);

            if ($reflClass->hasMethod($setter)) {
                if (!$reflClass->getMethod($setter)->isPublic()) {
                    throw new PropertyAccessDeniedException(sprintf('Method "%s()" is not public in class "%s"', $setter, $reflClass->getName()));
                }

                $instance->$setter($value);
            } else if ($reflClass->hasMethod('__set')) {
                // needed to support magic method __set
                $instance->$property = $value;
            } else if ($reflClass->hasProperty($property)) {
                if (!$reflClass->getProperty($property)->isPublic()) {
                    throw new PropertyAccessDeniedException(sprintf('Property "%s" is not public in class "%s". Maybe you should create the method "set%s()"?', $property, $reflClass->getName(), ucfirst($property)));
                }

                $instance->$property = $value;
            } else if ($reflection_property) {
                $reflection_property->setValue($instance, $value);
            }
        }

        return $instance;
    }

    /**
     * method taken from PropertyPath
     *
     * @param  $property
     * @return mixed
     */
    protected function camelize($property)
    {
       return preg_replace(array('/(^|_)+(.)/e', '/\.(.)/e'), array("strtoupper('\\2')", "'_'.strtoupper('\\1')"), $property);
    }

    /**
     * @param string $class
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getModelCollectionInstance($class)
    {
        return new \Doctrine\Common\Collections\ArrayCollection();
    }

    function collectionClear(&$collection)
    {
        return $collection->clear();
    }

    function collectionHasElement(&$collection, &$element)
    {
        return $collection->contains($element);
    }

    function collectionAddElement(&$collection, &$element)
    {
        return $collection->add($element);
    }

    function collectionRemoveElement(&$collection, &$element)
    {
        return $collection->removeElement($element);
    }
}
