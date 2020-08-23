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

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\App\Admin\FieldDescription;

final class ModelManager implements ModelManagerInterface
{
    /**
     * @var FooRepository
     */
    private $repository;

    public function __construct(FooRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getNewFieldDescriptionInstance($class, $name, array $options = [])
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

    public function create($object): void
    {
    }

    public function update($object): void
    {
    }

    public function delete($object): void
    {
    }

    public function findBy($class, array $criteria = [])
    {
        return [];
    }

    public function findOneBy($class, array $criteria = [])
    {
        return null;
    }

    public function find($class, $id)
    {
        return $this->repository->byId($id);
    }

    public function batchDelete($class, ProxyQueryInterface $queryProxy): void
    {
    }

    public function getParentFieldDescription($parentAssociationMapping, $class): FieldDescriptionInterface
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function createQuery($class, $alias = 'o'): ProxyQueryInterface
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function getModelIdentifier($class)
    {
        return 'id';
    }

    public function getIdentifierValues($model)
    {
        return [];
    }

    public function getIdentifierFieldNames($class)
    {
        return [];
    }

    public function getNormalizedIdentifier($model)
    {
        return null;
    }

    public function getUrlSafeIdentifier($model)
    {
        return $model->getId();
    }

    public function getModelInstance($class)
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
    public function getModelCollectionInstance($class)
    {
        return [];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function collectionRemoveElement(&$collection, &$element): void
    {
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function collectionAddElement(&$collection, &$element): void
    {
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function collectionHasElement(&$collection, &$element): void
    {
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function collectionClear(&$collection): void
    {
    }

    public function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid)
    {
        return [];
    }

    public function getDefaultSortValues($class)
    {
        return [];
    }

    public function getDefaultPerPageOptions(string $class): array
    {
        return [];
    }

    public function modelReverseTransform($class, array $array = []): object
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function modelTransform($class, $instance): object
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function executeQuery($query): void
    {
    }

    public function getDataSourceIterator(DatagridInterface $datagrid, array $fields, $firstResult = null, $maxResult = null): void
    {
    }

    public function getExportFields($class)
    {
        return [];
    }

    public function getPaginationParameters(DatagridInterface $datagrid, $page)
    {
        return [];
    }

    public function addIdentifiersToQuery($class, ProxyQueryInterface $query, array $idx): void
    {
    }
}
