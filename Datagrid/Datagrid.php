<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Datagrid;

use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\FilterInterface;

class Datagrid implements DatagridInterface
{
    /**
     *
     * The filter instances
     * @var array
     */
    protected $filters = array();

    protected $values;

    protected $columns;

    protected $pager;

    protected $bound = false;

    protected $query;

    public function __construct(ProxyQueryInterface $query, ListCollection $columns, PagerInterface $pager, array $values = array())
    {
        $this->pager    = $pager;
        $this->query    = $query;
        $this->values   = $values;
        $this->columns  = $columns;
    }

    /**
     * @return \Sonata\AdminBundle\Datagrid\PagerInterface
     */
    public function getPager()
    {
        return $this->pager;
    }


    public function getResults()
    {
        $this->buildPager();

        return $this->pager->getResults();
    }

    public function buildPager()
    {
        if($this->bound) {
            return;
        }

        foreach ($this->getFilters() as $name => $filter) {
            $filter->apply(
                $this->query,
                isset($this->values[$name]) ? $this->values[$name] : null
            );
        }

        $this->query->setSortBy(isset($this->values['_sort_by']) ? $this->values['_sort_by'] : null);
        $this->query->setSortOrder(isset($this->values['_sort_order']) ? $this->values['_sort_order'] : null);

        $this->pager->setPage(isset($this->values['_page']) ? $this->values['_page'] : 1);
        $this->pager->setQuery($this->query);
        $this->pager->init();

        $this->bound = true;
    }

    /**
     * @param \Sonata\AdminBundle\Filter\FilterInterface $filter
     * @return \Sonata\AdminBundle\Filter\FilterInterface
     */
    public function addFilter(FilterInterface $filter)
    {
        return $this->filters[$filter->getName()] = $filter;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getQuery()
    {
        return $this->query;
    }
}