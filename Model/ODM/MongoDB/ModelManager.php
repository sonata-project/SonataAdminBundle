<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Model\ODM\MongoDB;

use Sonata\AdminBundle\Model\BaseModelManager;
use Sonata\AdminBundle\Admin\ODM\MongoDB\FieldDescription;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Symfony\Component\Form\Exception\PropertyAccessDeniedException;

class ModelManager extends BaseModelManager
{

    protected $documentManager;

    /**
     *
     * @param \Doctrine\ODM\MongoDB\DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
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
        return $this->documentManager->getMetadataFactory()->getMetadataFor($class);
    }

    /**
     * Returns true is the model has some metadata
     *
     * @param $class
     * @return boolean
     */
    public function hasMetadata($class)
    {
        return $this->documentManager->getMetadataFactory()->hasMetadataFor($class);
    }

    /**
     * Returns a new FieldDescription
     *
     * @throws \RunTimeException
     * @param $class
     * @param $name
     * @param array $options
     * @return \Sonata\AdminBundle\Admin\ORM\FieldDescription
     */
    public function getNewFieldDescriptionInstance($class, $name, array $options = array())
    {
        if (!is_string($name)) {
            throw new \RunTimeException('The name argument must be a string');
        }

        $metadata = $this->getMetadata($class);

        $fieldDescription = new FieldDescription;
        $fieldDescription->setName($name);
        $fieldDescription->setOptions($options);

        if (isset($metadata->fieldMappings[$name]['reference'])) {
            $fieldDescription->setAssociationMapping($metadata->fieldMappings[$name]);
        }

        if (isset($metadata->fieldMappings[$name])) {
            $fieldDescription->setFieldMapping($metadata->fieldMappings[$name]);
        }

        return $fieldDescription;
    }

    public function create($object)
    {
        $this->documentManager->persist($object);
        $this->documentManager->flush();
    }

    public function update($object)
    {
        $this->documentManager->persist($object);
        $this->documentManager->flush();
    }

    public function delete($object)
    {
        $this->documentManager->remove($object);
        $this->documentManager->flush();
    }

    /**
     * Find one object from the given class repository.
     *
     * @param string $class Class name
     * @param string|int $id Identifier. Can be a string with several IDs concatenated, separated by '-'.
     * @return Object
     */
    public function find($class, $id)
    {
        if (is_numeric($id))
            $id = intval($id);
        return $this->documentManager->getRepository($class)->find($id);
    }

    /**
     * @param $class
     * @param array $criteria
     * @return array
     */
    public function findBy($class, array $criteria = array())
    {
        return $this->documentManager->getRepository($class)->findBy($criteria);
    }

    /**
     * @param $class
     * @param array $criteria
     * @return array
     */
    public function findOneBy($class, array $criteria = array())
    {
        return $this->documentManager->getRepository($class)->findOneBy($criteria);
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->documentManager;
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
     * @param $class
     * @param string $alias
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQuery($class, $alias = 'o')
    {
        $repository = $this->getEntityManager()->getRepository($class);

        return $repository->createQueryBuilder($alias);
    }

    /**
     * @param $query
     * @return mixed
     */
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

    /**
     * @throws \RuntimeException
     * @param $entity
     * @return
     */
    public function getIdentifierValues($document)
    {
        /* if (!$this->documentManager->getUnitOfWork()->isInIdentityMap($document)) {
          throw new \RuntimeException('Entities passed to the choice field must be managed');
          } */

        return array($this->documentManager->getUnitOfWork()->getDocumentIdentifier($document));
    }

    /**
     * @param $class
     * @return mixed
     */
    public function getIdentifierFieldNames($class)
    {
        return array($this->getMetadata($class)->getIdentifier());
    }

    /**
     * @throws \RunTimeException
     * @param $entity
     * @return null|string
     */
    public function getNormalizedIdentifier($document)
    {
        if (is_scalar($document)) {
            throw new \RunTimeException('Invalid argument, object or null required');
        }

        // the entities is not managed
        if (!$document || !$this->getEntityManager()->getUnitOfWork()->isInIdentityMap($document)) {
            return null;
        }

        $values = $this->getIdentifierValues($document);

        return implode('-', $values);
    }

    /**
     * @param $class
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryProxy
     * @param array $idx
     * @return void
     */
    public function addIdentifiersToQuery($class, ProxyQueryInterface $queryProxy, array $idx)
    {
        $queryBuilder = $queryProxy->getQueryBuilder();
        $queryBuilder->field('_id')->in($idx);
    }

    /**
     * Deletes a set of $class identified by the provided $idx array
     *
     * @param $class
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryProxy
     * @return void
     */
    public function batchDelete($class, ProxyQueryInterface $queryProxy)
    {
        $queryBuilder = $queryProxy->getQueryBuilder()->remove()->getQuery()->execute();

        $this->documentManager->flush();
        $this->documentManager->clear();
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
     * @return array
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
            $values['_sort_order'] = 'ASC';
            $values['_sort_by'] = $fieldDescription->getOption('sortable');
        }

        return array('filter' => $values);
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridInterface $datagrid
     * @param $page
     * @return array
     */
    public function getPaginationParameters(DatagridInterface $datagrid, $page)
    {
        $values = $datagrid->getValues();

        $values['_page'] = $page;

        return array('filter' => $values);
    }

    /**
     * @param sring $class
     * @return array
     */
    public function getDefaultSortValues($class)
    {
        return array(
            '_sort_order' => 'ASC',
            '_sort_by' => $this->getModelIdentifier($class),
            '_page' => 1
        );
    }

    /**
     * @param string $class
     * @param object $instance
     * @return mixed
     */
    public function modelTransform($class, $instance)
    {
        return $instance;
    }

    /**
     * @param string $class
     * @param array $array
     * @return object
     */
    public function modelReverseTransform($class, array $array = array())
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

            $setter = 'set' . $this->camelize($name);

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

}
