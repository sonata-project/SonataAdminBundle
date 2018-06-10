<?php

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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class Datagrid implements DatagridInterface
{
    /**
     * The filter instances.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * @var array
     */
    protected $values;

    /**
     * @var FieldDescriptionCollection
     */
    protected $columns;

    /**
     * @var PagerInterface
     */
    protected $pager;

    /**
     * @var bool
     */
    protected $bound = false;

    /**
     * @var ProxyQueryInterface
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
     * @var array
     */
    protected $results;

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
            $this->results = $this->pager->getResults();
        }

        return $this->results;
    }

    public function buildPager()
    {
        if ($this->bound) {
            return;
        }

        foreach ($this->getFilters() as $name => $filter) {
            list($type, $options) = $filter->getRenderSettings();

            $this->formBuilder->add($filter->getFormName(), $type, $options);
        }

        $hiddenType = HiddenType::class;

        $this->formBuilder->add('_sort_by', $hiddenType);
        $this->formBuilder->get('_sort_by')->addViewTransformer(new CallbackTransformer(
            function ($value) {
                return $value;
            },
            function ($value) {
                return $value instanceof FieldDescriptionInterface ? $value->getName() : $value;
            }
        ));

        $this->formBuilder->add('_sort_order', $hiddenType);
        $this->formBuilder->add('_page', $hiddenType);
        $this->formBuilder->add('_per_page', $hiddenType);

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
                $this->query->setSortOrder(isset($this->values['_sort_order']) ? $this->values['_sort_order'] : null);
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

    public function addFilter(FilterInterface $filter)
    {
        $this->filters[$filter->getName()] = $filter;
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
        return $this->hasFilter($name) ? $this->filters[$name] : null;
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
        foreach ($this->filters as $name => $filter) {
            if ($filter->isActive()) {
                return true;
            }
        }

        return false;
    }

    public function hasDisplayableFilters()
    {
        foreach ($this->filters as $name => $filter) {
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
}
