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
 * NEXT_MAJOR: Remove the \Iterator, \Countable and \Serializable implementation.
 *
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
     * NEXT_MAJOR: Remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.86
     *
     * @var int
     */
    protected $nbResults = 0;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * @var int
     */
    protected $cursor = 1;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * @var array<string, mixed>
     */
    protected $parameters = [];

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * @var int
     */
    protected $currentMaxLink = 1;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
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
     * NEXT_MAJOR: Remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Used by iterator interface
     *
     * @var object[]|null
     */
    protected $results;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Used by iterator interface
     *
     * @var int
     */
    protected $resultsCounter = 0;

    /**
     * @var ProxyQueryInterface|null
     */
    protected $query;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
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
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns the current pager's max link.
     */
    public function getCurrentMaxLink(): int
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        return $this->currentMaxLink;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns the current pager's max record limit.
     *
     * @return int|false
     */
    public function getMaxRecordLimit()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        return $this->maxRecordLimit;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Sets the current pager's max record limit.
     */
    public function setMaxRecordLimit(int $limit): void
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        $this->maxRecordLimit = $limit;
    }

    /**
     * Returns an array of page numbers to use in pagination links.
     *
     * @param int $nbLinks The maximum number of page numbers to return
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

        // NEXT_MAJOR: Remove this line.
        $this->currentMaxLink = \count($links) ? $links[\count($links) - 1] : 1;

        return $links;
    }

    /**
     * Returns true if the current query requires pagination.
     */
    public function haveToPaginate(): bool
    {
        // NEXT_MAJOR: remove the existence check and just use $pager->countResults() without casting to int
        if (method_exists($this, 'countResults')) {
            $countResults = (int) $this->countResults();
        } else {
            @trigger_error(sprintf(
                'Not implementing "%s::countResults()" is deprecated since sonata-project/admin-bundle 3.86 and will fail in 4.0.',
                'Sonata\AdminBundle\Datagrid\PagerInterface'
            ), E_USER_DEPRECATED);

            $countResults = (int) $this->getNbResults();
        }

        return $this->getMaxPerPage() && $countResults > $this->getMaxPerPage();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns the current cursor.
     */
    public function getCursor(): int
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        return $this->cursor;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Sets the current cursor.
     */
    public function setCursor(int $pos): void
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

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
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns an object by cursor position.
     */
    public function getObjectByCursor(int $pos): ?object
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        $this->setCursor($pos);

        return $this->getCurrent();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns the current object.
     */
    public function getCurrent(): ?object
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        return $this->retrieveObject($this->cursor);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns the next object.
     */
    public function getNext(): ?object
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        if ($this->cursor + 1 > $this->nbResults) {
            return null;
        }

        return $this->retrieveObject($this->cursor + 1);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns the previous object.
     */
    public function getPrevious(): ?object
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        if ($this->cursor - 1 < 1) {
            return null;
        }

        return $this->retrieveObject($this->cursor - 1);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns the first index on the current page.
     */
    public function getFirstIndex(): int
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (0 === $this->page) {
            return 1;
        }

        return ($this->page - 1) * $this->maxPerPage + 1;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns the last index on the current page.
     */
    public function getLastIndex(): int
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

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
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.86 and will be removed in 4.0. Use countResults() instead.',
            __METHOD__
        ), E_USER_DEPRECATED);

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

    public function setPage(int $page): void
    {
        $this->page = $page;

        if ($this->page <= 0) {
            // set first page, which depends on a maximum set
            $this->page = $this->getMaxPerPage() ? 1 : 0;
        }
    }

    public function getMaxPerPage(): int
    {
        return $this->maxPerPage;
    }

    public function setMaxPerPage(int $max): void
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
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns the current pager's parameter holder.
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
            @trigger_error(sprintf(
                'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        return $this->parameters;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns a parameter.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function getParameter(string $name, $default = null)
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        return $this->parameters[$name] ?? $default;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Checks whether a parameter has been set.
     */
    public function hasParameter(string $name): bool
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        return isset($this->parameters[$name]);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Sets a parameter.
     *
     * @param mixed $value
     */
    public function setParameter(string $name, $value): void
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        $this->parameters[$name] = $value;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * @return object|false
     */
    public function current()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return current($this->results);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * @return int|string
     */
    public function key()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return key($this->results);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * @return object|false
     */
    public function next()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        --$this->resultsCounter;

        // NEXT_MAJOR: remove `return` statement, to be compatible with Iterator::next(): void
        return next($this->results);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * @return object|false
     */
    public function rewind()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        $this->resultsCounter = \count($this->results);

        // NEXT_MAJOR: remove `return` statement, to be compatible with Iterator::rewind(): void
        return reset($this->results);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     */
    public function valid(): bool
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return $this->resultsCounter > 0;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84, use countResults instead
     */
    public function count(): int
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        return $this->getNbResults();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     */
    public function serialize(): string
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        $vars = get_object_vars($this);
        unset($vars['query']);

        return serialize($vars);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     */
    public function unserialize($serialized): void
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        $array = unserialize($serialized);

        foreach ($array as $name => $values) {
            $this->$name = $values;
        }
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * @return string[]
     */
    public function getCountColumn(): array
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        return $this->countColumn;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * @param string[] $countColumn
     */
    public function setCountColumn(array $countColumn): void
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

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

        // NEXT_MAJOR: Remove this code.
        if ($this->getPage() > $page) {
            $this->setPage($page);
        }
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns true if the properties used for iteration have been initialized.
     */
    protected function isIteratorInitialized(): bool
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        return null !== $this->results;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Loads data into properties used for iteration.
     */
    protected function initializeIterator(): void
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
            @trigger_error(sprintf(
                'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        $this->results = $this->getResults();
        $this->resultsCounter = \count($this->results);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Empties properties used for iteration.
     */
    protected function resetIterator(): void
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
            @trigger_error(sprintf(
                'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        $this->results = null;
        $this->resultsCounter = 0;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Retrieve the object for a certain offset.
     */
    protected function retrieveObject(int $offset): ?object
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

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
