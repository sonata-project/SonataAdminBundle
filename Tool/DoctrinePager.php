<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tool;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * Doctrine pager class.
 *
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrinePager.class.php 28897 2010-03-30 20:30:24Z Jonathan.Wage $
 */
class DoctrinePager extends Pager implements \Serializable
{

    protected
        $query                  = null,
        $query_builder          = null,
        $count_column           = 'id';
    
    /**
     * Serialize the pager object
     *
     * @return string $serialized
     */
    public function serialize()
    {
        $vars = get_object_vars($this);
        unset($vars['query']);
        return serialize($vars);
    }

    /**
     * Unserialize a pager object
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $array = unserialize($serialized);

        foreach ($array as $name => $values)
        {
            $this->$name = $values;
        }
    }

    /**
     * Returns a query for counting the total results.
     *
     * @return Doctrine\ORM\Query
     */
    public function getCountQuery()
    {
        $query_builder = clone $this->getQueryBuilder();

        $query_builder->select(sprintf('count(%s.%s) as nb', $query_builder->getRootAlias(), $this->getCountColumn()));
        
        return $query_builder->getQuery();
    }

    public function getCountColumn()
    {

        return $this->count_column;
    }

    public function setCountColumn($count_column) {

        return $this->count_column = $count_column;
    }

    /**
     * @see Pager
     */
    public function init()
    {
        $this->resetIterator();

        $countQuery = $this->getCountQuery();
        $countQuery->setParameters($this->getParameters());

        $count = $countQuery->getSingleScalarResult();

        $this->setNbResults($count);

        $query = $this->getQuery();
        
        $query
            ->setParameters($this->getParameters())
            ->setFirstResult(0)
            ->setMaxResults(0);

        if (0 == $this->getPage() || 0 == $this->getMaxPerPage() || 0 == $this->getNbResults()) {
            $this->setLastPage(0);
        }
        else
        {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

            $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));

            $query
                ->setFirstResult($offset)
                ->setMaxResults($this->getMaxPerPage());
        }
    }

    /**
     * Get the query builder for the pager.
     *
     * @return Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilder()
    {

        return $this->query_builder;
    }

    /**
     * Set query object for the pager
     *
     * @param Doctrine\ORM\QueryBuilder $query
     */
    public function setQueryBuilder(\Doctrine\ORM\QueryBuilder $query_builder)
    {
        $this->query_builder = $query_builder;
    }

    /**
     * Get the query for the pager.
     *
     * @return Doctrine\ORM\Query
     */

    public function getQuery()
    {

        if (!$this->query) {
            $this->query = $this->getQueryBuilder()->getQuery();
        }

        return $this->query;
    }

    /**
     * Retrieve the object for a certain offset
     *
     * @param integer $offset
     *
     * @return object
     */
    protected function retrieveObject($offset)
    {
        $queryForRetrieve = clone $this->getQuery();
        $queryForRetrieve
            ->setFirstResult($offset - 1)
            ->setMaxResults(1);

        $results = $queryForRetrieve->execute();

        return $results[0];
    }

    /**
     * Get all the results for the pager instance
     *
     * @param mixed $hydrationMode A hydration mode identifier
     *
     * @return  array
     */
    public function getResults($hydrationMode = Query::HYDRATE_OBJECT)
    {

        return $this->getQuery()->execute(array(), $hydrationMode);
    }

}
