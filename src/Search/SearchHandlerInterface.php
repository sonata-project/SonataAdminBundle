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

namespace SensioLabs\AdminBundle\Search;

use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Datagrid\PagerInterface;
use SensioLabs\AdminBundle\Datagrid\ProxyQueryInterface;

interface SearchHandlerInterface
{
    /**
     * @throws \RuntimeException
     *
     * @phpstan-template T of object
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-return PagerInterface<ProxyQueryInterface<T>>|null
     */
    public function search(AdminInterface $admin, string $term, int $page = 0, int $offset = 20): ?PagerInterface;
}
