<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Model;

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

interface ModelManagerInterface
{
    /**
     * Returns true if the model has a relation
     *
     * @abstract
     * @param string $name
     * @return booleab
     */
    function hasMetadata($name);

    /**
     *
     * @param string $name
     * @return \Doctrine\ORM\Mapping\ClassMetadataInfo
     */
    function getMetadata($name);

    /**
     * Returns a new FieldDescription
     *
     * @abstract
     * @param string $class
     * @param string $name
     * @param array $options
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    function getNewFieldDescriptionInstance($class, $name, array $options = array());

    /**
     * @param $object
     * @return void
     */
    function create($object);

    /**
     * @param object $object
     * @return void
     */
    function update($object);

    /**
     * @param object $object
     * @return void
     */
    function delete($object);

    /**
     * @param string $class
     * @param array $criteria
     * @return object
     */
    function findBy($class, array $criteria = array());

    /**
     * @abstract
     * @param $class
     * @param array $criteria
     * @return void
     */
    function findOneBy($class, array $criteria = array());

    /**
     * @abstract
     * @param $class
     * @param $id
     * @return void
     */
    function find($class, $id);

    /**
     * @abstract
     * @param $class
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryProxy
     * @return void
     */
    function batchDelete($class, ProxyQueryInterface $queryProxy);

    /**
     * @abstract
     * @param  $parentAssociationMapping
     * @param  $class
     * @return void
     */
    function getParentFieldDescription($parentAssociationMapping, $class);

    /**
     * @abstract
     * @param string $class
     * @param string $alias
     * @return a query instance
     */
    function createQuery($class, $alias = 'o');

    /**
     * @abstract
     * @param string $class
     * @return string
     */
    function getModelIdentifier($class);

    /**
     *
     * @param object $model
     * @return mixed
     */
    function getIdentifierValues($model);

    /**
     * @param string $class
     * @return array
     */
    function getIdentifierFieldNames($class);

    /**
     * @abstract
     * @param $entity
     */
    function getNormalizedIdentifier($entity);

    /**
     * @abstract
     * @param string $class
     * @return void
     */
    function getModelInstance($class);

    /**
     * @abstract
     * @param string $class
     * @return void
     */
    function getModelCollectionInstance($class);

    /**
     * Removes an element from the collection
     *
     * @param mixed $collection
     * @param mixed $element
     * @return void
     */
    function collectionRemoveElement(&$collection, &$element);

    /**
     * Add an element from the collection
     *
     * @param mixed $collection
     * @param mixed $element
     * @return mixed
     */
    function collectionAddElement(&$collection, &$element);

    /**
     * Check if the element exists in the collection
     *
     * @param mixed $collection
     * @param mixed $element
     * @return boolean
     */
    function collectionHasElement(&$collection, &$element);

    /**
     * Clear the collection
     *
     * @param mixed $collection
     * @return mixed
     */
    function collectionClear(&$collection);

    /**
     * Returns the parameters used in the columns header
     *
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @param \Sonata\AdminBundle\Datagrid\DatagridInterface $datagrid
     * @return string
     */
    function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid);

    /**
     * @param sring $class
     * @return array
     */
    function getDefaultSortValues($class);

    /**
     * @param string $class
     * @param array $array
     * @return void
     */
    function modelReverseTransform($class, array $array = array());

    /**
     * @param string $class
     * @param object $instance
     * @return void
     */
    function modelTransform($class, $instance);

    /**
     * @param mixed $query
     * @return void
     */
    function executeQuery($query);

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridInterface $datagrid
     * @param array $fields
     * @param null $firstResult
     * @param null $maxResult
     * @return void
     */
    function getDataSourceIterator(DatagridInterface $datagrid, array $fields, $firstResult = null, $maxResult = null);

    /**
     * @param $class
     * @return array
     */
    function getExportFields($class);
}
