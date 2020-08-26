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

namespace Sonata\AdminBundle\Model;

/**
 * A datagrid manager is a bridge between the model classes and the admin datagrid functionality.
 */
interface DatagridManagerInterface
{
    /**
     * @return array{_page?: int, _per_page?: int, _sort_by?: string, _sort_order?: string}
     *
     * @phpstan-param class-string $class
     */
    public function getDefaultSortValues(string $class): array;

    /**
     * Return all the allowed _per_page values.
     *
     * @return int[]
     *
     * @phpstan-param class-string $class
     */
    public function getDefaultPerPageOptions(string $class): array;
}
