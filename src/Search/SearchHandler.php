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
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
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

    /**
     * @var array<string, bool>
     */
    private $adminsSearchConfig = [];

    public function __construct(bool $caseSensitive = true)
    {
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * @param AdminInterface<object> $admin
     *
     * @throws \RuntimeException
     *
     * @return PagerInterface<ProxyQueryInterface>
     */
    public function search(AdminInterface $admin, string $term, int $page = 0, int $offset = 20): ?PagerInterface
    {
        // If the search is disabled for the whole admin, skip any further processing.
        if (false === ($this->adminsSearchConfig[$admin->getCode()] ?? true)) {
            return null;
        }

        $datagrid = $admin->getDatagrid();

        $datagridValues = $datagrid->getValues();

        $found = false;

        foreach ($datagrid->getFilters() as $filter) {
            /** @var FilterInterface $filter */
            $formName = $filter->getFormName();

            if ($filter->getOption('global_search', false)) {
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
            return null;
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
    public function configureAdminSearch(array $adminsSearchConfig): void
    {
        $this->adminsSearchConfig = $adminsSearchConfig;
    }
}
