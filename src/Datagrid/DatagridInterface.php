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
 */
interface DatagridInterface
{
    public function getPager(): PagerInterface;

    public function getQuery(): ProxyQueryInterface;

    /**
     * @return object[]
     */
    public function getResults(): array;

    public function buildPager(): void;

    public function addFilter(FilterInterface $filter): void;

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
