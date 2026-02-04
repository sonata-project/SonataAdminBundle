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

namespace SensioLabs\AdminBundle\Tests\Fixtures\Filter;

use SensioLabs\AdminBundle\Datagrid\ProxyQueryInterface;
use SensioLabs\AdminBundle\Filter\Filter;
use SensioLabs\AdminBundle\Filter\Model\FilterData;

final class BarFilter extends Filter
{
    public function apply(ProxyQueryInterface $query, FilterData $filterData): void
    {
    }

    public function getDefaultOptions(): array
    {
        return ['bar' => 'bar'];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptions(): array
    {
        return ['label' => 'label'];
    }
}
