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

namespace Sonata\AdminBundle\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
 */
interface DatagridBuilderInterface extends BuilderInterface
{
    /**
     * @phpstan-param DatagridInterface<T> $datagrid
     * @phpstan-param class-string|null    $type
     */
    public function addFilter(
        DatagridInterface $datagrid,
        ?string $type,
        FieldDescriptionInterface $fieldDescription
    ): void;

    /**
     * @param AdminInterface<object> $admin
     * @param array<string, mixed>   $values
     *
     * @phpstan-return DatagridInterface<T>
     */
    public function getBaseDatagrid(AdminInterface $admin, array $values = []): DatagridInterface;
}
