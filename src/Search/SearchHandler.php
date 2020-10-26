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
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Filter\FilterInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SearchHandler
{
    /**
     * @var bool
     */
    private $caseSensitive;

    public function __construct(bool $caseSensitive = true)
    {
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * @throws \RuntimeException
     */
    public function search(AdminInterface $admin, string $term, int $page = 0, int $offset = 20): ?PagerInterface
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
            return null;
        }

        $datagrid->buildPager();

        $pager = $datagrid->getPager();
        $pager->setPage($page);
        $pager->setMaxPerPage($offset);
        $pager->init();

        return $pager;
    }
}
