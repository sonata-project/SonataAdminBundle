<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Datagrid;

use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Filter\FilterInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Interface DatagridInterface.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface DatagridInterface
{
    /**
     * @return PagerInterface
     */
    public function getPager();

    /**
     * @return ProxyQueryInterface
     */
    public function getQuery();

    /**
     * @return array
     */
    public function getResults();

    public function buildPager();

    /**
     * @param FilterInterface $filter
     *
     * @return FilterInterface
     */
    public function addFilter(FilterInterface $filter);

    /**
     * @return array
     */
    public function getFilters();

    /**
     * Reorder filters.
     *
     * @param array $keys
     */
    public function reorderFilters(array $keys);

    /**
     * @return array
     */
    public function getValues();

    /**
     * @return FieldDescriptionCollection
     */
    public function getColumns();

    /**
     * @param string $name
     * @param string $operator
     * @param mixed  $value
     */
    public function setValue($name, $operator, $value);

    /**
     * @return FormInterface
     */
    public function getForm();

    /**
     * @param string $name
     *
     * @return FilterInterface
     */
    public function getFilter($name);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasFilter($name);

    /**
     * @param string $name
     */
    public function removeFilter($name);

    /**
     * @return bool
     */
    public function hasActiveFilters();

    /**
     * @return bool
     */
    public function hasDisplayableFilters();
}
