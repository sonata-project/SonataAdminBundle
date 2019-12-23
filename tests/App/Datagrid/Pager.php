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
use Sonata\AdminBundle\Tests\App\Model\FooRepository;

final class Pager implements PagerInterface
{
    private $repository;

    public function __construct(FooRepository $repository)
    {
        $this->repository = $repository;
    }

    public function init()
    {
    }

    public function getMaxPerPage()
    {
    }

    public function setMaxPerPage($max)
    {
    }

    public function setPage($page)
    {
    }

    public function setQuery($query)
    {
    }

    public function getResults(): array
    {
        return $this->repository->all();
    }

    public function setMaxPageLinks($maxPageLinks)
    {
    }

    public function getMaxPageLinks()
    {
    }
}
