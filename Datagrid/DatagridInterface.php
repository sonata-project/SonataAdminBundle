<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Sonata\AdminBundle\Datagrid;

use Sonata\AdminBundle\Filter\FilterInterface;

interface DatagridInterface
{

    /**
     * @abstract
     * @return \Sonata\AdminBundle\Datagrid\PagerInterface
     */
    function getPager();

    /**
     * @abstract
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    function getQuery();

    /**
     * @abstract
     * @return array
     */
    function getResults();

    /**
     * @abstract
     * @return void
     */
    function buildPager();

    /**
     * @abstract
     * @param \Sonata\AdminBundle\Filter\FilterInterface $filter
     * @return \Sonata\AdminBundle\Filter\FilterInterface
     */
    function addFilter(FilterInterface $filter);

    /**
     * @abstract
     * @return array
     */
    function getFilters();

    /**
     * @abstract
     * @return array
     */
    function getValues();

    /**
     * @abstract
     * @return array
     */
    function getColumns();
}