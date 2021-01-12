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

use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @method array getSortParameters(FieldDescriptionInterface $fieldDescription)
 * @method array getPaginationParameters(int $page)
 */
interface DatagridInterface
{
    /**
     * @return PagerInterface
     */
    public function getPager();

    /**
     * @return ProxyQueryInterface
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
