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
 * @phpstan-template T of ProxyQueryInterface
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

    public function getNextPage(): int;

    public function getPreviousPage(): int;

    public function getFirstPage(): int;

    public function isFirstPage(): bool;

    public function getLastPage(): int;

    public function isLastPage(): bool;

    /**
     * @phpstan-return T|null
     */
    public function getQuery(): ?ProxyQueryInterface;

    /**
     * @phpstan-param T $query
     */
    public function setQuery(ProxyQueryInterface $query): void;

    /**
     * Returns true if the current query requires pagination.
     */
    public function haveToPaginate(): bool;

    /**
     * Returns a collection of results on the given page.
     *
     * @return iterable<object>
     */
    public function getCurrentPageResults(): iterable;

    public function countResults(): int;

    /**
     * Returns an array of page numbers to use in pagination links.
     *
     * @param int|null $nbLinks The maximum number of page numbers to return
     *
     * @return int[]
     */
    public function getLinks(?int $nbLinks = null): array;

    /**
     * Sets the maximum number of page numbers.
     */
    public function setMaxPageLinks(int $maxPageLinks): void;

    /**
     * Returns the maximum number of page numbers.
     */
    public function getMaxPageLinks(): int;
}
