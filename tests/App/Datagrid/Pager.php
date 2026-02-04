<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\App\Datagrid;

use SensioLabs\AdminBundle\Datagrid\PagerInterface;
use SensioLabs\AdminBundle\Datagrid\ProxyQueryInterface;
use SensioLabs\AdminBundle\Tests\App\Model\FooRepository;

/**
 * @phpstan-implements PagerInterface<ProxyQueryInterface<object>>
 */
final class Pager implements PagerInterface
{
    public function __construct(
        private FooRepository $repository,
    ) {
    }

    public function init(): void
    {
    }

    public function getMaxPerPage(): int
    {
        return 1;
    }

    public function setMaxPerPage(int $max): void
    {
    }

    public function getPage(): int
    {
        return 1;
    }

    public function setPage(int $page): void
    {
    }

    public function getNextPage(): int
    {
        return 1;
    }

    public function getPreviousPage(): int
    {
        return 1;
    }

    public function getFirstPage(): int
    {
        return 1;
    }

    public function isFirstPage(): bool
    {
        return true;
    }

    public function getLastPage(): int
    {
        return 1;
    }

    public function isLastPage(): bool
    {
        return false;
    }

    /**
     * @phpstan-return ProxyQueryInterface<object>
     */
    public function getQuery(): ProxyQueryInterface
    {
        return new ProxyQuery();
    }

    public function setQuery(ProxyQueryInterface $query): void
    {
    }

    public function haveToPaginate(): bool
    {
        return false;
    }

    public function getCurrentPageResults(): iterable
    {
        return $this->repository->all();
    }

    public function countResults(): int
    {
        return 1;
    }

    public function getLinks(?int $nbLinks = null): array
    {
        return [];
    }

    public function setMaxPageLinks(int $maxPageLinks): void
    {
    }

    public function getMaxPageLinks(): int
    {
        return 1;
    }
}
