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
    }

    public function findOneBy($class, array $criteria = [])
    {
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
    }

    public function getIdentifierFieldNames($class)
    {
    }

    public function getNormalizedIdentifier($model)
    {
    }

    public function getUrlsafeIdentifier($model)
    {
        return $model->getId();
    }

    public function getModelInstance($class)
    {
    }

    public function getModelCollectionInstance($class)
    {
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
    }

    public function getDefaultSortValues($class)
    {
        return [];
    }

    public function modelReverseTransform($class, array $array = [])
    {
    }

    public function modelTransform($class, $instance)
    {
    }

    public function executeQuery($query)
    {
    }

    public function getDataSourceIterator(DatagridInterface $datagrid, array $fields, $firstResult = null, $maxResult = null)
    {
    }

    public function getExportFields($class)
    {
    }

    public function getPaginationParameters(DatagridInterface $datagrid, $page)
    {
        return [];
    }

    public function addIdentifiersToQuery($class, ProxyQueryInterface $query, array $idx)
    {
    }
}
