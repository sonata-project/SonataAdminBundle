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
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;

/**
 * A model manager is a bridge between the model classes and the admin functionality.
 */
interface ModelManagerInterface
{
    /**
     * @param array<string, mixed> $options
     *
     * @phpstan-param class-string $class
     */
    public function getNewFieldDescriptionInstance(string $class, string $name, array $options = []): FieldDescriptionInterface;

    /**
     * @throws ModelManagerException
     */
    public function create(object $object): void;

    /**
     * @throws ModelManagerException
     */
    public function update(object $object): void;

    /**
     * @throws ModelManagerException
     */
    public function delete(object $object): void;

    /**
     * @param array<string, mixed> $criteria
     *
     * @return object[] all objects matching the criteria
     *
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return T[]
     */
    public function findBy(string $class, array $criteria = []): array;

    /**
     * @param array<string, mixed> $criteria
     *
     * @return object|null an object matching the criteria or null if none match
     *
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return T|null
     */
    public function findOneBy(string $class, array $criteria = []): ?object;

    /**
     * @param int|string $id
     *
     * @return object|null the object with id or null if not found
     *
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return T|null
     */
    public function find(string $class, $id): ?object;

    /**
     * @throws ModelManagerException
     *
     * @phpstan-param class-string $class
     */
    public function batchDelete(string $class, ProxyQueryInterface $query): void;

    /**
     * @phpstan-param class-string $class
     */
    public function createQuery(string $class): ProxyQueryInterface;

    /**
     * Get the identifiers of this model class.
     *
     * This returns an array to handle cases like a primary key that is
     * composed of multiple columns. If you need a string representation,
     * use getNormalizedIdentifier resp. getUrlSafeIdentifier
     *
     * @return array<int|string> list of all identifiers of this model
     */
    public function getIdentifierValues(object $model): array;

    /**
     * Get a list of the field names models of the specified fully qualified
     * class name used to store the identifier.
     *
     * @return string[]
     *
     * @phpstan-param class-string $class
     */
    public function getIdentifierFieldNames(string $class): array;

    /**
     * Get the identifiers for this model class as a string.
     */
    public function getNormalizedIdentifier(object $model): ?string;

    /**
     * Get the identifiers as a string that is safe to use in a url.
     *
     * This is similar to getNormalizedIdentifier but guarantees an id that can
     * be used in a URL.
     */
    public function getUrlSafeIdentifier(object $model): ?string;

    /**
     * Create a new instance of the model of the specified class.
     *
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return T
     */
    public function getModelInstance(string $class): object;

    /**
     * @param array<string, mixed> $array
     *
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return T
     */
    public function modelReverseTransform(string $class, array $array = []): object;

    public function supportsQuery(object $query): bool;

    /**
     * @return mixed
     */
    public function executeQuery(object $query);

    /**
     * @return string[]
     *
     * @phpstan-param class-string $class
     */
    public function getExportFields(string $class): array;

    /**
     * @param array<int, int|string> $idx
     *
     * @phpstan-param class-string $class
     */
    public function addIdentifiersToQuery(string $class, ProxyQueryInterface $query, array $idx): void;
}
