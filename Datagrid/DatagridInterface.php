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

    function getPager();

    function getResults();

    function buildPager();

    function addFilter(FilterInterface $filter);

    function getFilters();

    function getValues();

    function getColumns();
}