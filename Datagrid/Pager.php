<?php

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
 * Class Pager.
 *
 * @author  Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class Pager implements \Iterator, \Countable, \Serializable, PagerInterface
{
    const TYPE_DEFAULT = 'default';
    const TYPE_SIMPLE = 'simple';

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
    protected $parameters = array();

    /**
     * @var int
     */
    protected $currentMaxLink = 1;

    /**
     * @var bool
     */
    protected $maxRecordLimit = false;

    /**
     * @var int
     */
    protected $maxPageLinks = 0;

    // used by iterator interface
    /**
     * @var array|null
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
    protected $countColumn = array('id');

    /**
     * Constructor.
     *
     * @param int $maxPerPage Number of records to display per page
     */
    public function __construct($maxPerPage = 10)
    {
        $this->setMaxPerPage($maxPerPage);
    }

    /**
     * Returns the current pager's max link.
     *
     * @return int
     */
    public function getCurrentMaxLink()
    {
        return $this->currentMaxLink;
    }

    /**
     * Returns the current pager's max record limit.
     *
     * @return int
     */
    public function getMaxRecordLimit()
    {
        return $this->maxRecordLimit;
    }

    /**
     * Sets the current pager's max record limit.
     *
     * @param int $limit
     */
    public function setMaxRecordLimit($limit)
    {
        $this->maxRecordLimit = $limit;
    }

    /**
     * Returns an array of page numbers to use in pagination links.
     *
     * @param int $nbLinks The maximum number of page numbers to return
     *
     * @return array
     */
    public function getLinks($nbLinks = null)
    {
        if ($nbLinks == null) {
            $nbLinks = $this->getMaxPageLinks();
        }
        $links = array();
        $tmp = $this->page - floor($nbLinks / 2);
        $check = $this->lastPage - $nbLinks + 1;
        $limit = $check > 0 ? $check : 1;
        $begin = $tmp > 0 ? ($tmp > $limit ? $limit : $tmp) : 1;

        $i = (int) $begin;
        while ($i < $begin + $nbLinks && $i <= $this->lastPage) {
            $links[] = $i++;
        }

        $this->currentMaxLink = count($links) ? $links[count($links) - 1] : 1;

        return $links;
    }

    /**
     * Returns true if the current query requires pagination.
     *
     * @return bool
     */
    public function haveToPaginate()
    {
        return $this->getMaxPerPage() && $this->getNbResults() > $this->getMaxPerPage();
    }

    /**
     * Returns the current cursor.
     *
     * @return int
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    /**
     * Sets the current cursor.
     *
     * @param int $pos
     */
    public function setCursor($pos)
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
     *
     * @return int
     */
    public function getFirstIndice()
    {
        if ($this->page == 0) {
            return 1;
        }

        return ($this->page - 1) * $this->maxPerPage + 1;
    }

    /**
     * Returns the last index on the current page.
     *
     * @return int
     */
    public function getLastIndice()
    {
        if ($this->page == 0) {
            return $this->nbResults;
        }
        if ($this->page * $this->maxPerPage >= $this->nbResults) {
            return $this->nbResults;
        }

        return $this->page * $this->maxPerPage;
    }

    /**
     * Returns the number of results.
     *
     * @return int
     */
    public function getNbResults()
    {
        return $this->nbResults;
    }

    /**
     * Returns the first page number.
     *
     * @return int
     */
    public function getFirstPage()
    {
        return 1;
    }

    /**
     * Returns the last page number.
     *
     * @return int
     */
    public function getLastPage()
    {
        return $this->lastPage;
    }

    /**
     * Returns the current page.
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Returns the next page.
     *
     * @return int
     */
    public function getNextPage()
    {
        return min($this->getPage() + 1, $this->getLastPage());
    }

    /**
     * Returns the previous page.
     *
     * @return int
     */
    public function getPreviousPage()
    {
        return max($this->getPage() - 1, $this->getFirstPage());
    }

    /**
     * {@inheritdoc}
     */
    public function setPage($page)
    {
        $this->page = intval($page);

        if ($this->page <= 0) {
            // set first page, which depends on a maximum set
            $this->page = $this->getMaxPerPage() ? 1 : 0;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxPerPage($max)
    {
        if ($max > 0) {
            $this->maxPerPage = $max;
            if ($this->page == 0) {
                $this->page = 1;
            }
        } else {
            if ($max == 0) {
                $this->maxPerPage = 0;
                $this->page = 0;
            } else {
                $this->maxPerPage = 1;
                if ($this->page == 0) {
                    $this->page = 1;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxPageLinks()
    {
        return $this->maxPageLinks;
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxPageLinks($maxPageLinks)
    {
        $this->maxPageLinks = $maxPageLinks;
    }

    /**
     * Returns true if on the first page.
     *
     * @return bool
     */
    public function isFirstPage()
    {
        return 1 == $this->page;
    }

    /**
     * Returns true if on the last page.
     *
     * @return bool
     */
    public function isLastPage()
    {
        return $this->page == $this->lastPage;
    }

    /**
     * Returns the current pager's parameter holder.
     *
     * @return array
     */
    public function getParameters()
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
     *
     * @return bool
     */
    public function hasParameter($name)
    {
        return isset($this->parameters[$name]);
    }

    /**
     * Sets a parameter.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return current($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return key($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        --$this->resultsCounter;

        return next($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        $this->resultsCounter = count($this->results);

        return reset($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return $this->resultsCounter > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->getNbResults();
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $vars = get_object_vars($this);
        unset($vars['query']);

        return serialize($vars);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $array = unserialize($serialized);

        foreach ($array as $name => $values) {
            $this->$name = $values;
        }
    }

    /**
     * @return array
     */
    public function getCountColumn()
    {
        return $this->countColumn;
    }

    /**
     * @param array $countColumn
     *
     * @return array
     */
    public function setCountColumn(array $countColumn)
    {
        return $this->countColumn = $countColumn;
    }

    /**
     * {@inheritdoc}
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @return ProxyQueryInterface
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Sets the number of results.
     *
     * @param int $nb
     */
    protected function setNbResults($nb)
    {
        $this->nbResults = $nb;
    }

    /**
     * Sets the last page number.
     *
     * @param int $page
     */
    protected function setLastPage($page)
    {
        $this->lastPage = $page;

        if ($this->getPage() > $page) {
            $this->setPage($page);
        }
    }

    /**
     * Returns true if the properties used for iteration have been initialized.
     *
     * @return bool
     */
    protected function isIteratorInitialized()
    {
        return null !== $this->results;
    }

    /**
     * Loads data into properties used for iteration.
     */
    protected function initializeIterator()
    {
        $this->results = $this->getResults();
        $this->resultsCounter = count($this->results);
    }

    /**
     * Empties properties used for iteration.
     */
    protected function resetIterator()
    {
        $this->results = null;
        $this->resultsCounter = 0;
    }

    /**
     * Retrieve the object for a certain offset.
     *
     * @param int $offset
     *
     * @return object
     */
    protected function retrieveObject($offset)
    {
        $queryForRetrieve = clone $this->getQuery();
        $queryForRetrieve
            ->setFirstResult($offset - 1)
            ->setMaxResults(1);

        $results = $queryForRetrieve->execute();

        return $results[0];
    }
}
