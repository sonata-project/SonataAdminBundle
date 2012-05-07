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
     * Returns a new FieldDescription
     *
     * @param string $class
     * @param string $name
     * @param array  $options
     *
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    function getNewFieldDescriptionInstance($class, $name, array $options = array());

    /**
     * @param mixed $object
     *
     * @return void
     */
    function create($object);

    /**
     * @param mixed $object
     *
     * @return void
     */
    function update($object);

    /**
     * @param object $object
     *
     * @return void
     */
    function delete($object);

    /**
     * @param string $class
     * @param array  $criteria
     *
     * @return object
     */
    function findBy($class, array $criteria = array());

    /**
     * @param string $class
     * @param array  $criteria
     *
     * @return void
     */
    function findOneBy($class, array $criteria = array());

    /**
     * @param string $class
     * @param mixed  $id
     *
     * @return void
     */
    function find($class, $id);

    /**
     * @param string                                           $class
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryProxy
     *
     * @return void
     */
    function batchDelete($class, ProxyQueryInterface $queryProxy);

    /**
     * @param array  $parentAssociationMapping
     * @param string $class
     *
     * @return void
     */
    function getParentFieldDescription($parentAssociationMapping, $class);

    /**
     * @param string $class
     * @param string $alias
     *
     * @return ProxyQueryInterface
     */
    function createQuery($class, $alias = 'o');

    /**
     * @param string $class
     *
     * @return string
     */
    function getModelIdentifier($class);

    /**
     *
     * @param object $model
     *
     * @return mixed
     */
    function getIdentifierValues($model);

    /**
     * @param string $class
     *
     * @return array
     */
    function getIdentifierFieldNames($class);

    /**
     * @param mixed $entity
     */
    function getNormalizedIdentifier($entity);

    /**
     * @param string $class
     *
     * @return mixed
     */
    function getModelInstance($class);

    /**
     * @param string $class
     *
     * @return void
     */
    function getModelCollectionInstance($class);

    /**
     * Removes an element from the collection
     *
     * @param mixed $collection
     * @param mixed $element
     *
     * @return void
     */
    function collectionRemoveElement(&$collection, &$element);

    /**
     * Add an element from the collection
     *
     * @param mixed $collection
     * @param mixed $element
     *
     * @return mixed
     */
    function collectionAddElement(&$collection, &$element);

    /**
     * Check if the element exists in the collection
     *
     * @param mixed $collection
     * @param mixed $element
     *
     * @return boolean
     */
    function collectionHasElement(&$collection, &$element);

    /**
     * Clear the collection
     *
     * @param mixed $collection
     *
     * @return mixed
     */
    function collectionClear(&$collection);

    /**
     * Returns the parameters used in the columns header
     *
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @param \Sonata\AdminBundle\Datagrid\DatagridInterface      $datagrid
     *
     * @return array
     */
    function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid);

    /**
     * @param string $class
     *
     * @return array
     */
    function getDefaultSortValues($class);

    /**
     * @param string $class
     * @param array  $array
     *
     * @return void
     */
    function modelReverseTransform($class, array $array = array());

    /**
     * @param string $class
     * @param object $instance
     *
     * @return void
     */
    function modelTransform($class, $instance);

    /**
     * @param mixed $query
     *
     * @return void
     */
    function executeQuery($query);

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridInterface $datagrid
     * @param array                                          $fields
     * @param null                                           $firstResult
     * @param null                                           $maxResult
     *
     * @return \Exporter\Source\SourceIteratorInterface
     */
    function getDataSourceIterator(DatagridInterface $datagrid, array $fields, $firstResult = null, $maxResult = null);

    /**
     * @param string $class
     *
     * @return array
     */
    function getExportFields($class);

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridInterface $datagrid
     * @param int                                            $page
     *
     * @return mixed
     */
    function getPaginationParameters(DatagridInterface $datagrid, $page);

    /**
     * @param string                                           $class
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $query
     * @param array                                            $idx
     *
     * @return void
     */
    function addIdentifiersToQuery($class, ProxyQueryInterface $query, array $idx);
}
