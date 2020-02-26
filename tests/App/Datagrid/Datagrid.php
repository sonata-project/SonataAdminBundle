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

namespace Sonata\AdminBundle\Tests\App\Datagrid;

use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;

final class Datagrid implements DatagridInterface
{
    private $formFactory;
    private $pager;

    public function __construct(FormFactoryInterface $formFactory, PagerInterface $pager)
    {
        $this->formFactory = $formFactory;
        $this->pager = $pager;
    }

    public function getPager()
    {
        return $this->pager;
    }

    public function getQuery()
    {
    }

    public function getResults()
    {
        return $this->pager->getResults();
    }

    public function buildPager()
    {
    }

    public function addFilter(FilterInterface $filter)
    {
    }

    public function getFilters()
    {
    }

    public function reorderFilters(array $keys)
    {
    }

    public function getValues()
    {
        return [];
    }

    public function getColumns()
    {
    }

    public function setValue($name, $operator, $value)
    {
    }

    public function getForm()
    {
        return $this->formFactory->createNamedBuilder('filter', FormType::class, [])->getForm();
    }

    public function getFilter($name)
    {
    }

    public function hasFilter($name)
    {
    }

    public function removeFilter($name)
    {
    }

    public function hasActiveFilters()
    {
    }

    public function hasDisplayableFilters()
    {
    }
}
