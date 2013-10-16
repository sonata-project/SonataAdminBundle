<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Search;


use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Filter\FilterInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class SearchHandler
{
    protected $pool;

    /**
     * @param Pool $pool
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @param AdminInterface $admin
     * @param string         $term
     * @param int            $page
     * @param int            $offset
     *
     * @return \Sonata\AdminBundle\Datagrid\PagerInterface
     *
     * @throws \RuntimeException
     */
    public function search(AdminInterface $admin, $term, $page = 0, $offset = 20)
    {
        $datagrid = $admin->getDatagrid();

        $found = false;
        foreach ($datagrid->getFilters() as $name => $filter) {
            /** @var $filter FilterInterface */
            if ($filter->getOption('global_search', false)) {
                $filter->setCondition(FilterInterface::CONDITION_OR);
                $datagrid->setValue($name, null, $term);
                $found = true;
            }
        }

        if (!$found) {
            return false;
        }

        $datagrid->buildPager();

        $pager = $datagrid->getPager();
        $pager->setPage($page);
        $pager->setMaxPerPage($offset);

        return $pager;
    }
}