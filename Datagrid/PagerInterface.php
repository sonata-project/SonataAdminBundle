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

interface PagerInterface
{
    /**
     * Initialize the Pager.
     */
    public function init();

    /**
     * Returns the maximum number of results per page.
     *
     * @return int
     */
    public function getMaxPerPage();

    /**
     * Sets the maximum number of results per page.
     *
     * @param int $max
     */
    public function setMaxPerPage($max);

    /**
     * Sets the current page.
     *
     * @param int $page
     */
    public function setPage($page);

    /**
     * Set query.
     *
     * @param mixed $query
     */
    public function setQuery($query);

    /**
     * Returns an array of results on the given page.
     *
     * @return array
     */
    public function getResults();
}
