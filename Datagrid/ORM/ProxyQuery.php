<?php
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Datagrid\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

/**
 * This class try to unify the query usage with Doctrine
 */
class ProxyQuery implements ProxyQueryInterface
{
    protected $queryBuilder;

    protected $sortBy;

    protected $sortOrder;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function execute(array $params = array(), $hydrationMode = null)
    {
        // always clone the original queryBuilder
        $queryBuilder = clone $this->queryBuilder;

        // todo : check how doctrine behave, potential SQL injection here ...
        if ($this->getSortBy()) {
            $sortBy = $this->getSortBy();
            if (strpos($sortBy, '.') === false) { // add the current alias
                $sortBy = $queryBuilder->getRootAlias().'.'.$sortBy;
            }
            $queryBuilder->orderBy($sortBy, $this->getSortOrder());
        }

        return $this->getFixedQueryBuilder($queryBuilder)->getQuery()->execute($params, $hydrationMode);
    }

    /**
     * This method alters the query to return a clean set of object with a working
     * set of Object
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return void
     */
    private function getFixedQueryBuilder(QueryBuilder $queryBuilder)
    {
        $queryBuilderId = clone $queryBuilder;

        // step 1 : retrieve the targeted class
        $from = $queryBuilderId->getDQLPart('from');
        $class = $from[0]->getFrom();

        // step 2 : retrieve the column id
        $idName = current($queryBuilderId->getEntityManager()->getMetadataFactory()->getMetadataFor($class)->getIdentifierFieldNames());

        // step 3 : retrieve the different subjects id
        $select = sprintf('%s.%s', $queryBuilderId->getRootAlias(), $idName);
        $queryBuilderId->resetDQLPart('select');
        $queryBuilderId->add('select', 'DISTINCT '.$select);

        $results  = $queryBuilderId->getQuery()->execute(array(), Query::HYDRATE_ARRAY);
        $idx      = array();
        $connection = $queryBuilder->getEntityManager()->getConnection();
        foreach($results as $id) {
            $idx[] = $connection->quote($id[$idName]);
        }

        // step 4 : alter the query to match the targeted ids
        if (count($idx) > 0) {
            $queryBuilder->andWhere(sprintf('%s IN (%s)', $select, implode(',', $idx)));
            $queryBuilder->setMaxResults(null);
            $queryBuilder->setFirstResult(null);
        }

        return $queryBuilder;
    }

    public function __call($name, $args)
    {
        return call_user_func_array(array($this->queryBuilder, $name), $args);
    }

    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
    }

    public function getSortBy()
    {
        return $this->sortBy;
    }

    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function getSingleScalarResult()
    {
        $query = $this->queryBuilder->getQuery();

        return $query->getSingleScalarResult();
    }

    public function __clone()
    {
        $this->queryBuilder = clone $this->queryBuilder;
    }

    public function getQueryBuilder()
    {
      return $this->queryBuilder;
    }

    function setFirstResult($firstResult)
    {
        $this->queryBuilder->setFirstResult($firstResult);
    }

    function getFirstResult()
    {
        $this->queryBuilder->getFirstResult();
    }

    function setMaxResults($maxResults)
    {
        $this->queryBuilder->setMaxResults($maxResults);
    }

    function getMaxResults()
    {
        $this->queryBuilder->getMaxResults();
    }
}
