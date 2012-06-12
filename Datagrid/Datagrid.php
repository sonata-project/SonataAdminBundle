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
     * @param ProxyQueryInterface                                  $query
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionCollection $columns
     * @param PagerInterface                                       $pager
     * @param \Symfony\Component\Form\FormBuilder                  $formBuilder
     * @param array                                                $values
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
     * @return \Sonata\AdminBundle\Datagrid\PagerInterface
     */
    public function getPager()
    {
        return $this->pager;
    }

    /**
     * @return array
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
     * @return void
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
        $this->formBuilder->get('_sort_by')->appendClientTransformer(new CallbackTransformer(
            function($value) {
                return $value;
            },
            function($value) {
                if ($value instanceof FieldDescriptionInterface) {
                    return $value->getName();
                } else {
                    return $value;
                }
            }
        ));
        $this->formBuilder->add('_sort_order', 'hidden');
        $this->formBuilder->add('_page', 'hidden');

        $this->form = $this->formBuilder->getForm();
        $this->form->bind($this->values);

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
                $this->query->setSortBy($this->values['_sort_by']->getParentAssociationMappings(), $this->values['_sort_by']->getSortFieldMapping());
                $this->query->setSortOrder(isset($this->values['_sort_order']) ? $this->values['_sort_order'] : null);
            }
        }

        $this->pager->setPage(isset($this->values['_page']) ? $this->values['_page'] : 1);
        $this->pager->setQuery($this->query);
        $this->pager->init();

        $this->bound = true;
    }

    /**
     * @param \Sonata\AdminBundle\Filter\FilterInterface $filter
     *
     * @return void
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[$filter->getName()] = $filter;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasFilter($name)
    {
        return isset($this->filters[$name]);
    }

    /**
     * @param string $name
     */
    public function removeFilter($name)
    {
        unset($this->filters[$name]);
    }

    /**
     * @param string $name
     *
     * @return null
     */
    public function getFilter($name)
    {
        return $this->hasFilter($name) ? $this->filters[$name] : null;
    }

    /**
     * @return FilterInterface[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    public function reorderFilters(array $keys)
    {
        $this->filters = array_merge(array_flip($keys), $this->filters);
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param string $name
     * @param string $operator
     * @param mixed  $value
     */
    public function setValue($name, $operator, $value)
    {
        $this->values[$name] = array(
            'type'  => $operator,
            'value' => $value
        );
    }

    /**
     * @return boolean
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
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionCollection
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return ProxyQueryInterface
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    public function getForm()
    {
        $this->buildPager();

        return $this->form;
    }
}
