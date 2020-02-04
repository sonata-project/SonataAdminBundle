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

namespace Sonata\AdminBundle\Search;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Filter\FilterInterface;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SearchHandler
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var bool
     */
    private $caseSensitive;

    /**
     * NEXT_MAJOR: remove default true value for $caseSensitive and add bool type hint.
     *
     * @param bool $caseSensitive
     */
    public function __construct(Pool $pool, $caseSensitive = true)
    {
        $this->pool = $pool;
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * @param string $term
     * @param int    $page
     * @param int    $offset
     *
     * @throws \RuntimeException
     *
     * @return PagerInterface|false
     */
    public function search(AdminInterface $admin, $term, $page = 0, $offset = 20)
    {
        $datagrid = $admin->getDatagrid();

        $found = false;
        foreach ($datagrid->getFilters() as $filter) {
            /** @var $filter FilterInterface */
            if ($filter->getOption('global_search', false)) {
                $filter->setOption('case_sensitive', $this->caseSensitive);
                $filter->setCondition(FilterInterface::CONDITION_OR);
                $datagrid->setValue($filter->getFormName(), null, $term);
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
        $pager->init();

        return $pager;
    }
}
