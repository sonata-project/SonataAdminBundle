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
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;

use Symfony\Component\Form\FormFactory;

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

    protected $formFactory;

    protected $results;

    public function __construct(ProxyQueryInterface $query, FieldDescriptionCollection $columns, PagerInterface $pager, array $values = array())
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

        if (!$this->results) {
            $this->results = $this->pager->getResults();
        }

        return $this->results;
    }

    public function buildPager()
    {
        if ($this->bound) {
            return;
        }

        foreach ($this->getFilters() as $name => $filter) {
            $value = isset($this->values[$name]) ? $this->values[$name] : null;

            $filter->getField()->bind($value);
            $filter->apply($this->query, $value);
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
     * @return void
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[$filter->getName()] = $filter;
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