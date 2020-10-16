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
     * @var Pool|null
     */
    protected $pool;

    /**
     * @var bool
     */
    private $caseSensitive;

    /**
     * NEXT_MAJOR: Change signature to __construct(bool $caseSensitive) and remove pool property.
     *
     * @param Pool|bool $deprecatedPoolOrCaseSensitive
     */
    public function __construct($deprecatedPoolOrCaseSensitive, bool $caseSensitive = true)
    {
        if ($deprecatedPoolOrCaseSensitive instanceof Pool) {
            @trigger_error(sprintf(
                'Passing %s as argument 1 to %s() is deprecated since sonata-project/admin-bundle 3.74.'
                    .' It will accept only bool in version 4.0.',
                Pool::class,
                __METHOD__
            ), E_USER_DEPRECATED);

            $this->pool = $deprecatedPoolOrCaseSensitive;
            $this->caseSensitive = $caseSensitive;
        } else {
            $this->caseSensitive = $deprecatedPoolOrCaseSensitive;
        }
    }

    /**
     * @throws \RuntimeException
     *
     * @return PagerInterface|false
     */
    public function search(AdminInterface $admin, string $term, int $page = 0, int $offset = 20)
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
