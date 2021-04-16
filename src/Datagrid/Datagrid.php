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
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of ProxyQueryInterface
 * @phpstan-implements DatagridInterface<T>
 */
class Datagrid implements DatagridInterface
{
    /**
     * The filter instances.
     *
     * @var array<string, mixed>
     */
    protected $filters = [];

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var FieldDescriptionCollection
     */
    protected $columns;

    /**
     * @var PagerInterface
     * @phpstan-var PagerInterface<T>
     */
    protected $pager;

    /**
     * @var bool
     */
    protected $bound = false;

    /**
     * @var ProxyQueryInterface
     * @phpstan-var T
     */
    protected $query;

    /**
     * @var FormBuilderInterface
     */
    protected $formBuilder;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var iterable<object>|null
     */
    protected $results;

    /**
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

    public function getPager()
    {
        return $this->pager;
    }

    public function getResults()
    {
        $this->buildPager();

        if (null === $this->results) {
            // NEXT_MAJOR: remove the existence check and just use $pager->getCurrentPageResults()
            if (method_exists($this->pager, 'getCurrentPageResults')) {
                $this->results = $this->pager->getCurrentPageResults();
            } else {
                @trigger_error(sprintf(
                    'Not implementing "%s::getCurrentPageResults()" is deprecated since sonata-project/admin-bundle 3.87 and will fail in 4.0.',
                    PagerInterface::class
                ), \E_USER_DEPRECATED);

                $this->results = $this->pager->getResults();
            }
        }

        return $this->results;
    }

    public function buildPager()
    {
        if ($this->bound) {
            return;
        }

        foreach ($this->getFilters() as $filter) {
            [$type, $options] = $filter->getRenderSettings();

            $this->formBuilder->add($filter->getFormName(), $type, $options);
        }

        $hiddenType = HiddenType::class;

        $this->formBuilder->add(DatagridInterface::SORT_BY, $hiddenType);
        $this->formBuilder->get(DatagridInterface::SORT_BY)->addViewTransformer(new CallbackTransformer(
            static function ($value) {
                return $value;
            },
            static function ($value) {
                return $value instanceof FieldDescriptionInterface ? $value->getName() : $value;
            }
        ));

        $this->formBuilder->add(DatagridInterface::SORT_ORDER, $hiddenType);
        $this->formBuilder->add(DatagridInterface::PAGE, $hiddenType);

        if (isset($this->values[DatagridInterface::PER_PAGE]) && \is_array($this->values[DatagridInterface::PER_PAGE])) {
            $this->formBuilder->add(DatagridInterface::PER_PAGE, CollectionType::class, [
                'entry_type' => $hiddenType,
                'allow_add' => true,
            ]);
        } else {
            $this->formBuilder->add(DatagridInterface::PER_PAGE, $hiddenType);
        }

        $this->form = $this->formBuilder->getForm();
        $this->form->submit($this->values);

        $this->applyFilters($this->form->getData() ?? []);
        $this->applySorting();

        $this->pager->setMaxPerPage($this->getMaxPerPage(25));
        $this->pager->setPage($this->getPage(1));
        $this->pager->setQuery($this->query);
        $this->pager->init();

        $this->bound = true;
    }

    public function addFilter(FilterInterface $filter)
    {
        $this->filters[$filter->getName()] = $filter;

        return $filter;
    }

    public function hasFilter($name)
    {
        return isset($this->filters[$name]);
    }

    public function removeFilter($name)
    {
        unset($this->filters[$name]);
    }

    public function getFilter($name)
    {
        if (!$this->hasFilter($name)) {
            @trigger_error(sprintf(
                'Passing a nonexistent filter name as argument 1 to %s() is deprecated since'
                .' sonata-project/admin-bundle 3.52 and will throw an exception in 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);

            // NEXT_MAJOR : remove the previous `trigger_error()` call, the `return null` statement, uncomment the following exception and declare FilterInterface as return type
            // throw new \InvalidArgumentException(sprintf(
            //    'Filter named "%s" doesn\'t exist.',
            //    $name
            // ));

            return null;
        }

        return $this->filters[$name];
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function reorderFilters(array $keys)
    {
        $this->filters = array_merge(array_flip($keys), $this->filters);
    }

    public function getValues()
    {
        return $this->values;
    }

    public function setValue($name, $operator, $value)
    {
        $this->values[$name] = [
            'type' => $operator,
            'value' => $value,
        ];
    }

    public function hasActiveFilters()
    {
        foreach ($this->filters as $filter) {
            if ($filter->isActive()) {
                return true;
            }
        }

        return false;
    }

    public function hasDisplayableFilters()
    {
        foreach ($this->filters as $filter) {
            $showFilter = $filter->getOption('show_filter', null);
            if (($filter->isActive() && null === $showFilter) || (true === $showFilter)) {
                return true;
            }
        }

        return false;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getForm()
    {
        $this->buildPager();

        return $this->form;
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
}

// NEXT_MAJOR: Remove next line.
interface_exists(FieldDescriptionInterface::class);
