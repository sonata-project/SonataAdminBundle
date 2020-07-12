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

    public function create($object)
    {
    }

    public function update($object)
    {
    }

    public function delete($object)
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

    public function batchDelete($class, ProxyQueryInterface $queryProxy)
    {
    }

    public function getParentFieldDescription($parentAssociationMapping, $class)
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function createQuery($class, $alias = 'o')
    {
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
        return new Foo('test_id', 'foo_name');
    }

    public function getModelCollectionInstance($class)
    {
        return [];
    }

    public function collectionRemoveElement(&$collection, &$element)
    {
    }

    public function collectionAddElement(&$collection, &$element)
    {
    }

    public function collectionHasElement(&$collection, &$element)
    {
    }

    public function collectionClear(&$collection)
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

    public function modelReverseTransform($class, array $array = [])
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function modelTransform($class, $instance)
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function executeQuery($query)
    {
    }

    public function getDataSourceIterator(DatagridInterface $datagrid, array $fields, $firstResult = null, $maxResult = null)
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

    public function addIdentifiersToQuery($class, ProxyQueryInterface $query, array $idx)
    {
    }
}
