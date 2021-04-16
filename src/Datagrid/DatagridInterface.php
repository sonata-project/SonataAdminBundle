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

use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @method array getSortParameters(FieldDescriptionInterface $fieldDescription)
 * @method array getPaginationParameters(int $page)
 *
 * @phpstan-template T of ProxyQueryInterface
 */
interface DatagridInterface
{
    public const SORT_ORDER = '_sort_order';
    public const SORT_BY = '_sort_by';
    public const PAGE = '_page';
    public const PER_PAGE = '_per_page';

    /**
     * @return PagerInterface
     *
     * @phpstan-return PagerInterface<T>
     */
    public function getPager();

    /**
     * @return ProxyQueryInterface
     *
     * @phpstan-return T
     */
    public function getQuery();

    /**
     * @return iterable<object>
     */
    public function getResults();

    public function buildPager();

    /**
     * @return FilterInterface
     */
    public function addFilter(FilterInterface $filter);

    /**
     * @return array<string, mixed>
     */
    public function getFilters();

    /**
     * Reorder filters.
     */
    public function reorderFilters(array $keys);

    /**
     * @return array
     */
    public function getValues();

    /**
     * @return FieldDescriptionCollection
     */
    public function getColumns();

    /**
     * @param string      $name
     * @param string|null $operator
     * @param mixed       $value
     */
    public function setValue($name, $operator, $value);

    /**
     * @return FormInterface
     */
    public function getForm();

    /**
     * @param string $name
     *
     * @return FilterInterface
     */
    public function getFilter($name);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasFilter($name);

    /**
     * @param string $name
     */
    public function removeFilter($name);

    /**
     * @return bool
     */
    public function hasActiveFilters();

    /**
     * @return bool
     */
    public function hasDisplayableFilters();

    /*
     * NEXT_MAJOR: Uncomment getSortParameters and getPaginationParameters.
     *
     * public function getSortParameters(FieldDescriptionInterface $fieldDescription): array;
     * public function getPaginationParameters(int $page): array;
     */
}

// NEXT_MAJOR: Remove next line.
interface_exists(FieldDescriptionInterface::class);
