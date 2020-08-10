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

use Doctrine\Common\Collections\Collection;

/**
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Sjoerd Peters <sjoerd.peters@gmail.com>
 */
final class SimplePager extends Pager
{
    /**
     * @var bool
     */
    private $haveToPaginate = false;

    /**
     * How many pages to look forward to create links to next pages.
     *
     * @var int
     */
    private $threshold;

    /**
     * thresholdCount is null prior to its initialization in `getResults()`.
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

    public function getNbResults(): int
    {
        $n = ($this->getLastPage() - 1) * $this->getMaxPerPage();
        if ($this->getLastPage() === $this->getPage()) {
            return $n + $this->thresholdCount;
        }

        return $n;
    }

    public function getResults(?int $hydrationMode = null): array
    {
        if ($this->results) {
            return $this->results;
        }

        $results = $this->getQuery()->execute([], $hydrationMode);

        // doctrine/phpcr-odm returns ArrayCollection
        if ($results instanceof Collection) {
            $results = $results->toArray();
        }

        $this->thresholdCount = \count($results);

        if (\count($results) > $this->getMaxPerPage()) {
            $this->haveToPaginate = true;
            $this->results = \array_slice($results, 0, $this->getMaxPerPage());
        } else {
            $this->haveToPaginate = false;
            $this->results = $results;
        }

        return $this->results;
    }

    public function haveToPaginate(): bool
    {
        return $this->haveToPaginate || $this->getPage() > 1;
    }

    /**
     * @throws \RuntimeException the QueryBuilder is uninitialized
     */
    public function init(): void
    {
        if (!$this->getQuery()) {
            throw new \RuntimeException('Uninitialized QueryBuilder');
        }
        $this->resetIterator();

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
            $this->initializeIterator();

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

    protected function resetIterator(): void
    {
        parent::resetIterator();
        $this->haveToPaginate = false;
    }
}
