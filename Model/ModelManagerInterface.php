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

/**
 * A model manager is a bridge between the model classes and the admin
 * functionality.
 */
interface ModelManagerInterface
{
    /**
     * Returns a new FieldDescription
     *
     * @param string $class
     * @param string $name
     * @param array  $options
     *
     * @return FieldDescriptionInterface
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
     * @return array all objects matching the criteria
     */
    function findBy($class, array $criteria = array());

    /**
     * @param string $class
     * @param array  $criteria
     *
     * @return object an object matching the criteria or null if none match
     */
    function findOneBy($class, array $criteria = array());

    /**
     * @param string $class
     * @param mixed  $id
     *
     * @return object the object with id or null if not found
     */
    function find($class, $id);

    /**
     * @param string                                           $class
     * @param ProxyQueryInterface $queryProxy
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
     * Get the identifier for the model type of this class.
     *
     * @param string $class fully qualified class name
     *
     * @return string
     */
    function getModelIdentifier($class);

    /**
     * Get the identifiers of this model class.
     *
     * This returns an array to handle cases like a primary key that is
     * composed of multiple columns. If you need a string representation,
     * use getNormalizedIdentifier resp. getUrlsafeIdentifier
     *
     * @param object $model
     *
     * @return array list of all identifiers of this model
     */
    function getIdentifierValues($model);

    /**
     * Get a list of the field names models of the specified class use to store
     * the identifier.
     *
     * @param string $class fully qualified class name
     *
     * @return array
     */
    function getIdentifierFieldNames($class);

    /**
     * Get the identifiers for this model class as a string.
     *
     * @param object $model
     *
     * @return string a string representation of the identifiers for this
     *      instance
     */
    function getNormalizedIdentifier($model);

    /**
     * Get the identifiers as a string that is save to use in an url.
     *
     * This is similar to getNormalizedIdentifier but guarantees an id that can
     * be used in an URL.
     *
     * @param object $model
     *
     * @return string string representation of the id that is save to use in an url
     */
    function getUrlsafeIdentifier($model);

    /**
     * Create a new instance of the model of the specified class.
     *
     * @param string $class
     *
     * @return mixed
     */
    function getModelInstance($class);

    /**
     * @param string $class
     *
     * @return array
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
     * @param FieldDescriptionInterface $fieldDescription
     * @param DatagridInterface      $datagrid
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
     */
    function executeQuery($query);

    /**
     * @param DatagridInterface $datagrid
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
     * @param DatagridInterface $datagrid
     * @param int                                            $page
     *
     * @return mixed
     */
    function getPaginationParameters(DatagridInterface $datagrid, $page);

    /**
     * @param string                                           $class
     * @param ProxyQueryInterface $query
     * @param array                                            $idx
     *
     * @return void
     */
    function addIdentifiersToQuery($class, ProxyQueryInterface $query, array $idx);
}
