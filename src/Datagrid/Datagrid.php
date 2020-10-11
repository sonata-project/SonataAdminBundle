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
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
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
     * @var FieldDescriptionCollection
     */
    private $columns;

    /**
     * @var PagerInterface
     */
    private $pager;

    /**
     * @var bool
     */
    private $bound = false;

    /**
     * @var ProxyQueryInterface
     */
    private $query;

    /**
     * @var FormBuilderInterface
     */
    private $formBuilder;

    /**
     * @var FormInterface
     */
    private $form;

    /**
     * Results are null prior to its initialization in `getResults()`.
     *
     * @var object[]|null
     */
    private $results;

    /**
     * @param array<string, mixed> $values
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

    public function getResults(): array
    {
        $this->buildPager();

        if (null === $this->results) {
            $this->results = $this->pager->getResults();
        }

        return $this->results;
    }

    public function buildPager(): void
    {
        if ($this->bound) {
            return;
        }

        foreach ($this->getFilters() as $name => $filter) {
            [$type, $options] = $filter->getRenderSettings();

            $this->formBuilder->add($filter->getFormName(), $type, $options);
        }

        $hiddenType = HiddenType::class;

        $this->formBuilder->add('_sort_by', $hiddenType);
        $this->formBuilder->get('_sort_by')->addViewTransformer(new CallbackTransformer(
            static function ($value) {
                return $value;
            },
            static function ($value) {
                return $value instanceof FieldDescriptionInterface ? $value->getName() : $value;
            }
        ));

        $this->formBuilder->add('_sort_order', $hiddenType);
        $this->formBuilder->add('_page', $hiddenType);

        if (isset($this->values['_per_page']) && \is_array($this->values['_per_page'])) {
            $this->formBuilder->add('_per_page', CollectionType::class, [
                'entry_type' => $hiddenType,
                'allow_add' => true,
            ]);
        } else {
            $this->formBuilder->add('_per_page', $hiddenType);
        }

        $this->form = $this->formBuilder->getForm();
        $this->form->submit($this->values);

        $data = $this->form->getData();

        foreach ($this->getFilters() as $name => $filter) {
            $this->values[$name] = isset($this->values[$name]) ? $this->values[$name] : null;
            $filterFormName = $filter->getFormName();
            if (isset($this->values[$filterFormName]['value']) && '' !== $this->values[$filterFormName]['value']) {
                $filter->apply($this->query, $data[$filterFormName]);
            }
        }

        if (isset($this->values['_sort_by'])) {
            if (!$this->values['_sort_by'] instanceof FieldDescriptionInterface) {
                throw new UnexpectedTypeException($this->values['_sort_by'], FieldDescriptionInterface::class);
            }

            if ($this->values['_sort_by']->isSortable()) {
                $this->query->setSortBy($this->values['_sort_by']->getSortParentAssociationMapping(), $this->values['_sort_by']->getSortFieldMapping());

                $this->values['_sort_order'] = $this->values['_sort_order'] ?? 'ASC';
                $this->query->setSortOrder($this->values['_sort_order']);
            }
        }

        $maxPerPage = 25;
        if (isset($this->values['_per_page'])) {
            if (isset($this->values['_per_page']['value'])) {
                $maxPerPage = $this->values['_per_page']['value'];
            } else {
                $maxPerPage = $this->values['_per_page'];
            }
        }
        $this->pager->setMaxPerPage($maxPerPage);

        $page = 1;
        if (isset($this->values['_page'])) {
            if (isset($this->values['_page']['value'])) {
                $page = $this->values['_page']['value'];
            } else {
                $page = $this->values['_page'];
            }
        }

        $this->pager->setPage($page);

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
        $this->filters = array_merge(array_flip($keys), $this->filters);
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
        foreach ($this->filters as $name => $filter) {
            if ($filter->isActive()) {
                return true;
            }
        }

        return false;
    }

    public function hasDisplayableFilters(): bool
    {
        foreach ($this->filters as $name => $filter) {
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
        $this->buildPager();

        return $this->form;
    }

    public function getSortParameters(FieldDescriptionInterface $fieldDescription): array
    {
        $values = $this->getValues();

        if ($this->isFieldAlreadySorted($fieldDescription)) {
            if ('ASC' === $values['_sort_order']) {
                $values['_sort_order'] = 'DESC';
            } else {
                $values['_sort_order'] = 'ASC';
            }
        } else {
            $values['_sort_order'] = 'ASC';
        }

        $values['_sort_by'] = \is_string($fieldDescription->getOption('sortable'))
            ? $fieldDescription->getOption('sortable')
            : $fieldDescription->getName();

        return ['filter' => $values];
    }

    public function getPaginationParameters(int $page): array
    {
        $values = $this->getValues();

        if (isset($values['_sort_by']) && $values['_sort_by'] instanceof FieldDescriptionInterface) {
            $values['_sort_by'] = $values['_sort_by']->getName();
        }
        $values['_page'] = $page;

        return ['filter' => $values];
    }

    private function isFieldAlreadySorted(FieldDescriptionInterface $fieldDescription): bool
    {
        $values = $this->getValues();

        if (!isset($values['_sort_by']) || !$values['_sort_by'] instanceof FieldDescriptionInterface) {
            return false;
        }

        return $values['_sort_by']->getName() === $fieldDescription->getName()
            || $values['_sort_by']->getName() === $fieldDescription->getOption('sortable');
    }
}
