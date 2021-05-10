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
use Doctrine\Common\Collections\Collection;
use Sonata\AdminBundle\Util\TraversableToCollection;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Sjoerd Peters <sjoerd.peters@gmail.com>
 *
 * @phpstan-template T of ProxyQueryInterface
 * @phpstan-extends Pager<T>
 */
class SimplePager extends Pager
{
    /**
     * @var Collection<array-key, object>|null
     */
    protected $results;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.99
     *
     * @var bool
     */
    protected $haveToPaginate;

    /**
     * How many pages to look forward to create links to next pages.
     *
     * @var int
     */
    protected $threshold;

    /**
     * @var int
     */
    protected $thresholdCount;

    /**
     * The threshold parameter can be used to determine how far ahead the pager
     * should fetch results.
     *
     * If set to 1 which is the minimal value the pager will generate a link to the next page
     * If set to 2 the pager will generate links to the next two pages
     * If set to 3 the pager will generate links to the next three pages
     * etc.
     *
     * @param int $maxPerPage Number of records to display per page
     * @param int $threshold
     */
    public function __construct($maxPerPage = 10, $threshold = 1)
    {
        parent::__construct($maxPerPage);
        $this->setThreshold($threshold);
    }

    /**
     * NEXT_MAJOR: remove this method.
     */
    public function getNbResults()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.86 and will be removed in 4.0. Use countResults() instead.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->countResults();
    }

    public function countResults(): int
    {
        return ($this->getPage() - 1) * $this->getMaxPerPage() + $this->thresholdCount;
    }

    public function getCurrentPageResults(): iterable
    {
        if (null !== $this->results) {
            return $this->results;
        }

        $this->results = TraversableToCollection::transform($this->getQuery()->execute());
        $this->thresholdCount = $this->results->count();

        if ($this->thresholdCount > $this->getMaxPerPage()) {
            $this->haveToPaginate = true;

            $this->results = new ArrayCollection($this->results->slice(0, $this->getMaxPerPage()));
        } else {
            $this->haveToPaginate = false;
        }

        return $this->results;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.87. To be removed in 4.0. Use getCurrentPageResults() instead.
     */
    public function getResults($hydrationMode = null)
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.87 and will be removed in 4.0. Use getCurrentPageResults() instead.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        if (null !== $this->results) {
            return $this->results;
        }

        // @phpstan-ignore-next-line
        $this->results = TraversableToCollection::transform($this->getQuery()->execute([], $hydrationMode));
        $this->thresholdCount = $this->results->count();

        if ($this->thresholdCount > $this->getMaxPerPage()) {
            $this->haveToPaginate = true;

            $this->results = new ArrayCollection($this->results->slice(0, $this->getMaxPerPage()));
        } else {
            $this->haveToPaginate = false;
        }

        return $this->results;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException the query is uninitialized
     */
    public function init()
    {
        if (!$this->getQuery()) {
            throw new \RuntimeException('Uninitialized query');
        }

        // NEXT_MAJOR: Remove this line and uncomment the following one instead.
        $this->resetIterator('sonata_deprecation_mute');
//        $this->haveToPaginate = false;

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

            // NEXT_MAJOR: Remove this line and uncomment the following one instead.
            $this->initializeIterator('sonata_deprecation_mute');
//            $this->results = $this->getCurrentPageResults();

            $t = (int) ceil($this->thresholdCount / $this->getMaxPerPage()) + $this->getPage() - 1;
            $this->setLastPage(max(1, $t));
        }
    }

    /**
     * Set how many pages to look forward to create links to next pages.
     *
     * @param int $threshold
     */
    public function setThreshold($threshold)
    {
        $this->threshold = (int) $threshold;
    }

    /**
     * @return int
     */
    public function getThreshold()
    {
        return $this->threshold;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     */
    protected function resetIterator()
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
            @trigger_error(sprintf(
                'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        parent::resetIterator('sonata_deprecation_mute');
        $this->haveToPaginate = false;
    }
}
