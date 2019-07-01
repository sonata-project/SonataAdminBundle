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
     * @var array
     */
    protected $parameters = [];

    /**
     * @var int
     */
    protected $currentMaxLink = 1;

    /**
     * @var mixed bool|int
     */
    protected $maxRecordLimit = false;

    /**
     * @var int
     */
    protected $maxPageLinks = 0;

    // used by iterator interface
    /**
     * @var \Traversable|array|null
     */
    protected $results = null;

    /**
     * @var int
     */
    protected $resultsCounter = 0;

    /**
     * @var ProxyQueryInterface|null
     */
    protected $query = null;

    /**
     * @var array
     */
    protected $countColumn = ['id'];

    /**
     * @param int $maxPerPage Number of records to display per page
     */
    public function __construct($maxPerPage = 10)
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
     */
    public function getMaxRecordLimit(): int
    {
        return $this->maxRecordLimit;
    }

    /**
     * Sets the current pager's max record limit.
     *
     * @param int $limit
     */
    public function setMaxRecordLimit($limit): void
    {
        $this->maxRecordLimit = $limit;
    }

    /**
     * Returns an array of page numbers to use in pagination links.
     *
     * @param int $nbLinks The maximum number of page numbers to return
     */
    public function getLinks($nbLinks = null): array
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
     *
     * @param int $pos
     */
    public function setCursor($pos): void
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
     *
     * @param int $pos
     *
     * @return mixed
     */
    public function getObjectByCursor($pos)
    {
        $this->setCursor($pos);

        return $this->getCurrent();
    }

    /**
     * Returns the current object.
     *
     * @return mixed
     */
    public function getCurrent()
    {
        return $this->retrieveObject($this->cursor);
    }

    /**
     * Returns the next object.
     *
     * @return mixed|null
     */
    public function getNext()
    {
        if ($this->cursor + 1 > $this->nbResults) {
            return;
        }

        return $this->retrieveObject($this->cursor + 1);
    }

    /**
     * Returns the previous object.
     *
     * @return mixed|null
     */
    public function getPrevious()
    {
        if ($this->cursor - 1 < 1) {
            return;
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
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since 3.11, will be removed in 4.0
     */
    public function getFirstIndice()
    {
        @trigger_error(
            'Method '.__METHOD__.' is deprecated since version 3.11 and will be removed in 4.0, '.
            'please use getFirstIndex() instead.',
            E_USER_DEPRECATED
        );

        return $this->getFirstIndex();
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

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since 3.11, will be removed in 4.0
     */
    public function getLastIndice()
    {
        @trigger_error(
            'Method '.__METHOD__.' is deprecated since version 3.11 and will be removed in 4.0, '.
            'please use getLastIndex() instead.',
            E_USER_DEPRECATED
        );

        return $this->getLastIndex();
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

    public function setMaxPageLinks($maxPageLinks): void
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
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Returns a parameter.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParameter($name, $default = null)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : $default;
    }

    /**
     * Checks whether a parameter has been set.
     *
     * @param string $name
     */
    public function hasParameter($name): bool
    {
        return isset($this->parameters[$name]);
    }

    /**
     * Sets a parameter.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setParameter($name, $value): void
    {
        $this->parameters[$name] = $value;
    }

    public function current()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return current($this->results);
    }

    public function key()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return key($this->results);
    }

    public function next()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        --$this->resultsCounter;

        return next($this->results);
    }

    public function rewind()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        $this->resultsCounter = \count($this->results);

        return reset($this->results);
    }

    public function valid()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return $this->resultsCounter > 0;
    }

    public function count()
    {
        return $this->getNbResults();
    }

    public function serialize()
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

    public function getCountColumn(): array
    {
        return $this->countColumn;
    }

    public function setCountColumn(array $countColumn): array
    {
        return $this->countColumn = $countColumn;
    }

    public function setQuery($query): void
    {
        $this->query = $query;
    }

    public function getQuery(): ProxyQueryInterface
    {
        return $this->query;
    }

    /**
     * @param int $nb
     */
    protected function setNbResults($nb): void
    {
        $this->nbResults = $nb;
    }

    /**
     * @param int $page
     */
    protected function setLastPage($page): void
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
     *
     * @param int $offset
     */
    protected function retrieveObject($offset): object
    {
        $queryForRetrieve = clone $this->getQuery();
        $queryForRetrieve
            ->setFirstResult($offset - 1)
            ->setMaxResults(1);

        $results = $queryForRetrieve->execute();

        return $results[0];
    }
}
