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

namespace SensioLabs\AdminBundle\Builder;

use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Datagrid\DatagridInterface;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of \SensioLabs\AdminBundle\Datagrid\ProxyQueryInterface
 */
interface DatagridBuilderInterface
{
    /**
     * Adds missing information to the given field description.
     */
    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void;

    /**
     * @phpstan-param DatagridInterface<T> $datagrid
     * @phpstan-param class-string|null    $type
     */
    public function addFilter(
        DatagridInterface $datagrid,
        ?string $type,
        FieldDescriptionInterface $fieldDescription,
    ): void;

    /**
     * @param AdminInterface<object> $admin
     * @param array<string, mixed>   $values
     *
     * @phpstan-return DatagridInterface<T>
     */
    public function getBaseDatagrid(AdminInterface $admin, array $values = []): DatagridInterface;
}
