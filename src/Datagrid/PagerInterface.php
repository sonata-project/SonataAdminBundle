<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Datagrid;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * NEXT_MAJOR: Remove these comments and uncomment corresponding methods.
 *
 * @method int  getPage()
 * @method bool isLastPage()
 * @method int  getNbResults()
 */
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
     * @param ProxyQueryInterface $query
     */
    public function setQuery($query);

    /**
     * Returns an array of results on the given page.
     *
     * @return object[]
     */
    public function getResults();

    /**
     * Sets the maximum number of page numbers.
     *
     * @param int $maxPageLinks
     */
    public function setMaxPageLinks($maxPageLinks);

    /**
     * Returns the maximum number of page numbers.
     *
     * @return int
     */
    public function getMaxPageLinks();

//    NEXT_MAJOR: uncomment this method in 4.0
//    /**
//     * Returns true if on the last page.
//     *
//     * @return bool
//     */
//    public function isLastPage(): bool;

//    NEXT_MAJOR: uncomment this method in 4.0
//    public function getNbResults(): int;
//
//    NEXT_MAJOR: uncomment this method in 4.0
//    public function getPage(): int;
}
