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

namespace Sonata\AdminBundle\Tests\App\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Model\LockInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\App\Admin\FieldDescription;
use Sonata\Exporter\Source\SourceIteratorInterface;

class ModelManager implements ModelManagerInterface, LockInterface
{
    /**
     * @var FooRepository
     */
    private $repository;

    public function __construct(FooRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getNewFieldDescriptionInstance(string $class, string $name, array $options = []): FieldDescriptionInterface
    {
        if (!isset($options['route']['name'])) {
            $options['route']['name'] = 'edit';
        }

        if (!isset($options['route']['parameters'])) {
            $options['route']['parameters'] = [];
        }

        $fieldDescription = new FieldDescription();
        $fieldDescription->setName($name);
        $fieldDescription->setOptions($options);

        return $fieldDescription;
    }

    public function create(object $object): void
    {
    }

    public function update(object $object): void
    {
    }

    public function delete(object $object): void
    {
    }

    public function findBy(string $class, array $criteria = []): array
    {
        return [];
    }

    public function findOneBy(string $class, array $criteria = []): ?object
    {
        return null;
    }

    public function find(string $class, $id): ?object
    {
        return $this->repository->byId($id);
    }

    public function batchDelete(string $class, ProxyQueryInterface $queryProxy): void
    {
    }

    public function createQuery(string $class, string $alias = 'o'): ProxyQueryInterface
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function getIdentifierValues(object $model): array
    {
        return [];
    }

    public function getIdentifierFieldNames(string $class): array
    {
        return [];
    }

    public function getNormalizedIdentifier(object $model): string
    {
        return $model->getId();
    }

    public function getUrlSafeIdentifier(object $model): string
    {
        return $this->getNormalizedIdentifier($model);
    }

    public function getModelInstance(string $class): object
    {
        switch ($class) {
            case Translated::class:
                return new Translated();
            default:
                return new Foo('test_id', 'foo_name');
        }
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function getModelCollectionInstance(string $class): Collection
    {
        return new ArrayCollection();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function collectionRemoveElement(Collection $collection, object $element): void
    {
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function collectionAddElement(Collection $collection, object $element): void
    {
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function collectionHasElement(Collection $collection, object $element): bool
    {
        return true;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function collectionClear(Collection $collection): void
    {
    }

    public function getDefaultSortValues(string $class): array
    {
        return [];
    }

    public function getDefaultPerPageOptions(string $class): array
    {
        return [];
    }

    public function modelReverseTransform(string $class, array $array = []): object
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function modelTransform(string $class, object $instance): object
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function executeQuery(object $query): void
    {
    }

    public function getDataSourceIterator(DatagridInterface $datagrid, array $fields, ?int $firstResult = null, ?int $maxResult = null): SourceIteratorInterface
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function getExportFields(string $class): array
    {
        return [];
    }

    public function addIdentifiersToQuery(string $class, ProxyQueryInterface $query, array $idx): void
    {
    }

    public function getLockVersion(object $object)
    {
        return null;
    }

    public function lock(object $object, ?int $expectedVersion): void
    {
    }
}
