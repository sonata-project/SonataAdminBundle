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
 */
interface PagerInterface
{
    /**
     * Initialize the Pager.
     */
    public function init(): void;

    /**
     * Returns the maximum number of results per page.
     */
    public function getMaxPerPage(): int;

    /**
     * Sets the maximum number of results per page.
     */
    public function setMaxPerPage(int $max): void;

    /**
     * Gets the current page.
     */
    public function getPage(): int;

    /**
     * Sets the current page.
     */
    public function setPage(int $page): void;

    /**
     * Set query.
     */
    public function setQuery(ProxyQueryInterface $query): void;

    /**
     * Returns an array of results on the given page.
     *
     * @return object[]
     */
    public function getResults(): array;

    /**
     * Sets the maximum number of page numbers.
     */
    public function setMaxPageLinks(int $maxPageLinks): void;

    /**
     * Returns the maximum number of page numbers.
     */
    public function getMaxPageLinks(): int;

    /**
     * Returns true if on the last page.
     */
    public function isLastPage(): bool;

    public function getNbResults(): int;
}
