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
use Sonata\AdminBundle\Filter\FilterInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface DatagridInterface
{
    public function getPager(): PagerInterface;

    public function getQuery(): ProxyQueryInterface;

    public function getResults(): array;

    public function buildPager();

    public function addFilter(FilterInterface $filter): void;

    public function getFilters(): array;

    /**
     * Reorder filters.
     */
    public function reorderFilters(array $keys);

    public function getValues(): array;

    public function getColumns(): FieldDescriptionCollection;

    /**
     * @param string      $name
     * @param string|null $operator
     * @param mixed       $value
     */
    public function setValue($name, $operator, $value);

    public function getForm(): FormInterface;

    /**
     * @param string $name
     */
    public function getFilter($name): FilterInterface;

    /**
     * @param string $name
     */
    public function hasFilter($name): bool;

    /**
     * @param string $name
     */
    public function removeFilter($name);

    public function hasActiveFilters(): bool;

    public function hasDisplayableFilters(): bool;
}
