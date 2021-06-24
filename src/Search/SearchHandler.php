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
     * @var array<string, bool>
     */
    private $adminsSearchConfig = [];

    /**
     * NEXT_MAJOR: Change signature to __construct(bool $caseSensitive) and remove pool property.
     *
     * @param Pool|bool $deprecatedPoolOrCaseSensitive
     * @param bool      $caseSensitive
     */
    public function __construct($deprecatedPoolOrCaseSensitive, $caseSensitive = true)
    {
        if ($deprecatedPoolOrCaseSensitive instanceof Pool) {
            @trigger_error(sprintf(
                'Passing %s as argument 1 to %s() is deprecated since sonata-project/admin-bundle 3.74.'
                .' It will accept only bool in version 4.0.',
                Pool::class,
                __METHOD__
            ), \E_USER_DEPRECATED);

            $this->pool = $deprecatedPoolOrCaseSensitive;
            $this->caseSensitive = $caseSensitive;
        } else {
            $this->caseSensitive = $deprecatedPoolOrCaseSensitive;
        }
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
        // If the search is disabled for the whole admin, skip any further processing.
        if (false === ($this->adminsSearchConfig[$admin->getCode()] ?? true)) {
            return false;
        }

        $datagrid = $admin->getDatagrid();

        $datagridValues = $datagrid->getValues();

        $found = false;
        foreach ($datagrid->getFilters() as $filter) {
            /** @var FilterInterface $filter */
            $formName = $filter->getFormName();

            // NEXT_MAJOR: Remove the $filter->getOption('global_search', false) part.
            if (
                $filter->getOption('global_search', false)
                || $filter instanceof SearchableFilterInterface && $filter->isSearchActive()
            ) {
                if (!$filter instanceof SearchableFilterInterface) {
                    @trigger_error(sprintf(
                        'Passing `global_search` to a filter which not implement %s is deprecated'
                        .' since sonata-project/admin-bundle 3.x and won\'t work in 4.0.',
                        SearchableFilterInterface::class
                    ), \E_USER_DEPRECATED);
                }

                $filter->setOption('case_sensitive', $this->caseSensitive);
                $filter->setOption('or_group', $admin->getCode());
                $filter->setCondition(FilterInterface::CONDITION_OR);
                $datagrid->setValue($formName, null, $term);
                $found = true;
            } elseif (isset($datagridValues[$formName])) {
                // Remove any previously set filter that is not configured for the global search.
                $datagrid->removeFilter($formName);
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

    /**
     * Sets whether the search must be enabled or not for the passed admin codes.
     * Receives an array with the admin code as key and a boolean as value.
     *
     * @param array<string, bool> $adminsSearchConfig
     */
    final public function configureAdminSearch(array $adminsSearchConfig): void
    {
        $this->adminsSearchConfig = $adminsSearchConfig;
    }
}
