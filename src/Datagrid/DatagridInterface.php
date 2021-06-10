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
 * @phpstan-template T of ProxyQueryInterface
 */
interface DatagridInterface
{
    public const SORT_ORDER = '_sort_order';
    public const SORT_BY = '_sort_by';
    public const PAGE = '_page';
    public const PER_PAGE = '_per_page';

    /**
     * @phpstan-return PagerInterface<T>
     */
    public function getPager(): PagerInterface;

    /**
     * @phpstan-return T
     */
    public function getQuery(): ProxyQueryInterface;

    /**
     * @return iterable<object>
     */
    public function getResults(): iterable;

    public function buildPager(): void;

    public function addFilter(FilterInterface $filter): FilterInterface;

    /**
     * @return array<string, FilterInterface>
     */
    public function getFilters(): array;

    /**
     * Reorder filters.
     *
     * @param string[] $keys
     */
    public function reorderFilters(array $keys): void;

    /**
     * @return array<string, mixed>
     */
    public function getValues(): array;

    /**
     * @return FieldDescriptionCollection<FieldDescriptionInterface>
     */
    public function getColumns(): FieldDescriptionCollection;

    /**
     * @param mixed $value
     */
    public function setValue(string $name, ?string $operator, $value): void;

    public function getForm(): FormInterface;

    public function getFilter(string $name): FilterInterface;

    public function hasFilter(string $name): bool;

    public function removeFilter(string $name): void;

    public function hasActiveFilters(): bool;

    public function hasDisplayableFilters(): bool;

    /**
     * TODO: avoid returning an array with one element and return its contents instead.
     *
     * @return array{filter: array<string, mixed>}
     */
    public function getSortParameters(FieldDescriptionInterface $fieldDescription): array;

    /**
     * TODO: avoid returning an array with one element and return its contents instead.
     *
     * @return array{filter: array<string, mixed>}
     */
    public function getPaginationParameters(int $page): array;
}
