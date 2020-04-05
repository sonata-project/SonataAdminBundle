<?php

declare(strict_types=1);

namespace Sonata\AdminBundle\Tests\Fixtures\Query;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

final class CustomQuery implements ProxyQueryInterface
{
    /**
     * @var mixed
     */
    protected $sortOrder;

    /**
     * @var string
     */
    protected $sortBy;

    public function __call($name, $args)
    {
        // TODO: Implement __call() method.
    }

    public function execute(array $params = [], $hydrationMode = null)
    {
        // TODO: Implement execute() method.
    }

    public function getSortBy()
    {
        return $this->sortBy;
    }

    public function setSortBy($parentAssociationMappings, $fieldMapping)
    {
        $alias = $this->entityJoin($parentAssociationMappings);
        $this->sortBy = $alias.'.'.$fieldMapping['fieldName'];

        return $this;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getSingleScalarResult()
    {
        // TODO: Implement getSingleScalarResult() method.
    }

    public function setFirstResult($firstResult)
    {
        // TODO: Implement setFirstResult() method.
    }

    public function getFirstResult()
    {
        // TODO: Implement getFirstResult() method.
    }

    public function setMaxResults($maxResults)
    {
        // TODO: Implement setMaxResults() method.
    }

    public function getMaxResults()
    {
        // TODO: Implement getMaxResults() method.
    }

    public function getUniqueParameterId()
    {
        // TODO: Implement getUniqueParameterId() method.
    }

    public function entityJoin(array $associationMappings)
    {
        // TODO: Implement entityJoin() method.
    }
}
