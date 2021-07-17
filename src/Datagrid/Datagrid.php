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
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of ProxyQueryInterface
 * @phpstan-implements DatagridInterface<T>
 */
final class Datagrid implements DatagridInterface
{
    /**
     * The filter instances.
     *
     * @var array<string, FilterInterface>
     */
    private $filters = [];

    /**
     * @var array<string, mixed>
     */
    private $values = [];

    /**
     * @var FieldDescriptionCollection<FieldDescriptionInterface>
     */
    private $columns;

    /**
     * @var PagerInterface
     * @phpstan-var PagerInterface<T>
     */
    private $pager;

    /**
     * @var bool
     */
    private $bound = false;

    /**
     * @var ProxyQueryInterface
     * @phpstan-var T
     */
    private $query;

    /**
     * @var FormBuilderInterface
     */
    private $formBuilder;

    /**
     * @var FormInterface|null
     */
    private $form;

    /**
     * Results are null prior to its initialization in `getResults()`.
     *
     * @var iterable<object>|null
     */
    private $results;

    /**
     * @param FieldDescriptionCollection<FieldDescriptionInterface> $columns
     * @param array<string, mixed>                                  $values
     *
     * @phpstan-param T                 $query
     * @phpstan-param PagerInterface<T> $pager
     */
    public function __construct(
        ProxyQueryInterface $query,
        FieldDescriptionCollection $columns,
        PagerInterface $pager,
        FormBuilderInterface $formBuilder,
        array $values = []
    ) {
        $this->pager = $pager;
        $this->query = $query;
        $this->values = $values;
        $this->columns = $columns;
        $this->formBuilder = $formBuilder;
    }

    public function getPager(): PagerInterface
    {
        return $this->pager;
    }

    public function getResults(): iterable
    {
        $this->buildPager();

        if (null === $this->results) {
            $this->results = $this->pager->getCurrentPageResults();
        }

        return $this->results;
    }

    public function buildPager(): void
    {
        if ($this->bound) {
            return;
        }

        $form = $this->buildForm();

        $this->applyFilters($form->getData() ?? []);
        $this->applySorting();

        $this->pager->setMaxPerPage($this->getMaxPerPage(25));
        $this->pager->setPage($this->getPage(1));
        $this->pager->setQuery($this->query);
        $this->pager->init();

        $this->bound = true;
    }

    public function addFilter(FilterInterface $filter): FilterInterface
    {
        $this->filters[$filter->getName()] = $filter;

        return $filter;
    }

    public function hasFilter(string $name): bool
    {
        return isset($this->filters[$name]);
    }

    public function removeFilter(string $name): void
    {
        unset($this->filters[$name]);
    }

    public function getFilter(string $name): FilterInterface
    {
        if (!$this->hasFilter($name)) {
            throw new \InvalidArgumentException(sprintf(
                'Filter named "%s" doesn\'t exist.',
                $name
            ));
        }

        return $this->filters[$name];
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function reorderFilters(array $keys): void
    {
        $orderedFilters = [];
        foreach ($keys as $name) {
            if (!$this->hasFilter($name)) {
                throw new \InvalidArgumentException(sprintf('Filter "%s" does not exist.', $name));
            }

            $orderedFilters[$name] = $this->filters[$name];
        }

        $this->filters = $orderedFilters + $this->filters;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function setValue(string $name, ?string $operator, $value): void
    {
        $this->values[$name] = [
            'type' => $operator,
            'value' => $value,
        ];
    }

    public function hasActiveFilters(): bool
    {
        foreach ($this->filters as $filter) {
            if ($filter->isActive()) {
                return true;
            }
        }

        return false;
    }

    public function hasDisplayableFilters(): bool
    {
        foreach ($this->filters as $filter) {
            $showFilter = $filter->getOption('show_filter', null);
            if (($filter->isActive() && null === $showFilter) || (true === $showFilter)) {
                return true;
            }
        }

        return false;
    }

    public function getColumns(): FieldDescriptionCollection
    {
        return $this->columns;
    }

    public function getQuery(): ProxyQueryInterface
    {
        return $this->query;
    }

    public function getForm(): FormInterface
    {
        return $this->buildForm();
    }

    public function getSortParameters(FieldDescriptionInterface $fieldDescription): array
    {
        $values = $this->getValues();

        if ($this->isFieldAlreadySorted($fieldDescription)) {
            if ('ASC' === $values[DatagridInterface::SORT_ORDER]) {
                $values[DatagridInterface::SORT_ORDER] = 'DESC';
            } else {
                $values[DatagridInterface::SORT_ORDER] = 'ASC';
            }
        } else {
            $values[DatagridInterface::SORT_ORDER] = 'ASC';
        }

        $values[DatagridInterface::SORT_BY] = \is_string($fieldDescription->getOption('sortable'))
            ? $fieldDescription->getOption('sortable')
            : $fieldDescription->getName();

        return ['filter' => $values];
    }

    public function getPaginationParameters(int $page): array
    {
        $values = $this->getValues();

        if (isset($values[DatagridInterface::SORT_BY]) && $values[DatagridInterface::SORT_BY] instanceof FieldDescriptionInterface) {
            $values[DatagridInterface::SORT_BY] = $values[DatagridInterface::SORT_BY]->getName();
        }
        $values[DatagridInterface::PAGE] = $page;

        return ['filter' => $values];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function applyFilters(array $data): void
    {
        foreach ($this->getFilters() as $name => $filter) {
            $this->values[$name] = $this->values[$name] ?? null;
            $filterFormName = $filter->getFormName();

            $value = $this->values[$filterFormName]['value'] ?? '';
            $type = $this->values[$filterFormName]['type'] ?? '';

            if ('' !== $value || '' !== $type) {
                $filter->apply($this->query, $data[$filterFormName]);
            }
        }
    }

    private function applySorting(): void
    {
        if (!isset($this->values[DatagridInterface::SORT_BY])) {
            return;
        }

        if (!$this->values[DatagridInterface::SORT_BY] instanceof FieldDescriptionInterface) {
            throw new UnexpectedTypeException($this->values[DatagridInterface::SORT_BY], FieldDescriptionInterface::class);
        }

        if (!$this->values[DatagridInterface::SORT_BY]->isSortable()) {
            return;
        }

        $this->query->setSortBy(
            $this->values[DatagridInterface::SORT_BY]->getSortParentAssociationMapping(),
            $this->values[DatagridInterface::SORT_BY]->getSortFieldMapping()
        );

        $this->values[DatagridInterface::SORT_ORDER] = $this->values[DatagridInterface::SORT_ORDER] ?? 'ASC';
        $this->query->setSortOrder($this->values[DatagridInterface::SORT_ORDER]);
    }

    private function getMaxPerPage(int $default): int
    {
        if (!isset($this->values[DatagridInterface::PER_PAGE])) {
            return $default;
        }

        if (isset($this->values[DatagridInterface::PER_PAGE]['value'])) {
            return (int) $this->values[DatagridInterface::PER_PAGE]['value'];
        }

        return (int) $this->values[DatagridInterface::PER_PAGE];
    }

    private function getPage(int $default): int
    {
        if (!isset($this->values[DatagridInterface::PAGE])) {
            return $default;
        }

        if (isset($this->values[DatagridInterface::PAGE]['value'])) {
            return (int) $this->values[DatagridInterface::PAGE]['value'];
        }

        return (int) $this->values[DatagridInterface::PAGE];
    }

    private function isFieldAlreadySorted(FieldDescriptionInterface $fieldDescription): bool
    {
        $values = $this->getValues();

        if (!isset($values[DatagridInterface::SORT_BY]) || !$values[DatagridInterface::SORT_BY] instanceof FieldDescriptionInterface) {
            return false;
        }

        return $values[DatagridInterface::SORT_BY]->getName() === $fieldDescription->getName()
            || $values[DatagridInterface::SORT_BY]->getName() === $fieldDescription->getOption('sortable');
    }

    private function buildForm(): FormInterface
    {
        if (null !== $this->form) {
            return $this->form;
        }

        foreach ($this->getFilters() as $filter) {
            [$type, $options] = $filter->getRenderSettings();

            $this->formBuilder->add($filter->getFormName(), $type, $options);
        }

        $this->formBuilder->add(DatagridInterface::SORT_BY, HiddenType::class);
        $this->formBuilder->get(DatagridInterface::SORT_BY)->addViewTransformer(new CallbackTransformer(
            static function ($value) {
                return $value;
            },
            static function ($value) {
                return $value instanceof FieldDescriptionInterface ? $value->getName() : $value;
            }
        ));

        $this->formBuilder->add(DatagridInterface::SORT_ORDER, HiddenType::class);
        $this->formBuilder->add(DatagridInterface::PAGE, HiddenType::class);

        if (isset($this->values[DatagridInterface::PER_PAGE]) && \is_array($this->values[DatagridInterface::PER_PAGE])) {
            $this->formBuilder->add(DatagridInterface::PER_PAGE, CollectionType::class, [
                'entry_type' => HiddenType::class,
                'allow_add' => true,
            ]);
        } else {
            $this->formBuilder->add(DatagridInterface::PER_PAGE, HiddenType::class);
        }

        $this->form = $this->formBuilder->getForm();
        $this->form->submit($this->values);

        return $this->form;
    }
}
