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

use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Tests\App\Model\FooRepository;

final class Pager implements PagerInterface
{
    /**
     * @var FooRepository
     */
    private $repository;

    public function __construct(FooRepository $repository)
    {
        $this->repository = $repository;
    }

    public function init(): void
    {
    }

    public function getMaxPerPage()
    {
        return 1;
    }

    public function setMaxPerPage($max): void
    {
    }

    public function getPage(): int
    {
        return 1;
    }

    public function setPage($page): void
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
        return 2;
    }

    public function isLastPage(): bool
    {
        return true;
    }

    public function getQuery($query): ProxyQueryInterface
    {
        return new ProxyQuery();
    }

    public function setQuery($query): void
    {
    }

    public function haveToPaginate(): bool
    {
        return false;
    }

    public function getResults(): array
    {
        return $this->repository->all();
    }

    // NEXT_MAJOR: remove this method
    public function getNbResults(): int
    {
        return $this->countResults();
    }

    public function countResults(): int
    {
        return \count($this->getResults());
    }

    public function getLinks(?int $nbLinks = null): array
    {
        return [];
    }

    public function setMaxPageLinks($maxPageLinks): void
    {
    }

    public function getMaxPageLinks()
    {
        return 1;
    }
}
