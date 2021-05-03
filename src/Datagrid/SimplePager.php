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
     * @var iterable<array-key, object>|null
     */
    protected $results;

    /**
     * How many pages to look forward to create links to next pages.
     *
     * @var int
     */
    private $threshold;

    /**
     * thresholdCount is null prior to its initialization in `getCurrentPageResults()`.
     *
     * @var int|null
     */
    private $thresholdCount;

    /**
     * The threshold parameter can be used to determine how far ahead the pager
     * should fetch results.
     *
     * If set to 1 which is the minimal value the pager will generate a link to the next page
     * If set to 2 the pager will generate links to the next two pages
     * If set to 3 the pager will generate links to the next three pages
     * etc.
     */
    public function __construct(int $maxPerPage = 10, int $threshold = 1)
    {
        parent::__construct($maxPerPage);
        $this->setThreshold($threshold);
    }

    public function countResults(): int
    {
        $n = ($this->getLastPage() - 1) * $this->getMaxPerPage();
        if ($this->getLastPage() === $this->getPage()) {
            return $n + $this->thresholdCount;
        }

        return $n;
    }

    public function getCurrentPageResults(): iterable
    {
        if (null !== $this->results) {
            return $this->results;
        }

        $results = TraversableToCollection::transform($this->getQuery()->execute());
        $this->thresholdCount = $results->count();

        if ($this->thresholdCount > $this->getMaxPerPage()) {
            $results = new ArrayCollection($results->slice(0, $this->getMaxPerPage()));
        }

        $this->results = $results;

        return $this->results;
    }

    /**
     * @throws \RuntimeException the query is uninitialized
     */
    public function init(): void
    {
        if (!$this->getQuery()) {
            throw new \RuntimeException('Uninitialized query');
        }

        if (0 === $this->getPage() || 0 === $this->getMaxPerPage()) {
            $this->setLastPage(0);
            $this->getQuery()->setFirstResult(0);
            $this->getQuery()->setMaxResults(0);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();
            $this->getQuery()->setFirstResult($offset);

            $maxOffset = $this->getThreshold() > 0
                ? $this->getMaxPerPage() * $this->threshold + 1 : $this->getMaxPerPage() + 1;

            $this->getQuery()->setMaxResults($maxOffset);

            $this->results = $this->getCurrentPageResults();

            $t = (int) ceil($this->thresholdCount / $this->getMaxPerPage()) + $this->getPage() - 1;
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
}
