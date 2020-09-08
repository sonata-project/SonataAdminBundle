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
 * A model manager is a bridge between the model classes and the admin functionality.
 *
 * @method bool supportsQuery(object $query)
 */
interface ModelManagerInterface extends DatagridManagerInterface
{
    /**
     * @param string $class
     * @param string $name
     *
     * @return FieldDescriptionInterface
     *
     * @phpstan-param class-string $class
     */
    public function getNewFieldDescriptionInstance($class, $name, array $options = []);

    /**
     * @param object $object
     *
     * @throws ModelManagerException
     */
    public function create($object);

    /**
     * @param object $object
     *
     * @throws ModelManagerException
     */
    public function update($object);

    /**
     * @param object $object
     *
     * @throws ModelManagerException
     */
    public function delete($object);

    /**
     * @param string               $class
     * @param array<string, mixed> $criteria
     *
     * @return object[] all objects matching the criteria
     *
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return T[]
     */
    public function findBy($class, array $criteria = []);

    /**
     * @param string               $class
     * @param array<string, mixed> $criteria
     *
     * @return object|null an object matching the criteria or null if none match
     *
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return T|null
     */
    public function findOneBy($class, array $criteria = []);

    /**
     * @param string $class
     * @param mixed  $id
     *
     * @return object|null the object with id or null if not found
     *
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return T|null
     */
    public function find($class, $id);

    /**
     * @param string $class
     *
     * @throws ModelManagerException
     *
     * @phpstan-param class-string $class
     */
    public function batchDelete($class, ProxyQueryInterface $queryProxy);

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.73. To be removed in 4.0.
     * Use AdminInterface::getParentFieldDescription instead.
     *
     * @param array  $parentAssociationMapping
     * @param string $class
     *
     * @phpstan-param class-string $class
     */
    public function getParentFieldDescription($parentAssociationMapping, $class);

    /**
     * @param string $class
     * @param string $alias
     *
     * @return ProxyQueryInterface
     *
     * @phpstan-param class-string $class
     */
    public function createQuery($class, $alias = 'o');

    /**
     * Get the identifier for the model type of this class.
     *
     * NEXT_MAJOR: Remove this function in favor of getIdentifierFieldNames
     *
     * @deprecated Prefer to use getIdentifierFieldNames
     *
     * @param string $class fully qualified class name
     *
     * @return string
     *
     * @phpstan-param class-string $class
     */
    public function getModelIdentifier($class);

    /**
     * Get the identifiers of this model class.
     *
     * This returns an array to handle cases like a primary key that is
     * composed of multiple columns. If you need a string representation,
     * use getNormalizedIdentifier resp. getUrlSafeIdentifier
     *
     * @param object $model
     *
     * @return array list of all identifiers of this model
     */
    public function getIdentifierValues($model);

    /**
     * Get a list of the field names models of the specified class use to store
     * the identifier.
     *
     * @param string $class fully qualified class name
     *
     * @return string[]
     *
     * @phpstan-param class-string $class
     */
    public function getIdentifierFieldNames($class);

    /**
     * Get the identifiers for this model class as a string.
     *
     * @param object $model
     *
     * @return string a string representation of the identifiers for this
     *                instance
     */
    public function getNormalizedIdentifier($model);

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
    public function getUrlSafeIdentifier($model);

    /**
     * Create a new instance of the model of the specified class.
     *
     * @param string $class
     *
     * @return object
     *
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return T
     */
    public function getModelInstance($class);

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.75. To be removed in 4.0. Use doctrine/collections instead.
     *
     * @param string $class
     *
     * @return array|\ArrayAccess
     */
    public function getModelCollectionInstance($class);

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.75. To be removed in 4.0. Use doctrine/collections instead.
     *
     * Removes an element from the collection.
     *
     * @param array  $collection
     * @param object $element
     */
    public function collectionRemoveElement(&$collection, &$element);

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.75. To be removed in 4.0. Use doctrine/collections instead.
     *
     * Add an element from the collection.
     *
     * @param array  $collection
     * @param object $element
     */
    public function collectionAddElement(&$collection, &$element);

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.75. To be removed in 4.0. Use doctrine/collections instead.
     *
     * Check if the element exists in the collection.
     *
     * @param array  $collection
     * @param object $element
     *
     * @return bool
     */
    public function collectionHasElement(&$collection, &$element);

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.75. To be removed in 4.0. Use doctrine/collections instead.
     *
     * Clear the collection.
     *
     * @param array $collection
     */
    public function collectionClear(&$collection);

    /**
     * Returns the parameters used in the columns header.
     *
     * NEXT_MAJOR: - Remove this function
     *             - Replace admin.modelmanager.sortparameters to admin.datagrid.sortparameters
     *
     * @deprecated since sonata-project/admin-bundle 3.66. To be removed in 4.0.
     *
     * @return array<string, mixed>
     */
    public function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid);

    /**
     * @param string $class
     *
     * @return object
     *
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return T
     */
    public function modelReverseTransform($class, array $array = []);

    /**
     * @param string $class
     * @param object $instance
     *
     * @return object
     *
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return T
     */
    public function modelTransform($class, $instance);

    // NEXT_MAJOR: Uncomment this.
//    public function supportsQuery(object $query): bool;

    /**
     * @param object $query
     */
    public function executeQuery($query);

    /**
     * @param int|null $firstResult
     * @param int|null $maxResult
     *
     * @return SourceIteratorInterface
     */
    public function getDataSourceIterator(
        DatagridInterface $datagrid,
        array $fields,
        $firstResult = null,
        $maxResult = null
    );

    /**
     * @param string $class
     *
     * @return string[]
     *
     * @phpstan-param class-string $class
     */
    public function getExportFields($class);

    /**
     * @param int $page
     *
     * NEXT_MAJOR: - Remove this function
     *             - Replace admin.modelmanager.paginationparameters to admin.datagrid.paginationparameters
     *
     * @deprecated since sonata-project/admin-bundle 3.66. To be removed in 4.0.
     *
     * @return array<string, mixed>
     */
    public function getPaginationParameters(DatagridInterface $datagrid, $page);

    /**
     * @param string $class
     *
     * @phpstan-param class-string $class
     */
    public function addIdentifiersToQuery($class, ProxyQueryInterface $query, array $idx);
}
