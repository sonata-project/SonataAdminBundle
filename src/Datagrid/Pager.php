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
 * @implements \Iterator<object>
 */
abstract class Pager implements \Iterator, \Countable, \Serializable, PagerInterface
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
    protected $nbResults = 0;

    /**
     * @var int
     */
    protected $cursor = 1;

    /**
     * @var array<string, mixed>
     */
    protected $parameters = [];

    /**
     * @var int
     */
    protected $currentMaxLink = 1;

    /**
     * @var int|false
     */
    protected $maxRecordLimit = false;

    /**
     * @var int
     */
    protected $maxPageLinks = 0;

    /**
     * Results are null prior to its initialization in `initializeIterator()`.
     *
     * @var object[]|null
     */
    protected $results;

    /**
     * @var int
     */
    protected $resultsCounter = 0;

    /**
     * @var ProxyQueryInterface|null
     */
    protected $query;

    /**
     * @var string[]
     */
    protected $countColumn = ['id'];

    /**
     * @param int $maxPerPage Number of records to display per page
     */
    public function __construct(int $maxPerPage = 10)
    {
        $this->setMaxPerPage($maxPerPage);
    }

    /**
     * Returns the current pager's max link.
     */
    public function getCurrentMaxLink(): int
    {
        return $this->currentMaxLink;
    }

    /**
     * Returns the current pager's max record limit.
     *
     * @return int|false
     */
    public function getMaxRecordLimit()
    {
        return $this->maxRecordLimit;
    }

    /**
     * Sets the current pager's max record limit.
     */
    public function setMaxRecordLimit(int $limit): void
    {
        $this->maxRecordLimit = $limit;
    }

    /**
     * Returns an array of page numbers to use in pagination links.
     *
     * @return int[]
     */
    public function getLinks(?int $nbLinks = null): array
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

        $this->currentMaxLink = \count($links) ? $links[\count($links) - 1] : 1;

        return $links;
    }

    /**
     * Returns true if the current query requires pagination.
     */
    public function haveToPaginate(): bool
    {
        return $this->getMaxPerPage() && $this->getNbResults() > $this->getMaxPerPage();
    }

    /**
     * Returns the current cursor.
     */
    public function getCursor(): int
    {
        return $this->cursor;
    }

    /**
     * Sets the current cursor.
     */
    public function setCursor(int $pos): void
    {
        if ($pos < 1) {
            $this->cursor = 1;
        } else {
            if ($pos > $this->nbResults) {
                $this->cursor = $this->nbResults;
            } else {
                $this->cursor = $pos;
            }
        }
    }

    /**
     * Returns an object by cursor position.
     */
    public function getObjectByCursor(int $pos): ?object
    {
        $this->setCursor($pos);

        return $this->getCurrent();
    }

    /**
     * Returns the current object.
     */
    public function getCurrent(): ?object
    {
        return $this->retrieveObject($this->cursor);
    }

    /**
     * Returns the next object.
     */
    public function getNext(): ?object
    {
        if ($this->cursor + 1 > $this->nbResults) {
            return null;
        }

        return $this->retrieveObject($this->cursor + 1);
    }

    /**
     * Returns the previous object.
     */
    public function getPrevious(): ?object
    {
        if ($this->cursor - 1 < 1) {
            return null;
        }

        return $this->retrieveObject($this->cursor - 1);
    }

    /**
     * Returns the first index on the current page.
     */
    public function getFirstIndex(): int
    {
        if (0 === $this->page) {
            return 1;
        }

        return ($this->page - 1) * $this->maxPerPage + 1;
    }

    /**
     * Returns the last index on the current page.
     */
    public function getLastIndex(): int
    {
        if (0 === $this->page) {
            return $this->nbResults;
        }
        if ($this->page * $this->maxPerPage >= $this->nbResults) {
            return $this->nbResults;
        }

        return $this->page * $this->maxPerPage;
    }

    public function getNbResults(): int
    {
        return $this->nbResults;
    }

    public function getFirstPage(): int
    {
        return 1;
    }

    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getNextPage(): int
    {
        return min($this->getPage() + 1, $this->getLastPage());
    }

    public function getPreviousPage(): int
    {
        return max($this->getPage() - 1, $this->getFirstPage());
    }

    public function setPage($page): void
    {
        $this->page = (int) $page;

        if ($this->page <= 0) {
            // set first page, which depends on a maximum set
            $this->page = $this->getMaxPerPage() ? 1 : 0;
        }
    }

    public function getMaxPerPage(): int
    {
        return $this->maxPerPage;
    }

    public function setMaxPerPage($max): void
    {
        $max = (int) $max;

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

    public function getMaxPageLinks(): int
    {
        return $this->maxPageLinks;
    }

    public function setMaxPageLinks(int $maxPageLinks): void
    {
        $this->maxPageLinks = $maxPageLinks;
    }

    /**
     * Returns true if on the first page.
     */
    public function isFirstPage(): bool
    {
        return 1 === $this->page;
    }

    /**
     * Returns true if on the last page.
     */
    public function isLastPage(): bool
    {
        return $this->page === $this->lastPage;
    }

    /**
     * Returns the current pager's parameter holder.
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Returns a parameter.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function getParameter(string $name, $default = null)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : $default;
    }

    /**
     * Checks whether a parameter has been set.
     */
    public function hasParameter(string $name): bool
    {
        return isset($this->parameters[$name]);
    }

    /**
     * Sets a parameter.
     *
     * @param mixed $value
     */
    public function setParameter(string $name, $value): void
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @return object|false
     */
    public function current()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return current($this->results);
    }

    /**
     * @return int|string
     */
    public function key()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return key($this->results);
    }

    /**
     * @return object|false
     */
    public function next()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        --$this->resultsCounter;

        // NEXT_MAJOR: remove `return` statement, to be compatible with Iterator::next(): void
        return next($this->results);
    }

    /**
     * @return object|false
     */
    public function rewind()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        $this->resultsCounter = \count($this->results);

        // NEXT_MAJOR: remove `return` statement, to be compatible with Iterator::rewind(): void
        return reset($this->results);
    }

    public function valid(): bool
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return $this->resultsCounter > 0;
    }

    public function count(): int
    {
        return $this->getNbResults();
    }

    public function serialize(): string
    {
        $vars = get_object_vars($this);
        unset($vars['query']);

        return serialize($vars);
    }

    public function unserialize($serialized): void
    {
        $array = unserialize($serialized);

        foreach ($array as $name => $values) {
            $this->$name = $values;
        }
    }

    /**
     * @return string[]
     */
    public function getCountColumn(): array
    {
        return $this->countColumn;
    }

    /**
     * @param string[] $countColumn
     */
    public function setCountColumn(array $countColumn): void
    {
        $this->countColumn = $countColumn;
    }

    public function setQuery(ProxyQueryInterface $query): void
    {
        $this->query = $query;
    }

    public function getQuery(): ?ProxyQueryInterface
    {
        return $this->query;
    }

    protected function setNbResults(int $nbResults): void
    {
        $this->nbResults = $nbResults;
    }

    protected function setLastPage(int $page): void
    {
        $this->lastPage = $page;

        if ($this->getPage() > $page) {
            $this->setPage($page);
        }
    }

    /**
     * Returns true if the properties used for iteration have been initialized.
     */
    protected function isIteratorInitialized(): bool
    {
        return null !== $this->results;
    }

    /**
     * Loads data into properties used for iteration.
     */
    protected function initializeIterator(): void
    {
        $this->results = $this->getResults();
        $this->resultsCounter = \count($this->results);
    }

    /**
     * Empties properties used for iteration.
     */
    protected function resetIterator(): void
    {
        $this->results = null;
        $this->resultsCounter = 0;
    }

    /**
     * Retrieve the object for a certain offset.
     */
    protected function retrieveObject(int $offset): ?object
    {
        $query = $this->getQuery();

        if (null === $query) {
            return null;
        }

        $queryForRetrieve = clone $query;
        $queryForRetrieve
            ->setFirstResult($offset - 1)
            ->setMaxResults(1);

        $results = $queryForRetrieve->execute();

        return $results[0] ?? null;
    }
}
