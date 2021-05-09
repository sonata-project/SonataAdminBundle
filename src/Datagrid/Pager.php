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
 * @phpstan-template T of ProxyQueryInterface
 * @phpstan-implements PagerInterface<T>
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
     * @var mixed bool|int
     */
    protected $maxRecordLimit = false;

    /**
     * @var int
     */
    protected $maxPageLinks = 0;

    /**
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
     *
     * @phpstan-var T|null
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
    public function __construct($maxPerPage = 10)
    {
        $this->setMaxPerPage($maxPerPage);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns the current pager's max link.
     *
     * @return int
     */
    public function getCurrentMaxLink()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->currentMaxLink;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns the current pager's max record limit.
     *
     * @return int
     */
    public function getMaxRecordLimit()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->maxRecordLimit;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Sets the current pager's max record limit.
     *
     * @param int $limit
     */
    public function setMaxRecordLimit($limit)
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $this->maxRecordLimit = $limit;
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     *
     * Returns an array of page numbers to use in pagination links.
     *
     * @param int $nbLinks The maximum number of page numbers to return
     *
     * @return int[]
     */
    public function getLinks($nbLinks = null)
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
     * @final since sonata-project/admin-bundle 3.99.
     *
     * Returns true if the current query requires pagination.
     *
     * @return bool
     */
    public function haveToPaginate()
    {
        // NEXT_MAJOR: remove the existence check and just use $pager->countResults() without casting to int
        if (method_exists($this, 'countResults')) {
            $countResults = (int) $this->countResults();
        } else {
            @trigger_error(sprintf(
                'Not implementing "%s::countResults()" is deprecated since sonata-project/admin-bundle 3.86 and will fail in 4.0.',
                'Sonata\AdminBundle\Datagrid\PagerInterface'
            ), \E_USER_DEPRECATED);

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
     *
     * @return int
     */
    public function getCursor()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->cursor;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Sets the current cursor.
     *
     * @param int $pos
     */
    public function setCursor($pos)
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

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
     *
     * @param int $pos
     *
     * @return object
     */
    public function getObjectByCursor($pos)
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $this->setCursor($pos);

        return $this->getCurrent();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns the current object.
     *
     * @return object
     */
    public function getCurrent()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->retrieveObject($this->cursor);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns the next object.
     *
     * @return object|null
     */
    public function getNext()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

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
     *
     * @return mixed|null
     */
    public function getPrevious()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

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
     *
     * @return int
     */
    public function getFirstIndex()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        if (0 === $this->page) {
            return 1;
        }

        return ($this->page - 1) * $this->maxPerPage + 1;
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.11, will be removed in 4.0
     */
    public function getFirstIndice()
    {
        @trigger_error(sprintf(
            'Method %s is deprecated since version 3.11 and will be removed in 4.0,'
            .' please use getFirstIndex() instead.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->getFirstIndex();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Returns the last index on the current page.
     *
     * @return int
     */
    public function getLastIndex()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

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
     * @deprecated since sonata-project/admin-bundle 3.11, will be removed in 4.0
     */
    public function getLastIndice()
    {
        @trigger_error(sprintf(
            'Method %s is deprecated since version 3.11 and will be removed in 4.0,'
            .' please use getLastIndex() instead.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->getLastIndex();
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, use countResults instead
     *
     * @return int
     */
    public function getNbResults()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.86 and will be removed in 4.0. Use countResults() instead.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->nbResults;
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     *
     * @return int
     */
    public function getFirstPage()
    {
        return 1;
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     *
     * @return int
     */
    public function getLastPage()
    {
        return $this->lastPage;
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     *
     * @return int
     */
    public function getNextPage()
    {
        return min($this->getPage() + 1, $this->getLastPage());
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     *
     * @return int
     */
    public function getPreviousPage()
    {
        return max($this->getPage() - 1, $this->getFirstPage());
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     */
    public function setPage($page)
    {
        $this->page = (int) $page;

        if ($this->page <= 0) {
            // set first page, which depends on a maximum set
            $this->page = $this->getMaxPerPage() ? 1 : 0;
        }
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     */
    public function setMaxPerPage($max)
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

    /**
     * @final since sonata-project/admin-bundle 3.99.
     */
    public function getMaxPageLinks()
    {
        return $this->maxPageLinks;
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     */
    public function setMaxPageLinks($maxPageLinks)
    {
        $this->maxPageLinks = $maxPageLinks;
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     *
     * Returns true if on the first page.
     *
     * @return bool
     */
    public function isFirstPage()
    {
        return 1 === $this->page;
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     *
     * Returns true if on the last page.
     *
     * @return bool
     */
    public function isLastPage()
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
    public function getParameters()
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
            @trigger_error(sprintf(
                'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
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
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParameter($name, $default = null)
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->parameters[$name] ?? $default;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Checks whether a parameter has been set.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasParameter($name)
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return isset($this->parameters[$name]);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Sets a parameter.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setParameter($name, $value)
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $this->parameters[$name] = $value;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     */
    public function current()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return current($this->results);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     */
    public function key()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return key($this->results);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     */
    public function next()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

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
     */
    public function rewind()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

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
    public function valid()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

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
    public function count()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->getNbResults();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     */
    public function serialize()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $vars = get_object_vars($this);
        unset($vars['query']);

        return serialize($vars);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     */
    public function unserialize($serialized)
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

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
    public function getCountColumn()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->countColumn;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * @return string[]
     */
    public function setCountColumn(array $countColumn)
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->countColumn = $countColumn;
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     *
     * @return ProxyQueryInterface|null
     *
     * @phpstan-return T|null $query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @param int $nb
     *
     * @deprecated since sonata-project/admin-bundle 3.86
     *
     * @return void
     */
    protected function setNbResults($nb)
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.86 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $this->nbResults = $nb;
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     *
     * @param int $page
     *
     * @return void
     */
    protected function setLastPage($page)
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
     *
     * @return bool
     */
    protected function isIteratorInitialized()
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return null !== $this->results;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Loads data into properties used for iteration.
     *
     * @return void
     */
    protected function initializeIterator()
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
            @trigger_error(sprintf(
                'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        if (method_exists($this, 'getCurrentPageResults')) {
            $results = $this->getCurrentPageResults();
        } else {
            @trigger_error(sprintf(
                'Not implementing "%s::getCurrentPageResults()" is deprecated since sonata-project/admin-bundle 3.87 and will fail in 4.0.',
                PagerInterface::class
            ), \E_USER_DEPRECATED);

            $results = $this->getResults();
        }

        if (\is_array($results)) {
            $this->results = $results;
        } else {
            $this->results = iterator_to_array($results);
        }

        $this->resultsCounter = \count($this->results);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.84
     *
     * Empties properties used for iteration.
     *
     * @return void
     */
    protected function resetIterator()
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
            @trigger_error(sprintf(
                'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
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
     *
     * @param int $offset
     *
     * @return object|null
     */
    protected function retrieveObject($offset)
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 3.84 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $queryForRetrieve = clone $this->getQuery();
        $queryForRetrieve
            ->setFirstResult($offset - 1)
            ->setMaxResults(1);

        $results = $queryForRetrieve->execute();

        return $results[0] ?? null;
    }
}
