<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
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
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\Exporter\Source\SourceIteratorInterface;

/**
 * A model manager is a bridge between the model classes and the admin
 * functionality.
 */
interface ModelManagerInterface
{
    /**
     * @param string $class
     * @param string $name
     */
    public function getNewFieldDescriptionInstance($class, $name, array $options = []): FieldDescriptionInterface;

    /**
     * @param mixed $object
     *
     * @throws ModelManagerException
     */
    public function create($object);

    /**
     * @param mixed $object
     *
     * @throws ModelManagerException
     */
    public function update($object);

    /**
     * @param mixed $object
     *
     * @throws ModelManagerException
     */
    public function delete($object);

    /**
     * @param string $class
     *
     * @return array all objects matching the criteria
     */
    public function findBy($class, array $criteria = []): array;

    /**
     * @param string $class
     *
     * @return object an object matching the criteria or null if none match
     */
    public function findOneBy($class, array $criteria = []): object;

    /**
     * @param string $class
     * @param mixed  $id
     *
     * @return object|null the object with id or null if not found
     */
    public function find($class, $id): ?object;

    /**
     * @param string $class
     *
     * @throws ModelManagerException
     */
    public function batchDelete($class, ProxyQueryInterface $queryProxy);

    /**
     * @param array  $parentAssociationMapping
     * @param string $class
     */
    public function getParentFieldDescription($parentAssociationMapping, $class);

    /**
     * @param string $class
     * @param string $alias
     */
    public function createQuery($class, $alias = 'o'): ProxyQueryInterface;

    /**
     * Get the identifier for the model type of this class.
     *
     * @param string $class fully qualified class name
     */
    public function getModelIdentifier($class): string;

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
    public function getIdentifierValues($model): array;

    /**
     * Get a list of the field names models of the specified class use to store
     * the identifier.
     *
     * @param string $class fully qualified class name
     */
    public function getIdentifierFieldNames($class): array;

    /**
     * Get the identifiers for this model class as a string.
     *
     * @param object $model
     *
     * @return string a string representation of the identifiers for this
     *                instance
     */
    public function getNormalizedIdentifier($model): string;

    /**
     * Get the identifiers as a string that is safe to use in a url.
     *
     * This is similar to getNormalizedIdentifier but guarantees an id that can
     * be used in a URL.
     *
     * @param object $model
     *
     * @return string string representation of the id that is safe to use in a url
     */
    public function getUrlsafeIdentifier($model): string;

    /**
     * Create a new instance of the model of the specified class.
     *
     * @param string $class
     *
     * @return mixed
     */
    public function getModelInstance($class);

    /**
     * @param string $class
     *
     * @return array|\ArrayAccess
     */
    public function getModelCollectionInstance($class);

    /**
     * Removes an element from the collection.
     *
     * @param mixed $collection
     * @param mixed $element
     */
    public function collectionRemoveElement(&$collection, &$element);

    /**
     * Add an element from the collection.
     *
     * @param mixed $collection
     * @param mixed $element
     *
     * @return mixed
     */
    public function collectionAddElement(&$collection, &$element);

    /**
     * Check if the element exists in the collection.
     *
     * @param mixed $collection
     * @param mixed $element
     */
    public function collectionHasElement(&$collection, &$element): bool;

    /**
     * Clear the collection.
     *
     * @param mixed $collection
     *
     * @return mixed
     */
    public function collectionClear(&$collection);

    /**
     * Returns the parameters used in the columns header.
     */
    public function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid): array;

    /**
     * @param string $class
     */
    public function getDefaultSortValues($class): array;

    /**
     * @param string $class
     */
    public function modelReverseTransform($class, array $array = []);

    /**
     * @param string $class
     * @param object $instance
     */
    public function modelTransform($class, $instance);

    /**
     * @param mixed $query
     */
    public function executeQuery($query);

    /**
     * @param int|null $firstResult
     * @param int|null $maxResult
     */
    public function getDataSourceIterator(
        DatagridInterface $datagrid,
        array $fields,
        $firstResult = null,
        $maxResult = null
    ): SourceIteratorInterface;

    /**
     * @param string $class
     */
    public function getExportFields($class): array;

    /**
     * @param int $page
     *
     * @return mixed
     */
    public function getPaginationParameters(DatagridInterface $datagrid, $page);

    /**
     * @param string $class
     */
    public function addIdentifiersToQuery($class, ProxyQueryInterface $query, array $idx);
}
