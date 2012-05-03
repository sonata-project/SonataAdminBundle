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
     * @return \Sonata\AdminBundle\Datagrid\PagerInterface
     */
    function getPager();

    /**
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    function getQuery();

    /**
     * @return array
     */
    function getResults();

    /**
     * @return void
     */
    function buildPager();

    /**
     * @param \Sonata\AdminBundle\Filter\FilterInterface $filter
     *
     * @return \Sonata\AdminBundle\Filter\FilterInterface
     */
    function addFilter(FilterInterface $filter);

    /**
     * @return array
     */
    function getFilters();

    /**
     * @return array
     */
    function getValues();

    /**
     * @return array
     */
    function getColumns();

    /**
     * @param string $name
     * @param string $operator
     * @param mixed  $value
     */
    function setValue($name, $operator, $value);

    /**
     * @return \Symfony\Component\Form\Form
     */
    function getForm();

    /**
     * @param string $name
     *
     * @return \Sonata\AdminBundle\Filter\FilterInterface
     */
    function getFilter($name);

    /**
     * @param string $name
     *
     * @return bool
     */
    function hasFilter($name);

    /**
     * @param string $name
     */
    function removeFilter($name);

    /**
     * @return boolean
     */
    public function hasActiveFilters();
}
