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

use Doctrine\Common\Collections\ArrayCollection;
use Sonata\AdminBundle\Util\TraversableToCollection;

/**
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Sjoerd Peters <sjoerd.peters@gmail.com>
 *
 * @phpstan-template T of ProxyQueryInterface
 * @phpstan-extends Pager<T>
 */
final class SimplePager extends Pager
{
    /**
     * @var iterable<object>|null
     */
    protected $results;

    /**
     * thresholdCount is null prior to its initialization in `getCurrentPageResults()`.
     */
    private ?int $thresholdCount = null;

    /**
     * Whether the last fetched result window was completely filled, which means that
     * further results may exist beyond it. Null as long as no results have been fetched.
     */
    private ?bool $windowSaturated = null;

    /**
     * The offset of the last fetched result window.
     */
    private ?int $windowOffset = null;

    /**
     * The threshold parameter can be used to determine how far ahead the pager
     * should fetch results.
     *
     * If set to 1 which is the minimal value the pager will generate a link to the next page
     * If set to 2 the pager will generate links to the next two pages
     * If set to 3 the pager will generate links to the next three pages
     * etc.
     */
    public function __construct(
        int $maxPerPage = 10,
        private int $threshold = 1,
    ) {
        parent::__construct($maxPerPage);
    }

    public function countResults(): int
    {
        return ($this->getPage() - 1) * $this->getMaxPerPage() + ($this->thresholdCount ?? 0);
    }

    public function getCurrentPageResults(): iterable
    {
        if (null !== $this->results) {
            return $this->results;
        }

        $query = $this->getQuery();
        if (null === $query) {
            throw new \LogicException('Uninitialized query.');
        }

        $results = TraversableToCollection::transform($query->execute());
        $this->thresholdCount = $results->count();

        $maxResults = $query->getMaxResults();
        $this->windowSaturated = null !== $maxResults && $this->thresholdCount >= $maxResults;
        $this->windowOffset = $query->getFirstResult() ?? 0;

        if ($this->thresholdCount > $this->getMaxPerPage()) {
            $results = new ArrayCollection($results->slice(0, $this->getMaxPerPage()));
        }

        $this->results = $results;

        return $this->results;
    }

    /**
     * @throws \LogicException the query is uninitialized
     */
    public function init(): void
    {
        $query = $this->getQuery();
        if (null === $query) {
            throw new \LogicException('Uninitialized query.');
        }

        if (0 === $this->getPage() || 0 === $this->getMaxPerPage()) {
            $this->setLastPage(0);
            $query->setFirstResult(0);
            $query->setMaxResults(0);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();
            $query->setFirstResult($offset);

            $maxOffset = $this->getThreshold() > 0
                ? $this->getMaxPerPage() * $this->threshold + 1 : $this->getMaxPerPage() + 1;

            $query->setMaxResults($maxOffset);

            $this->results = $this->getCurrentPageResults();

            $t = (int) ceil(($this->thresholdCount ?? 0) / $this->getMaxPerPage()) + $this->getPage() - 1;
            $this->setLastPage(max(1, $t));
        }
    }

    /**
     * Set how many pages to look forward to create links to next pages.
     */
    public function setThreshold(int $threshold): void
    {
        $this->threshold = $threshold;
    }

    public function getThreshold(): int
    {
        return $this->threshold;
    }

    public function isDeterministic(): bool
    {
        // Nothing has been fetched yet, so nothing is known about the number of results.
        if (null === $this->thresholdCount || null === $this->windowOffset) {
            return false;
        }

        // The pager fetches a limited window of results ahead. The count is only a lower bound
        // when this window is saturated, otherwise all the remaining results have been fetched.
        if (true === $this->windowSaturated) {
            return false;
        }

        // `countResults()` derives the count from the current page settings. It is only exact
        // if they still match the offset of the fetched window.
        $offset = ($this->getPage() - 1) * $this->getMaxPerPage();
        if ($offset !== $this->windowOffset) {
            return false;
        }

        // An empty window beyond the first page only tells that there are less results than
        // the offset, but not how many.
        return 0 === $offset || $this->thresholdCount > 0;
    }
}
