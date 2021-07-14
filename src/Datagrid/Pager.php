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
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of ProxyQueryInterface
 * @phpstan-implements PagerInterface<T>
 */
abstract class Pager implements PagerInterface
{
    public const TYPE_DEFAULT = 'default';
    public const TYPE_SIMPLE = 'simple';

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var int
     */
    protected $maxPerPage = 0;

    /**
     * @var int
     */
    protected $lastPage = 1;

    /**
     * @var int
     */
    protected $maxPageLinks = 0;

    /**
     * @var ProxyQueryInterface|null
     *
     * @phpstan-var T|null
     */
    protected $query;

    /**
     * @param int $maxPerPage Number of records to display per page
     */
    public function __construct(int $maxPerPage = 10)
    {
        $this->setMaxPerPage($maxPerPage);
    }

    /**
     * Returns an array of page numbers to use in pagination links.
     *
     * @param int $nbLinks The maximum number of page numbers to return
     *
     * @return int[]
     */
    final public function getLinks(?int $nbLinks = null): array
    {
        if (null === $nbLinks) {
            $nbLinks = $this->getMaxPageLinks();
        }
        $links = [];
        $tmp = $this->page - floor($nbLinks / 2);
        $check = $this->lastPage - $nbLinks + 1;
        $limit = $check > 0 ? $check : 1;
        $begin = $tmp > 0 ? ($tmp > $limit ? $limit : $tmp) : 1;

        $i = (int) $begin;
        while ($i < $begin + $nbLinks && $i <= $this->lastPage) {
            $links[] = $i++;
        }

        return $links;
    }

    /**
     * Returns true if the current query requires pagination.
     */
    final public function haveToPaginate(): bool
    {
        $countResults = $this->countResults();

        return $this->getMaxPerPage() > 0 && $countResults > $this->getMaxPerPage();
    }

    final public function getFirstPage(): int
    {
        return 1;
    }

    final public function getLastPage(): int
    {
        return $this->lastPage;
    }

    final public function getPage(): int
    {
        return $this->page;
    }

    final public function getNextPage(): int
    {
        return min($this->getPage() + 1, $this->getLastPage());
    }

    final public function getPreviousPage(): int
    {
        return max($this->getPage() - 1, $this->getFirstPage());
    }

    final public function setPage(int $page): void
    {
        $this->page = $page;

        if ($this->page <= 0) {
            // set first page, which depends on a maximum set
            $this->page = $this->getMaxPerPage() > 0 ? 1 : 0;
        }
    }

    final public function getMaxPerPage(): int
    {
        return $this->maxPerPage;
    }

    final public function setMaxPerPage(int $max): void
    {
        if ($max > 0) {
            $this->maxPerPage = $max;
            if (0 === $this->page) {
                $this->page = 1;
            }
        } else {
            if (0 === $max) {
                $this->maxPerPage = 0;
                $this->page = 0;
            } else {
                $this->maxPerPage = 1;
                if (0 === $this->page) {
                    $this->page = 1;
                }
            }
        }
    }

    final public function getMaxPageLinks(): int
    {
        return $this->maxPageLinks;
    }

    final public function setMaxPageLinks(int $maxPageLinks): void
    {
        $this->maxPageLinks = $maxPageLinks;
    }

    final public function isFirstPage(): bool
    {
        return 1 === $this->page;
    }

    final public function isLastPage(): bool
    {
        return $this->page === $this->lastPage;
    }

    final public function setQuery(ProxyQueryInterface $query): void
    {
        $this->query = $query;
    }

    /**
     * @phpstan-return T|null $query
     */
    final public function getQuery(): ?ProxyQueryInterface
    {
        return $this->query;
    }

    final protected function setLastPage(int $page): void
    {
        $this->lastPage = $page;
    }
}
