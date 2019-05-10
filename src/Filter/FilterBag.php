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

namespace Sonata\AdminBundle\Filter;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class FilterBag implements \IteratorAggregate, \Countable
{
    /**
     * Filter storage.
     */
    private $filters = [];

    /**
     * @param array $filters An array of filters
     */
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Returns the filters.
     *
     * @return array An array of filters
     */
    public function all(): array
    {
        return $this->filters;
    }

    /**
     * Returns the filter keys.
     *
     * @return array An array of filter keys
     */
    public function keys(): array
    {
        return array_keys($this->filters);
    }

    /**
     * Replaces the current filters by a new set.
     *
     * @param array $filters An array of filters
     */
    public function replace(array $filters = []): void
    {
        $this->filters = $filters;
    }

    /**
     * Adds filters.
     *
     * @param array $filters An array of filters
     */
    public function add(array $filters = []): void
    {
        $this->filters = array_replace($this->filters, $filters);
    }

    /**
     * Returns a filter by name.
     *
     * @param string $key     The key
     * @param mixed  $default The default value if the filter key does not exist
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return \array_key_exists($key, $this->filters) ? $this->filters[$key] : $default;
    }

    /**
     * Sets a filter by name.
     *
     * @param string $key   The key
     * @param mixed  $value The value
     */
    public function set(string $key, $value): void
    {
        $this->filters[$key] = $value;
    }

    /**
     * Returns true if the filter is defined.
     *
     * @param string $key The key
     *
     * @return bool true if the filter exists, false otherwise
     */
    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->filters);
    }

    /**
     * Removes a filter.
     *
     * @param string $key The key
     */
    public function remove(string $key): void
    {
        unset($this->filters[$key]);
    }

    /**
     * Returns the alphabetic characters of the filter value.
     *
     * @param string $key     The filter key
     * @param string $default The default value if the filter key does not exist
     *
     * @return string The filtered value
     */
    public function getAlpha(string $key, string $default = ''): string
    {
        return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default));
    }

    /**
     * Returns the alphabetic characters and digits of the filter value.
     *
     * @param string $key     The filter key
     * @param string $default The default value if the filter key does not exist
     *
     * @return string The filtered value
     */
    public function getAlnum(string $key, string $default = ''): string
    {
        return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default));
    }

    /**
     * Returns the digits of the filter value.
     *
     * @param string $key     The filter key
     * @param string $default The default value if the filter key does not exist
     *
     * @return string The filtered value
     */
    public function getDigits(string $key, string $default = ''): string
    {
        // we need to remove - and + because they're allowed in the filter
        return str_replace(['-', '+'], '', $this->filter($key, $default, FILTER_SANITIZE_NUMBER_INT));
    }

    /**
     * Returns the filter value converted to integer.
     *
     * @param string $key     The filter key
     * @param int    $default The default value if the filter key does not exist
     *
     * @return int The filtered value
     */
    public function getInt(string $key, int $default = 0): int
    {
        return (int) $this->get($key, $default);
    }

    /**
     * Returns the filter value converted to boolean.
     *
     * @param string $key     The filter key
     * @param bool   $default The default value if the filter key does not exist
     *
     * @return bool The filtered value
     */
    public function getBoolean(string $key, bool $default = false): bool
    {
        return $this->filter($key, $default, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Filter key.
     *
     * @param string $key     Key
     * @param mixed  $default Default = null
     * @param int    $filter  FILTER_* constant
     * @param mixed  $options Filter options
     *
     * @see http://php.net/manual/en/function.filter-var.php
     *
     * @return mixed
     */
    public function filter(string $key, $default = null, int $filter = FILTER_DEFAULT, $options = [])
    {
        $value = $this->get($key, $default);

        // Always turn $options into an array - this allows filter_var option shortcuts.
        if (!\is_array($options) && $options) {
            $options = ['flags' => $options];
        }

        // Add a convenience check for arrays.
        if (\is_array($value) && !isset($options['flags'])) {
            $options['flags'] = FILTER_REQUIRE_ARRAY;
        }

        return filter_var($value, $filter, $options);
    }

    /**
     * Returns an iterator for filters.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->filters);
    }

    /**
     * Returns the number of filters.
     *
     * @return int The number of filters
     */
    public function count(): int
    {
        return \count($this->filters);
    }
}
