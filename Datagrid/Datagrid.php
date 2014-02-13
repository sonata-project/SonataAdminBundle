<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Datagrid;

use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\CallbackTransformer;

class Datagrid implements DatagridInterface
{
    /**
     *
     * The filter instances
     * @var array
     */
    protected $filters = array();

    protected $values;

    protected $columns;

    protected $pager;

    protected $bound = false;

    protected $query;

    protected $formBuilder;

    protected $form;

    protected $results;

    /**
     * @param ProxyQueryInterface        $query
     * @param FieldDescriptionCollection $columns
     * @param PagerInterface             $pager
     * @param FormBuilder                $formBuilder
     * @param array                      $values
     */
    public function __construct(ProxyQueryInterface $query, FieldDescriptionCollection $columns, PagerInterface $pager, FormBuilder $formBuilder, array $values = array())
    {
        $this->pager       = $pager;
        $this->query       = $query;
        $this->values      = $values;
        $this->columns     = $columns;
        $this->formBuilder = $formBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getPager()
    {
        return $this->pager;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $this->buildPager();

        if (!$this->results) {
            $this->results = $this->pager->getResults();
        }

        return $this->results;
    }

    /**
     * {@inheritdoc}
     */
    public function buildPager()
    {
        if ($this->bound) {
            return;
        }

        foreach ($this->getFilters() as $name => $filter) {
            list($type, $options) = $filter->getRenderSettings();

            $this->formBuilder->add($filter->getFormName(), $type, $options);
        }

        $this->formBuilder->add('_sort_by', 'hidden');
        $this->formBuilder->get('_sort_by')->addViewTransformer(new CallbackTransformer(
            function($value) { return $value; },
            function($value) { return $value instanceof FieldDescriptionInterface ? $value->getName() : $value; }
        ));

        $this->formBuilder->add('_sort_order', 'hidden');
        $this->formBuilder->add('_page', 'hidden');
        $this->formBuilder->add('_per_page', 'hidden');

        $this->form = $this->formBuilder->getForm();
        $this->form->submit($this->values);

        $data = $this->form->getData();

        foreach ($this->getFilters() as $name => $filter) {
            $this->values[$name] = isset($this->values[$name]) ? $this->values[$name] : null;
            $filter->apply($this->query, $data[$filter->getFormName()]);
        }

        if (isset($this->values['_sort_by'])) {
            if (!$this->values['_sort_by'] instanceof FieldDescriptionInterface) {
                throw new UnexpectedTypeException($this->values['_sort_by'],'FieldDescriptionInterface');
            }

            if ($this->values['_sort_by']->isSortable()) {
                $this->query->setSortBy($this->values['_sort_by']->getSortParentAssociationMapping(), $this->values['_sort_by']->getSortFieldMapping());
                $this->query->setSortOrder(isset($this->values['_sort_order']) ? $this->values['_sort_order'] : null);
            }
        }

        $this->pager->setMaxPerPage(isset($this->values['_per_page']) ? $this->values['_per_page'] : 25);
        $this->pager->setPage(isset($this->values['_page']) ? $this->values['_page'] : 1);
        $this->pager->setQuery($this->query);
        $this->pager->init();

        $this->bound = true;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[$filter->getName()] = $filter;
    }

    /**
     * {@inheritdoc}
     */
    public function hasFilter($name)
    {
        return isset($this->filters[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function removeFilter($name)
    {
        unset($this->filters[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilter($name)
    {
        return $this->hasFilter($name) ? $this->filters[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * {@inheritdoc}
     */
    public function reorderFilters(array $keys)
    {
        $this->filters = array_merge(array_flip($keys), $this->filters);
    }

    /**
     * {@inheritdoc}
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($name, $operator, $value)
    {
        $this->values[$name] = array(
            'type'  => $operator,
            'value' => $value
        );
    }

    /**
     * {@inheritdoc}
     */
    public function hasActiveFilters()
    {
        foreach ($this->filters as $name => $filter) {
            if ($filter->isActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        $this->buildPager();

        return $this->form;
    }
}
