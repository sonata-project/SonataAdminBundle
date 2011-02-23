<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BaseApplicationBundle\Tool;

/**
 * Pager class.
 *
 * @package    symfony
 * @subpackage addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
abstract class Pager implements \Iterator, \Countable
{
    protected
        $page = 1,
        $maxPerPage = 0,
        $lastPage = 1,
        $nbResults = 0,
        $class = '',
        $tableName = '',
        $objects = null,
        $cursor = 1,
        $parameters = array(),
        $currentMaxLink = 1,
        $parameterBag = null,
        $maxRecordLimit = false,

        // used by iterator interface
        $results = null,
        $resultsCounter = 0;

    /**
     * Constructor.
     *
     * @param string  $class      The model class
     * @param integer $maxPerPage Number of records to display per page
     */
    public function __construct($class, $maxPerPage = 10)
    {
        $this->setClass($class);
        $this->setMaxPerPage($maxPerPage);
    }

    /**
     * Initialize the pager.
     *
     * Function to be called after parameters have been set.
     */
    abstract public function init();

    /**
     * Returns an array of results on the given page.
     *
     * @return array
     */
    abstract public function getResults();

    /**
     * Returns an object at a certain offset.
     *
     * Used internally by {@link getCurrent()}.
     *
     * @return mixed
     */
    abstract protected function retrieveObject($offset);

    /**
     * Returns the current pager's max link.
     *
     * @return integer
     */
    public function getCurrentMaxLink()
    {
        return $this->currentMaxLink;
    }

    /**
     * Returns the current pager's max record limit.
     *
     * @return integer
     */
    public function getMaxRecordLimit()
    {
        return $this->maxRecordLimit;
    }

    /**
     * Sets the current pager's max record limit.
     *
     * @param integer $limit
     */
    public function setMaxRecordLimit($limit)
    {
        $this->maxRecordLimit = $limit;
    }

    /**
     * Returns an array of page numbers to use in pagination links.
     *
     * @param  integer $nb_links The maximum number of page numbers to return
     *
     * @return array
     */
    public function getLinks($nb_links = 5)
    {
        $links = array();
        $tmp = $this->page - floor($nb_links / 2);
        $check = $this->lastPage - $nb_links + 1;
        $limit = $check > 0 ? $check : 1;
        $begin = $tmp > 0 ? ($tmp > $limit ? $limit : $tmp) : 1;

        $i = (int) $begin;
        while ($i < $begin + $nb_links && $i <= $this->lastPage)
        {
            $links[] = $i++;
        }

        $this->currentMaxLink = count($links) ? $links[count($links) - 1] : 1;

        return $links;
    }

    /**
     * Returns true if the current query requires pagination.
     *
     * @return boolean
     */
    public function haveToPaginate()
    {
        return $this->getMaxPerPage() && $this->getNbResults() > $this->getMaxPerPage();
    }

    /**
     * Returns the current cursor.
     *
     * @return integer
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    /**
     * Sets the current cursor.
     *
     * @param integer $pos
     */
    public function setCursor($pos)
    {
        if ($pos < 1) {
            $this->cursor = 1;
        }
        else {
            if ($pos > $this->nbResults) {
                $this->cursor = $this->nbResults;
            }
            else
            {
                $this->cursor = $pos;
            }
        }
    }

    /**
     * Returns an object by cursor position.
     *
     * @param  integer $pos
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
            return null;
        }
        else
        {
            return $this->retrieveObject($this->cursor + 1);
        }
    }

    /**
     * Returns the previous object.
     *
     * @return mixed|null
     */
    public function getPrevious()
    {
        if ($this->cursor - 1 < 1) {
            return null;
        }
        else
        {
            return $this->retrieveObject($this->cursor - 1);
        }
    }

    /**
     * Returns the first index on the current page.
     *
     * @return integer
     */
    public function getFirstIndice()
    {
        if ($this->page == 0) {
            return 1;
        }
        else
        {
            return ($this->page - 1) * $this->maxPerPage + 1;
        }
    }

    /**
     * Returns the last index on the current page.
     *
     * @return integer
     */
    public function getLastIndice()
    {
        if ($this->page == 0) {
            return $this->nbResults;
        }
        else
        {
            if ($this->page * $this->maxPerPage >= $this->nbResults) {
                return $this->nbResults;
            }
            else
            {
                return $this->page * $this->maxPerPage;
            }
        }
    }

    /**
     * Returns the current class.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Sets the current class.
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Returns the number of results.
     *
     * @return integer
     */
    public function getNbResults()
    {
        return $this->nbResults;
    }

    /**
     * Sets the number of results.
     *
     * @param integer $nb
     */
    protected function setNbResults($nb)
    {
        $this->nbResults = $nb;
    }

    /**
     * Returns the first page number.
     *
     * @return integer
     */
    public function getFirstPage()
    {
        return 1;
    }

    /**
     * Returns the last page number.
     *
     * @return integer
     */
    public function getLastPage()
    {
        return $this->lastPage;
    }

    /**
     * Sets the last page number.
     *
     * @param integer $page
     */
    protected function setLastPage($page)
    {
        $this->lastPage = $page;

        if ($this->getPage() > $page) {
            $this->setPage($page);
        }
    }

    /**
     * Returns the current page.
     *
     * @return integer
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Returns the next page.
     *
     * @return integer
     */
    public function getNextPage()
    {
        return min($this->getPage() + 1, $this->getLastPage());
    }

    /**
     * Returns the previous page.
     *
     * @return integer
     */
    public function getPreviousPage()
    {
        return max($this->getPage() - 1, $this->getFirstPage());
    }

    /**
     * Sets the current page.
     *
     * @param integer $page
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
     * Returns the maximum number of results per page.
     *
     * @return integer
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    /**
     * Sets the maximum number of results per page.
     *
     * @param integer $max
     */
    public function setMaxPerPage($max)
    {
        if ($max > 0) {
            $this->maxPerPage = $max;
            if ($this->page == 0) {
                $this->page = 1;
            }
        }
        else {
            if ($max == 0) {
                $this->maxPerPage = 0;
                $this->page = 0;
            }
            else
            {
                $this->maxPerPage = 1;
                if ($this->page == 0) {
                    $this->page = 1;
                }
            }
        }
    }

    /**
     * Returns true if on the first page.
     *
     * @return boolean
     */
    public function isFirstPage()
    {
        return 1 == $this->page;
    }

    /**
     * Returns true if on the last page.
     *
     * @return boolean
     */
    public function isLastPage()
    {
        return $this->page == $this->lastPage;
    }

    /**
     * Returns the current pager's parameter holder.
     *
     * @return sfParameterHolder
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns a parameter.
     *
     * @param  string $name
     * @param  mixed  $default
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
     * @param  string $name
     *
     * @return boolean
     */
    public function hasParameter($name)
    {
        return isset($this->parameters[$name]);
    }

    /**
     * Sets a parameter.
     *
     * @param  string $name
     * @param  mixed  $value
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Returns true if the properties used for iteration have been initialized.
     *
     * @return boolean
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
     * Returns the current result.
     *
     * @see Iterator
     */
    public function current()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return current($this->results);
    }

    /**
     * Returns the current key.
     *
     * @see Iterator
     */
    public function key()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return key($this->results);
    }

    /**
     * Advances the internal pointer and returns the current result.
     *
     * @see Iterator
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
     * Resets the internal pointer and returns the current result.
     *
     * @see Iterator
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
     * Returns true if pointer is within bounds.
     *
     * @see Iterator
     */
    public function valid()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return $this->resultsCounter > 0;
    }

    /**
     * Returns the total number of results.
     *
     * @see Countable
     */
    public function count()
    {
        return $this->getNbResults();
    }
}